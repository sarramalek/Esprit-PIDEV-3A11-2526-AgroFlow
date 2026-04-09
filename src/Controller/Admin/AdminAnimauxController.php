<?php

namespace App\Controller\Admin;

use App\Entity\Animals\Animaux;
use App\Form\Admin\AdminAnimauxType;
use App\Repository\Animals\AnimauxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/animaux')]
#[IsGranted('ROLE_ADMIN')]
final class AdminAnimauxController extends AbstractController
{
    #[Route('', name: 'admin_animaux_index', methods: ['GET'])]
    public function index(Request $request, AnimauxRepository $animauxRepository): Response
    {
        $searchTerm = $request->query->get('q');
        $sortBy     = $request->query->get('sort', 'id');
        $direction  = $request->query->get('direction', 'DESC');

        // L'admin voit TOUT (null = pas de filtre par user)
        $animaux  = $animauxRepository->searchDashboard($searchTerm, $sortBy, $direction, null);
        $averages = $animauxRepository->getAverageWeightsBySpecies();

        return $this->render('admin/animaux/index.html.twig', [
            'animaux'          => $animaux,
            'searchTerm'       => $searchTerm,
            'currentSort'      => $sortBy,
            'currentDirection' => $direction,
            'averages'         => $averages,
        ]);
    }

    #[Route('/new', name: 'admin_animaux_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $animal = new Animaux();
        $form   = $this->createForm(AdminAnimauxType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($animal);
            $em->flush();
            $this->addFlash('success', 'Animal ajouté avec succès.');
            return $this->redirectToRoute('admin_animaux_index');
        }

        return $this->render('admin/animaux/form.html.twig', [
            'form'  => $form,
            'title' => 'Ajouter un animal',
            'animal' => $animal,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_animaux_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Animaux $animal, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AdminAnimauxType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Animal modifié avec succès.');
            return $this->redirectToRoute('admin_animaux_index');
        }

        return $this->render('admin/animaux/form.html.twig', [
            'form'   => $form,
            'title'  => 'Modifier : ' . $animal->getNom(),
            'animal' => $animal,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_animaux_delete', methods: ['POST'])]
    public function delete(Request $request, Animaux $animal, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_animal_' . $animal->getId(), $request->request->get('_token'))) {
            $em->remove($animal);
            $em->flush();
            $this->addFlash('success', 'Animal supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_animaux_index');
    }
}
