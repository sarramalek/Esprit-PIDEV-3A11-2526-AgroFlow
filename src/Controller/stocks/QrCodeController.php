<?php

namespace App\Controller\stocks;

use App\Entity\stocks\Article;
use App\Service\QRCodeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/agriculteur/articles/qr')]
class QrCodeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private QRCodeService $qrCodeService
    ) {}

    #[Route('/{id}', name: 'article_qr_code', methods: ['GET'])]
    public function generateQrCode(int $id): Response
    {
        $article = $this->entityManager->getRepository(Article::class)->find($id);
        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        return $this->qrCodeService->generateQRCodeResponseForArticle($article);
    }

    #[Route('/{id}/download', name: 'article_qr_code_download', methods: ['GET'])]
    public function downloadQrCode(int $id): Response
    {
        $article = $this->entityManager->getRepository(Article::class)->find($id);
        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        return $this->qrCodeService->generateQRCodeDownloadResponseForArticle($article);
    }

    #[Route('/{id}/view', name: 'article_qr_code_view', methods: ['GET'])]
    public function viewQrCode(int $id): Response
    {
        $article = $this->entityManager->getRepository(Article::class)->find($id);
        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        $articleUrl = $this->generateUrl('article_show', ['id' => $article->getId()], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->render('stocks/qr_code/view.html.twig', [
            'article' => $article,
            'qrCodeUrl' => $this->generateUrl('article_qr_code', ['id' => $article->getId()]),
            'downloadUrl' => $this->generateUrl('article_qr_code_download', ['id' => $article->getId()]),
            'articleUrl' => $articleUrl,
        ]);
    }
}
