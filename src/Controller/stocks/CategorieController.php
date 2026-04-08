<?php

namespace App\Controller\stocks;

use App\Entity\stocks\Categorie;
use App\Entity\stocks\MouvementStock;
use App\Form\stocks\CategorieType;
use App\Repository\stocks\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/agriculteur/stocks/categories')]
class CategorieController extends AbstractController
{
    #[Route('/', name: 'agri_categories', methods: ['GET'])]
    public function index(Request $request, CategorieRepository $repository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $search = $request->query->get('q');

        // Correction : Utilisation de la propriété PHP dateCreation
        $sort = $request->query->get('sort', 'dateCreation');
        $direction = $request->query->get('direction', 'DESC');

        // Préparation de la période pour la rotation (3 derniers mois)
        $dateRotationDebut = new \DateTime('-3 months');
        $dateRotationFin = new \DateTime('now');

        $queryBuilder = $repository->createQueryBuilder('c')
            ->leftJoin('c.articles', 'a')
            ->addSelect('COUNT(a.id) as HIDDEN articleCount')
            ->addSelect('SUM(a.quantite_en_stock) as totalQuantite')
            ->where('c.agriculteur = :user')
            ->setParameter('user', $user)
            ->groupBy('c.id');

        if ($search) {
            $queryBuilder->andWhere('c.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Gestion du tri corrigée
        if ($sort === 'nom') {
            $queryBuilder->orderBy('c.nom', $direction);
        } elseif ($sort === 'articles') {
            $queryBuilder->orderBy('articleCount', $direction);
        } else {
            // Correction ici : date_creation devient dateCreation
            $queryBuilder->orderBy('c.dateCreation', $direction);
        }

        $results = $queryBuilder->getQuery()->getResult();
        $aujourdhui = new \DateTime();
        $categoriesData = [];

        foreach ($results as $result) {
            $categorie = $result[0];
            $totalStock = $result['totalQuantite'] ?? 0;
            $articles = $categorie->getArticles();
            $nbProduits = count($articles);

            // --- 1. LOGIQUE DE ROTATION ---
            $totalSorties = $em->getRepository(MouvementStock::class)->createQueryBuilder('m')
                ->select('SUM(m.quantite)')
                ->join('m.article', 'art')
                ->where('art.categorie = :cat')
                ->andWhere('m.type = :type')
                ->andWhere('m.dateMouvement BETWEEN :debut AND :fin')
                ->setParameter('cat', $categorie)
                ->setParameter('type', 'SORTIE')
                ->setParameter('debut', $dateRotationDebut)
                ->setParameter('fin', $dateRotationFin)
                ->getQuery()
                ->getSingleScalarResult() ?: 0;

            // Indice de rotation : Sorties / Stock Actuel
            $indiceRotation = ($totalStock > 0) ? ($totalSorties / $totalStock) : ($totalSorties > 0 ? 1 : 0);

            // --- 2. LOGIQUE DES 60 JOURS ---
            $diff = $aujourdhui->diff($categorie->getDateCreation());
            $joursPasses = $diff->days;
            $joursRestants = 60 - $joursPasses;

            $afficherAlerte = ($nbProduits === 0 && $joursPasses > 30 && $joursPasses <= 60);
            $estExpiree = ($nbProduits === 0 && $joursPasses > 60);

            if (!$estExpiree) {
                $categoriesData[] = [
                    'info' => $categorie,
                    'isNouveau' => ($joursPasses <= 30),
                    'totalStock' => $totalStock,
                    'nbProduits' => $nbProduits,
                    'afficherAlerte' => $afficherAlerte,
                    'joursRestants' => $joursRestants,
                    'totalSorties' => $totalSorties,
                    'indiceRotation' => round($indiceRotation, 2),
                ];
            }
        }

        return $this->render('stocks/categorie/index.html.twig', [
            'categoriesData' => $categoriesData,
            'searchTerm' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction,
        ]);
    }

    #[Route('/new', name: 'agri_categories_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categorie = new Categorie();
        // On définit l'agriculteur automatiquement
        $categorie->setAgriculteur($this->getUser());

        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($categorie);
            $entityManager->flush();
            return $this->redirectToRoute('agri_categories');
        }

        return $this->render('stocks/categorie/new.html.twig', [
            'categorie' => $categorie,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'agri_categories_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('agri_categories');
        }

        return $this->render('stocks/categorie/edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'agri_categories_delete', methods: ['POST'])]
    public function delete(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $categorie->getId(), $request->request->get('_token'))) {
            $entityManager->remove($categorie);
            $entityManager->flush();
        }
        return $this->redirectToRoute('agri_categories');
    }
}
