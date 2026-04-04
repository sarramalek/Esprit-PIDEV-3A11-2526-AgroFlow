<?php

namespace App\Controller\stocks;

use App\Entity\stocks\Article;
use App\Form\stocks\ArticleType;
use App\Repository\stocks\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Cette route parente définit le début de l'URL pour toutes les méthodes
#[Route('/admin/produits')]
class ArticleController extends AbstractController
{
    // L'URL finale sera : /admin/produits/
    #[Route('/', name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('stocks/article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    // L'URL finale sera : /admin/produits/new
    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_article_index');
        }

        return $this->render('stocks/article/new.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    // L'URL finale sera : /admin/produits/{id}/edit
    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_article_index');
        }

        return $this->render('stocks/article/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    // L'URL finale sera : /admin/produits/{id} en méthode POST
    #[Route('/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_article_index');
    }
}
