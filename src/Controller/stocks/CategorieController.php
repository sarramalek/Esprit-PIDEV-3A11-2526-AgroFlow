<?php

namespace App\Controller\stocks;

use App\Entity\stocks\Categorie;
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
    public function index(Request $request, CategorieRepository $repository): Response
    {
        $search = $request->query->get('q');
        $sort = $request->query->get('sort', 'date_creation');
        $direction = $request->query->get('direction', 'DESC');

        $queryBuilder = $repository->createQueryBuilder('c')
            ->leftJoin('c.articles', 'a')
            ->addSelect('COUNT(a.id) as HIDDEN articleCount')
            // CORRECTION ICI : On utilise le nom de la propriété PHP 'quantite_en_stock'
            ->addSelect('SUM(a.quantite_en_stock) as totalQuantite')
            ->groupBy('c.id');

        if ($search) {
            $queryBuilder->andWhere('c.nom LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Gestion du tri
        if ($sort === 'nom') {
            $queryBuilder->orderBy('c.nom', $direction);
        } elseif ($sort === 'articles') {
            $queryBuilder->orderBy('articleCount', $direction);
        } else {
            $queryBuilder->orderBy('c.date_creation', $direction);
        }

        $results = $queryBuilder->getQuery()->getResult();

        $aujourdhui = new \DateTime();
        $categoriesData = [];

        foreach ($results as $result) {
            $categorie = $result[0];
            $totalStock = $result['totalQuantite'] ?? 0;

            $diff = $aujourdhui->diff($categorie->getDateCreation());

            $categoriesData[] = [
                'info' => $categorie,
                'isNouveau' => ($diff->days <= 30),
                'totalStock' => $totalStock
            ];
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
