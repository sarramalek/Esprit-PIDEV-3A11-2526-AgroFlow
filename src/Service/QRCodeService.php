<?php

namespace App\Service;

use App\Entity\stocks\Article;
use Symfony\Component\HttpFoundation\Response;

class QRCodeService
{
    public function generateQRCodeForArticle(Article $article): string
    {
        // Créer les données pour le QR code
        $qrData = [
            'id' => $article->getId(),
            'nom' => $article->getNom(),
            'categorie' => $article->getCategorie()?->getNom() ?? '',
            'prix' => $article->getPrixUnitaire(),
            'quantite' => $article->getQuantiteEnStock(),
            'unite' => $article->getUniteMesure() ?? '',
            'date_generation' => (new \DateTime())->format('Y-m-d H:i:s'),
            'agroflow' => true
        ];

        $jsonData = json_encode($qrData, JSON_UNESCAPED_UNICODE);

        // Utiliser une API externe pour générer le QR code (temporaire)
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($jsonData);
        
        // Pour l'instant, retourner les données JSON
        return $jsonData;
    }

    public function generateQRCodeResponseForArticle(Article $article): Response
    {
        $qrData = $this->generateQRCodeForArticle($article);
        
        // Créer une image SVG simple pour le QR code
        $svg = '<svg width="300" height="300" xmlns="http://www.w3.org/2000/svg">
            <rect width="300" height="300" fill="white"/>
            <text x="150" y="150" text-anchor="middle" font-family="Arial" font-size="12" fill="black">
                QR Code pour: ' . htmlspecialchars($article->getNom()) . '
            </text>
            <text x="150" y="170" text-anchor="middle" font-family="Arial" font-size="10" fill="gray">
                ID: ' . $article->getId() . '
            </text>
        </svg>';
        
        return new Response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'inline; filename="qr_code_article_' . $article->getId() . '.svg"',
        ]);
    }

    public function generateQRCodeDownloadResponseForArticle(Article $article): Response
    {
        return $this->generateQRCodeResponseForArticle($article);
    }
}
