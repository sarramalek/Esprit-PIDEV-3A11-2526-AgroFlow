<?php

namespace App\Controller\stocks;

use App\Entity\stocks\Article;
use App\Entity\User\User;
use App\Form\stocks\ArticleAdminType;
use App\Repository\stocks\ArticleRepository;
use App\Repository\stocks\CategorieRepository;
use App\Service\CurrencyService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/gestion-stocks')]
class AdminStockController extends AbstractController
{
    #[Route('/', name: 'admin_stock_index', methods: ['GET'])]
    public function index(Request $request, ArticleRepository $articleRepository, CategorieRepository $categorieRepo): Response
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || !\in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            throw $this->createAccessDeniedException();
        }

        $search = $request->query->get('q');
        $categoryId = $request->query->get('category');

        // L'Admin ne voit QUE ses propres articles, mais peut chercher dedans
        $articles = $articleRepository->findBySearchCriteria($search, $categoryId, null, 'nom', $currentUser->getCin());
        
        $categories = $categorieRepo->findAll(); // Pour le filtre par catégorie

        return $this->render('stocks/article/admin_index.html.twig', [
            'articles' => $articles,
            'categories' => $categories,
            'searchTerm' => $search,
            'currentCategory' => $categoryId,
        ]);
    }

    #[Route('/ajouter-produit', name: 'admin_stock_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, CurrencyService $currencyService): Response
    {
        $article = new Article();
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();

        $form = $this->createForm(ArticleAdminType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Marquage de l'admin qui crée l'article
            if ($currentUser) {
                $article->setIdAdmin($currentUser->getCin());
            }

            // Conversion automatique de devise
            if ($article->getDevise() !== 'TND' && $article->getPrixAchatDevise() > 0) {
                $prixTnd = $currencyService->convertToTND($article->getPrixAchatDevise(), $article->getDevise());
                $article->setPrixUnitaire($prixTnd);
            }

            $em->persist($article);
            $em->flush();

            $this->addFlash('success', 'Votre article a ete ajoute avec succes (Prix converti : ' . $article->getPrixUnitaire() . ' TND).');
            return $this->redirectToRoute('admin_stock_index');
        }

        return $this->render('stocks/article/Admin_new_article.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_stock_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $em, CurrencyService $currencyService): Response
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || !\in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            throw $this->createAccessDeniedException();
        }
        if ($article->getIdAdmin() !== $currentUser->getCin()) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres articles.');
        }

        $form = $this->createForm(ArticleAdminType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Marquage de l'admin
            $article->setIdAdmin($currentUser->getCin());

            // Conversion automatique de devise
            if ($article->getDevise() !== 'TND' && $article->getPrixAchatDevise() > 0) {
                $prixTnd = $currencyService->convertToTND($article->getPrixAchatDevise(), $article->getDevise());
                $article->setPrixUnitaire($prixTnd);
                $this->addFlash('success', sprintf('Conversion effectuee : %s %s = %s TND', $article->getPrixAchatDevise(), $article->getDevise(), $prixTnd));
            } else {
                $this->addFlash('success', 'Le produit a ete mis a jour avec succes (Admin).');
            }
            $em->flush();
            return $this->redirectToRoute('admin_stock_index');
        }

        return $this->render('stocks/article/admin_edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_stock_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $em): Response
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || !\in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            throw $this->createAccessDeniedException();
        }
        
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $em->remove($article);
            $em->flush();

            $this->addFlash('success', 'L\'article a ete supprime avec succes.');
        }

        return $this->redirectToRoute('admin_stock_index');
    }

    #[Route('/mouvement/{id}', name: 'admin_stock_mouvement', methods: ['POST'])]
    public function gestionStock(
        Article $article,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $type = $request->request->get('type');
        $quantite = floatval($request->request->get('quantite'));
        $motif = $request->request->get('motif');

        if ($quantite <= 0) {
            $this->addFlash('danger', 'La quantite doit etre superieure a 0.');
            return $this->redirectToRoute('admin_stock_index');
        }

        $stockActuel = $article->getQuantiteEnStock();
        if ($type === 'ENTREE') {
            $article->setQuantiteEnStock($stockActuel + $quantite);
        } elseif ($type === 'SORTIE') {
            if ($stockActuel < $quantite) {
                $this->addFlash('danger', 'Stock insuffisant.');
                return $this->redirectToRoute('admin_stock_index');
            }
            $article->setQuantiteEnStock($stockActuel - $quantite);
        }

        $mouvement = new \App\Entity\stocks\MouvementStock();
        $mouvement->setArticle($article);
        $mouvement->setType($type);
        $mouvement->setQuantite($quantite);
        $mouvement->setMotif($motif);
        $mouvement->setDateMouvement(new \DateTimeImmutable());
        $mouvement->setUser($this->getUser());
        
        // Marquage de l'admin
        if ($this->getUser()) {
            $mouvement->setIdAdmin($this->getUser()->getCin());
        }

        $em->persist($mouvement);
        $em->flush();

        $this->addFlash('success', 'Mouvement enregistre avec succes.');
        return $this->redirectToRoute('admin_stock_index');
    }

    #[Route('/export/pdf', name: 'admin_stock_pdf')]
    public function exportPdf(Request $request, ArticleRepository $articleRepo, \App\Repository\stocks\MouvementStockRepository $mouvementRepo, EntityManagerInterface $em): Response
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        $agriId = $request->query->get('agriculteur');
        $search = $request->query->get('q');
        $type = $request->query->get('type', 'articles'); // 'articles' ou 'mouvements'

        // On filtre par idAdmin pour l'export aussi
        if ($type === 'mouvements') {
            $results = $mouvementRepo->findByAdminSearch($search, $currentUser->getCin());
        } else {
            $results = $articleRepo->findBySearchCriteria($search, null, null, 'nom', $currentUser->getCin());
        }

        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new \Dompdf\Dompdf($options);

        $html = $this->renderView('stocks/article/admin_pdf.html.twig', [
            'results' => $results,
            'type' => $type,
            'agriculteur' => $agriId
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="export_stock_' . date('Y-m-d') . '.pdf"'
        ]);
    }
    #[Route('/mouvements', name: 'admin_stock_mouvements', methods: ['GET'])]
    public function mouvements(Request $request, \App\Repository\stocks\MouvementStockRepository $mouvementRepo): Response
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser || !\in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            throw $this->createAccessDeniedException();
        }

        $search = $request->query->get('q');
        
        // L'Admin ne voit QUE ses propres mouvements, avec recherche
        $mouvements = $mouvementRepo->findByAdminSearch($search, $currentUser->getCin());

        return $this->render('stocks/article/admin_mouvements.html.twig', [
            'mouvements' => $mouvements,
            'searchTerm' => $search,
        ]);
    }
}
