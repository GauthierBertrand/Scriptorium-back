<?php

namespace App\Controller\Api;

use App\Entity\Sheet;
use App\Utils\CheckSerializer;
use App\Utils\PdfService;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class PdfController extends AbstractController
{
    /**
     * @Route("/api/generator", name="app_api_pdf")
     */
    public function generatePdf(PdfService $pdf, CheckSerializer $checker, Request $request): JsonResponse
    {
        // On instencie FilesystemAdapter pour gérer le cache
        $cache = new FilesystemAdapter();

        //On recupere la clé de session pour la concatener à la clé de cache et en faire une clé unique à chaque utilisateur
        $cacheKey = 'pdf_content_' . $request->getSession()->getId();
        // dd($cacheKey);

        // Par précaution on vide le cache de la clé pdf_content
        $cache->deleteItem($cacheKey);

        $jsonContent = $request->getContent();

        // On enregistre dans le cache le contenu de la $jsonContent (Request)
        $cache->get($cacheKey, function (ItemInterface $item) use ($jsonContent) {
            $item->expiresAfter(900);
            return $jsonContent;
        });

        // On deserialize $jsonContent (Request) pour l'utiliser dans notre template twig
        $result = $checker->serializeValidation($jsonContent, Sheet::class);

        if (!$result instanceof Sheet) {
            return $this->json(
                ["error" => $result],
                404,
                []
            );
        }

        //On stock le template twig avec le contenu de jsonContent (Request) dans $html
        $html = $this->render('api/pdf/fiche.html.twig', [
            'pdfContent' => $result,
        ]);

        // On envoie le template twig à la methode de DomPdf dans le pdfService
        $pdf->showPdf($html);

        //On retourne une confirmation en json
        return $this->json(
            ['confirmation' => 'pdf generé'],
            Response::HTTP_CREATED,
            []
        );
    }
}
