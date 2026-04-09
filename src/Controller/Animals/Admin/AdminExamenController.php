<?php

namespace App\Controller\Animals\Admin;

use App\Entity\Animals\Examen;
use App\Form\Animals\ExamenType;
use App\Repository\Animals\ExamenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/examens')]
#[IsGranted('ROLE_ADMIN')]
final class AdminExamenController extends AbstractController
{
    #[Route('', name: 'admin_examens_index', methods: ['GET'])]
    public function index(Request $request, ExamenRepository $examenRepository): Response
    {
        $searchTerm  = $request->query->get('q');
        $sortBy      = $request->query->get('sort', 'id');
        $direction   = $request->query->get('direction', 'DESC');
        $typeFilter  = $request->query->get('type');

        // Admin ne voit que les examens qu'il a ajoutés
        $session = $request->getSession();
        $adminExamenIds = $session->get('admin_added_examens', []);

        $examens = $examenRepository->searchExamenAdmin($searchTerm, $sortBy, $direction, $typeFilter, $adminExamenIds);

        return $this->render('Animals/admin/examen/index.html.twig', [
            'examens'          => $examens,
            'searchTerm'       => $searchTerm,
            'currentSort'      => $sortBy,
            'currentDirection' => $direction,
            'currentType'      => $typeFilter,
        ]);
    }

    #[Route('/new', name: 'admin_examens_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $examen = new Examen();
        $form   = $this->createForm(ExamenType::class, $examen, ['user' => null]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($examen);
            $em->flush();

            // Mémoriser l'examen ajouté dans la session pour l'admin
            $session = $request->getSession();
            $adminExamenIds = $session->get('admin_added_examens', []);
            $adminExamenIds[] = $examen->getId();
            $session->set('admin_added_examens', $adminExamenIds);

            $this->addFlash('success', 'Examen ajouté avec succès.');
            return $this->redirectToRoute('admin_examens_index');
        }

        return $this->render('Animals/admin/examen/form.html.twig', [
            'form'   => $form,
            'title'  => 'Ajouter un examen',
            'examen' => $examen,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_examens_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Examen $examen, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ExamenType::class, $examen, ['user' => null]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Examen modifié avec succès.');
            return $this->redirectToRoute('admin_examens_index');
        }

        return $this->render('Animals/admin/examen/form.html.twig', [
            'form'   => $form,
            'title'  => 'Modifier l\'examen',
            'examen' => $examen,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_examens_delete', methods: ['POST'])]
    public function delete(Request $request, Examen $examen, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_examen_' . $examen->getId(), $request->request->get('_token'))) {
            $em->remove($examen);
            $em->flush();
            $this->addFlash('success', 'Examen supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_examens_index');
    }
}
