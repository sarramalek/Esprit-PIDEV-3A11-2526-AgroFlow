<?php

namespace App\Controller\stocks;

use App\Entity\stocks\Article;
use App\Entity\stocks\MouvementStock;
use App\Form\stocks\ArticleType;
use App\Repository\stocks\ArticleRepository;
use App\Repository\stocks\CategorieRepository;
use App\Service\EmailService;
use App\Service\QRCodeService;
use App\Service\TelegramService;
use App\Service\CurrencyService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/agriculteur/stocks')]
class ArticleController extends AbstractController
{
    #[Route('/', name: 'agri_produits', methods: ['GET', 'POST'])]
    public function index(Request $request, ArticleRepository $articleRepository, CategorieRepository $catRepo, EntityManagerInterface $entityManager, CurrencyService $currencyService): Response
    {
        $user = $this->getUser();

        $newArticle = new Article();
        $form = $this->createForm(ArticleType::class, $newArticle, ['agriculteur' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $devise = $newArticle->getDevise();
            $prixAchat = $newArticle->getPrixAchatDevise();

            if ($devise && $devise !== 'TND' && $prixAchat > 0) {
                $prixTnd = $currencyService->convertToTND($prixAchat, $devise);
                $newArticle->setPrixUnitaire($prixTnd);
                $this->addFlash('success', sprintf('Conversion effectuee : %s %s = %s TND', $prixAchat, $devise, $prixTnd));
            } else {
                $this->addFlash('success', 'Le produit a ete ajoute avec succes.');
            }

            $newArticle->setUser($user);
            $entityManager->persist($newArticle);
            $entityManager->flush();

            return $this->redirectToRoute('agri_produits');
        }

        return $this->renderIndexPage($request, $articleRepository, $catRepo, $form);
    }

    private function renderIndexPage(Request $request, ArticleRepository $articleRepository, CategorieRepository $catRepo, $form = null, array $extra = []): Response
    {
        $user = $this->getUser();
        $searchTerm = $request->query->get('search');
        $categoryId = $request->query->get('category');
        $sortBy = $request->query->get('sort', 'nom');

        $articles = $articleRepository->findBySearchCriteria($searchTerm, $categoryId, $user, $sortBy);
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

        if (!$form) {
            $newArticle = new Article();
            $form = $this->createForm(ArticleType::class, $newArticle, ['agriculteur' => $user]);
        }

        return $this->render('stocks/article/index.html.twig', array_merge([
            'articles'          => $articles,
            'categories'        => $catRepo->findBy(['agriculteur' => $user]),
            'currentSearch'     => $searchTerm,
            'currentCategory'   => $categoryId,
            'currentSort'       => $sortBy,
            'totalArticles'     => $totalArticles,
            'alerteCount'       => $alerteCount,
            'valeurTotaleStock' => $valeurTotaleStock,
            'form'              => $form->createView(),
        ], $extra));
    }

    // Gardez la méthode new au cas où vous auriez besoin d'un accès direct par URL
    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, CurrencyService $currencyService): Response
    {
        $article = new Article();
        $user = $this->getUser();

        $form = $this->createForm(ArticleType::class, $article, ['agriculteur' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Conversion automatique de devise
            if ($article->getDevise() !== 'TND' && $article->getPrixAchatDevise() > 0) {
                $prixTnd = $currencyService->convertToTND($article->getPrixAchatDevise(), $article->getDevise());
                $article->setPrixUnitaire($prixTnd);
            }

            $article->setUser($user);
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
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager, CurrencyService $currencyService): Response
    {
        if ($article->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ArticleType::class, $article, ['agriculteur' => $this->getUser()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $devise = $article->getDevise();
            $prixAchat = $article->getPrixAchatDevise();

            if ($devise && $devise !== 'TND' && $prixAchat > 0) {
                $prixTnd = $currencyService->convertToTND($prixAchat, $devise);
                $article->setPrixUnitaire($prixTnd);
                $this->addFlash('success', sprintf('Mise a jour avec conversion : %s %s = %s TND', $prixAchat, $devise, $prixTnd));
            } else {
                $this->addFlash('success', 'Le produit a ete mis a jour.');
            }

            $entityManager->flush();
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


    #[Route('/mouvements/new/{id}', name: 'app_mouvement_new', methods: ['GET', 'POST'])]

    public function gestionStock(
        Article $article,
        Request $request,
        EntityManagerInterface $em,
        EmailService $emailService,
        TelegramService $telegramService
    ): Response {
        if ($article->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $mouvement = new MouvementStock();
        $mouvement->setArticle($article);
        $mouvement->setDateMouvement(new \DateTimeImmutable());
        $mouvement->setUser($this->getUser());

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

            $nouveauStock = $stockActuel - $quantite;
            $article->setQuantiteEnStock($nouveauStock);

            if ($nouveauStock <= $article->getSeuilAlerte()) {
                $mailOk = $emailService->envoyerMailAlerte($article);
                if (!$mailOk) {
                    $this->addFlash('warning', 'Alerte créée, mais l\'envoi de l\'email a échoué.');
                }

                // Notification Telegram
                $telegramChatId = ($article->getUser() ? $article->getUser()->getTelegramChatId() : null) ?? $_ENV['TELEGRAM_CHAT_ID'] ?? null;
                if ($telegramChatId) {
                    $message = sprintf(
                        "Alerte de stock critique !\n\nArticle: %s\nStock actuel: %d %s\nSeuil d'alerte: %d %s",
                        $article->getNom(),
                        $nouveauStock,
                        $article->getUniteMesure() ?? 'unités',
                        $article->getSeuilAlerte(),
                        $article->getUniteMesure() ?? 'unités'
                    );
                    $telegramService->notifier($message, $telegramChatId);
                }
            }
        }

        $mouvement->setType($type);
        $mouvement->setQuantite($quantite);
        $mouvement->setMotif($motif);

        $em->persist($mouvement);
        $em->flush();

        $this->addFlash('success', 'Mouvement enregistré avec succès.');
        return $this->redirectToRoute('agri_produits');
    }

    // ── QR Code ───────────────────────────────────────────────────────────────
#[Route('/{id}/qr-code', name: 'article_qr_code', methods: ['GET'])]
    public function generateQRCode(Article $article, QRCodeService $qrCodeService): Response
    {
        // Vérification simple de l'article
        if (!$article) {
            return new Response('Article non trouvé', 404);
        }

        return $qrCodeService->generateQRCodeResponseForArticle($article);
    }

#[Route('/{id}/qr-code/download', name: 'article_qr_code_download', methods: ['GET'])]
    public function downloadQRCode(Article $article, QRCodeService $qrCodeService): Response
    {
        return $qrCodeService->generateQRCodeDownloadResponseForArticle($article);
    }

#[Route('/{id}/qr-code/view', name: 'article_qr_code_view', methods: ['GET'])]
    public function viewQRCode(Article $article): Response
    {
        return $this->render('stocks/qr_code/view.html.twig', [
            'article' => $article,
            'qrCodeUrl' => $this->generateUrl('article_qr_code', ['id' => $article->getId()]),
            'downloadUrl' => $this->generateUrl('article_qr_code_download', ['id' => $article->getId()]),
        ]);
    }

    #[Route('/{id}/details', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        if ($article->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('stocks/article/show.html.twig', [
            'article' => $article,
            'movements' => $article->getMouvements(), 
        ]);
    }

    #[Route('/{id}/scan-redirect', name: 'article_scan_redirect', methods: ['GET'])]
    public function scanRedirect(Article $article): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Si c'est un ouvrier (ROLE_OUVRIER)
        if (in_array('ROLE_OUVRIER', $user->getRoles())) {
            return $this->redirectToRoute('ouvrier_article_show', ['id' => $article->getId()]);
        }

        // Par défaut pour l'agriculteur (le propriétaire de l'article)
        return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
    }
}
