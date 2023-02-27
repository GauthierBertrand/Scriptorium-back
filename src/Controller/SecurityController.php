<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Token;
use App\Utils\MailService;
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

class SecurityController extends AbstractController
{
    /**
     * Inscription de l'utilisateur en utilisant les données envoyées au format JSON
     * @Route("/inscription", name="app_security_inscription")
     * @return JsonResponse
     */
    public function inscription(Request $request, SerializerInterface $serialiser, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $manager, MailerInterface $mailer, MailService $mail): JsonResponse
    {
        $userDatas = $request->getContent();

        $user = $serialiser->deserialize($userDatas, User::class, "json");

        $errors = $validator->validate($user);

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

        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));

        // On génère un token aléatoire de 32 caractères
        $token = bin2hex(random_bytes(16));

        // On créé une nouvelle instance de l'entité Token et on la lie à l'utilisateur
        $tokenEntity = new Token();
        $tokenEntity->setToken($token);
        $tokenEntity->setUser($user);

        $manager->persist($user);
        $manager->persist($tokenEntity);
        $manager->flush();

        // Générer le lien d'activation avec le token avec la fonction generateUrl
        $activationLink = $this->generateUrl('app_security_activation', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

        //J'injecte mon service d'envoi de mail et j'appel mon objet mail
        $mail->send(
            'webmaster@scriptorium.com',
            $user->getEmail(),
            'Veuillez activer votre compte Scriptorium',
            "Bonjour,<br><br>Veuillez cliquer sur le lien suivant pour activer votre compte : <a href=\"$activationLink\">$activationLink</a><br><br>Cordialement,<br>L'équipe du Scriptorium",
            [
                'user' => $user,
                'token' => $token
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
     * @Route("/activation/{token}", name="app_security_activation")
     * @return Response
     */
    public function activation($token, EntityManagerInterface $manager): Response
    {
        $tokenEntity = $manager->getRepository(Token::class)->findOneBy(['token' => $token]);

        if (!$tokenEntity) {
            return new Response("Le token d'activation est invalide.", 400);
        }

        $user = $tokenEntity->getUser();
        $user->setIsVerified(true);

        //On supprime le token de la base de données
        $manager->remove($tokenEntity);
        $manager->flush();

        return new Response("Le compte a bien été activé.", 200);
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
