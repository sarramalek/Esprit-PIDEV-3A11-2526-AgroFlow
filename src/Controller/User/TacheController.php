<?php

namespace App\Controller\User;

use App\Entity\User\Tache;
use App\Form\TacheType;
use App\Repository\User\TacheRepository;
use App\Repository\User\UserRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tache')]
class TacheController extends AbstractController
{
    // ==================== LIST ====================
    #[Route('/', name: 'app_tache_index', methods: ['GET'])]
public function index(Request $request, TacheRepository $tacheRepo): Response
{
    $search    = $request->query->get('q', '');
    $sort      = $request->query->get('sort', 'idTache');
    $direction = $request->query->get('direction', 'ASC');

    // Champs autorisés pour éviter les injections
    $allowedSorts = ['idTache', 'nomTache', 'etat', 'priorite', 'dateEcheancee'];
    if (!in_array($sort, $allowedSorts)) {
        $sort = 'idTache';
    }
    if (!in_array($direction, ['ASC', 'DESC'])) {
        $direction = 'ASC';
    }

    $taches = $tacheRepo->searchAndSort($search, $sort, $direction);

    return $this->render('User/listTache.html.twig', [
        'taches'       => $taches,
        'searchTerm'   => $search,
        'currentSort'  => $sort,
        'currentDir'   => $direction,
        'stats' => [
            'total'    => count($tacheRepo->findAll()),
            'enCours'  => $tacheRepo->countByEtat('en cours'),
            'terminee' => $tacheRepo->countByEtat('terminée'),
            'enRetard' => count($tacheRepo->findTachesEnRetard()),
        ],
    ]);
}
// ==================== NEW ====================
#[Route('/new', name: 'app_tache_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
{
    $tache = new Tache();

    // Récupérer le user depuis l'URL
    $assigneeId = $request->query->get('assignee');
    if ($assigneeId) {
        $user = $userRepository->find($assigneeId);
        if ($user) {
            $tache->setAssignee($user);
        }
    }

    $form = $this->createForm(TacheType::class, $tache);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($tache);
        $entityManager->flush();

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_tache_index');
        } elseif ($this->isGranted('ROLE_AGRICULTEUR')) {
            return $this->redirectToRoute('app_tache_front');
        } else {
            return $this->redirectToRoute('app_tache_index');
        }
    }

    return $this->render('User/newTache.html.twig', [
        'form'  => $form->createView(),
        'tache' => $tache,
    ]);
}

// ==================== EDIT ====================
#[Route('/{idTache}/edit', name: 'app_tache_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Tache $tache, EntityManagerInterface $em, UserRepository $userRepository): Response
{
    // Récupérer le user depuis l'URL si fourni
    $assigneeId = $request->query->get('assignee');
    if ($assigneeId) {
        $user = $userRepository->find($assigneeId);
        if ($user) {
            $tache->setAssignee($user);
        }
    }

    $form = $this->createForm(TacheType::class, $tache);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_tache_index');
        } elseif ($this->isGranted('ROLE_AGRICULTEUR')) {
            return $this->redirectToRoute('app_tache_front');
        } else {
            return $this->redirectToRoute('app_tache_index');
        }
    }

    return $this->render('User/editTache.html.twig', [
        'tache' => $tache,
        'form'  => $form->createView(),
    ]);
}

// ==================== DELETE ====================
#[Route('/{idTache}/delete', name: 'app_tache_delete', methods: ['POST'])]
public function delete(Request $request, Tache $tache, EntityManagerInterface $em): Response
{
    if ($this->isCsrfTokenValid('delete' . $tache->getIdTache(), $request->request->get('_token'))) {
        $em->remove($tache);
        $em->flush();
        $this->addFlash('success', 'Tâche supprimée avec succès.');
    }

    if ($this->isGranted('ROLE_ADMIN')) {
        return $this->redirectToRoute('app_tache_index');
    } elseif ($this->isGranted('ROLE_AGRICULTEUR')) {
        return $this->redirectToRoute('app_tache_front');
    } else {
        return $this->redirectToRoute('app_tache_index');
    }
}

    
}