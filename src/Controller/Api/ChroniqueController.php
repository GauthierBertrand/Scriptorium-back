<?php

namespace App\Controller\Api;

use App\Repository\ClasseRepository;
use App\Repository\RaceRepository;
use App\Repository\StatRepository;
use App\Repository\WayRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api", name="app_api_")
 * Class wich get all necessary datas for the application 
 */
class ChroniqueController extends AbstractController
{
    //!A revoir avec ajout d'un champ stats 
    /**
     * @Route("/classes", name="classes_get_collection", methods={"GET"})
     * Get all classes for the classes choice page
     */
    public function getClasses(ClasseRepository $classeRepository): JsonResponse
    {
        $classes = $classeRepository->findAll();
        return $this->json(
            ['classes' => $classes],
            Response::HTTP_OK,
            [],
            ['groups'=>'classes_get_collection']
        );
    }

    /**
     * @Route("/races", name="races_get_collection", methods={"GET"})
     * Get all races for rhe races choice page
     */
    public function getRaces(RaceRepository $raceRepository): JsonResponse
    {
        $races = $raceRepository->findAll();
        return $this->json(
            ['races' => $races],
            Response::HTTP_OK,
            [],
            ['groups' => 'races_get_collection']
        );
        
    }

    /**
     * @Route("/ways", name="ways_get_collection", methods={"GET"})
     * Get all ways for the ways selection page
     */
    public function getWays(WayRepository $wayRepository)
    {
        $ways = $wayRepository->findAll();
        return $this->json(
            ['ways'=>$ways],
            Response::HTTP_OK,
            [],
            ['groups'=>'ways_get_collection']
        );
    }


}
