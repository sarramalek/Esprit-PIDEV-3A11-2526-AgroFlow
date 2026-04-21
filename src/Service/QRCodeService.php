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
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        if (str_contains($host, 'localhost')) {
            $qrContent = sprintf(
                "AgroFlow - Détails Article\n" .
                "--------------------------\n" .
                "Nom: %s\n" .
                "Prix: %s DT\n" .
                "Stock: %s %s\n" .
                "Catégorie: %s",
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

        $result = $this->builder
            ->data($qrContent)
            ->encoding(new Encoding('UTF-8'))
            ->size(300)
            ->margin(10)
            ->labelFont(new NotoSans(12))
            ->labelAlignment(LabelAlignment::Center)
            ->build();

        return new Response($result->getString(), 200, ['Content-Type' => $result->getMimeType()]);
    }

    public function generateQRCodeDownloadResponseForArticle(Article $article): Response
    {
        $response = $this->generateQRCodeResponseForArticle($article);
        $response->headers->set('Content-Disposition', 'attachment; filename="qr_code_article_' . $article->getId() . '.png"');
        
        return $response;
    }
}
