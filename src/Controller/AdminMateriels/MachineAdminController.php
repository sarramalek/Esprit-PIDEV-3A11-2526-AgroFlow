<?php

namespace App\Controller\AdminMateriels;

use App\Entity\Materiels\Machine;
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

        if ($request->isMethod('POST')) {
            $this->hydrateMachine($machine, $request);
            $errors = $this->validateMachine($machine, $request);

            if (!empty($errors)) {
                foreach ($errors as $err) {
                    $this->addFlash('error', $err);
                }
                return $this->render('admin/machines/new.html.twig', [
                    'machine' => $machine,
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
        if ($request->isMethod('POST')) {
            $this->hydrateMachine($machine, $request);
            $errors = $this->validateMachine($machine, $request);

            if (!empty($errors)) {
                foreach ($errors as $err) {
                    $this->addFlash('error', $err);
                }
                return $this->render('admin/machines/edit.html.twig', [
                    'machine' => $machine,
                    'errors'  => $errors,
                ]);
            }

            $em->flush();

            $this->addFlash('success', '✓ Machine « ' . $machine->getNom() . ' » modifiée avec succès !');
            return $this->redirectToRoute('admin_machines_index');
        }

        return $this->render('admin/machines/edit.html.twig', [
            'machine' => $machine,
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
       VALIDATION CÔTÉ SERVEUR
    ════════════════════════════════════════════ */
    private function validateMachine(Machine $machine, Request $request): array
    {
        $errors = [];

        // Nom
        $nom = trim($machine->getNom());
        if ($nom === '') {
            $errors[] = 'Le nom de la machine est obligatoire.';
        } elseif (mb_strlen($nom) < 3) {
            $errors[] = 'Le nom doit contenir au moins 3 caractères.';
        } elseif (mb_strlen($nom) > 100) {
            $errors[] = 'Le nom ne peut pas dépasser 100 caractères.';
        }

        // Marque
        $marque = trim($machine->getMarque());
        if ($marque === '') {
            $errors[] = 'La marque est obligatoire.';
        } elseif (mb_strlen($marque) < 2) {
            $errors[] = 'La marque doit contenir au moins 2 caractères.';
        } elseif (mb_strlen($marque) > 80) {
            $errors[] = 'La marque ne peut pas dépasser 80 caractères.';
        }

        // Modèle
        $modele = trim($machine->getModele());
        if ($modele === '') {
            $errors[] = 'Le modèle est obligatoire.';
        } elseif (mb_strlen($modele) > 80) {
            $errors[] = 'Le modèle ne peut pas dépasser 80 caractères.';
        }

        // N° de série
        $serie = trim($machine->getNumeroSerie());
        if ($serie === '') {
            $errors[] = 'Le numéro de série est obligatoire.';
        } elseif (mb_strlen($serie) < 3) {
            $errors[] = 'Le numéro de série doit contenir au moins 3 caractères.';
        } elseif (mb_strlen($serie) > 60) {
            $errors[] = 'Le numéro de série ne peut pas dépasser 60 caractères.';
        } elseif (!preg_match('/^[A-Za-z0-9\-_]+$/', $serie)) {
            $errors[] = 'Le numéro de série ne doit contenir que des lettres, chiffres et tirets.';
        }

        // État
        $etatsAutorises = ['Neuf', 'Bon', 'Actif', 'Occasion', 'En panne', 'Hors service'];
        $etat = trim($machine->getEtatM());
        if ($etat === '') {
            $errors[] = "L'état de la machine est obligatoire.";
        } elseif (!in_array($etat, $etatsAutorises, true)) {
            $errors[] = "L'état sélectionné est invalide.";
        }

        // Date d'achat (optionnel)
        $dateStr = $request->request->get('dateAchat');
        if ($dateStr !== null && $dateStr !== '') {
            try {
                $date  = new \DateTime($dateStr);
                $today = new \DateTime('today');
                if ($date > $today) {
                    $errors[] = "La date d'achat ne peut pas être dans le futur.";
                }
            } catch (\Exception) {
                $errors[] = "Le format de la date d'achat est invalide.";
            }
        }

        return $errors;
    }

    /* ════════════════════════════════════════════
       HELPERS PRIVÉS
    ════════════════════════════════════════════ */
    private function hydrateMachine(Machine $machine, Request $request): void
    {
        $machine->setNom(trim($request->request->get('nom', '')));
        $machine->setMarque(trim($request->request->get('marque', '')));
        $machine->setModele(trim($request->request->get('modele', '')));
        $machine->setNumeroSerie(trim($request->request->get('numeroSerie', '')));
        $machine->setEtatM(trim($request->request->get('etatM', '')));

        $dateStr = $request->request->get('dateAchat');
        if ($dateStr && $dateStr !== '') {
            try {
                $machine->setDateAchat(new \DateTime($dateStr));
            } catch (\Exception) {
                $machine->setDateAchat(null);
            }
        } else {
            $machine->setDateAchat(null);
        }
    }

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
                'date'   => $m->getDateAchat()
                            ? $m->getDateAchat()->format('Y-m-d')
                            : null,
                'csrf'   => 'delete' . $m->getId(),
            ];
        }, $machines);

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
    }
}
