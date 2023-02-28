<?php

namespace App\Controller;

use App\Entity\User;
use App\Utils\CheckSerializer;
use Doctrine\ORM\EntityManagerInterface;
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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SecurityController extends AbstractController
{
    /**
     * Inscription de l'utilisateur en utilisant les données envoyées au format JSON
     * @Route("/inscription", name="app_security_inscription")
     * @return JsonResponse
     */
    public function inscription(Request $request, CheckSerializer $checker, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $manager, MailerInterface $mail): JsonResponse    {
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

        $manager->persist($result);
        $manager->flush();

        // On génère le lien d'activation avec le token avec la fonction generateUrl
        $activationLink = $this->generateUrl('app_security_activation', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

        //J'injecte mon service d'envoi de mail et j'appel mon objet mail
        $mail->send(
            'webmaster@scriptorium.com',
            $user->getEmail(),
            'Veuillez activer votre compte Scriptorium',
            "validation",
            [
                'user' => $user,
                'link' => $activationLink
            ]
        );

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
     * @Route("/api/resendactivation", name="app_resend_activation_link")
     * @return Response
     */
    public function resendActivation(EntityManagerInterface $manager,MailService $mail, TokenStorageInterface $tokenInterface): Response
    {
        $userToken = $tokenInterface->getToken();
       
        if (!$userToken) {
            return $this->json(
                ["erreur" => "L\utilisateur doit etre connecté"],
                403,
                []
            );  
        }

        $user = $userToken->getUser();

        $mailToken = $manager->getRepository(Token::class)->findOneBy(['user' => $user]);

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
}
