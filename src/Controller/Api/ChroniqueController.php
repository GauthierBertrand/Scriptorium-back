<?php

namespace App\Controller\Api;

use App\Repository\WayRepository;
use App\Repository\RaceRepository;
use App\Repository\StatRepository;
use App\Repository\ClasseRepository;
use App\Repository\ReligionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @Route("/api", name="app_api_")
 * Class wich get all necessary datas for the application 
 */
class ChroniqueController extends AbstractController
{
    /**
     * @Route("/classes", name="classes_get_collection", methods={"GET"})
     * Get all classes for the classes choice page
     */
    public function getClasses(ClasseRepository $classeRepository): JsonResponse
    {
        $cache = new FilesystemAdapter;
        $dataSheet = $cache->getItem('pdf_content');

        // ->get('value') pour recuperer la valeur du cache
        $pdfContent = $dataSheet->get('value');

        // $cacheDeleted supprime le contenu de la clé du cache
        $cacheDeleted = $cache->deleteItem('pdf_content');

        // dd($pdfContent);
        dd($cacheDeleted);

        $classes = $classeRepository->getClassesAndEquipments();
        return $this->json(
            ['classes' => $classes],
            Response::HTTP_OK,
            []
        );
    }

    /**
     * @Route("/races", name="races_get_collection", methods={"GET"})
     * Get all races for rhe races choice page
     */
    public function getRaces(RaceRepository $raceRepository): JsonResponse
    {
        $races = $raceRepository->getRacesAndRacialAbilities();
        // $races = $raceRepository->findAll();
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
        $ways = $wayRepository->getWaysAndWayAbilities();
        // $ways = $wayRepository->findAll();
        return $this->json(
            ['ways' => $ways],
            Response::HTTP_OK,
            [],
            ['groups' => 'ways_get_collection']
        );
    }

    /**
     * @Route("/stats", name="stats_get_collection", methods={"GET"})
     * Get all stats for the stats selection page
     */
    public function getStats(StatRepository $statRepository)
    {
        $stats = $statRepository->findAll();
        return $this->json(
            ['stats' => $stats],
            Response::HTTP_OK,
            [],
            ['groups' => 'stats_get_collection']
        );
    }

    /**
     * @Route("/religions", name="religions_get_collection", methods={"GET"})
     * Get all religions for the character information page
     */
    public function getReligions(ReligionRepository $religionRepository)
    {
        $religions = $religionRepository->findAll();
        return $this->json(
            ['religions' => $religions],
            Response::HTTP_OK,
            [],
            ['groups' => 'religions_get_collection']
        );
    }
}
