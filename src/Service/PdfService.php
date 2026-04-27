<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    private Dompdf $domPdf;

    public function __construct()
    {
        $this->domPdf = new Dompdf();

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);

        $this->domPdf->setOptions($pdfOptions);
    }

    public function generateBinaryPdf(string $html): ?string
    {
        $this->domPdf->loadHtml($html);
        $this->domPdf->render();
        return $this->domPdf->output();
    }
}
