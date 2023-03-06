<?php

namespace App\Controller\Api;

use App\Entity\Group;
use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use App\Utils\CheckSerializer;
use App\Utils\GenerateRandomCode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/game-master", name="app_api_game_master")
 */
class GameMasterController extends AbstractController
{
    /**
     * @Route("/users", name="app_api_game_master_upgrade", methods="PATCH")
     */
    public function becomeGameMaster(EntityManagerInterface $manager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $user = $tokenStorage->getToken()->getUser();

        $roles = $user->getRoles();

        if (in_array("ROLE_GAME_MASTER", $roles)) {
            return $this->json(
                ["error" => "L'utilisateur est déjà un GameMaster."],
                400,
                []
            );
        }

        $roles[] = "ROLE_GAME_MASTER";

        $user->setRoles($roles);
        $manager->flush();

        return $this->json(
            ["confirmation" => "Le compte à bien été upgrade."],
            200,
            []
        );
    }

    /**
     * @Route("/groups", name="app_api_game_master_get", methods="GET")
     */
    public function getGroups(TokenStorageInterface $tokenStorage) 
    {
        $user = $tokenStorage->getToken()->getUser();

        $groups = $user->getGroups();
        
        return $this->json(
            ["groups" => $groups],
            200,
            [],
            ["groups" => "group_get_information"]
        );
    }

    /**
     * @Route("/groups", name="app_api_game_master_group", methods="POST")
     */
    public function createGroup(Request $request, GenerateRandomCode $generator, ValidatorInterface $validator, EntityManagerInterface $manager, TokenStorageInterface $tokenStorage, CheckSerializer $checker, GroupRepository $groupRepository)
    {
        $user = $tokenStorage->getToken()->getUser();

        $jsonContent = $request->getContent();

        $result = $checker->serializeValidation($jsonContent, Group::class);

        if (!$result instanceof Group) {
            return $this->json(
                ["error", $result],
                404,
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
                404,
                []
            );
        }

        $manager->persist($result);
        $manager->flush();

        return $this->json(
            ["code" => $result->getCodeRegister()],
            201,
            []
        );
    }

    /**
     * @Route("/groups/add", name="app_api_game_master_add_user", methods="POST")
     */
    public function addToGroup(EntityManagerInterface $manager, Request $request, TokenStorageInterface $tokenStorage, GroupRepository $groupRepository) 
    {
        $user = $tokenStorage->getToken()->getUser();
        $jsonContent = json_decode($request->getContent(), true);
  
        if (!isset($jsonContent["code_register"]) OR gettype($jsonContent["code_register"]) !== "string") {
            return $this->json(
                ["error" => "Vous devez indiquer un champ code_register valide."],
                404,
                []
            );
        }

        $group = $groupRepository->findGroupByToken($jsonContent["code_register"]);

        if (!$group instanceof Group) {
            return $this->json(
                ["error" => $group],
                404,
                []
            );
        }

        $this->denyAccessUnlessGranted("ADD_PLAYER", $group);

        $group->addPlayer($user);

        $manager->flush();

        return $this->json(
            ["confirmation" => "Le joueur a bien été ajouté au groupe."],
            201,
            []
        );
    }

    /**
     * @Route("/groups/{id}/users", name="app_api_gamemaster_delete_user", methods="DELETE")
     */
    public function deleteUserToGroup(Request $request, Group $group = null, UserRepository $userRepository, EntityManagerInterface $manager) 
    {
        if(empty($group)) {
            return $this->json(
                ["error" => "Ce groupe n'existe pas."],
                404,
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
                404,
                []
            );
        }

        if (!$group->getPlayers()->contains($user)) {
            return $this->json(
                ["error" => "Cet utilisateur n'est pas dans ce groupe."],
                404,
                []
            );
        }

        $group->removePlayer($user);

        $manager->flush();

        return $this->json(
            ["confirmation" => "Le joueur a bien été supprimé du groupe."],
            200,
            []
        );
    }   

    /**
     * @Route("/groups/{id}/delete", name="app_api_group_delete", methods="DELETE")
     */
    public function deleteGroup(Group $group = null, EntityManagerInterface $manager) 
    {
        if (empty($group)) {
            return $this->json(
                ["error" => "Ce group n'existe pas!"],
                404,
                []
            );
        }

        $this->denyAccessUnlessGranted("DELETE_BY_GM", $group);

        $manager->remove($group);
        $manager->flush();

        return $this->json(
            ["confirmation" => "Le group a bien été supprimé"],
            200,
            []
        );
    }
}
    