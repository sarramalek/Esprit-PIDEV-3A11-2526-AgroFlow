<?php

namespace App\Controller\AdminMateriels;

use App\Entity\Materiels\Machine;
use App\Repository\Materiels\MachineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[Route('/admin/machines', name: 'admin_machines_')]
class MachineAdminController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(MachineRepository $repo): Response
    {
        return $this->render('admin/machines/index.html.twig', [
            'machines' => $repo->findAll(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $machine = new Machine();

        if ($request->isMethod('POST')) {
            // TODO : À remplacer par un vrai formulaire Symfony + validation plus tard
            $machine->setNom($request->request->get('nom'));
            $machine->setMarque($request->request->get('marque'));
            $machine->setModele($request->request->get('modele'));
            $machine->setNumeroSerie($request->request->get('numeroSerie'));
            $machine->setEtatM($request->request->get('etatM'));

            $dateStr = $request->request->get('dateAchat');
            if ($dateStr) {
                try {
                    $machine->setDateAchat(new \DateTime($dateStr));
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Format de date invalide');
                    return $this->render('admin/machines/new.html.twig', ['machine' => $machine]);
                }
            }

            $em->persist($machine);
            $em->flush();

            $this->addFlash('success', 'Machine ajoutée avec succès !');
            return $this->redirectToRoute('admin_machines_index');
        }

        return $this->render('admin/machines/new.html.twig', [
            'machine' => $machine,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Machine $machine): Response
    {
        return $this->render('admin/machines/show.html.twig', [
            'machine' => $machine,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Machine $machine, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $machine->setNom($request->request->get('nom'));
            $machine->setMarque($request->request->get('marque'));
            $machine->setModele($request->request->get('modele'));
            $machine->setNumeroSerie($request->request->get('numeroSerie'));
            $machine->setEtatM($request->request->get('etatM'));

            $dateStr = $request->request->get('dateAchat');
            if ($dateStr) {
                try {
                    $machine->setDateAchat(new \DateTime($dateStr));
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Format de date invalide');
                    return $this->render('admin/machines/edit.html.twig', ['machine' => $machine]);
                }
            }

            $em->flush();

            $this->addFlash('success', 'Machine modifiée avec succès !');
            return $this->redirectToRoute('admin_machines_index');
        }

        return $this->render('admin/machines/edit.html.twig', [
            'machine' => $machine,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Machine $machine, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $machine->getId(), $request->request->get('_token'))) {
            $em->remove($machine);
            $em->flush();

            $this->addFlash('success', 'Machine supprimée avec succès !');
        } else {
            $this->addFlash('error', 'Token CSRF invalide');
        }

        return $this->redirectToRoute('admin_machines_index');
    }
}