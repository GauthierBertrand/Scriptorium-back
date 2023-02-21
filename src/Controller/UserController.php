<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    /**
     * Permet la modification du mot de passe d'un utilisateur recuperé via son token JWT
     * @Route("/api/user/password", name="app_user_modify_password", methods="PATCH")
     */
    public function modifyPassword(Request $request, TokenStorageInterface $tokenStorage, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator, EntityManagerInterface $manager): JsonResponse
    {
        // Decode de content Request and return an array with keys
        $passwords = json_decode($request->getContent(), true);

        // Recover the user associate to token
        $token = $tokenStorage->getToken();
        $user = $token->getUser();

        // Verify if the plaintext password given by request match with the user's password
        // ! Error doesn't exist, don't worry for this !
        if ($passwordHasher->isPasswordValid($user, $passwords["current_password"])) {
            $user->setPassword($passwords["new_password"]);
            
            $errors = $validator->validate($user);

            if (count($errors) > 0) {
                // dd($error[0]);
                $error = $errors[0];
                $errorJson[$error->getPropertyPath()] = $error->getMessage();

                return $this->json(
                    ["error" => $errorJson],
                    400,
                    []
                );
            }

            // ? On hash de nouveau le password après la verification du mot de passe au clair pour utiliser sans probleme la methode isPasswordValid
            $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));

            $manager->flush();

            return $this->json(
                ["confirmation" => "Password changed"],
                201,
                []);
        }

        return $this->json(
            ["invalidation" => "Invalid Password"],
            403,
            []    
        );

    }
}
