<?php

namespace App\Controller\stocks;

use App\Entity\stocks\Article;
use App\Entity\stocks\MouvementStock; // <--- AJOUTÉ
use App\Form\stocks\ArticleType;
use App\Form\stocks\MouvementStockType; // <--- AJOUTÉ
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
    /**
     * Liste des produits avec filtrage, recherche et statistiques financières
     */
    #[Route('/', name: 'agri_produits', methods: ['GET'])]
    public function index(Request $request, ArticleRepository $articleRepository, CategorieRepository $catRepo): Response
    {
        $searchTerm = $request->query->get('search');
        $categoryId = $request->query->get('category');

        $articles = $articleRepository->findBySearchCriteria($searchTerm, $categoryId);

        $allArticles = $articleRepository->findAll();
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
            'categories' => $catRepo->findAll(),
            'currentSearch' => $searchTerm,
            'currentCategory' => $categoryId,
            'totalArticles' => $totalArticles,
            'alerteCount' => $alerteCount,
            'valeurTotaleStock' => $valeurTotaleStock,
        ]);
    }

    /**
     * Création d'un nouveau produit
     */
    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

    /**
     * Modification d'un produit existant
     */
    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
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

    /**
     * Suppression d'un produit
     */
    #[Route('/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
            $this->addFlash('danger', 'Le produit a été supprimé.');
        }

        return $this->redirectToRoute('agri_produits');
    }

    /**
     * Gestion des mouvements de stock (Entrées/Sorties)
     */
    #[Route('/{id}/mouvement', name: 'app_article_mouvement', methods: ['POST'])]
    public function gestionStock(Article $article, Request $request, EntityManagerInterface $em): Response
    {
        $mouvement = new MouvementStock();
        $mouvement->setArticle($article);
        $mouvement->setDateMouvement(new \DateTimeImmutable());

        // On récupère les données manuellement depuis la requête POST
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
                // AU LIEU DE RENDER LE DESIGN MOCHE, ON ENVOIE UN FLASH ET ON RESTE SUR LA LISTE
                $this->addFlash('danger', 'Stock insuffisant pour ' . $article->getNom() . ' ! (Disponible: ' . $stockActuel . ')');
                return $this->redirectToRoute('agri_produits');
            }
            $article->setQuantiteEnStock($stockActuel - $quantite);
        }

        $mouvement->setType($type);
        $mouvement->setQuantite($quantite);
        $mouvement->setMotif($motif);

        $em->persist($mouvement);
        $em->flush();

        $this->addFlash('success', 'Mouvement enregistré avec succès pour ' . $article->getNom());

        return $this->redirectToRoute('agri_produits');
    }
}
