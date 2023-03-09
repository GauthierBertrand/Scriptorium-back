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
     * Get one sheet by its id
     * @param Sheet $sheet
     * 
     * @return JsonResponse
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
     * 
     * @param SheetRepository $sheetRepository
     * @param TokenStorageInterface $tokenInterface
     * 
     * @return JsonResponse
     */
    public function getUserSheets(SheetRepository $sheetRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $token = $tokenStorage->getToken();
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
     * Post a sheet in database from cache
     * 
     * @param Request $request
     * @param CheckSerializer $checker
     * @param ValidatorInterface $validator
     * @param EntityManagerInterface $manager
     * @param TokenStorageInterface $tokenStorage
     * 
     * @return JsonResponse
     */
    public function createSheet(Request $request, CheckSerializer $checker, ValidatorInterface $validator, EntityManagerInterface $manager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $token = $tokenStorage->getToken();
        $user = $token->getUser();

        if (!$user->getIsVerified()) {
            return $this->json(
                ["error" => "L'utilisateur doit avoir un compte valide."],
                Response::HTTP_UNAUTHORIZED,
                []
            );
        }

        $cache = new FilesystemAdapter;
        $cacheKey = 'pdf_content_' . $request->getSession()->getId();

        $dataSheet = $cache->getItem($cacheKey);
        
        if (!$dataSheet->isHit()) {
            return $this->json(
                ['erreur' => 'Le cache est vide'],
                Response::HTTP_BAD_REQUEST,
                []
            );
        }

        $jsonContent = $dataSheet->get('value');
        
        $result = $checker->serializeValidation($jsonContent, Sheet::class);
            
        if (!$result instanceof Sheet) {
            return $this->json(
                ["error" => $result],
                Response::HTTP_NOT_FOUND,
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

        $manager->persist($result);
        $manager->flush();

        $cache->deleteItem($cacheKey);

        return $this->json(
            ['confirmation' => 'Fiche bien ajoutée'],
            Response::HTTP_CREATED,
            []
        );
    }

    /**
     * @Route("/characters/{id<\d+>}", name="sheets_patch_item", methods={"PATCH"})
     * Update a saved user character sheet
     * @param Sheet $sheet
     * @param Request $request
     * @param CheckSerializer $checker
     * @param ValidatorInterface $validator
     * @param EntityManagerInterface $manager
     * 
     * @return JsonResponse
     */
    public function patch(Sheet $sheet = null, Request $request, CheckSerializer $checker, ValidatorInterface $validator, EntityManagerInterface $manager): JsonResponse
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
                Response::HTTP_NOT_FOUND,
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

        $manager->flush();

        return $this->json(
            ['sheet' => $sheet],
            Response::HTTP_OK,
            [],
            ['groups' => 'sheet_get_item']
        );
    }

    /**
     * @Route("/characters/{id<\d+>}", name="sheets_delete_item", methods={"DELETE"})
     * Delete a saved user character sheet
     * 
     * @param Sheet $sheet
     * @param EntityManagerInterface $manager
     * 
     * @return JsonResponse
     */
    public function deleteSheet(Sheet $sheet = null, EntityManagerInterface $manager): JsonResponse
    {
        if (empty($sheet)) {
            return $this->json(
                ['message' => 'Fiche non trouvée'],
                Response::HTTP_NOT_FOUND,
                []
            );
        }

        $this->denyAccessUnlessGranted('POST_EDIT', $sheet);

        $manager->remove($sheet);
        $manager->flush();

        return $this->json(
            ['message' => 'La fiche a bien été supprimée'],
            Response::HTTP_OK,
            [],
            ['groups' => 'sheet_get_item']
        );
    }
}
