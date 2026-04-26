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
    // Content removed to avoid route name collisions with ArticleController.
}

