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

        $error = $validator->validate($user);

        if (count($error) > 0) {
            return $this->json("Bad");
        }
        // dd($user);
        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
        // dd($user);
        
        $manager->persist($user);
        $manager->flush();

        return $this->json([
            "good"
        ]);
    }
}
