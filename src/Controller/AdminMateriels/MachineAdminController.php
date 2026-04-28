<?php

namespace App\Controller\AdminMateriels;

use App\Entity\Materiels\Machine;
use App\Entity\User\User;
use App\Repository\Materiels\MachineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/admin/materiels/machines', name: 'admin_machines_')]
class MachineAdminController extends AbstractController
{
    /* ════════════════════════════════════════════
       INDEX - Liste des machines
    ════════════════════════════════════════════ */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(MachineRepository $repo): Response
    {
        $machines = $repo->findAll();

        return $this->render('admin/machines/index.html.twig', [
            'machinesJson' => $this->serializeMachines($machines),
        ]);
    }

    /* ════════════════════════════════════════════
       NEW - Ajouter une machine
    ════════════════════════════════════════════ */
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $machine = new Machine();
        
        $agriculteurs = $this->getAgriculteurs($em);

        if ($request->isMethod('POST')) {
            $cinAgriculteur = $request->request->get('agriculteur');
            if ($cinAgriculteur) {
                $agriculteur = $em->getRepository(User::class)->find($cinAgriculteur);
                if ($agriculteur) {
                    $machine->setAgriculteur($agriculteur);
                }
            }
            
            $this->hydrateMachine($machine, $request);
            $errors = $this->validateMachine($machine, $request);

            if (!empty($errors)) {
                foreach ($errors as $err) {
                    $this->addFlash('error', $err);
                }
                return $this->render('admin/machines/new.html.twig', [
                    'machine' => $machine,
                    'agriculteurs' => $agriculteurs,
                    'errors'  => $errors,
                ]);
            }

            $em->persist($machine);
            $em->flush();

            $this->addFlash('success', '✓ Machine « ' . $machine->getNom() . ' » ajoutée avec succès !');
            return $this->redirectToRoute('admin_machines_index');
        }

        return $this->render('admin/machines/new.html.twig', [
            'machine' => $machine,
            'agriculteurs' => $agriculteurs,
            'errors'  => [],
        ]);
    }

    /* ════════════════════════════════════════════
       SHOW - Voir une machine
    ════════════════════════════════════════════ */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id, MachineRepository $repository): Response
    {
        $machine = $repository->find((int)$id);
        
        if (!$machine) {
            $this->addFlash('error', 'Machine non trouvée.');
            return $this->redirectToRoute('admin_machines_index');
        }
        
        return $this->render('admin/machines/show.html.twig', [
            'machine' => $machine,
        ]);
    }

    /* ════════════════════════════════════════════
       EDIT - Modifier une machine
    ════════════════════════════════════════════ */
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        string $id,
        Request $request,
        MachineRepository $repository,
        EntityManagerInterface $em
    ): Response {
        $machine = $repository->find((int)$id);
        
        if (!$machine) {
            $this->addFlash('error', 'Machine non trouvée.');
            return $this->redirectToRoute('admin_machines_index');
        }
        
        $agriculteurs = $this->getAgriculteurs($em);
        
        if ($request->isMethod('POST')) {
            $cinAgriculteur = $request->request->get('agriculteur');
            if ($cinAgriculteur) {
                $agriculteur = $em->getRepository(User::class)->find($cinAgriculteur);
                if ($agriculteur) {
                    $machine->setAgriculteur($agriculteur);
                }
            } else {
                $machine->setAgriculteur(null);
            }
            
            $this->hydrateMachine($machine, $request);
            $errors = $this->validateMachine($machine, $request);

            if (!empty($errors)) {
                foreach ($errors as $err) {
                    $this->addFlash('error', $err);
                }
                return $this->render('admin/machines/edit.html.twig', [
                    'machine' => $machine,
                    'agriculteurs' => $agriculteurs,
                    'errors'  => $errors,
                ]);
            }

            $em->flush();

            $this->addFlash('success', '✓ Machine « ' . $machine->getNom() . ' » modifiée avec succès !');
            return $this->redirectToRoute('admin_machines_index');
        }

        return $this->render('admin/machines/edit.html.twig', [
            'machine' => $machine,
            'agriculteurs' => $agriculteurs,
            'errors'  => [],
        ]);
    }

    /* ════════════════════════════════════════════
       DELETE - Supprimer une machine
    ════════════════════════════════════════════ */
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(
        string $id,
        Request $request,
        MachineRepository $repository,
        EntityManagerInterface $em
    ): Response {
        $machine = $repository->find((int)$id);
        
        if (!$machine) {
            $this->addFlash('error', 'Machine non trouvée.');
            return $this->redirectToRoute('admin_machines_index');
        }
        
        if ($this->isCsrfTokenValid('delete' . $machine->getId(), $request->request->get('_token'))) {
            $nom = $machine->getNom();
            $em->remove($machine);
            $em->flush();
            $this->addFlash('success', '✓ Machine « ' . $nom . ' » supprimée avec succès !');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('admin_machines_index');
    }

    /* ════════════════════════════════════════════
       DELETE CONFIRMATION
    ════════════════════════════════════════════ */
    #[Route('/{id}/delete-confirm', name: 'delete_confirm', methods: ['GET'])]
    public function deleteConfirm(string $id, MachineRepository $repository): Response
    {
        $machine = $repository->find((int)$id);
        
        if (!$machine) {
            $this->addFlash('error', 'Machine non trouvée.');
            return $this->redirectToRoute('admin_machines_index');
        }
        
        return $this->render('admin/machines/delete.html.twig', [
            'machine' => $machine,
        ]);
    }

    

    /* ════════════════════════════════════════════
       VALIDATION CÔTÉ SERVEUR
    ════════════════════════════════════════════ */
    private function validateMachine(Machine $machine, Request $request): array
    {
        $errors = [];

        if ($machine->getAgriculteur() === null) {
            $errors[] = 'Veuillez sélectionner un agriculteur.';
        }

        $nom = trim($machine->getNom());
        if ($nom === '') {
            $errors[] = 'Le nom est obligatoire.';
        } elseif (mb_strlen($nom) < 2) {
            $errors[] = 'Le nom doit contenir au moins 2 caractères.';
        } elseif (mb_strlen($nom) > 255) {
            $errors[] = 'Le nom ne peut pas dépasser 255 caractères.';
        }

        $marque = trim($machine->getMarque());
        if ($marque === '') {
            $errors[] = 'La marque est obligatoire.';
        } elseif (mb_strlen($marque) < 2) {
            $errors[] = 'La marque doit contenir au moins 2 caractères.';
        } elseif (mb_strlen($marque) > 255) {
            $errors[] = 'La marque ne peut pas dépasser 255 caractères.';
        }

        $modele = trim($machine->getModele());
        if ($modele === '') {
            $errors[] = 'Le modèle est obligatoire.';
        } elseif (mb_strlen($modele) > 255) {
            $errors[] = 'Le modèle ne peut pas dépasser 255 caractères.';
        }

        $serie = trim($machine->getNumeroSerie());
        if ($serie !== '' && mb_strlen($serie) > 255) {
            $errors[] = 'Le numéro de série ne peut pas dépasser 255 caractères.';
        }

        $etatsAutorises = ['Neuf', 'Bon', 'Occasion', 'En panne'];
        $etat = trim($machine->getEtatM());
        if ($etat === '') {
            $errors[] = "L'état est obligatoire.";
        } elseif (!in_array($etat, $etatsAutorises, true)) {
            $errors[] = "L'état sélectionné est invalide.";
        }

        $kilometrage = $machine->getKilometrage();
        if ($kilometrage === null) {
            $errors[] = 'Le kilométrage est obligatoire.';
        } elseif ($kilometrage < 0) {
            $errors[] = 'Le kilométrage ne peut pas être négatif.';
        }

        $kmLastVisite = $machine->getKmLastVisite();
        if ($kmLastVisite === null) {
            $errors[] = 'Le kilométrage de dernière visite est obligatoire.';
        } elseif ($kmLastVisite < 0) {
            $errors[] = 'Le kilométrage de dernière visite ne peut pas être négatif.';
        }

        if ($kilometrage !== null && $kmLastVisite !== null && $kmLastVisite > $kilometrage) {
            $errors[] = 'Le kilométrage de dernière visite ne peut pas être supérieur au kilométrage actuel.';
        }

        return $errors;
    }

    private function getAgriculteurs(EntityManagerInterface $em): array
    {
        return $em->getRepository(User::class)->findBy(['role' => 2]);
    }

    private function hydrateMachine(Machine $machine, Request $request): void
    {
        $machine->setNom(trim($request->request->get('nom', '')));
        $machine->setMarque(trim($request->request->get('marque', '')));
        $machine->setModele(trim($request->request->get('modele', '')));
        $machine->setNumeroSerie(trim($request->request->get('numeroSerie', '')) ?: null);
        $machine->setEtatM(trim($request->request->get('etatM', '')));
        
        $kilometrage = $request->request->get('kilometrage');
        $machine->setKilometrage($kilometrage !== '' && $kilometrage !== null ? (int)$kilometrage : 0);
        
        $kmLastVisite = $request->request->get('kmLastVisite');
        $machine->setKmLastVisite($kmLastVisite !== '' && $kmLastVisite !== null ? (int)$kmLastVisite : 0);
        
        try {
            $dateAchatStr = $request->request->get('dateAchat');
            $machine->setDateAchat($dateAchatStr ? new \DateTime($dateAchatStr) : null);
        } catch (\Exception $e) {
            $machine->setDateAchat(null);
        }
        
        try {
            $dateLastVisiteStr = $request->request->get('dateLastVisite');
            $machine->setDateLastVisite($dateLastVisiteStr ? new \DateTime($dateLastVisiteStr) : null);
        } catch (\Exception $e) {
            $machine->setDateLastVisite(null);
        }
        
        try {
            $prochaineMaintenanceStr = $request->request->get('prochaineMaintenance');
            $machine->setProchaineMaintenance($prochaineMaintenanceStr ? new \DateTime($prochaineMaintenanceStr) : null);
        } catch (\Exception $e) {
            $machine->setProchaineMaintenance(null);
        }
    }

    private function serializeMachines(array $machines): string
    {
        $data = array_map(function (Machine $m) {
            return [
                'id' => $m->getId(),
                'nom' => $m->getNom(),
                'marque' => $m->getMarque(),
                'modele' => $m->getModele(),
                'serie' => $m->getNumeroSerie(),
                'etat' => $m->getEtatM(),
                'kilometrage' => $m->getKilometrage(),
                'kmLastVisite' => $m->getKmLastVisite(),
                'dateAchat' => $m->getDateAchat() ? $m->getDateAchat()->format('Y-m-d') : null,
                'dateLastVisite' => $m->getDateLastVisite() ? $m->getDateLastVisite()->format('Y-m-d') : null,
                'prochaineMaintenance' => $m->getProchaineMaintenance() ? $m->getProchaineMaintenance()->format('Y-m-d') : null,
                'cinAgriculteur' => $m->getCinAgriculteur(),
                'nomAgriculteur' => $m->getNomAgriculteur(),
                'csrf' => 'delete' . $m->getId(),
            ];
        }, $machines);

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
    }
}