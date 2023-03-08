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
     * @var FilesystemAdapter $cache
     */
    private $cache;

    public function __construct()
    {
        $this->cache = new FilesystemAdapter();
    }

    /**
     * @Route("/classes", name="classes_get_collection", methods={"GET"})
     * Get all classes for the classes choice page with cache gestion
     * 
     * @param ClasseRepository $classeRepository
     * 
     * @return JsonResponse
     */
    public function getClasses(ClasseRepository $classeRepository): JsonResponse
    {
        $classes = $this->cache->get("classes", function(ItemInterface $item) use ($classeRepository) {
            $item->expiresAfter(3600);

            return $classeRepository->getClassesAndEquipments();
        });
        return $this->json(
            ['classes' => $classes],
            Response::HTTP_OK,
            []
        );
    }

    /**
     * @Route("/races", name="races_get_collection", methods={"GET"})
     * Get all races for rhe races choice page with cache gestion
     * 
     * @param RaceRepository $raceRepository
     * 
     * @return JsonResponse
     */
    public function getRaces(RaceRepository $raceRepository): JsonResponse
    {
        $races = $this->cache->get("races", function(ItemInterface $item) use ($raceRepository) {
            $item->expiresAfter(3600);

            return $raceRepository->getRacesAndRacialAbilities();
        });
        
        return $this->json(
            ['races' => $races],
            Response::HTTP_OK,
            [],
            ['groups' => 'races_get_collection']
        );
    }

    /**
     * @Route("/ways/{id}", name="ways_get_collection", methods={"GET"})
     * Get all ways for the ways selection page by classe's id
     * 
     * @param int $id
     * @param WayRepository $wayRepository
     * 
     * @return JsonResponse 
     */
    public function getWays($id, WayRepository $wayRepository): JsonResponse
    {
        $ways = $wayRepository->getWaysAndWayAbilities($id);

        return $this->json(
            ['ways' => $ways],
            Response::HTTP_OK,
            [],
            ['groups' => 'ways_get_collection']
        );
    }

    /**
     * @Route("/stats", name="stats_get_collection", methods={"GET"})
     * Get all stats for the stats selection page with cache gestion
     * 
     * @param StatRepository $statRepository
     * 
     * @return JsonResponse
     */
    public function getStats(StatRepository $statRepository): JsonResponse
    {
        $stats = $this->cache->get("stats", function(ItemInterface $item) use ($statRepository) {
            $item->expiresAfter(3600);

            return $statRepository->findAll();
        });

        return $this->json(
            ['stats' => $stats],
            Response::HTTP_OK,
            [],
            ['groups' => 'stats_get_collection']
        );
    }

    /**
     * @Route("/religions", name="religions_get_collection", methods={"GET"})
     * Get all religions for the character information page with cache gestion
     * 
     * @param ReligionRepository $religionRepository
     * 
     * @return JsonResponse
     */
    public function getReligions(ReligionRepository $religionRepository): JsonResponse
    {
        $religions = $this->cache->get("religions", function(ItemInterface $item) use ($religionRepository) {
            $item->expiresAfter(3600);

            return $religionRepository->findAll();
        });

        return $this->json(
            ['religions' => $religions],
            Response::HTTP_OK,
            [],
            ['groups' => 'religions_get_collection']
        );
    }
}
