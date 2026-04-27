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
   private function resolveAgriculteur(?User $user, EntityManagerInterface $em): ?User
{
    if (!$user) return null;
    
    $terrain = $user->getTerrain();
    
    // Si pas de terrain, cherche via la DB directement
    if (!$terrain) {
        $terrain = $em->getRepository(\App\Entity\Terrain\Terrain::class)
            ->createQueryBuilder('t')
            ->join('t.ouvriers', 'o')
            ->where('o.cin = :cin')
            ->setParameter('cin', $user->getCin())
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    if (!$terrain || !$terrain->getCin()) return null;
    
    return $em->getRepository(User::class)->find($terrain->getCin());
}



    #[Route('/', name: 'home')]
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }
        return $this->render('home_ouvrier.html.twig', []);
    }

    #[Route('/stocks/categories', name: 'categories')]
public function categories(CategorieRepository $categorieRepo, EntityManagerInterface $em): Response
{
    /** @var User|null $user */
    $user = $this->getUser();
    $agriculteur = $this->resolveAgriculteur($user, $em);

    if (!$agriculteur) {
        return $this->render('stocks/ouvrier/categories.html.twig', [
            'categories' => [],
            'error' => 'Aucun agriculteur lié n\'a été trouvé pour cet ouvrier.',
        ]);
    }

    // Utiliser le CIN directement
    $categories = $categorieRepo->createQueryBuilder('c')
        ->where('c.agriculteur = :cin')
        ->setParameter('cin', $agriculteur->getCin())
        ->orderBy('c.nom', 'ASC')
        ->getQuery()
        ->getResult();

    return $this->render('stocks/ouvrier/categories.html.twig', [
        'categories'  => $categories,
        'agriculteur' => $agriculteur,
    ]);
}

    #[Route('/stocks/produits', name: 'produits')]
public function produits(Request $request, ArticleRepository $articleRepo, CategorieRepository $categorieRepo, EntityManagerInterface $em): Response
{
    /** @var User|null $user */
    $user = $this->getUser();
    $agriculteur = $this->resolveAgriculteur($user, $em);

    if (!$agriculteur) {
        return $this->render('stocks/ouvrier/produits.html.twig', [
            'articles' => [],
            'categories' => [],
            'currentCategory' => null,
            'error' => 'Aucun agriculteur lié n\'a été trouvé pour cet ouvrier.',
        ]);
    }

    $categoryId = $request->query->get('category');
    $category = null;

    if ($categoryId) {
        $category = $categorieRepo->find((int)$categoryId);

        if ($category) {
            $catAgriculteur = $category->getAgriculteur();
            // getAgriculteur() peut retourner un int (CIN) ou un objet User
            $catCin = $catAgriculteur->getCin();

            if ($catCin !== (int)$agriculteur->getCin()) {
                $category = null;
            }
        }
    }

    // Récupérer TOUS les articles de l'agriculteur d'abord
    $allArticles = $articleRepo->findBy(['user' => $agriculteur], ['nom' => 'ASC']);

    // Filtrer manuellement par catégorie si nécessaire
    if ($category !== null) {
        $articles = array_filter($allArticles, function($article) use ($category) {
            return $article->getCategorie() && $article->getCategorie()->getId() === $category->getId();
        });
        $articles = array_values($articles);
    } else {
        $articles = $allArticles;
    }

    // Récupérer les catégories qui ont des articles
    $categories = $categorieRepo->createQueryBuilder('c')
        ->join('c.articles', 'a')
        ->where('a.user = :agriculteur')
        ->setParameter('agriculteur', $agriculteur)
        ->orderBy('c.nom', 'ASC')
        ->getQuery()
        ->getResult();

    return $this->render('stocks/ouvrier/produits.html.twig', [
        'articles'        => $articles,
        'categories'      => $categories,
        'agriculteur'     => $agriculteur,
        'currentCategory' => $category,
    ]);
}
    #[Route('/stocks/produits/{id}', name: 'article_show', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function articleShow(Article $article, EntityManagerInterface $em): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        $agriculteur = $this->resolveAgriculteur($user, $em);

        if (!$agriculteur || $article->getUser()->getCin() !== $agriculteur->getCin()) {
            throw $this->createAccessDeniedException('Accès refusé à cet article.');
        }

        return $this->render('stocks/ouvrier/article_show.html.twig', [
            'article' => $article,
            'agriculteur' => $agriculteur,
        ]);
    }

    #[Route('/stocks/produits/{id}/sortie', name: 'article_sortie', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function sortie(Article $article, Request $request, EntityManagerInterface $em, EmailService $emailService): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();
        $agriculteur = $this->resolveAgriculteur($user, $em);

        if (!$agriculteur || $article->getUser()->getCin() !== $agriculteur->getCin()) {
            throw $this->createAccessDeniedException('Accès refusé à cet article.');
        }

        $quantite = (float)$request->request->get('quantite');
        if ($quantite <= 0) {
            $this->addFlash('danger', 'La quantité doit être supérieure à 0.');
            return $this->redirectToRoute('ouvrier_produits');
        }

        $stockActuel = $article->getQuantiteEnStock();
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
        $mouvement->setMotif('Sortie par l\'ouvrier ' . $user->getCin());

        $em->persist($mouvement);
        $em->flush();

        if ($nouveauStock <= $article->getSeuilAlerte()) {
            if (!$emailService->envoyerMailAlerte($article)) {
                $this->addFlash('warning', 'Alerte créée, mais l\'envoi de l\'email a échoué.');
            }
        }

        $this->addFlash('success', 'Sortie enregistrée dans les mouvements.');
        return $this->redirectToRoute('ouvrier_produits');
    }

    #[Route('/stocks/produits/{id}/mouvements', name: 'article_mouvements', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function mouvements(Article $article, MouvementStockRepository $mouvementRepo, EntityManagerInterface $em): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        $agriculteur = $this->resolveAgriculteur($user, $em);

        if (!$agriculteur || $article->getUser()->getCin() !== $agriculteur->getCin()) {
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
                'id'       => $mouvement->getId(),
                'type'     => $mouvement->getType(),
                'quantite' => $mouvement->getQuantite(),
                'date'     => $mouvement->getDateMouvement()->format('d/m/Y H:i'),
                'motif'    => (string)$mouvement->getMotif(),
            ];
        }, $mouvements);

        return new JsonResponse(['success' => true, 'mouvements' => $data]);
    }

    #[Route('/stocks/mouvements/{id}/modifier', name: 'mouvement_modifier', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function modifierMouvement(MouvementStock $mouvement, Request $request, EntityManagerInterface $em): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        $agriculteur = $this->resolveAgriculteur($user, $em);

        if (!$agriculteur
            || $mouvement->getUser()->getCin() !== $user->getCin()
            || $mouvement->getArticle()->getUser()->getCin() !== $agriculteur->getCin()
        ) {
            return new JsonResponse(['success' => false, 'message' => 'Accès refusé.'], Response::HTTP_FORBIDDEN);
        }

        $quantite = (float)$request->request->get('quantite');
        if ($quantite <= 0) {
            return new JsonResponse(['success' => false, 'message' => 'La quantité doit être supérieure à 0.']);
        }

        $article = $mouvement->getArticle();
        $ancienneQuantite = $mouvement->getQuantite();
        $delta = $quantite - $ancienneQuantite;

        if ($mouvement->getType() === 'SORTIE') {
            $nouveauStock = $article->getQuantiteEnStock() - $delta;
            if ($nouveauStock < 0) {
                return new JsonResponse(['success' => false, 'message' => 'Stock insuffisant pour cette modification.']);
            }
            $article->setQuantiteEnStock($nouveauStock);
        } else {
            $article->setQuantiteEnStock($article->getQuantiteEnStock() + $delta);
        }

        $mouvement->setQuantite($quantite);
        $em->flush();

        return new JsonResponse([
            'success'  => true,
            'message'  => 'Mouvement mis à jour.',
            'quantite' => $quantite,
            'motif'    => $mouvement->getMotif(),
        ]);
    }

    #[Route('/profile/update', name: 'profile_update', methods: ['POST'])]
    public function profileUpdate(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['success' => false, 'message' => 'Utilisateur non authentifié.'], Response::HTTP_UNAUTHORIZED);
        }

        $email    = $request->request->get('email');
        $prenom   = $request->request->get('prenom');
        $nom      = $request->request->get('nom');
        $tel      = $request->request->get('tel');
        $ville    = $request->request->get('ville');
        $password = $request->request->get('password');
        $photo    = $request->request->get('photo');

        if ($email !== null)  $user->setEmail($email);
        if ($prenom !== null) $user->setPrenom($prenom);
        if ($nom !== null)    $user->setNom($nom);
        if ($tel !== null)    $user->setTel($tel);
        if ($ville !== null)  $user->setVille($ville);
        if ($photo !== null && trim($photo) !== '') $user->setImg($photo);

        if ($password !== null && trim($password) !== '') {
            $user->setMdp($passwordHasher->hashPassword($user, $password));
        }

        try {
            $em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'Profil mis à jour avec succès.',
            'prenom'  => $user->getPrenom(),
            'nom'     => $user->getNom(),
            'photo'   => $user->getImg(),
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
        return $this->render('ouvrier/evenements.html.twig', [
            'info' => 'Module de participation en cours de développement',
        ]);
    }
}