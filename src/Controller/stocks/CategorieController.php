<?php

namespace App\Controller\stocks;

use App\Entity\stocks\Categorie;
use App\Repository\stocks\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/categories')]
class CategorieController extends AbstractController
{
    #[Route('/', name: 'app_categorie_index', methods: ['GET'])]
    public function index(CategorieRepository $categorieRepository): Response
    {
        return $this->render('stocks/categorie/index.html.twig', [
            'categories' => $categorieRepository->findAll(),
        ]);
    }
}
