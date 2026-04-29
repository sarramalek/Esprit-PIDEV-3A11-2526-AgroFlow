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

#[Route('/admin/machines', name: 'admin_machines_')]
class MachineAdminController extends AbstractController
{
    /* ════════════════════════════════════════════
       INDEX
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
       NEW
    ════════════════════════════════════════════ */
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $machine = new Machine();
        
        // Récupérer la liste des agriculteurs (role = 2)
        $agriculteurs = $this->getAgriculteurs($em);

        if ($request->isMethod('POST')) {
            // Récupérer le CIN de l'agriculteur sélectionné
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
       SHOW
    ════════════════════════════════════════════ */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Machine $machine): Response
    {
        return $this->render('admin/machines/show.html.twig', [
            'machine' => $machine,
        ]);
    }

    /* ════════════════════════════════════════════
       EDIT
    ════════════════════════════════════════════ */
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Machine $machine,
        EntityManagerInterface $em
    ): Response {
        // Récupérer la liste des agriculteurs (role = 2)
        $agriculteurs = $this->getAgriculteurs($em);
        
        if ($request->isMethod('POST')) {
            // Récupérer le CIN de l'agriculteur sélectionné
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
       DELETE
    ════════════════════════════════════════════ */
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Machine $machine,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $machine->getId(), $request->request->get('_token'))) {
            $nom = $machine->getNom();
            $em->remove($machine);
            $em->flush();
            $this->addFlash('success', '✓ Machine « ' . $nom . ' » supprimée avec succès !');
        } else {
            $this->addFlash('error', 'Token CSRF invalide. Veuillez réessayer.');
        }

        return $this->redirectToRoute('admin_machines_index');
    }

    /* ════════════════════════════════════════════
       DELETE CONFIRMATION
    ════════════════════════════════════════════ */
    #[Route('/{id}/delete-confirm', name: 'delete_confirm', methods: ['GET'])]
    public function deleteConfirm(Machine $machine): Response
    {
        return $this->render('admin/machines/delete.html.twig', [
            'machine' => $machine,
        ]);
    }

    /* ════════════════════════════════════════════
       VALIDATION CÔTÉ SERVEUR
    ════════════════════════════════════════════ */
    /**
     * @return array<int, string>
     */
    private function validateMachine(Machine $machine, Request $request): array
    {
        $errors = [];

        // Vérifier que l'agriculteur est sélectionné
        if ($machine->getAgriculteur() === null) {
            $errors[] = 'Veuillez sélectionner un agriculteur propriétaire de la machine.';
        }

        // Nom
        $nom = trim($machine->getNom());
        if ($nom === '') {
            $errors[] = 'Le nom de la machine est obligatoire.';
        } elseif (mb_strlen($nom) < 2) {
            $errors[] = 'Le nom doit contenir au moins 2 caractères.';
        } elseif (mb_strlen($nom) > 255) {
            $errors[] = 'Le nom ne peut pas dépasser 255 caractères.';
        }

        // Marque
        $marque = trim($machine->getMarque());
        if ($marque === '') {
            $errors[] = 'La marque est obligatoire.';
        } elseif (mb_strlen($marque) < 2) {
            $errors[] = 'La marque doit contenir au moins 2 caractères.';
        } elseif (mb_strlen($marque) > 255) {
            $errors[] = 'La marque ne peut pas dépasser 255 caractères.';
        }

        // Modèle
        $modele = trim($machine->getModele());
        if ($modele === '') {
            $errors[] = 'Le modèle est obligatoire.';
        } elseif (mb_strlen($modele) > 255) {
            $errors[] = 'Le modèle ne peut pas dépasser 255 caractères.';
        }

        // N° de série (optionnel)
        $serie = trim($machine->getNumeroSerie());
        if ($serie !== '' && mb_strlen($serie) > 255) {
            $errors[] = 'Le numéro de série ne peut pas dépasser 255 caractères.';
        }

        // État (selon les choices de l'entité)
        $etatsAutorises = ['Neuf', 'Bon', 'Occasion', 'En panne'];
        $etat = trim($machine->getEtatM());
        if ($etat === '') {
            $errors[] = "L'état de la machine est obligatoire.";
        } elseif (!in_array($etat, $etatsAutorises, true)) {
            $errors[] = "L'état sélectionné est invalide. Valeurs acceptées : " . implode(', ', $etatsAutorises);
        }

        // Kilométrage
        $kilometrage = $machine->getKilometrage();
        if ($kilometrage < 0) {
            $errors[] = 'Le kilométrage ne peut pas être négatif.';
        }

        // Kilométrage dernière visite
        $kmLastVisite = $machine->getKmLastVisite();
        if ($kmLastVisite < 0) {
            $errors[] = 'Le kilométrage de dernière visite ne peut pas être négatif.';
        }

        // Vérification cohérence des kilométrages
        if ($kmLastVisite > $kilometrage) {
            $errors[] = 'Le kilométrage de dernière visite ne peut pas être supérieur au kilométrage actuel.';
        }

        // Date d'achat (optionnel)
        $dateStr = $request->request->get('dateAchat');
        if ($dateStr !== null && $dateStr !== '') {
            try {
                $date = new \DateTime($dateStr);
                $today = new \DateTime('today');
                if ($date > $today) {
                    $errors[] = "La date d'achat ne peut pas être dans le futur.";
                }
            } catch (\Exception) {
                $errors[] = "Le format de la date d'achat est invalide.";
            }
        }

        // Date dernière visite (optionnel)
        $dateLastVisiteStr = $request->request->get('dateLastVisite');
        if ($dateLastVisiteStr !== null && $dateLastVisiteStr !== '') {
            try {
                $dateLastVisite = new \DateTime($dateLastVisiteStr);
                $today = new \DateTime('today');
                if ($dateLastVisite > $today) {
                    $errors[] = "La date de dernière visite ne peut pas être dans le futur.";
                }
            } catch (\Exception) {
                $errors[] = "Le format de la date de dernière visite est invalide.";
            }
        }

        // Prochaine maintenance (optionnel)
        $prochaineMaintenanceStr = $request->request->get('prochaineMaintenance');
        if ($prochaineMaintenanceStr !== null && $prochaineMaintenanceStr !== '') {
            try {
                new \DateTime($prochaineMaintenanceStr);
            } catch (\Exception) {
                $errors[] = "Le format de la date de prochaine maintenance est invalide.";
            }
        }

        return $errors;
    }

    /* ════════════════════════════════════════════
       HELPERS PRIVÉS
    ════════════════════════════════════════════ */
    
    /**
     * Récupère la liste des agriculteurs (role = 2)
     */
    /**
     * @return array<int, User>
     */
    private function getAgriculteurs(EntityManagerInterface $em): array
    {
        return $em->getRepository(User::class)->findBy(['role' => 2]);
    }
    
    /**
     * Hydrate toutes les propriétés de la machine
     */
    private function hydrateMachine(Machine $machine, Request $request): void
    {
        // Champs texte
        $machine->setNom(trim($request->request->get('nom', '')));
        $machine->setMarque(trim($request->request->get('marque', '')));
        $machine->setModele(trim($request->request->get('modele', '')));
        $machine->setNumeroSerie(trim($request->request->get('numeroSerie', '')) ?: null);
        $machine->setEtatM(trim($request->request->get('etatM', '')));
        
        // Kilométrages
        $kilometrage = $request->request->get('kilometrage');
        $machine->setKilometrage($kilometrage !== '' && $kilometrage !== null ? (int)$kilometrage : 0);
        
        $kmLastVisite = $request->request->get('kmLastVisite');
        $machine->setKmLastVisite($kmLastVisite !== '' && $kmLastVisite !== null ? (int)$kmLastVisite : 0);
        
        // Dates
        $dateAchatStr = $request->request->get('dateAchat');
        if ($dateAchatStr) {
            try {
                $machine->setDateAchat(new \DateTime($dateAchatStr));
            } catch (\Exception) {
                $machine->setDateAchat(null);
            }
        } else {
            $machine->setDateAchat(null);
        }
        
        $dateLastVisiteStr = $request->request->get('dateLastVisite');
        if ($dateLastVisiteStr) {
            try {
                $machine->setDateLastVisite(new \DateTime($dateLastVisiteStr));
            } catch (\Exception) {
                $machine->setDateLastVisite(null);
            }
        } else {
            $machine->setDateLastVisite(null);
        }
        
        $prochaineMaintenanceStr = $request->request->get('prochaineMaintenance');
        if ($prochaineMaintenanceStr) {
            try {
                $machine->setProchaineMaintenance(new \DateTime($prochaineMaintenanceStr));
            } catch (\Exception) {
                $machine->setProchaineMaintenance(null);
            }
        } else {
            $machine->setProchaineMaintenance(null);
        }
    }

    /**
     * Sérialise les machines pour le DataTable
     */
    /**
     * @param array<int, Machine> $machines
     */
    private function serializeMachines(array $machines): string
    {
        $data = array_map(function (Machine $m) {
            return [
                'id'     => $m->getId(),
                'nom'    => $m->getNom(),
                'marque' => $m->getMarque(),
                'modele' => $m->getModele(),
                'serie'  => $m->getNumeroSerie(),
                'etat'   => $m->getEtatM(),
                'date'   => $m->getDateAchat() ? $m->getDateAchat()->format('Y-m-d') : null, // Changé ici : 'date' au lieu de 'dateAchat'
                'kilometrage' => $m->getKilometrage(),
                'kmLastVisite' => $m->getKmLastVisite(),
                'dateAchat' => $m->getDateAchat() ? $m->getDateAchat()->format('Y-m-d') : null,
                'dateLastVisite' => $m->getDateLastVisite() ? $m->getDateLastVisite()->format('Y-m-d') : null,
                'prochaineMaintenance' => $m->getProchaineMaintenance() ? $m->getProchaineMaintenance()->format('Y-m-d') : null,
                // Infos propriétaire
                'cinAgriculteur' => $m->getCinAgriculteur(),
                'nomAgriculteur' => $m->getNomAgriculteur(),
                'csrf'   => 'delete' . $m->getId(),
            ];
        }, $machines);

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
    }
}