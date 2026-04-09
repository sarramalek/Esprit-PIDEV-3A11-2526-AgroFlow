<?php

namespace App\Controller\stocks;

use App\Entity\stocks\Article;
use App\Form\stocks\ArticleAdminType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/gestion-stocks')]
class AdminStockController extends AbstractController
{
    #[Route('/ajouter-produit', name: 'admin_stock_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $article = new Article();
        // On ne fait PAS de setUser ici, on laisse le formulaire s'en charger

        $form = $this->createForm(ArticleAdminType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($article);
            $em->flush();

            $this->addFlash('success', 'Votre article a été ajouté avec succès.');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('stocks/article/Admin_new_article.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
