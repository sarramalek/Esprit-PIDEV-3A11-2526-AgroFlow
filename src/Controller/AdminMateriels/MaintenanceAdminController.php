<?php

namespace App\Controller\AdminMateriels;

use App\Entity\Materiels\Maintenance;
use App\Entity\Materiels\Machine;
use App\Repository\Materiels\MaintenanceRepository;
use App\Repository\Materiels\MachineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/admin/materiels/maintenances', name: 'admin_maintenances_')]
class MaintenanceAdminController extends AbstractController
{
    // ─────────────────────────────────────────────────────────────────────────
    // INDEX - Liste des maintenances
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        MaintenanceRepository $repo,
        MachineRepository $machineRepo
    ): Response {
        $search     = $request->query->get('search', '');
        $type       = $request->query->get('type', '');
        $sort       = $request->query->get('sort', 'dateMain');
        $dir        = $request->query->get('dir', 'DESC');
        $coutFilter = $request->query->get('coutFilter', '');
        $idM        = $request->query->get('idM', '');

        if ($coutFilter === 'asc') {
            $sort = 'cout'; $dir = 'ASC';
        } elseif ($coutFilter === 'desc') {
            $sort = 'cout'; $dir = 'DESC';
        }

        $maintenances = $repo->searchWithMaterielName($search, $type, $sort, $dir, $idM);
        $types        = array_column($repo->countByTypePanne(), 'type');
        $totalCout    = $repo->getTotalCout();
        $machines     = $machineRepo->findAll();

        return $this->render('admins/maintenances/index.html.twig', [
            'maintenances' => $maintenances,
            'types'        => $types,
            'totalCout'    => $totalCout,
            'search'       => $search,
            'selectedType' => $type,
            'selectedIdM'  => $idM,
            'sort'         => $sort,
            'dir'          => $dir,
            'coutFilter'   => $coutFilter,
            'machines'     => $machines,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STATISTIQUES
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/statistiques/bar', name: 'stats_bar', methods: ['GET'])]
    public function statsBar(MaintenanceRepository $repo): Response
    {
        $byType    = $repo->countByTypePanne();
        $coutMonth = $repo->getCoutByMonth();
        $totalCout = $repo->getTotalCout();
        $total     = array_sum(array_column($byType, 'total'));

        return $this->render('admins/maintenances/stats_bar.html.twig', [
            'byType'    => $byType,
            'coutMonth' => $coutMonth,
            'totalCout' => $totalCout,
            'total'     => $total,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EXPORT PDF
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/export/pdf', name: 'export_pdf', methods: ['GET'])]
    public function exportPdf(MaintenanceRepository $repo): Response
    {
        $maintenances = $repo->findAllOrderedByDate();
        $totalCout    = $repo->getTotalCout();
        $byType       = $repo->countByTypePanne();

        $html = $this->renderView('admins/maintenances/pdf.html.twig', [
            'maintenances' => $maintenances,
            'totalCout'    => $totalCout,
            'byType'       => $byType,
            'date'         => new \DateTime(),
        ]);

        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // NEW - Créer une nouvelle maintenance (AVEC CONTROLE DE SAISIE PHP)
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        MachineRepository $machineRepo
    ): Response {
        $maintenance = new Maintenance();
        $machines = $machineRepo->findAll();
        $errors = [];

        // Définir les valeurs par défaut
        $maintenance->setStatut('planifie');
        $maintenance->setPriorite('moyenne');

        if ($request->isMethod('POST')) {
            // =============================================================
            // CONTROLE DE SAISIE PHP - Validation des champs
            // =============================================================
            
            // 1. Validation du type de panne
            $typePanne = trim($request->request->get('typePanne', ''));
            if (empty($typePanne)) {
                $errors['typePanne'] = 'Le type de panne est obligatoire.';
            } else {
                $maintenance->setTypePanne($typePanne);
            }

            // 2. Validation du coût
            $cout = $request->request->get('cout');
            if ($cout === null || $cout === '') {
                $errors['cout'] = 'Le coût est obligatoire.';
            } else {
                $coutFloat = (float) $cout;
                if ($coutFloat <= 0) {
                    $errors['cout'] = 'Le coût doit être supérieur à 0.';
                } elseif ($coutFloat > 999999.99) {
                    $errors['cout'] = 'Le coût ne peut pas dépasser 999 999,99 DT.';
                } else {
                    $maintenance->setCout($coutFloat);
                }
            }

            // 3. Validation de la date
            $dateStr = $request->request->get('dateMain');
            if (empty($dateStr)) {
                $errors['dateMain'] = 'La date de maintenance est obligatoire.';
            } else {
                try {
                    $date = new \DateTime($dateStr);
                    $today = new \DateTime('today');
                    if ($date > $today) {
                        $errors['dateMain'] = 'La date ne peut pas être dans le futur.';
                    } else {
                        $maintenance->setDateMain($date);
                    }
                } catch (\Exception $e) {
                    $errors['dateMain'] = 'Format de date invalide.';
                }
            }

            // 4. Validation de la description (optionnelle)
            $description = trim($request->request->get('description', ''));
            if (!empty($description)) {
                if (strlen($description) > 1000) {
                    $errors['description'] = 'La description ne peut pas dépasser 1000 caractères.';
                } elseif (preg_match('/[<>{}]/', $description)) {
                    $errors['description'] = 'La description ne doit pas contenir les caractères < > { }.';
                } else {
                    $maintenance->setDescription($description);
                }
            }

            // 5. Validation de la recommandation (optionnelle)
            $recommandation = trim($request->request->get('recommandation', ''));
            if (!empty($recommandation)) {
                if (strlen($recommandation) > 2000) {
                    $errors['recommandation'] = 'La recommandation ne peut pas dépasser 2000 caractères.';
                } else {
                    $maintenance->setRecommandation($recommandation);
                }
            }

            // 6. Validation du statut
            $statut = $request->request->get('statut', 'planifie');
            $allowedStatuts = ['planifie', 'en_cours', 'termine'];
            if (!in_array($statut, $allowedStatuts)) {
                $errors['statut'] = 'Statut invalide.';
            } else {
                $maintenance->setStatut($statut);
            }

            // 7. Validation de la priorité
            $priorite = $request->request->get('priorite', 'moyenne');
            $allowedPriorites = ['faible', 'moyenne', 'haute', 'urgente'];
            if (!in_array($priorite, $allowedPriorites)) {
                $errors['priorite'] = 'Priorité invalide.';
            } else {
                $maintenance->setPriorite($priorite);
            }

            // 8. Validation du kilométrage (optionnel)
            $kilometrage = $request->request->get('kilometrage');
            if (!empty($kilometrage)) {
                $kmInt = (int) $kilometrage;
                if ($kmInt < 0) {
                    $errors['kilometrage'] = 'Le kilométrage doit être positif ou nul.';
                } elseif ($kmInt > 9999999) {
                    $errors['kilometrage'] = 'Kilométrage trop élevé.';
                } else {
                    $maintenance->setKilometrage($kmInt);
                }
            }

            // 9. Validation de la machine (optionnelle)
            $idM = $request->request->get('idM');
            if (!empty($idM)) {
                $idMInt = (int) $idM;
                $machine = $machineRepo->find($idMInt);
                if ($machine) {
                    $maintenance->setIdM($idMInt);
                    $maintenance->setNom($machine->getNom());
                } else {
                    $errors['idM'] = 'La machine sélectionnée n\'existe pas.';
                }
            } else {
                $maintenance->setIdM(null);
                $maintenance->setNom(null);
            }

            // =============================================================
            // SI AUCUNE ERREUR, ON SAUVEGARDE
            // =============================================================
            if (empty($errors)) {
                $em->persist($maintenance);
                $em->flush();
                $this->addFlash('success', 'Maintenance ajoutée avec succès.');
                return $this->redirectToRoute('admin_maintenances_index');
            } else {
                // Afficher toutes les erreurs
                foreach ($errors as $field => $error) {
                    $this->addFlash('error', $error);
                }
            }
        }

        return $this->render('admins/maintenances/new.html.twig', [
            'maintenance' => $maintenance,
            'machines'    => $machines,
            'errors'      => $errors,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SHOW - Afficher une maintenance
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, MaintenanceRepository $repo): Response
    {
        $maintenance = $repo->findOneWithMaterielName($id);
        if (!$maintenance) {
            $this->addFlash('error', 'Maintenance introuvable.');
            return $this->redirectToRoute('admin_maintenances_index');
        }

        return $this->render('admins/maintenances/show.html.twig', [
            'maintenance' => $maintenance,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EDIT - Modifier une maintenance (AVEC CONTROLE DE SAISIE PHP)
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(
        int $id,
        Request $request,
        MaintenanceRepository $repo,
        MachineRepository $machineRepo,
        EntityManagerInterface $em
    ): Response {
        $maintenance = $repo->find($id);
        if (!$maintenance) {
            $this->addFlash('error', 'Maintenance introuvable.');
            return $this->redirectToRoute('admin_maintenances_index');
        }

        $machines = $machineRepo->findAll();
        $errors = [];

        if ($request->isMethod('POST')) {
            // =============================================================
            // CONTROLE DE SAISIE PHP - Validation des champs
            // =============================================================
            
            // 1. Validation du type de panne
            $typePanne = trim($request->request->get('typePanne', ''));
            if (empty($typePanne)) {
                $errors['typePanne'] = 'Le type de panne est obligatoire.';
            } else {
                $maintenance->setTypePanne($typePanne);
            }

            // 2. Validation du coût
            $cout = $request->request->get('cout');
            if ($cout === null || $cout === '') {
                $errors['cout'] = 'Le coût est obligatoire.';
            } else {
                $coutFloat = (float) $cout;
                if ($coutFloat <= 0) {
                    $errors['cout'] = 'Le coût doit être supérieur à 0.';
                } elseif ($coutFloat > 999999.99) {
                    $errors['cout'] = 'Le coût ne peut pas dépasser 999 999,99 DT.';
                } else {
                    $maintenance->setCout($coutFloat);
                }
            }

            // 3. Validation de la date
            $dateStr = $request->request->get('dateMain');
            if (empty($dateStr)) {
                $errors['dateMain'] = 'La date de maintenance est obligatoire.';
            } else {
                try {
                    $date = new \DateTime($dateStr);
                    $today = new \DateTime('today');
                    if ($date > $today) {
                        $errors['dateMain'] = 'La date ne peut pas être dans le futur.';
                    } else {
                        $maintenance->setDateMain($date);
                    }
                } catch (\Exception $e) {
                    $errors['dateMain'] = 'Format de date invalide.';
                }
            }

            // 4. Validation de la description (optionnelle)
            $description = trim($request->request->get('description', ''));
            if (!empty($description)) {
                if (strlen($description) > 1000) {
                    $errors['description'] = 'La description ne peut pas dépasser 1000 caractères.';
                } elseif (preg_match('/[<>{}]/', $description)) {
                    $errors['description'] = 'La description ne doit pas contenir les caractères < > { }.';
                } else {
                    $maintenance->setDescription($description);
                }
            } else {
                $maintenance->setDescription(null);
            }

            // 5. Validation de la recommandation (optionnelle)
            $recommandation = trim($request->request->get('recommandation', ''));
            if (!empty($recommandation)) {
                if (strlen($recommandation) > 2000) {
                    $errors['recommandation'] = 'La recommandation ne peut pas dépasser 2000 caractères.';
                } else {
                    $maintenance->setRecommandation($recommandation);
                }
            } else {
                $maintenance->setRecommandation(null);
            }

            // 6. Validation du statut
            $statut = $request->request->get('statut', 'planifie');
            $allowedStatuts = ['planifie', 'en_cours', 'termine'];
            if (!in_array($statut, $allowedStatuts)) {
                $errors['statut'] = 'Statut invalide.';
            } else {
                $maintenance->setStatut($statut);
            }

            // 7. Validation de la priorité
            $priorite = $request->request->get('priorite', 'moyenne');
            $allowedPriorites = ['faible', 'moyenne', 'haute', 'urgente'];
            if (!in_array($priorite, $allowedPriorites)) {
                $errors['priorite'] = 'Priorité invalide.';
            } else {
                $maintenance->setPriorite($priorite);
            }

            // 8. Validation du kilométrage (optionnel)
            $kilometrage = $request->request->get('kilometrage');
            if (!empty($kilometrage)) {
                $kmInt = (int) $kilometrage;
                if ($kmInt < 0) {
                    $errors['kilometrage'] = 'Le kilométrage doit être positif ou nul.';
                } elseif ($kmInt > 9999999) {
                    $errors['kilometrage'] = 'Kilométrage trop élevé.';
                } else {
                    $maintenance->setKilometrage($kmInt);
                }
            } else {
                $maintenance->setKilometrage(null);
            }

            // 9. Validation de la machine (optionnelle)
            $idM = $request->request->get('idM');
            if (!empty($idM)) {
                $idMInt = (int) $idM;
                $machine = $machineRepo->find($idMInt);
                if ($machine) {
                    $maintenance->setIdM($idMInt);
                    $maintenance->setNom($machine->getNom());
                } else {
                    $errors['idM'] = 'La machine sélectionnée n\'existe pas.';
                }
            } else {
                $maintenance->setIdM(null);
                $maintenance->setNom(null);
            }

            // =============================================================
            // SI AUCUNE ERREUR, ON SAUVEGARDE
            // =============================================================
            if (empty($errors)) {
                $em->flush();
                $this->addFlash('success', 'Maintenance mise à jour avec succès.');
                return $this->redirectToRoute('admin_maintenances_index');
            } else {
                // Afficher toutes les erreurs
                foreach ($errors as $field => $error) {
                    $this->addFlash('error', $error);
                }
            }
        }

        return $this->render('admins/maintenances/edit.html.twig', [
            'maintenance' => $maintenance,
            'machines'    => $machines,
            'errors'      => $errors,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DELETE - Supprimer une maintenance
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(
        int $id,
        Request $request,
        MaintenanceRepository $repo,
        EntityManagerInterface $em
    ): Response {
        $maintenance = $repo->find($id);
        if (!$maintenance) {
            $this->addFlash('error', 'Maintenance introuvable.');
            return $this->redirectToRoute('admin_maintenances_index');
        }

        if ($this->isCsrfTokenValid('delete_maintenance_' . $id, $request->request->get('_token'))) {
            $em->remove($maintenance);
            $em->flush();
            $this->addFlash('success', 'Maintenance supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('admin_maintenances_index');
    }
}