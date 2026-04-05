<?php

namespace App\Controller\User;

use App\Entity\User\Tache;
use App\Repository\User\TacheRepository;
use App\Repository\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/agriculteur/tache')]
class TacheFrontController extends AbstractController
{
    // ==================== FRONT OFFICE ====================
    #[Route('/front', name: 'app_tache_front', methods: ['GET'])]
    public function front(UserRepository $userRepo): Response
    {
        return $this->render('User/FrontTache.html.twig', [
            'ouvriers' => $userRepo->findAllOuvriers(),
        ]);
    }

    // ==================== AJAX : taches par ouvrier ====================
    #[Route('/front/agriculteur/{cin}', name: 'app_tache_by_ouvrier', methods: ['GET'])]
    public function tachesByOuvrier(
        int $cin,
        UserRepository $userRepo,
        TacheRepository $tacheRepo,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        $ouvrier = $userRepo->findByCin($cin);

        if (!$ouvrier) {
            return $this->json(['error' => 'Ouvrier non trouvé'], 404);
        }

        $taches = $tacheRepo->findByAssignee($ouvrier);

        $data = array_map(fn($t) => [
            'id'          => $t->getIdTache(),
            'nom'         => $t->getNomTache(),
            'description' => $t->getDescription(),
            'etat'        => $t->getEtat(),
            'priorite'    => $t->getPriorite(),
            'echeance'    => $t->getDateEcheancee()?->format('d/m/Y'),
            'enRetard'    => $t->getDateEcheancee()
                             && $t->getDateEcheancee() < new \DateTime()
                             && $t->getEtat() !== 'terminée',
            'csrfToken'   => $csrfTokenManager->getToken('delete' . $t->getIdTache())->getValue(),
        ], $taches);

        return $this->json($data);
    }

    // ==================== AUTO-ASSIGNATION ====================
    #[Route('/auto-assigner', name: 'app_tache_auto_assigner', methods: ['POST'])]
    public function autoAssigner(
        Request $request,
        TacheRepository $tacheRepo,
        UserRepository $userRepo,
        EntityManagerInterface $em
    ): Response {
        // Récupérer les tâches non assignées ou à faire
        $tachesNonAssignees = $tacheRepo->findTachesAAssigner();

        if (empty($tachesNonAssignees)) {
            return $this->json([
                'success' => false,
                'message' => 'Aucune tâche à assigner.',
            ]);
        }

        $ouvriers  = $userRepo->findAllOuvriers();

        if (empty($ouvriers)) {
            return $this->json([
                'success' => false,
                'message' => 'Aucun ouvrier disponible.',
            ]);
        }

        $assignations = [];

        foreach ($tachesNonAssignees as $tache) {
            $debut = new \DateTime();
            $fin   = $tache->getDateEcheancee() ?? (new \DateTime())->modify('+7 days');

            // Mélanger les ouvriers aléatoirement
            $ouvriersMelanges = $ouvriers;
            shuffle($ouvriersMelanges);

            $ouvriersAssignes = false;

            foreach ($ouvriersMelanges as $ouvrier) {
                // Vérifier la disponibilité : pas de tâche active sur la même période
                $tachesConflict = $tacheRepo->findTachesConflict(
                    $ouvrier,
                    $debut,
                    $fin
                );

                if (empty($tachesConflict)) {
                    $tache->setAssignee($ouvrier);
                    $em->flush();

                    $assignations[] = [
                        'tache'   => $tache->getNomTache(),
                        'ouvrier' => $ouvrier->getPrenom() . ' ' . $ouvrier->getNom(),
                    ];

                    $ouvriersAssignes = true;
                    break;
                }
            }

            if (!$ouvriersAssignes) {
                $assignations[] = [
                    'tache'   => $tache->getNomTache(),
                    'ouvrier' => null,
                    'message' => 'Aucun ouvrier disponible pour cette période',
                ];
            }
        }

        return $this->json([
            'success'      => true,
            'assignations' => $assignations,
            'total'        => count($assignations),
        ]);
    }
}