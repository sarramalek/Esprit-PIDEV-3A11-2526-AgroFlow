<?php

namespace App\Controller;

use App\Entity\stocks\Article;
use App\Entity\User\User;
use App\Entity\stocks\MouvementStock;
use App\Repository\stocks\ArticleRepository;
use App\Repository\stocks\CategorieRepository;
use App\Repository\stocks\MouvementStockRepository;
use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/ouvrier', name: 'ouvrier_')]
#[IsGranted('ROLE_OUVRIER')]
class OuvrierDashboardController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('home_ouvrier.html.twig', []);
    }

    #[Route('/stocks/categories', name: 'categories')]
    public function categories(CategorieRepository $categorieRepo): Response
    {
        /** @var \App\Entity\User\User|null $user */
        $user = $this->getUser();
        $agriculteur = $user?->getAgriculteur();

        if (!$agriculteur) {
            return $this->render('ouvrier/categories.html.twig', [
                'categories' => [],
                'error' => 'Aucun agriculteur lié n’a été trouvé pour cet ouvrier.',
            ]);
        }

        $categories = $categorieRepo->findByAgriculteur($agriculteur);

        return $this->render('ouvrier/categories.html.twig', [
            'categories' => $categories,
            'agriculteur' => $agriculteur,
        ]);
    }

    #[Route('/stocks/produits', name: 'produits')]
    public function produits(Request $request, ArticleRepository $articleRepo, CategorieRepository $categorieRepo): Response
    {
        /** @var \App\Entity\User\User|null $user */
        $user = $this->getUser();
        $agriculteur = $user?->getAgriculteur();

        if (!$agriculteur) {
            return $this->render('ouvrier/produits.html.twig', [
                'articles' => [],
                'error' => 'Aucun agriculteur lié n’a été trouvé pour cet ouvrier.',
            ]);
        }

        $categoryId = $request->query->get('category');
        $category = null;
        if ($categoryId) {
            $category = $categorieRepo->find((int)$categoryId);
            if ($category && $category->getAgriculteur()?->getCin() !== $agriculteur->getCin()) {
                $category = null;
            }
        }

        $criteria = ['user' => $agriculteur];
        if ($category) {
            $criteria['categorie'] = $category;
        }

        $articles = $articleRepo->findBy($criteria, ['nom' => 'ASC']);

        return $this->render('ouvrier/produits.html.twig', [
            'articles' => $articles,
            'agriculteur' => $agriculteur,
            'currentCategory' => $category,
        ]);
    }

    #[Route('/stocks/produits/{id}', name: 'article_show', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function articleShow(Article $article): Response
    {
        /** @var \App\Entity\User\User|null $user */
        $user = $this->getUser();
        $agriculteur = $user?->getAgriculteur();

        if (!$agriculteur || $article->getUser()?->getCin() !== $agriculteur->getCin()) {
            throw $this->createAccessDeniedException('Accès refusé à cet article.');
        }

        return $this->render('ouvrier/article_show.html.twig', [
            'article' => $article,
            'agriculteur' => $agriculteur,
        ]);
    }

    #[Route('/stocks/produits/{id}/sortie', name: 'article_sortie', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function sortie(Article $article, Request $request, EntityManagerInterface $em, EmailService $emailService): Response
    {
        /** @var \App\Entity\User\User|null $user */
        $user = $this->getUser();
        $agriculteur = $user?->getAgriculteur();

        if (!$agriculteur || $article->getUser()?->getCin() !== $agriculteur->getCin()) {
            throw $this->createAccessDeniedException('Accès refusé à cet article.');
        }

        $quantite = (float)$request->request->get('quantite');
        if ($quantite <= 0) {
            $this->addFlash('danger', 'La quantité doit être supérieure à 0.');
            return $this->redirectToRoute('ouvrier_produits');
        }

        $stockActuel = $article->getQuantiteEnStock() ?? 0;
        if ($stockActuel < $quantite) {
            $this->addFlash('danger', 'Stock insuffisant pour « ' . $article->getNom() . ' ».');
            return $this->redirectToRoute('ouvrier_produits');
        }

        $nouveauStock = $stockActuel - $quantite;
        $article->setQuantiteEnStock($nouveauStock);

        $mouvement = new MouvementStock();
        $mouvement->setArticle($article);
        $mouvement->setUser($user);
        $mouvement->setType('SORTIE');
        $mouvement->setQuantite($quantite);
        $mouvement->setDateMouvement(new \DateTimeImmutable());
        $mouvement->setMotif('Sortie par l’ouvrier ' . $user->getCin());

        $em->persist($mouvement);
        $em->flush();

        if ($nouveauStock <= $article->getSeuilAlerte()) {
            $mailOK = $emailService->envoyerMailAlerte($article);
            if (!$mailOK) {
                $this->addFlash('warning', 'Alerte créée, mais l’envoi de l’email a échoué.');
            }
        }

        $this->addFlash('success', 'Sortie enregistrée dans les mouvements.');
        return $this->redirectToRoute('ouvrier_produits');
    }

    #[Route('/stocks/produits/{id}/mouvements', name: 'article_mouvements', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function mouvements(Article $article, MouvementStockRepository $mouvementRepo): JsonResponse
    {
        /** @var \App\Entity\User\User|null $user */
        $user = $this->getUser();
        $agriculteur = $user?->getAgriculteur();

        if (!$agriculteur || $article->getUser()?->getCin() !== $agriculteur->getCin()) {
            return new JsonResponse(['success' => false, 'message' => 'Accès refusé.'], Response::HTTP_FORBIDDEN);
        }

        $mouvements = $mouvementRepo->createQueryBuilder('m')
            ->andWhere('m.article = :article')
            ->andWhere('m.user = :user')
            ->setParameter('article', $article)
            ->setParameter('user', $user)
            ->orderBy('m.dateMouvement', 'DESC')
            ->getQuery()
            ->getResult();

        $data = array_map(function (MouvementStock $mouvement) {
            return [
                'id' => $mouvement->getId(),
                'type' => $mouvement->getType(),
                'quantite' => $mouvement->getQuantite(),
                'date' => $mouvement->getDateMouvement()?->format('d/m/Y H:i') ?? '',
                'motif' => $mouvement->getMotif() ?: '',
            ];
        }, $mouvements);

        return new JsonResponse(['success' => true, 'mouvements' => $data]);
    }

    #[Route('/stocks/mouvements/{id}/modifier', name: 'mouvement_modifier', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function modifierMouvement(MouvementStock $mouvement, Request $request, EntityManagerInterface $em): JsonResponse
    {
        /** @var \App\Entity\User\User|null $user */
        $user = $this->getUser();
        $agriculteur = $user?->getAgriculteur();

        if (!$agriculteur || $mouvement->getUser()?->getCin() !== $user->getCin() || $mouvement->getArticle()?->getUser()?->getCin() !== $agriculteur->getCin()) {
            return new JsonResponse(['success' => false, 'message' => 'Accès refusé.'], Response::HTTP_FORBIDDEN);
        }

        $quantite = (float) $request->request->get('quantite');
        $motif = trim((string) $request->request->get('motif', ''));
        if ($quantite <= 0) {
            return new JsonResponse(['success' => false, 'message' => 'La quantité doit être supérieure à 0.']);
        }

        $article = $mouvement->getArticle();
        $ancienneQuantite = $mouvement->getQuantite() ?? 0;
        $delta = $quantite - $ancienneQuantite;

        if ($mouvement->getType() === 'SORTIE') {
            $nouveauStock = ($article->getQuantiteEnStock() ?? 0) - $delta;
            if ($nouveauStock < 0) {
                return new JsonResponse(['success' => false, 'message' => 'Stock insuffisant pour cette modification.']);
            }
            $article->setQuantiteEnStock($nouveauStock);
        } else {
            $article->setQuantiteEnStock(($article->getQuantiteEnStock() ?? 0) + $delta);
        }

        $mouvement->setQuantite($quantite);
        $mouvement->setMotif($motif ?: $mouvement->getMotif());
        $em->flush();

        return new JsonResponse(['success' => true, 'message' => 'Mouvement mis à jour.', 'quantite' => $quantite, 'motif' => $motif]);
    }

    #[Route('/profile/update', name: 'profile_update', methods: ['POST'])]
    public function profileUpdate(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        /** @var \App\Entity\User\User|null $user */
        $user = $this->getUser();

        // 1. Vérification d'instance pour corriger l'erreur Intelephense (P1013)
        if (!$user instanceof \App\Entity\User\User) {
            return new JsonResponse(['success' => false, 'message' => 'Utilisateur non authentifié ou invalide.'], Response::HTTP_UNAUTHORIZED);
        }

        // 2. Récupération des données du formulaire
        $email = $request->request->get('email');
        $prenom = $request->request->get('prenom');
        $nom = $request->request->get('nom');
        $tel = $request->request->get('tel');
        $ville = $request->request->get('ville');
        $password = $request->request->get('password');

        // 3. Mise à jour des champs (Intelephense ne soulignera plus setEmail)
        if ($email !== null) {
            $user->setEmail($email);
        }
        if ($prenom !== null) {
            $user->setPrenom($prenom);
        }
        if ($nom !== null) {
            $user->setNom($nom);
        }
        if ($tel !== null) {
            $user->setTel($tel);
        }
        if ($ville !== null) {
            $user->setVille($ville);
        }

        // Note : On ne modifie pas le CIN car c'est la clé primaire (ID) de votre table.

        // 4. Gestion sécurisée du mot de passe
        if ($password !== null && trim($password) !== '') {
            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setMdp($hashedPassword);
        }

        // 5. Sauvegarde en base de données
        try {
            $em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Erreur lors de la sauvegarde : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'Profil mis à jour avec succès.',
            'prenom' => $user->getPrenom(),
            'nom' => $user->getNom(),
        ]);
    }

    #[Route('/taches', name: 'taches')]
    public function taches(): Response
    {
        return $this->render('ouvrier/taches.html.twig');
    }

    #[Route('/evenements', name: 'evenements')]
    public function evenements(): Response
    {
        return $this->render('ouvrier/evenements.html.twig');
    }

    #[Route('/participations', name: 'participation_index')]
    public function participations(): Response
    {
        // On redirige vers les événements pour l'instant ou on affiche une vue simple
        return $this->render('ouvrier/evenements.html.twig', [
            'info' => 'Module de participation en cours de développement'
        ]);
    }
}
