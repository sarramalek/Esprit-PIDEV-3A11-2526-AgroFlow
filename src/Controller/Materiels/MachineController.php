<?php

namespace App\Controller\Materiels;

use App\Entity\Materiels\Machine;
use App\Entity\User\User;
use App\Form\Materiels\MachineType;
use App\Repository\Materiels\MachineRepository;
use App\Repository\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/agriculteur/materiels/machines', name: 'agri_')]
final class MachineController extends AbstractController
{
    private function getFullUser(UserRepository $userRepo): ?User
    {
        $sessionUser = $this->getUser();
        if (!$sessionUser) {
            return null;
        }
        return $userRepo->findOneBy(['email' => $sessionUser->getUserIdentifier()]);
    }

    // ── Liste (sans pagination) ──────────────────────────────────────────────
    #[Route('', name: 'machines', methods: ['GET'])]
    public function machineIndex(
        Request           $request,
        MachineRepository $repo,
        UserRepository    $userRepo,
        PaginatorInterface $paginator
    ): Response {
        $user = $this->getFullUser($userRepo);

        if (!$user) {
            $this->addFlash('error', 'Veuillez vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        // search() retourne un array
        $machines = $repo->search([
            'cin'     => $user->getCin(),
            'search'  => trim((string) $request->query->get('search',  '')),
            'etat'    => trim((string) $request->query->get('etat',    '')),
            'sortBy'  => $request->query->get('sortBy',  'dateAchat'),
            'sortDir' => $request->query->get('sortDir', 'DESC'),
        ]);

        $pagination = $paginator->paginate(
            $machines,
            $request->query->getInt('page', 1),
            9
        );

        return $this->render('machines/index.html.twig', [
            'machines' => $pagination,
            'machinesPagination' => $pagination,
        ]);
    }

    // ── Statistiques ─────────────────────────────────────────────────────────
    #[Route('/statistiques', name: 'machine_statistiques', methods: ['GET'])]
    public function machineStatistiques(MachineRepository $repo): Response
    {
        $stats = $repo->getStatistiques();

        return $this->render('machines/statistiques.html.twig', [
            'stats'        => $stats,
            'etatLabels'   => array_keys($stats['parEtat']),
            'etatValues'   => array_values($stats['parEtat']),
            'marqueLabels' => array_keys($stats['parMarque']),
            'marqueValues' => array_values($stats['parMarque']),
        ]);
    }

    // ── Nouvelle machine ──────────────────────────────────────────────────────
    #[Route('/new', name: 'machine_new', methods: ['GET', 'POST'])]
    public function machineNew(
        Request                $request,
        EntityManagerInterface $em,
        UserRepository         $userRepo
    ): Response {
        $user = $this->getFullUser($userRepo);

        if (!$user) {
            $this->addFlash('error', 'Veuillez vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        $machine = new Machine();
        $form    = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $machine->setAgriculteur($user);
            $em->persist($machine);
            $em->flush();

            $this->addFlash('success', '✅ Machine « ' . $machine->getNom() . ' » ajoutée.');
            return $this->redirectToRoute('agri_machines');
        }

        return $this->render('machines/new.html.twig', [
            'form'    => $form,
            'machine' => $machine,
        ]);
    }

    // ── Détail ────────────────────────────────────────────────────────────────
    #[Route('/{id}', name: 'machine_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function machineShow(Machine $machine): Response
    {
        return $this->render('machines/show.html.twig', [
            'machine'        => $machine,
            'nomAgriculteur' => $machine->getNomAgriculteur(),
        ]);
    }

    // ── Édition ───────────────────────────────────────────────────────────────
    #[Route('/{id}/edit', name: 'machine_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function machineEdit(
        Request                $request,
        Machine                $machine,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', '✅ Machine « ' . $machine->getNom() . ' » mise à jour.');
            return $this->redirectToRoute('agri_machines');
        }

        return $this->render('machines/edit.html.twig', [
            'form'    => $form,
            'machine' => $machine,
        ]);
    }

    // ── Suppression ───────────────────────────────────────────────────────────
    #[Route('/{id}/delete', name: 'machine_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function machineDelete(
        Request                $request,
        Machine                $machine,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete_machine_' . $machine->getId(),
            $request->getPayload()->getString('_token')
        )) {
            $em->remove($machine);
            $em->flush();
            $this->addFlash('success', '🗑️ Machine supprimée.');
        } else {
            $this->addFlash('error', '❌ Token CSRF invalide.');
        }

        return $this->redirectToRoute('agri_machines');
    }
}