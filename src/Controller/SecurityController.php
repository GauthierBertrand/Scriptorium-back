<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SecurityController extends AbstractController
{
    /**
     * Inscription de l'utilisateur en utilisant les données envoyées au format JSON
     * @Route("/inscription", name="app_security_inscription")
     * @return JsonResponse
     */
    public function inscription(Request $request, SerializerInterface $serialiser, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $manager): JsonResponse
    {
        $userDatas = $request->getContent();

        $user = $serialiser->deserialize($userDatas, User::class, "json");

        $errors = $validator->validate($user);

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

        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
        
        $manager->persist($user);
        $manager->flush();

        return $this->json(
            ["confirmation" => "Le compte à bien été créé."],
            201,
            []
        );
    }
}
