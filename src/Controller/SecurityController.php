<?php

namespace App\Controller;

use App\Entity\User;
use App\Utils\CheckSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
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
    public function inscription(Request $request, CheckSerializer $checker, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $manager): JsonResponse
    {
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

            foreach($errors as $error) {
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

        return $this->json(
            ["confirmation" => "Le compte à bien été créé."],
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
