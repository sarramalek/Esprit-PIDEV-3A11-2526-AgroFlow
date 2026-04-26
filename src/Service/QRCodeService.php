<?php

namespace App\Service;

use App\Entity\stocks\Article;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\HttpFoundation\Response;

use Endroid\QrCode\QrCode;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class QRCodeService
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function generateQRCodeResponseForArticle(Article $article): Response
    {
        $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
        
        if (str_contains($host, 'localhost') || str_contains($host, '127.0.0.1')) {
            $qrContent = sprintf(
                "AgroFlow - Details Article\n" .
                "--------------------------\n" .
                "Nom: %s\n" .
                "Prix: %s DT\n" .
                "Stock: %s %s\n" .
                "Categorie: %s",
                $article->getNom(),
                number_format($article->getPrixUnitaire(), 3, ',', ' '),
                $article->getQuantiteEnStock(),
                $article->getUniteMesure(),
                $article->getCategorie() ? $article->getCategorie()->getNom() : 'N/A'
            );
        } else {
            $qrContent = $this->urlGenerator->generate(
                'article_scan_redirect',
                ['id' => $article->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        $qrCode = QrCode::create($qrContent)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
            ->setSize(300)
            ->setMargin(10)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return new Response($result->getString(), 200, ['Content-Type' => $result->getMimeType()]);
    }

    public function generateQRCodeDownloadResponseForArticle(Article $article): Response
    {
        $response = $this->generateQRCodeResponseForArticle($article);
        $response->headers->set('Content-Disposition', 'attachment; filename="qr_code_article_' . $article->getId() . '.png"');
        
        return $response;
    }
}