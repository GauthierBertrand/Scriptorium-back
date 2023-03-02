<?php

namespace App\Controller\Api;

use App\Entity\Sheet;
use App\Repository\SheetRepository;
use App\Utils\CheckSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/api", name="app_api_")
 * Class wich manage sheets datas
 */
class SheetController extends AbstractController
{
    /**
     * @Route("/characters/{id<\d+>}", name="sheets_get_item", methods={"GET"})
     * Get one sheet by id
     */
    public function getSheetItem(Sheet $sheet = null): JsonResponse
    {
        if (empty($sheet)) {
            return $this->json(['message' => 'Fiche de personnage non trouvée.'], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted('GET_SHEET', $sheet);
        return $this->json(
            ['sheet' => $sheet],
            Response::HTTP_OK,
            [],
            ['groups' => 'sheet_get_item']
        );
    }

    /**
     * @Route("/characters/users", name="sheets_get_collection", methods={"GET"})
     * Get all sheets by user id
     */
    public function getUserSheets(SheetRepository $sheetRepository, TokenStorageInterface $tokenInterface): JsonResponse
    {
        $token = $tokenInterface->getToken();
        $user = $token->getUser();
        $sheets = $sheetRepository->getSheetsByUser($user);

        if (empty($sheets)) {
            return $this->json(
                ['message' => 'Aucune fiche sauvegardée.'],
                Response::HTTP_NOT_FOUND,
                []
            );
        }

        return $this->json(
            ['sheets' => $sheets],
            Response::HTTP_OK,
            [],
            ['groups' => 'sheets_get_collection']
        );
    }

    /**
     * @Route("/characters", name="post_sheets_item", methods={"POST"})
     * Post a sheet in database
     */
    public function createSheet(Request $request, CheckSerializer $checker, ValidatorInterface $validator, EntityManagerInterface $entityManager, TokenStorageInterface $tokenInterface): JsonResponse
    {
        $token = $tokenInterface->getToken();
        $user = $token->getUser();

        // On récupère le contenu du cache généré via la route api/generator grace à la clé pdf_content+id de session de l'utilisateur
        $cache = new FilesystemAdapter;
        $cacheKey = 'pdf_content_' . $request->getSession()->getId();
        // dd($cacheKey);

        $dataSheet = $cache->getItem($cacheKey);

        // On verifie si le cache n'est pas vide (on renvoie une erreur 400 s'il est vide)
        if (!$dataSheet->isHit()) {
            return $this->json(
                ['erreur' => 'Le cache est vide'],
                Response::HTTP_BAD_REQUEST,
                []
            );
        }

        // ->get('value') pour recuperer la valeur du cache
        $jsonContent = $dataSheet->get('value');
        
        // On deserialise le contenu du cache
        $result = $checker->serializeValidation($jsonContent, Sheet::class);
            
        if (!$result instanceof Sheet) {
            return $this->json(
                ["error" => $result],
                404,
                []
            );
        }
        
        $result->setUser($user);

        $errors = $validator->validate($result);
        $errorList = [];

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $errorList[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(["errors" => $errorList], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->persist($result);
        $entityManager->flush();

        // On vide le cache après l'envoi à la BDD
        $cache->deleteItem($cacheKey);

        return $this->json(
            ['confirmation' => 'Fiche bien ajoutée'],
            Response::HTTP_CREATED,
            []
        );
    }

    /**
     * Update character sheet
     * @Route("/characters/{id<\d+>}", name="sheets_patch_item", methods={"PATCH"})
     */
    public function patch(Sheet $sheet = null, Request $request, CheckSerializer $checker, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {
        if (empty($sheet)) {
            return $this->json(
                ['message' => 'Fiche non trouvée'],
                Response::HTTP_NOT_FOUND,
                []
            );
        }

        $this->denyAccessUnlessGranted('POST_EDIT', $sheet);
        $jsonContent = $request->getContent();

        $result = $checker->serializeValidation($jsonContent, Sheet::class, [AbstractNormalizer::OBJECT_TO_POPULATE => $sheet]);
            
        if (!$result instanceof Sheet) {
            return $this->json(
                ["error" => $result],
                404,
                []
            );
        }

        $errors = $validator->validate($sheet);

        if (count($errors) > 0) {
            foreach($errors as $error) {
                $errorJson[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json(["errors" => $errorJson], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->flush();

        return $this->json(
            ['sheet' => $sheet],
            Response::HTTP_OK,
            [],
            ['groups' => 'sheet_get_item']
        );
    }

    /**
     * Delete a sheet
     * @Route("/characters/{id<\d+>}", name="sheets_delete_item", methods={"DELETE"})
     */
    public function deleteSheet(Sheet $sheet = null, Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        if (empty($sheet)) {
            return $this->json(
                ['message' => 'Fiche non trouvée'],
                Response::HTTP_NOT_FOUND,
                []
            );
        }

        $this->denyAccessUnlessGranted('POST_EDIT', $sheet);

        $entityManager->remove($sheet);
        $entityManager->flush();

        return $this->json(
            ['message' => 'La fiche a bien été supprimée'],
            Response::HTTP_OK,
            [],
            ['groups' => 'sheet_get_item']
        );
    }
}
