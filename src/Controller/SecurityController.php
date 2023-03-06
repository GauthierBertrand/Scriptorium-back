<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Token;
use App\Form\ResetPasswordType;
use App\Repository\TokenRepository;
use App\Utils\CheckSerializer;
use Doctrine\ORM\EntityManager;
use App\Utils\RateLimiterService;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\DependencyInjection\ResettableServicePass;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SecurityController extends AbstractController
{
    /**
     * Inscription de l'utilisateur en utilisant les données envoyées au format JSON
     * @Route("/inscription", name="app_security_inscription", methods="POST")
     * @return JsonResponse
     */
    public function inscription(Request $request, CheckSerializer $checker, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $manager, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator, RateLimiterService $rateLimiter): JsonResponse
    {
        $rateLimiter->limit($request);

        $userDatas = $request->getContent();

        $result = $checker->serializeValidation($userDatas, User::class);

        if (!$result instanceof User) {
            return $this->json(
                ["error" => $result],
                404,
                []
            );
        }

        $errors = $validator->validate($result);

        if (count($errors) > 0) {
            $errorsJson = [];

            foreach ($errors as $error) {
                $errorsJson[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json(
                ["errors" => $errorsJson],
                400,
                []
            );
        }

        $result->setPassword($passwordHasher->hashPassword($result, $result->getPassword()));

        // On génère un token
        $token = $tokenGenerator->generateToken();

        // On créé une nouvelle instance de l'entité Token et on la lie à l'utilisateur
        $tokenEntity = new Token();
        $tokenEntity->setToken($token);
        $tokenEntity->setUser($result);

        $manager->persist($tokenEntity);
        $manager->persist($result);
        $manager->flush();


        // On génère le lien d'activation avec le token avec la fonction generateUrl
        $activationLink = $this->generateUrl('app_security_activation', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

        //J'injecte mon service d'envoi de mail et j'appel mon objet mail
        $email = (new TemplatedEmail())
            ->from('webmaster@scriptorium.com')
            ->to($result->getEmail())
            ->subject('Veuillez activer votre compte Scriptorium')
            ->htmlTemplate("api/mail/validation.html.twig")
            ->context([
                'user' => $result,
                'link' => $activationLink
            ]);

        $mailer->send($email);

        return $this->json(
            ["confirmation" => "Le compte à bien été créé et un email de validation envoyé."],
            201,
            []
        );
    }

    /**
     * Activation du compte de l'utilisateur en utilisant le token envoyé par email
     * @Route("/activation/{token}", name="app_security_activation", methods="GET")
     * @return Response
     */
    public function activation($token, EntityManagerInterface $manager): Response
    {
        //On cherche dans la BDD le token similaire à celui recupéré dans le lien
        $tokenEntity = $manager->getRepository(Token::class)->findOneBy(['token' => $token]);

        //Si aucun token ne correspond alors
        if (!$tokenEntity) {
            return $this->json(
                ["erreur" => "Le token d'activation est invalide ou le compte est déja activé"],
                400,
                []
            );
        }

        //Si le token correspond on recup le user et on passe isVerified à true
        $actualUser = $tokenEntity->getUser();
        $actualUser->setIsVerified(true);

        //On supprime ensuite le token de la base de données
        $manager->remove($tokenEntity);
        $manager->flush();

        return $this->json(
            ["confirmation" => "Le compte à bien été activé."],
            201,
            []
        );
    }

    /**
     * Renvoi du lien d'activation du compte
     * @Route("/api/resend-activation", name="app_resend_activation_link", methods="GET")
     * @return Response
     */
    public function resendActivation(EntityManagerInterface $manager, MailerInterface $mailer, TokenStorageInterface $tokenInterface): Response
    {
        $userToken = $tokenInterface->getToken();

        $user = $userToken->getUser();

        $mailToken = $manager->getRepository(Token::class)->findOneBy(['user' => $user]);

        if ($mailToken == null) {
            return $this->json(
                ["erreur" => "Aucun token pour ce compte"],
                404,
                []
            );
        }

        //On recréé un lien avec le token de l'utilisateur
        $activationLink = $this->generateUrl('app_security_activation', ['token' => $mailToken->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);

        //On lui renvoi le mail
        $email = (new TemplatedEmail())
            ->from('webmaster@scriptorium.com')
            ->to($mailToken->getUser()->getEmail())
            ->subject('Veuillez activer votre compte Scriptorium')
            ->htmlTemplate("api/mail/validation.html.twig")
            ->context([
                'user' => $user,
                'token' => $mailToken->getToken(),
                'link' => $activationLink

            ]);

        $mailer->send($email);

        return $this->json(
            ["success" => "Le mail de validation à bien été renvoyé"],
            201,
            []
        );
    }

    /**
     * @Route("/login", name="login", methods={"GET", "POST"})
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@EasyAdmin/page/login.html.twig', [
            // parameters usually defined in Symfony login forms
            'error' => $error,
            'last_username' => $lastUsername
        ]);
    }

    /**
     * @Route("/reset-password", name="app_reset_password", methods="POST")
     */
    public function ResetMailSend(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator): Response
    {
        $email = $request->getContent();
        $data = json_decode($email, true);
        $email = $data['email'];
        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return $this->json(
                ["erreur" => "Aucun utilisateur avec cette adresse mail"],
                403,
                []
            );
        }

        $token = $tokenGenerator->generateToken();

        $tokenEntity = new Token();
        $tokenEntity->setToken($token);
        $tokenEntity->setUser($user);

        $entityManager->persist($tokenEntity);
        $entityManager->persist($user);
        $entityManager->flush();

        $activationLink = $this->generateUrl('app_reset_password_form', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new TemplatedEmail())
            ->from('webmaster@scriptorium.com')
            ->to($email)
            ->subject('Scriptorium - Reinitialisez votre mot de passe')
            ->htmlTemplate("api/mail/reinitialisation.html.twig")
            ->context([
                'user' => $user,
                'token' => $token,
                'link' => $activationLink

            ]);

        $mailer->send($email);

        return $this->json(
            ["success" => "Le mail de réinitialisation à bien été renvoyé"],
            201,
            []
        );
    }


    /**
     * @Route("/reset-password/{token}", name="app_reset_password_form", methods={"GET", "POST"})
     */
    public function PasswordUpdate(Request $request, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository, TokenRepository $tokenRepository, EntityManagerInterface $manager)
    {
        $token = $request->get('token');
        $email = $request->getContent();
        $data = json_decode($email, true);
        $tokenEntity = $tokenRepository->findOneBy(['token' => $token]);
        $user = $tokenRepository->findOneBy(['token' => $token])->getUser();

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('password')->getData() !== null) {
                // avant de sauvegarder, on hash le mot de passe
                $hashedPassword = $passwordHasher->hashPassword($user, $form->get('password')->getData());

                // on le met à jour dans le User
                $user->setPassword($hashedPassword);
                $manager->remove($tokenEntity);
            }
            // sinon, le mot passe d'origine est conservé
            $userRepository->add($user, true);

            return $this->redirectToRoute('app_reset_password_success');
        }
        return $this->renderForm('/forms/reset_password.html.twig', [
            'form' => $form,
            'user' => $user
        ]);
    }

    /**
     * @Route("/reset-password-success/", name="app_reset_password_success", methods="GET")
     */
    public function ResetSuccess()
    {
        //! faire un return vers une la page de connexion front dans le twig ci dessous lorsque les serveurs seront deployés

        return $this->render('/forms/reset_password_success.html.twig');
    }
}
