<?php

namespace App\Utils;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generate pdf files
 */
class PdfService
{

    private $domPdf;

    public function __construct()
    {
        $this->domPdf = new Dompdf();

        $pdfOptions = new Options();

        // Pour autoriser l'utilisation de ressources externes( CSS, images) voir si necessaire
        // $pdfOptions->set('isHtml5ParserEnabled', true);

        $this->domPdf->setOptions($pdfOptions);
    }

public function showPdf($html)
{
    $this->domPdf->loadHtml($html);
    $this->domPdf->setPaper('A4', 'portrait');
    $this->domPdf->render();
    $this->domPdf->stream( "fiche_personnage.pdf", [
        'Attachment' => false
    ]);
}

}