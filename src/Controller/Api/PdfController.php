<?php

namespace App\Controller\Api;

use App\Entity\Sheet;
use App\Utils\PdfService;
use App\Utils\CheckSerializer;
use App\Utils\RateLimiterService;
use App\Repository\SheetRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PdfController extends AbstractController
{
    /**
     * @Route("/api/generator", name="app_api_pdf", methods="POST")
     * Method to generate a pdf file from datas given by an anonymous user and saving datas in cache
     * 
     * @param PdfService $pdf
     * @param CheckSerializer $checker
     * @param Request $request
     * @param RateLimiterService $rateLimiter
     * @param ValidatorInterface $validator
     * 
     * @return JsonResponse|Response
     */
    public function generatePdf(PdfService $pdf, CheckSerializer $checker, RateLimiterService $rateLimiter, Request $request, ValidatorInterface $validator)
    {
        $rateLimiter->limit($request);
        
        $cache = new FilesystemAdapter();
       
        $cache->deleteItem('pdf_content');
        $jsonContent = $request->getContent();

        $cacheKey = 'pdf_content_' . $request->getSession()->getId();
        
        $cache->get($cacheKey, function (ItemInterface $item) use ($jsonContent) {
            $item->expiresAfter(900);
            return $jsonContent;
        });
        
        $result = $checker->serializeValidation($jsonContent, Sheet::class);
        
        if (!$result instanceof Sheet) {
            return $this->json(
                ["error" => $result],
                Response::HTTP_NOT_FOUND,
                []
            );
        }

        $errors = $validator->validate($result);

        if (count($errors) > 0) {
            foreach($errors as $error) {
                $errorJson[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json(
                ["errors" => $errorJson],
                Response::HTTP_BAD_REQUEST,
                []
            );
        }

        $html = $this->render('api/pdf/fiche.html.twig', [
            'pdfContent' => $result,
        ]);

        $output = $pdf->showPdf($html);

        $response = new Response();
        $response->setContent($output);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, Authorization');

        return $response;
    }

    /**
     * @Route("/api/generator/sheet/{id<\d+>}", name="sheets_get_pdf", methods="GET")
     * Get a user saved sheet in pdf
     * 
     * @param int $id,
     * @param PdfService $knpSnappyPdf
     * @param SheetRepository $sheet
     * 
     * @return Response
     */
    public function getSavedSheet($id, PdfService $pdf, SheetRepository $sheet): Response
   {
       $sheetContent = $sheet->getSavedSheet($id);

       $this->denyAccessUnlessGranted("GET_SHEET", $sheetContent);

       $html =  $this->render('/api/pdf/saved_sheet.html.twig', [
           'character' => $sheetContent,
       ]);

       $output = $pdf->showPdf($html);

        $response = new Response();
        $response->setContent($output);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, Authorization');

        return $response;
   }
}
