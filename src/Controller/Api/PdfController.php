<?php

namespace App\Controller\Api;

use App\Entity\Sheet;
use App\Utils\PdfService;
use App\Repository\SheetRepository;
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
    public function generatePdf(PdfService $pdf, SerializerInterface $serializer, Request $request): JsonResponse
    {
        $cache = new FilesystemAdapter();
        $cache->deleteItem('pdf_content');
        $jsonContent = $request->getContent();
        $PdfContent = $cache->get('pdf_content', function (ItemInterface $item) use ($jsonContent) {
            $item->expiresAfter(400);
            return $jsonContent;
        });

        // $cacheDeleted supprime le contenu de la clé du cache
        // $cacheDeleted = $cache->deleteItem('pdf_content');

        // dd($sheet);
        // dd($cacheDeleted);

        // dd($PdfContent);
        $jsonContent = $serializer->deserialize($jsonContent,Sheet::class,'json');

        //On stock le template twig avec le contenu de Request dans $html
        $html = $this->render('api/pdf/fiche.html.twig', [
            'pdfContent' => $jsonContent,
        ]);

        // On envoie le template twig à la methode de DomPdf dans le pdfService
        $pdf->showPdf($html);
        $pdf->showPdf($html);


        //On retourne une confirmation en json
        return $this->json(
            ['confirmation' => 'pdf generé'],
            Response::HTTP_CREATED,
            []
        );
    }
}
