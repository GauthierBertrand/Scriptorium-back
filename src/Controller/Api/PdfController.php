<?php

namespace App\Controller\Api;

use App\Entity\Sheet;
use App\Utils\CheckSerializer;
use App\Utils\RateLimiterService;
use App\Repository\SheetRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PdfController extends AbstractController
{
    /**
     * @Route("/api/generator", name="app_api_pdf", methods="POST")
     * Method to generate a pdf file from datas given by an anonymous user and saving datas in cache
     * 
     * @param \Knp\Snappy\Pdf $knpSnappyPdf
     * @param CheckSerializer $checker
     * @param Request $request
     * @param RateLimiterService $rateLimiter
     * @param ValidatorInterface $validator
     * 
     * @return JsonResponse|PdfResponse
     */
    public function generatePdf(\Knp\Snappy\Pdf $knpSnappyPdf, CheckSerializer $checker, Request $request, RateLimiterService $rateLimiter, ValidatorInterface $validator)
    {
        $rateLimiter->limit($request);

        $cache = new FilesystemAdapter();

        //Create a unique key with the user session id
        $cacheKey = 'pdf_content_' . $request->getSession()->getId();

        $jsonContent = $request->getContent();
        
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
        
        $html = $this->renderView('api/pdf/fiche.html.twig', [
            'pdfContent' => $result,
        ]);
      
        return new PdfResponse(
            $knpSnappyPdf->getOutputFromHtml($html),
            'file.pdf'
        );
    }

    /**
     * @Route("/api/generator/sheet/{id<\d+>}", name="sheets_get_pdf", methods="GET")
     * Get a user saved sheet in pdf
     * 
     * @param int $id,
     * @param \Knp\Snappy\Pdf $knpSnappyPdf
     * @param SheetRepository $sheet
     * 
     * @return PdfResponse
     */
    public function getSavedSheet($id, \Knp\Snappy\Pdf $knpSnappyPdf, SheetRepository $sheet): PdfResponse
   {
       $sheetContent = $sheet->getSavedSheet($id);

       $this->denyAccessUnlessGranted("GET_SHEET", $sheetContent);

       $html =  $this->render('/api/pdf/saved_sheet.html.twig', [
           'character' => $sheetContent,
       ]);

       return new PdfResponse(
        $knpSnappyPdf->getOutputFromHtml($html->getContent()),
        'file.pdf'
    );
   }
}
