<?php

namespace App\Controller\User;

use App\Entity\User\Offre;
use App\Form\User\OffreType;
use App\Repository\User\OffreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/offre')]
class OffreController extends AbstractController
{
    // ==================== LIST ====================
    #[Route('/', name: 'app_offre_list', methods: ['GET'])]
    public function list(Request $request, OffreRepository $offreRepo): Response
    {
        $search    = $request->query->get('q', '');
        $sort      = $request->query->get('sort', 'idOffres');
        $direction = $request->query->get('direction', 'ASC');

        $allowedSorts = ['idOffres', 'nomOffre', 'prix', 'dureeOffre'];
        if (!in_array($sort, $allowedSorts)) $sort = 'idOffres';
        if (!in_array($direction, ['ASC', 'DESC'])) $direction = 'ASC';

        return $this->render('User/listOffre.html.twig', [
            'offres'      => $offreRepo->searchAndSort($search, $sort, $direction),
            'searchTerm'  => $search,
            'currentSort' => $sort,
            'currentDir'  => $direction,
            'stats' => [
                'total'   => $offreRepo->countAll(),
                'avgPrix' => round($offreRepo->avgPrix(), 2),
                'minCher' => $offreRepo->findMoinsCher(1)[0] ?? null,
                'maxLong' => $offreRepo->findPlusLong(1)[0] ?? null,
            ],
        ]);
    }

    // ==================== NEW ====================
    #[Route('/new', name: 'app_offre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $offre = new Offre();
        $form  = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($offre);
            $em->flush();
            $this->addFlash('success', 'Offre créée avec succès.');
            return $this->redirectToRoute('app_offre_list');
        }

        return $this->render('User/newOffre.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ==================== SHOW ====================
    #[Route('/{idOffres}', name: 'app_offre_show', methods: ['GET'])]
    public function show(Offre $offre): Response
    {
        return $this->render('User/showOffre.html.twig', [
            'offre' => $offre,
        ]);
    }

    // ==================== EDIT ====================
    #[Route('/{idOffres}/edit', name: 'app_offre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Offre $offre, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(OffreType::class, $offre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Offre modifiée avec succès.');
            return $this->redirectToRoute('app_offre_list');
        }

        return $this->render('User/editOffre.html.twig', [
            'offre' => $offre,
            'form'  => $form->createView(),
        ]);
    }

    // ==================== DELETE ====================
    #[Route('/{idOffres}/delete', name: 'app_offre_delete', methods: ['POST'])]
    public function delete(Request $request, Offre $offre, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $offre->getIdOffres(), $request->request->get('_token'))) {
            $em->remove($offre);
            $em->flush();
            $this->addFlash('success', 'Offre supprimée avec succès.');
        }
        return $this->redirectToRoute('app_offre_list');
    }
}