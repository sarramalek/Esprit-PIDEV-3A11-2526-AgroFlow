<?php

namespace App\Controller\stocks;

use App\Entity\stocks\Article;
use App\Entity\stocks\MouvementStock;
use App\Form\stocks\ArticleType;
use App\Repository\stocks\ArticleRepository;
use App\Repository\stocks\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/agriculteur/stocks')]
class ArticleController extends AbstractController
{
    #[Route('/', name: 'agri_produits', methods: ['GET'])]
    public function index(Request $request, ArticleRepository $articleRepository, CategorieRepository $catRepo): Response
    {
        $user = $this->getUser();
        $searchTerm = $request->query->get('search');
        $categoryId = $request->query->get('category');

        // Appel du repository avec l'utilisateur
        $articles = $articleRepository->findBySearchCriteria($searchTerm, $categoryId, $user);

        // Statistiques basées sur les articles de l'utilisateur uniquement
        $allArticles = $articleRepository->findBy(['user' => $user]);
        $totalArticles = count($allArticles);

        $alerteCount = 0;
        $valeurTotaleStock = 0;

        foreach ($allArticles as $art) {
            if ($art->getQuantiteEnStock() <= $art->getSeuilAlerte()) {
                $alerteCount++;
            }

            $prix = $art->getPrixUnitaire() ?? 0;
            $quantite = $art->getQuantiteEnStock() ?? 0;
            $valeurTotaleStock += ($quantite * $prix);
        }

        return $this->render('stocks/article/index.html.twig', [
            'articles' => $articles,
            'categories' => $catRepo->findBy(['agriculteur' => $user]),
            'currentSearch' => $searchTerm,
            'currentCategory' => $categoryId,
            'totalArticles' => $totalArticles,
            'alerteCount' => $alerteCount,
            'valeurTotaleStock' => $valeurTotaleStock,
        ]);
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $user = $this->getUser();

        $form = $this->createForm(ArticleType::class, $article, ['agriculteur' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setUser($user); // Important pour lier au CIN
            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash('success', 'Le produit a été ajouté avec succès.');
            return $this->redirectToRoute('agri_produits');
        }

        return $this->render('stocks/article/new.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($article->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ArticleType::class, $article, ['agriculteur' => $this->getUser()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Le produit a été mis à jour.');
            return $this->redirectToRoute('agri_produits');
        }

        return $this->render('stocks/article/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($article->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
            $this->addFlash('danger', 'Le produit a été supprimé.');
        }

        return $this->redirectToRoute('agri_produits');
    }

    #[Route('/{id}/mouvement', name: 'app_article_mouvement', methods: ['POST'])]
    public function gestionStock(Article $article, Request $request, EntityManagerInterface $em): Response
    {
        if ($article->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $mouvement = new MouvementStock();
        $mouvement->setArticle($article);
        $mouvement->setDateMouvement(new \DateTimeImmutable());

        $type = $request->request->get('type');
        $quantite = floatval($request->request->get('quantite'));
        $motif = $request->request->get('motif');

        if ($quantite <= 0) {
            $this->addFlash('danger', 'La quantité doit être supérieure à 0.');
            return $this->redirectToRoute('agri_produits');
        }

        $stockActuel = $article->getQuantiteEnStock();

        if ($type === 'ENTREE') {
            $article->setQuantiteEnStock($stockActuel + $quantite);
        } elseif ($type === 'SORTIE') {
            if ($stockActuel < $quantite) {
                $this->addFlash('danger', 'Stock insuffisant pour ' . $article->getNom());
                return $this->redirectToRoute('agri_produits');
            }
            $article->setQuantiteEnStock($stockActuel - $quantite);
        }

        $mouvement->setType($type);
        $mouvement->setQuantite($quantite);
        $mouvement->setMotif($motif);

        $em->persist($mouvement);
        $em->flush();

        $this->addFlash('success', 'Mouvement enregistré avec succès.');
        return $this->redirectToRoute('agri_produits');
    }
}
