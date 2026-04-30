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
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

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
        
        // Valeurs par défaut
        $machine->setKilometrage(0);
        $machine->setKmLastVisite(0);
        
        $agriculteurs = $this->getAgriculteurs($em);

        if ($request->isMethod('POST')) {
            // Assigner l'agriculteur
            $cinAgriculteur = $request->request->get('agriculteur');
            if ($cinAgriculteur) {
                $agriculteur = $em->getRepository(User::class)->find($cinAgriculteur);
                if ($agriculteur) {
                    $machine->setAgriculteur($agriculteur);
                }
            }
            
            // Hydrater et valider
            $this->hydrateMachine($machine, $request);
            $errors = $this->validateMachine($machine, $request, $em);

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
        
        // Corriger les valeurs nulles
        if ($machine->getKilometrage() === null) {
            $machine->setKilometrage(0);
        }
        if ($machine->getKmLastVisite() === null) {
            $machine->setKmLastVisite(0);
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
            $errors = $this->validateMachine($machine, $request, $em);

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
        
        // Vérifier si la machine est utilisée dans des maintenances
        $maintenancesCount = $em->createQueryBuilder()
            ->select('COUNT(m.idMain)')
            ->from('App\Entity\Materiels\Maintenance', 'm')
            ->where('m.idM = :machineId')
            ->setParameter('machineId', $machine->getId())
            ->getQuery()
            ->getSingleScalarResult();
            
        if ($maintenancesCount > 0) {
            $this->addFlash('error', '⚠️ Impossible de supprimer cette machine car elle est associée à ' . $maintenancesCount . ' maintenance(s).');
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
       VALIDATION CÔTÉ SERVEUR (COMPLÈTE)
    ════════════════════════════════════════════ */
    private function validateMachine(Machine $machine, Request $request, EntityManagerInterface $em): array
    {
        $errors = [];

        // ─────────────────────────────────────────────────────────
        // 1. VALIDATION AGRICULTEUR
        // ─────────────────────────────────────────────────────────
        $cinAgriculteur = $request->request->get('agriculteur');
        if (empty($cinAgriculteur)) {
            $errors[] = '❌ Veuillez sélectionner un agriculteur.';
        } else {
            $agriculteur = $em->getRepository(User::class)->find($cinAgriculteur);
            if (!$agriculteur || $agriculteur->getRole() !== 2) {
                $errors[] = '❌ L\'agriculteur sélectionné n\'existe pas ou n\'a pas le bon rôle.';
            }
        }

        // ─────────────────────────────────────────────────────────
        // 2. VALIDATION NOM
        // ─────────────────────────────────────────────────────────
        $nom = trim($machine->getNom());
        if ($nom === '') {
            $errors[] = '❌ Le nom de la machine est obligatoire.';
        } elseif (mb_strlen($nom) < 2) {
            $errors[] = '❌ Le nom doit contenir au moins 2 caractères.';
        } elseif (mb_strlen($nom) > 100) {
            $errors[] = '❌ Le nom ne peut pas dépasser 100 caractères.';
        } elseif (!preg_match('/^[a-zA-Z0-9\s\-_\'àáâãäåçèéêëìíîïðòóôõöùúûüýÿ]+$/u', $nom)) {
            $errors[] = '❌ Le nom contient des caractères non autorisés.';
        }

        // ─────────────────────────────────────────────────────────
        // 3. VALIDATION MARQUE
        // ─────────────────────────────────────────────────────────
        $marque = trim($machine->getMarque());
        if ($marque === '') {
            $errors[] = '❌ La marque est obligatoire.';
        } elseif (mb_strlen($marque) < 2) {
            $errors[] = '❌ La marque doit contenir au moins 2 caractères.';
        } elseif (mb_strlen($marque) > 100) {
            $errors[] = '❌ La marque ne peut pas dépasser 100 caractères.';
        } elseif (!preg_match('/^[a-zA-Z0-9\s\-_\'àáâãäåçèéêëìíîïðòóôõöùúûüýÿ]+$/u', $marque)) {
            $errors[] = '❌ La marque contient des caractères non autorisés.';
        }

        // ─────────────────────────────────────────────────────────
        // 4. VALIDATION MODÈLE
        // ─────────────────────────────────────────────────────────
        $modele = trim($machine->getModele());
        if ($modele === '') {
            $errors[] = '❌ Le modèle est obligatoire.';
        } elseif (mb_strlen($modele) > 100) {
            $errors[] = '❌ Le modèle ne peut pas dépasser 100 caractères.';
        } elseif (mb_strlen($modele) < 1) {
            $errors[] = '❌ Le modèle doit contenir au moins 1 caractère.';
        }

        // ─────────────────────────────────────────────────────────
        // 5. VALIDATION NUMÉRO DE SÉRIE (optionnel)
        // ─────────────────────────────────────────────────────────
        $serie = trim($machine->getNumeroSerie());
        if ($serie !== '' && $serie !== null) {
            if (mb_strlen($serie) > 100) {
                $errors[] = '❌ Le numéro de série ne peut pas dépasser 100 caractères.';
            } elseif (!preg_match('/^[a-zA-Z0-9\-_\s]+$/', $serie)) {
                $errors[] = '❌ Le numéro de série contient des caractères non autorisés.';
            }
        }

        // ─────────────────────────────────────────────────────────
        // 6. VALIDATION ÉTAT
        // ─────────────────────────────────────────────────────────
        $etatsAutorises = ['Neuf', 'Bon', 'Occasion', 'En panne', 'En maintenance', 'Hors service'];
        $etat = trim($machine->getEtatM());
        if ($etat === '') {
            $errors[] = "❌ L'état de la machine est obligatoire.";
        } elseif (!in_array($etat, $etatsAutorises, true)) {
            $errors[] = "❌ L'état sélectionné est invalide. Valeurs autorisées : " . implode(', ', $etatsAutorises);
        }

        // ─────────────────────────────────────────────────────────
        // 7. VALIDATION KILOMÉTRAGE
        // ─────────────────────────────────────────────────────────
        $kilometrage = $machine->getKilometrage();
        if ($kilometrage === null) {
            $errors[] = '❌ Le kilométrage est obligatoire.';
        } elseif (!is_numeric($kilometrage)) {
            $errors[] = '❌ Le kilométrage doit être un nombre.';
        } elseif ($kilometrage < 0) {
            $errors[] = '❌ Le kilométrage ne peut pas être négatif.';
        } elseif ($kilometrage > 9999999) {
            $errors[] = '❌ Le kilométrage ne peut pas dépasser 9 999 999 km.';
        }

        // ─────────────────────────────────────────────────────────
        // 8. VALIDATION KILOMÉTRAGE DERNIÈRE VISITE
        // ─────────────────────────────────────────────────────────
        $kmLastVisite = $machine->getKmLastVisite();
        if ($kmLastVisite === null) {
            $errors[] = '❌ Le kilométrage de dernière visite est obligatoire.';
        } elseif (!is_numeric($kmLastVisite)) {
            $errors[] = '❌ Le kilométrage de dernière visite doit être un nombre.';
        } elseif ($kmLastVisite < 0) {
            $errors[] = '❌ Le kilométrage de dernière visite ne peut pas être négatif.';
        } elseif ($kmLastVisite > 9999999) {
            $errors[] = '❌ Le kilométrage de dernière visite ne peut pas dépasser 9 999 999 km.';
        }

        // Vérification cohérence kilométrages
        if ($kilometrage !== null && $kmLastVisite !== null) {
            if ($kmLastVisite > $kilometrage) {
                $errors[] = '❌ Le kilométrage de dernière visite ne peut pas être supérieur au kilométrage actuel.';
            }
        }

        // ─────────────────────────────────────────────────────────
        // 9. VALIDATION DATE D'ACHAT (optionnelle)
        // ─────────────────────────────────────────────────────────
        $dateAchat = $machine->getDateAchat();
        if ($dateAchat !== null) {
            $today = new \DateTime();
            if ($dateAchat > $today) {
                $errors[] = '❌ La date d\'achat ne peut pas être dans le futur.';
            }
            $minDate = (new \DateTime())->modify('-100 years');
            if ($dateAchat < $minDate) {
                $errors[] = '❌ La date d\'achat semble trop ancienne (plus de 100 ans).';
            }
        }

        // ─────────────────────────────────────────────────────────
        // 10. VALIDATION DATE DERNIÈRE VISITE (optionnelle)
        // ─────────────────────────────────────────────────────────
        $dateLastVisite = $machine->getDateLastVisite();
        if ($dateLastVisite !== null) {
            $today = new \DateTime();
            if ($dateLastVisite > $today) {
                $errors[] = '❌ La date de dernière visite ne peut pas être dans le futur.';
            }
            if ($dateAchat !== null && $dateLastVisite < $dateAchat) {
                $errors[] = '❌ La date de dernière visite ne peut pas être antérieure à la date d\'achat.';
            }
        }

        // ─────────────────────────────────────────────────────────
        // 11. VALIDATION PROCHAINE MAINTENANCE (optionnelle)
        // ─────────────────────────────────────────────────────────
        $prochaineMaintenance = $machine->getProchaineMaintenance();
        if ($prochaineMaintenance !== null) {
            $today = new \DateTime();
            if ($prochaineMaintenance < $today) {
                $errors[] = '❌ La date de prochaine maintenance ne peut pas être dans le passé.';
            }
            if ($dateLastVisite !== null && $prochaineMaintenance <= $dateLastVisite) {
                $errors[] = '❌ La date de prochaine maintenance doit être postérieure à la date de dernière visite.';
            }
            if ($dateAchat !== null && $prochaineMaintenance <= $dateAchat) {
                $errors[] = '❌ La date de prochaine maintenance doit être postérieure à la date d\'achat.';
            }
            // Alerte si trop éloignée (plus de 10 ans)
            $maxDate = (new \DateTime())->modify('+10 years');
            if ($prochaineMaintenance > $maxDate) {
                $errors[] = '⚠️ Attention: La date de prochaine maintenance est très éloignée (plus de 10 ans).';
            }
        }

        // ─────────────────────────────────────────────────────────
        // 12. VALIDATION DOUBLONS
        // ─────────────────────────────────────────────────────────
        $existingMachine = $em->getRepository(Machine::class)->findOneBy([
            'nom' => $nom,
            'marque' => $marque,
            'modele' => $modele
        ]);
        
        if ($existingMachine && $existingMachine->getId() !== $machine->getId()) {
            $errors[] = '⚠️ Une machine avec le même nom, marque et modèle existe déjà.';
        }

        // ─────────────────────────────────────────────────────────
        // 13. ALERTE KILOMÉTRAGE ÉLEVÉ
        // ─────────────────────────────────────────────────────────
        if ($kilometrage !== null && $kilometrage > 500000) {
            $errors[] = '⚠️ Attention: Le kilométrage est très élevé (> 500 000 km). Vérifiez l\'état de la machine.';
        }

        // ─────────────────────────────────────────────────────────
        // 14. ALERTE ÉCART KILOMÉTRAGE
        // ─────────────────────────────────────────────────────────
        if ($kilometrage !== null && $kmLastVisite !== null && ($kilometrage - $kmLastVisite) > 50000) {
            $errors[] = '⚠️ Attention: Plus de 50 000 km depuis la dernière visite. Une maintenance peut être nécessaire.';
        }

        return $errors;
    }

    /* ════════════════════════════════════════════
       RÉCUPÉRER LES AGRICULTEURS
    ════════════════════════════════════════════ */
    private function getAgriculteurs(EntityManagerInterface $em): array
    {
        return $em->getRepository(User::class)->findBy(['role' => 2]);
    }

    /* ════════════════════════════════════════════
       HYDRATATION AVEC NETTOYAGE
    ════════════════════════════════════════════ */
    private function hydrateMachine(Machine $machine, Request $request): void
    {
        // Nettoyage et assignation des champs texte
        $machine->setNom($this->sanitizeString($request->request->get('nom', '')));
        $machine->setMarque($this->sanitizeString($request->request->get('marque', '')));
        $machine->setModele($this->sanitizeString($request->request->get('modele', '')));
        
        $numeroSerie = $this->sanitizeString($request->request->get('numeroSerie', ''));
        $machine->setNumeroSerie($numeroSerie !== '' ? $numeroSerie : null);
        
        $machine->setEtatM($this->sanitizeString($request->request->get('etatM', '')));
        
        // Kilométrages (toujours des entiers, jamais null)
        $kilometrage = $request->request->get('kilometrage');
        $machine->setKilometrage($kilometrage !== '' && $kilometrage !== null ? (int)$kilometrage : 0);
        
        $kmLastVisite = $request->request->get('kmLastVisite');
        $machine->setKmLastVisite($kmLastVisite !== '' && $kmLastVisite !== null ? (int)$kmLastVisite : 0);
        
        // Dates avec validation
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

    /* ════════════════════════════════════════════
       NETTOYAGE DE CHAÎNES
    ════════════════════════════════════════════ */
    private function sanitizeString(?string $input): string
    {
        if ($input === null) {
            return '';
        }
        
        // Supprimer les espaces au début et à la fin
        $cleaned = trim($input);
        
        // Supprimer les caractères de contrôle
        $cleaned = preg_replace('/[\x00-\x1F\x7F]/', '', $cleaned);
        
        // Éviter les injections HTML
        $cleaned = htmlspecialchars($cleaned, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        return $cleaned;
    }

    /* ════════════════════════════════════════════
       SÉRIALISATION POUR DATATABLES
    ════════════════════════════════════════════ */
    private function serializeMachines(array $machines): string
    {
        $data = array_map(function (Machine $m) {
            return [
                'id' => $m->getId(),
                'nom' => $this->sanitizeString($m->getNom()),
                'marque' => $this->sanitizeString($m->getMarque()),
                'modele' => $this->sanitizeString($m->getModele()),
                'serie' => $m->getNumeroSerie() ? $this->sanitizeString($m->getNumeroSerie()) : null,
                'etat' => $m->getEtatM(),
                'kilometrage' => $m->getKilometrage(),
                'kmLastVisite' => $m->getKmLastVisite(),
                'dateAchat' => $m->getDateAchat() ? $m->getDateAchat()->format('Y-m-d') : null,
                'dateLastVisite' => $m->getDateLastVisite() ? $m->getDateLastVisite()->format('Y-m-d') : null,
                'prochaineMaintenance' => $m->getProchaineMaintenance() ? $m->getProchaineMaintenance()->format('Y-m-d') : null,
                'cinAgriculteur' => $m->getCinAgriculteur(),
                'nomAgriculteur' => $this->sanitizeString($m->getNomAgriculteur()),
                'csrf' => 'delete' . $m->getId(),
            ];
        }, $machines);

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    }
}