<?php

namespace App\Controller\Api;

use App\Utils\RateLimiterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/api/users")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/password", name="app_user_modify_password", methods="PATCH")
     * Changing user password from token
     * 
     * @param Request $request
     * @param TokenStorageInterface $tokenStorage
     * @param UserPasswordHasherInterface $passwordHasher
     * @param ValidatorInterface $validtor
     * @param EntityManagerInterface $manager
     * @param RateLimiterService $rateLimiter
     * 
     * @return JsonResponse
     */
    public function modifyPassword(Request $request, TokenStorageInterface $tokenStorage, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator, EntityManagerInterface $manager, RateLimiterService $rateLimiter): JsonResponse
    {
        $rateLimiter->limit($request);

        $passwords = json_decode($request->getContent(), true);

        if (!isset($passwords["current_password"]) or !isset($passwords["new_password"])) {
            return $this->json(
                ['error' => "Vous devez indiquer un champ 'current_password' et un champ 'new_password' dans votre requête."],
                Response::HTTP_BAD_REQUEST,
                []
            );
        }

        $token = $tokenStorage->getToken();
        $user = $token->getUser();

        // Verify if the plaintext password given by request match with the user's password
        if ($passwordHasher->isPasswordValid($user, $passwords["current_password"])) {
            $user->setPassword($passwords["new_password"]);

            $errors = $validator->validate($user);

            if (count($errors) > 0) {
                $error = $errors[0];
                $errorJson[$error->getPropertyPath()] = $error->getMessage();

                return $this->json(
                    ["error" => $errorJson],
                    Response::HTTP_BAD_REQUEST,
                    []
                );
            }

            $manager->flush();

            return $this->json(
                ["confirmation" => "Mot de passe modifié"],
                Response::HTTP_CREATED,
                []
            );
        }

        return $this->json(
            ["invalidation" => "Mot de passe invalide"],
            Response::HTTP_FORBIDDEN,
            []
        );
    }

    /**
     * @Route("/delete", name="app_user_delete", methods="DELETE")
     * Delete user from auhtentification token
     * 
     * @param TokenStorageInterface $tokenStorage
     * @param EntityManagerInterface $manager
     * 
     * @return JsonResponse
     */
    public function deleteUser(TokenStorageInterface $tokenStorage, EntityManagerInterface $manager): JsonResponse
    {
        $token = $tokenStorage->getToken();
        $user = $token->getUser();

        $manager->remove($user);
        $manager->flush();

        return $this->json(
            ["confirmation" => "Utilisateur supprimé"],
            Response::HTTP_OK,
            []
        );
    }
}
