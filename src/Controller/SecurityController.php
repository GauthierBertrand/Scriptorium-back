<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Token;
use App\Utils\MailService;
use App\Utils\CheckSerializer;
use Doctrine\ORM\EntityManager;
use App\Utils\RateLimiterService;
use Symfony\Component\Mime\Email;
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
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SecurityController extends AbstractController
{
    /**
     * Inscription de l'utilisateur en utilisant les données envoyées au format JSON
     * @Route("/inscription", name="app_security_inscription")
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
     * @Route("/api/activation/{token}", name="app_security_activation")
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

        $this->denyAccessUnlessGranted('ACCOUNT_VALIDATION', $tokenEntity);

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
     * @Route("/api/resend-activation", name="app_resend_activation_link")
     * @return Response
     */
    public function resendActivation(EntityManagerInterface $manager, MailService $mail, MailerInterface $mailer, TokenStorageInterface $tokenInterface): Response
    {
        $userToken = $tokenInterface->getToken();

        $user = $userToken->getUser();

        $mailToken = $manager->getRepository(Token::class)->findOneBy(['user' => $user]);

        if($mailToken == null){
            return $this->json(
                ["erreur" => "Aucun token pour ce compte"],
                404,
                []
            );
            }

        //On recréé un lien avec le token de l'utilisateur
        $activationLink = $this->generateUrl('app_security_activation', ['token' => $mailToken->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);

        //On lui renvoi le mail
        $mail->send(
            'webmaster@scriptorium.com',
            $mailToken->getUser()->getEmail(),
            'Veuillez activer votre compte Scriptorium',
            "validation",
            [
                'user' => $user,
                'token' => $mailToken->getToken(),
                'link' => $activationLink

            ]
        );

        return $this->json(
            ["success" => "Le mail de validation à bien été renvoyé"],
            201,
            []
        );
    }

    /**
     * @Route("/login", name="login")
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
     * @Route("api/reset-password", name="app_reset_password")
     */
    public function request(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager, MailService $mail, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator): Response
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

        $token = bin2hex(random_bytes(16));

        $tokenEntity = new Token();
        $tokenEntity->setToken($token);
        $tokenEntity->setUser($user);
        // dd($tokenEntity);

        $entityManager->persist($tokenEntity);
        $entityManager->persist($user);
        $entityManager->flush();
        // dd($tokenEntity);

        $activationLink = $this->generateUrl('app_security_activation', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

        $mail->send(
            'webmaster@scriptorium.com',
            $email,
            'Scriptorium - Reinitialisez votre mot de passe',
            "reinitialisation",
            [
                'user' => $user,
                'token' => $token,
                'link' => $activationLink

            ]
        );

        return $this->json(
            ["success" => "Le mail de réinitialisation à bien été renvoyé"],
            201,
            []
        );
    }
}
