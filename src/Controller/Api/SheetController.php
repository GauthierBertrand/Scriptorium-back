<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Entity\Sheet;
use App\Repository\SheetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    public function getSheetItem(Sheet $sheet=null): JsonResponse
    {
        if ($sheet === null) {

            return $this->json(['message' => 'Fiche de personnage non trouvée.'], Response::HTTP_NOT_FOUND);
        }
        return $this->json(
            ['sheet'=>$sheet],
            Response::HTTP_OK,
            [],
            ['groups'=>'sheet_get_item']
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
        $sheets = $sheetRepository->findBy(['user'=>$user]);
        if (empty($sheets)){
            return $this->json(['message'=>'Aucune fiche sauvegardée.'], Response::HTTP_NOT_FOUND);
        }
        return $this->json(
            ['sheets'=>$sheets],
            Response::HTTP_OK,
            [],
            ['groups'=>'sheets_get_collection']
        );
    }

    /**
     * @Route("/characters", name="post_sheets_item", methods={"POST"})
     * Post a sheet in database
     */
    public function createSheet(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $entityManager, TokenStorageInterface $tokenInterface): JsonResponse
    {
        $token=$tokenInterface->getToken();
        $user=$token->getUser();

        $jsonContent = $request->getContent();
        
        $sheet = $serializer->deserialize($jsonContent,Sheet::class,'json');
        $sheet->setUser($user);
        $errors = $validator->validate($sheet);
        $errorList=[];
        if(count($errors)>0){
            foreach ($errors as $error){
                $errorList[$error->getPropertyPath()][]=$error->getMessage();
            }
            return $this->json($errorList, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->persist($sheet);
        $entityManager->flush();

        return $this->json(
            ['confirmation'=>'Fiche bien ajoutée'],
            Response::HTTP_CREATED,
            [],
        );   
    }

}
