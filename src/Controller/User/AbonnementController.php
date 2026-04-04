<?php

namespace App\Controller\User;

use App\Entity\User\Abonnement;
use App\Form\User\AbonnementType;
use App\Repository\User\AbonnementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/abonnements', name: 'admin_abonnements')]
class AbonnementController extends AbstractController
{
    // ── LIST ─────────────────────────────────────────────────────────────────
    #[Route('', name: '_index', methods: ['GET'])]
    public function index(AbonnementRepository $repo): Response
    {
        return $this->render('User/listAbonn.html.twig', [
            'abonnements' => $repo->findAll(),
        ]);
    }

    // ── CREATE ────────────────────────────────────────────────────────────────
    #[Route('/new', name: '_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $abonnement = new Abonnement();
        $form = $this->createForm(AbonnementType::class, $abonnement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($abonnement);
            $em->flush();
            $this->addFlash('success', 'Abonnement créé avec succès.');
            return $this->redirectToRoute('admin_abonnements_index');
        }

        return $this->render('User/newAbonn.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ── SHOW ──────────────────────────────────────────────────────────────────
    #[Route('/{idAbonn}', name: '_show', methods: ['GET'])]
    public function show(Abonnement $abonnement): Response
    {
        return $this->render('User/showAbonn.html.twig', [
            'abonnement' => $abonnement,
        ]);
    }

    // ── EDIT ──────────────────────────────────────────────────────────────────
    #[Route('/{idAbonn}/edit', name: '_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Abonnement $abonnement, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AbonnementType::class, $abonnement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Abonnement mis à jour.');
            return $this->redirectToRoute('admin_abonnements_index');
        }

        return $this->render('User/editAbonn.html.twig', [
            'abonnement' => $abonnement,
            'form'       => $form->createView(),
        ]);
    }

    // ── DELETE ────────────────────────────────────────────────────────────────
    #[Route('/{idAbonn}/delete', name: '_delete', methods: ['POST'])]
    public function delete(Request $request, Abonnement $abonnement, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $abonnement->getIdAbonn(), $request->request->get('_token'))) {
            $em->remove($abonnement);
            $em->flush();
            $this->addFlash('success', 'Abonnement supprimé.');
        }

        return $this->redirectToRoute('admin_abonnements_index');
    }
}