<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class PdfService
{
    private $twig;
    private $domPdf;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
        $this->domPdf = new Dompdf();

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);

        $this->domPdf->setOptions($pdfOptions);
    }

    public function generateBinaryPdf($html)
    {
        $this->domPdf->loadHtml($html);
        $this->domPdf->render();
        return $this->domPdf->output();
    }
}
