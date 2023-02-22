<?php

namespace App\Controller\Api;

use App\Entity\Sheet;
use App\Service\PdfService;
use App\Repository\SheetRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PdfController extends AbstractController
{
    /**
     * @Route("/api/generator", name="app_api_pdf")
     */
    public function generatePdf(PdfService $pdf, SerializerInterface $serializer, Request $request): JsonResponse
    {
        $jsonContent = $request->getContent();
        $PdfContent = $serializer->deserialize($jsonContent, Sheet::class, "json");

        //On stock le template twig avec le contenu de Request dans $html
        $html = $this->render('api/pdf/fiche.html.twig', [
            'pdfContent' => $PdfContent,
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
