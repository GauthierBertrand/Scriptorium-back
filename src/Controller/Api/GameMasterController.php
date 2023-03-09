<?php

namespace App\Controller\Api;

use App\Entity\Group;
use App\Utils\CheckSerializer;
use App\Repository\UserRepository;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/api/game-master", name="app_api_game_master")
 * Control all gamemaster's features
 */
class GameMasterController extends AbstractController
{
    /**
     * @Route("/users", name="app_api_game_master_upgrade", methods="PATCH")
     * Allows a logged user (USER or ADMIN) to upgrade his account to ROLE_GAME_MASTER
     * 
     * @param EntityManagerInterface $manager
     * @param TokenStorageInterface $tokenStorage
     * 
     * @return JsonResponse
     */
    public function becomeGameMaster(EntityManagerInterface $manager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $user = $tokenStorage->getToken()->getUser();

        if (!$user->getIsVerified()) {
            return $this->json(
                ["error" => "L'utilisateur doit avoir un compte valide."],
                Response::HTTP_UNAUTHORIZED,
                []
            );
        }

        $roles = $user->getRoles();

        if (in_array("ROLE_GAME_MASTER", $roles)) {
            return $this->json(
                ["error" => "L'utilisateur est déjà un GameMaster."],
                Response::HTTP_NOT_ACCEPTABLE,
                []
            );
        }

        $roles[] = "ROLE_GAME_MASTER";

        $user->setRoles($roles);
        $manager->flush();

        return $this->json(
            ["confirmation" => "Le compte à bien été upgrade."],
            Response::HTTP_ACCEPTED,
            []
        );
    }

    /**
     * @Route("/groups", name="app_api_game_master_get", methods="GET")
     * Allows a logged GameMaster to access his groups and players informations
     * 
     * @param TokenStorageInterface $tokenStorage
     * 
     * @return JsonResponse
     */
    public function getGroups(TokenStorageInterface $tokenStorage): JsonResponse
    {
        $user = $tokenStorage->getToken()->getUser();

        $groups = $user->getGroups();
        
        return $this->json(
            ["groups" => $groups],
            Response::HTTP_OK,
            [],
            ["groups" => "group_get_information"]
        );
    }

    /**
     * @Route("/groups", name="app_api_game_master_group", methods="POST")
     * Allows to create a new group and return his register code 
     * 
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param EntityManagerInterface $manager
     * @param TokenStorageInterface $tokenStorage
     * @param CheckSerializer $checker
     * 
     * @return JsonResponse
     */
    public function createGroup(Request $request, ValidatorInterface $validator, EntityManagerInterface $manager, TokenStorageInterface $tokenStorage, CheckSerializer $checker): JsonResponse
    {
        $user = $tokenStorage->getToken()->getUser();

        $jsonContent = $request->getContent();

        // method wich verified some possible errors compared with context to call and return a specific response
        $result = $checker->serializeValidation($jsonContent, Group::class);

        if (!$result instanceof Group) {
            return $this->json(
                ["error", $result],
                Response::HTTP_BAD_REQUEST,
                []
            );
        }

        $result->setGameMaster($user);
        $errors = $validator->validate($result);

        if (count($errors) > 0) {
            foreach($errors as $error) {
                $errorsJson[$error->getPropertyPath()] = $error->getMessage();
            }
            
            return $this->json(
                ["errors" => $errorsJson],
                Response::HTTP_NOT_ACCEPTABLE,
                []
            );
        }

        $manager->persist($result);
        $manager->flush();

        return $this->json(
            ["code" => $result->getCodeRegister()],
            Response::HTTP_CREATED,
            []
        );
    }

    /**
     * @Route("/groups/add", name="app_api_game_master_add_user", methods="POST")
     * Allows a logged user to link his account to an existing group with his register group code
     * 
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @param TokenStorageInterface $tokenStorage
     * @param GroupRepository $groupRepository
     * @param ValidatorInterface $validator
     * 
     * @return JsonResponse
     */
    public function addToGroup(EntityManagerInterface $manager, Request $request, ValidatorInterface $validator, TokenStorageInterface $tokenStorage, GroupRepository $groupRepository): JsonResponse
    {
        $user = $tokenStorage->getToken()->getUser();

        if (!$user->getIsVerified()) {
            return $this->json(
                ["error" => "L'utilisateur doit avoir un compte valide."],
                Response::HTTP_UNAUTHORIZED,
                []
            );
        }

        $jsonContent = json_decode($request->getContent(), true);
  
        if (!isset($jsonContent["code_register"]) OR gettype($jsonContent["code_register"]) !== "string") {
            return $this->json(
                ["error" => "Vous devez indiquer un champ code_register valide."],
                Response::HTTP_BAD_REQUEST,
                []
            );
        }

        $group = $groupRepository->findGroupByToken($jsonContent["code_register"]);

        if (!$group instanceof Group) {
            return $this->json(
                ["error" => $group],
                Response::HTTP_BAD_REQUEST,
                []
            );
        }

        $errors = $validator->validate($group);

        if (count($errors) > 0) {
            foreach($errors as $error) {
                $errorsJson[$error->getPropertyPath()] = $error->getMessage();
            }
            
            return $this->json(
                ["errors" => $errorsJson],
                Response::HTTP_NOT_ACCEPTABLE,
                []
            );
        }

        if ($group->getPlayers()->contains($user)) {
            return $this->json(
                ["error" => "Cet utilisateur fait déjà partie de ce groupe."],
                Response::HTTP_NOT_ACCEPTABLE,
                []
            );
        }

        $group->addPlayer($user);

        $manager->flush();

        return $this->json(
            ["confirmation" => "Le joueur a bien été ajouté au groupe."],
            Response::HTTP_CREATED,
            []
        );
    }

    /**
     * @Route("/groups/{id}/users", name="app_api_gamemaster_delete_user", methods="DELETE")
     * Allows the GameMaster of a specific group to delete a user of the group by user id
     * 
     * @param Request $request
     * @param Group $group
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $manager
     * 
     * @return JsonResponse
     */
    public function deleteUserToGroup(Request $request, Group $group = null, UserRepository $userRepository, EntityManagerInterface $manager): JsonResponse
    {
        if(empty($group)) {
            return $this->json(
                ["error" => "Ce groupe n'existe pas."],
                Response::HTTP_NOT_FOUND,
                []
            );
        }

        $this->denyAccessUnlessGranted("DELETE_BY_GM", $group);

        $jsonContent = json_decode($request->getContent(), true);

        if (!isset($jsonContent["id"]) OR gettype($jsonContent['id']) !== 'integer') {
            return $this->json(
                ["error" => "Vous devez indiquer un champ id valide."]
            );
        }

        $user = $userRepository->find($jsonContent["id"]);

        if (!$user) {
            return $this->json(
                ["error" => "Cet utilisateur n'existe pas."],
                Response::HTTP_BAD_REQUEST,
                []
            );
        }

        if (!$group->getPlayers()->contains($user)) {
            return $this->json(
                ["error" => "Cet utilisateur n'est pas dans ce groupe."],
                Response::HTTP_BAD_REQUEST,
                []
            );
        }

        $group->removePlayer($user);

        $manager->flush();

        return $this->json(
            ["confirmation" => "Le joueur a bien été supprimé du groupe."],
            Response::HTTP_OK,
            []
        );
    }   

    /**
     * @Route("/groups/{id}/delete", name="app_api_group_delete", methods="DELETE")
     * Allows the GameMaster of a specific group to delete it
     * 
     * @param Group $group
     * @param EntityManagerInterface $manager
     * 
     * @return JsonResponse
     */
    public function deleteGroup(Group $group = null, EntityManagerInterface $manager): JsonResponse
    {
        if (empty($group)) {
            return $this->json(
                ["error" => "Ce group n'existe pas!"],
                Response::HTTP_BAD_REQUEST,
                []
            );
        }

        $this->denyAccessUnlessGranted("DELETE_BY_GM", $group);

        $manager->remove($group);
        $manager->flush();

        return $this->json(
            ["confirmation" => "Le group a bien été supprimé"],
            Response::HTTP_OK,
            []
        );
    }
}
    