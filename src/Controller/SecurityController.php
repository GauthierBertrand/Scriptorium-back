<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Token;
use App\Form\ResetPasswordType;
use App\Repository\TokenRepository;
use App\Utils\CheckSerializer;
use App\Utils\RateLimiterService;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="app_security_inscription", methods="POST") 
     * User incription with user datas and sending validation mail for token verification
     * 
     * @param Request $request
     * @param CheckSerializer $checker
     * @param ValidatorInterface $validator
     * @param EntityManagerInterface $manager
     * @param MailerInterface $mailer
     * @param TokenGeneratorInterface $tokenGenerator
     * @param RateLimiterService $rateLimiter
     * 
     * @return JsonResponse
     */
    public function inscription(Request $request, CheckSerializer $checker, ValidatorInterface $validator, EntityManagerInterface $manager, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator, RateLimiterService $rateLimiter): JsonResponse
    {
        $rateLimiter->limit($request);

        $userDatas = $request->getContent();

        $result = $checker->serializeValidation($userDatas, User::class);

        if (!$result instanceof User) {
            return $this->json(
                ["error" => $result],
                Response::HTTP_NOT_FOUND,
                []
            );
        }

        $result->setRoles(["ROLE_USER"]);

        $errors = $validator->validate($result);

        if (count($errors) > 0) {
            $errorsJson = [];

            foreach ($errors as $error) {
                $errorsJson[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json(
                ["errors" => $errorsJson],
                Response::HTTP_BAD_REQUEST,
                []
            );
        }

        $token = $tokenGenerator->generateToken();

        $tokenEntity = new Token();
        $tokenEntity->setToken($token)
                    ->setUser($result);

        $manager->persist($tokenEntity);
        $manager->persist($result);
        $manager->flush();


        // generation of the validation link with token sent by email
        $activationLink = $this->generateUrl('app_security_activation', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

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
            Response::HTTP_CREATED,
            []
        );
    }

    /**
     * @Route("/activation/{token}", name="app_security_activation", methods="GET")
     * activation of user account by token verification sent by email
     *      
     * @param string $token
     * @param EntityManagerInterface $manager 
     * 
     * @return JsonResponse
     */
    public function activation($token, EntityManagerInterface $manager): JsonResponse
    {
        $tokenEntity = $manager->getRepository(Token::class)->findOneBy(['token' => $token]);

        if (!$tokenEntity) {
            return $this->json(
                ["erreur" => "Le token d'activation est invalide ou le compte est déja activé"],
                Response::HTTP_BAD_REQUEST,
                []
            );
        }

        $actualUser = $tokenEntity->getUser();
        $actualUser->setIsVerified(true);

        $manager->remove($tokenEntity);
        $manager->flush();

        return $this->json(
            ["confirmation" => "Le compte à bien été activé."],
            Response::HTTP_CREATED,
            []
        );
    }

    /**
     * @Route("/api/resend-activation", name="app_resend_activation_link", methods="GET")
     * Resend activation link for user account validation
     * 
     * @param EntityManagerInterface $manager
     * @param MailerInterface $mailer
     * @param TokenStorageInterface $tokenInterface
     * 
     * @return Response
     */
    public function resendActivation(EntityManagerInterface $manager, MailerInterface $mailer, TokenStorageInterface $tokenInterface, RateLimiterService $rateLimiter, Request $request): Response
    {
        $rateLimiter->limit($request);

        $userToken = $tokenInterface->getToken();

        $user = $userToken->getUser();

        $mailToken = $manager->getRepository(Token::class)->findOneBy(['user' => $user]);

        if ($mailToken == null) {
            return $this->json(
                ["erreur" => "Aucun token pour ce compte"],
                Response::HTTP_NOT_FOUND,
                []
            );
        }

        $activationLink = $this->generateUrl('app_security_activation', ['token' => $mailToken->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);

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
            Response::HTTP_OK,
            []
        );
    }

    /**
     * @Route("/login", name="login", methods={"GET", "POST"})
     * Login Form to easyadmin
     * 
     * @param AuthenticationUtils $authenticationUtils
     * 
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@EasyAdmin/page/login.html.twig', [
            'error' => $error,
            'last_username' => $lastUsername
        ]);
    }

    /**
     * @Route("/reset-password", name="app_reset_password", methods="POST")
     * Route for send a mail to reset user password
     * 
     * @param Request $request
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $manager
     * @param MailerInterface $mailer
     * @param TokenGeneratorInterface $tokenGenerator
     * 
     * @return JsonResponse
     */
    public function ResetMailSend(Request $request, UserRepository $userRepository, EntityManagerInterface $manager, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator): JsonResponse
    {
        $email = $request->getContent();
        $data = json_decode($email, true);
        $email = $data['email'];
        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return $this->json(
                ["erreur" => "Aucun utilisateur avec cette adresse mail"],
                Response::HTTP_NOT_FOUND,
                []
            );
        }

        $token = $tokenGenerator->generateToken();

        $tokenEntity = new Token();
        $tokenEntity->setToken($token)
                    ->setUser($user);

        $manager->persist($tokenEntity);
        $manager->persist($user);
        $manager->flush();

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
            Response::HTTP_CREATED,
            []
        );
    }

    /**
     * @Route("/reset-password/{token}", name="app_reset_password_form", methods={"GET", "POST"})
     * Display the password reset form and reset password from validation email token
     * 
     * @param Request $request
     * @param TokenRepository $tokenRepository
     * @param EntityManagerInterface $manager
     * 
     * @return Response
     */
    public function PasswordUpdate($token, Request $request, TokenRepository $tokenRepository, EntityManagerInterface $manager): Response
    {
        $tokenEntity = $tokenRepository->findOneBy(['token' => $token]);

        $user = $tokenEntity->getUser();

        $form = $this->createForm(ResetPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->remove($tokenEntity);
            
            $manager->flush();

            return $this->redirectToRoute('app_reset_password_success');
        }

        return $this->renderForm('/forms/reset_password.html.twig', [
            'form' => $form,
            'user' => $user
        ]);
    }

    /**
     * @Route("/reset-password-success/", name="app_reset_password_success", methods="GET")
     * Redirection to user login page
     * 
     * @return Response
     */
    public function ResetSuccess(): Response
    {

        return $this->render('/forms/reset_password_success.html.twig');
    }
}
