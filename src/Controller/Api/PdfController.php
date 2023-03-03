<?php

namespace App\Controller\Api;

use App\Entity\Sheet;
use App\Utils\CheckSerializer;
use App\Utils\RateLimiterService;
use App\Repository\SheetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PdfController extends AbstractController
{
    /**
     * @Route("/api/generator", name="app_api_pdf", methods="POST")
     */
    public function generatePdf(\Knp\Snappy\Pdf $knpSnappyImage, CheckSerializer $checker, Request $request, RateLimiterService $rateLimiter, ValidatorInterface $validator): PdfResponse
    {
        //$rateLimiter->limit($request);
        // On instencie FilesystemAdapter pour gérer le cache
        $cache = new FilesystemAdapter();

        //On recupere la clé de session pour la concatener à la clé de cache et en faire une clé unique à chaque utilisateur
        $cacheKey = 'pdf_content_' . $request->getSession()->getId();
        // dd($cacheKey);

        // Par précaution on vide le cache de la clé pdf_content
        //$cache->deleteItem($cacheKey);

        $jsonContent = $request->getContent();
        
        // On enregistre dans le cache le contenu de la $jsonContent (Request)
        $cache->get($cacheKey, function (ItemInterface $item) use ($jsonContent) {
            $item->expiresAfter(900);
            return $jsonContent;
        });
        
        // On deserialize $jsonContent (Request) pour l'utiliser dans notre template twig
        //!Avec les valeurs commentées ci dessous ça marche en navigateur
        //!et insomnia alors qu'en dessous ça ne marche que pour insomnia
        //$test = $cache->getItem('pdf_content_')->get('value');
        //$result = $checker->serializeValidation($test, Sheet::class);
        $result = $checker->serializeValidation($jsonContent, Sheet::class);
        
        if (!$result instanceof Sheet) {
            return $this->json(
                ["error" => $result],
                404,
                []
            );
        }

        $errors = $validator->validate($result);

        if (count($errors) > 0) {
            foreach($errors as $error) {
                $errorJson[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json("ok");
        }
        
        //On stock le template twig avec le contenu de jsonContent (Request) dans $html
        $html = $this->renderView('api/pdf/fiche.html.twig', [
            'pdfContent' => $result,
        ]);
      
        // $html = $this->renderView('api/pdf/test.html.twig');
        return new PdfResponse(
            $knpSnappyImage->getOutputFromHtml($html),
            'file.pdf'
        );
    }

    /**
     * Get a saved sheet in pdf
     * @Route("/api/characters/sheet/{id<\d+>}", name="sheets_get_pdf")
     */
    public function getSavedSheet($id, \Knp\Snappy\Pdf $knpSnappyImage, EntityManagerInterface $entityManager, SheetRepository $sheet): PdfResponse
   {
       $sheetContent = $sheet->getSavedSheet($id);

       $html =  $this->render('/api/pdf/saved_sheet.html.twig', [
           'character' => $sheetContent,
       ]);

       return new PdfResponse(
        $knpSnappyImage->getOutputFromHtml($html),
        'file.pdf'
    );

   }

}
