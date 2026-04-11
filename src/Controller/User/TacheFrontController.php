<?php

namespace App\Controller\User;

use App\Entity\User\Tache;
use App\Repository\User\TacheRepository;
use App\Repository\Terrain\TerrainRepository;
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

    /**
     * Page principale : liste les terrains de l'agriculteur connecté
     * avec leurs ouvriers assignés.
     */
    #[Route('/front', name: 'app_tache_front', methods: ['GET'])]
    public function front(
        TerrainRepository $terrainRepo,
        UserRepository $userRepo
    ): Response {
        /** @var \App\Entity\User\User $agriculteur */
        $agriculteur = $this->getUser();
        $cinAgriculteur = $agriculteur->getCin();

        // Terrains de l'agriculteur avec leurs ouvriers (eager load)
        $terrains = $terrainRepo->findByAgriculteurWithOuvriers($cinAgriculteur);

        // Ouvriers disponibles (sans terrain) pour le formulaire d'assignation
        $ouvriersDispo = $userRepo->findOuvriersDisponibles();

        return $this->render('User/FrontTache.html.twig', [
            'terrains'       => $terrains,
            'ouvriersDispo'  => $ouvriersDispo,
        ]);
    }

    // ==================== AJAX : tâches par ouvrier ====================

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

    // ==================== AJAX : ouvriers d'un terrain ====================

    #[Route('/terrain/{idTerrain}/ouvriers', name: 'app_ouvriers_by_terrain', methods: ['GET'])]
    public function ouvriersByTerrain(
        int $idTerrain,
        UserRepository $userRepo,
        TerrainRepository $terrainRepo
    ): Response {
        $terrain = $terrainRepo->find($idTerrain);

        if (!$terrain) {
            return $this->json(['error' => 'Terrain non trouvé'], 404);
        }

        // Vérification : ce terrain appartient bien à l'agriculteur connecté
        /** @var \App\Entity\User\User $agriculteur */
        $agriculteur = $this->getUser();
        if ($terrain->getCin() !== $agriculteur->getCin()) {
            return $this->json(['error' => 'Accès refusé'], 403);
        }

        $ouvriers = $userRepo->findOuvriersByTerrain($idTerrain);

        $data = array_map(fn($o) => [
            'cin'     => $o->getCin(),
            'nom'     => $o->getNom(),
            'prenom'  => $o->getPrenom(),
            'tel'     => $o->getTel(),
            'email'   => $o->getEmail(),
            'terrain' => $o->getTerrain()?->getNomTerrain(),
        ], $ouvriers);

        return $this->json($data);
    }

    // ==================== ASSIGNER UN OUVRIER À UN TERRAIN ====================

    #[Route('/terrain/{idTerrain}/assigner-ouvrier', name: 'app_assigner_ouvrier_terrain', methods: ['POST'])]
    public function assignerOuvrierTerrain(
        int $idTerrain,
        Request $request,
        UserRepository $userRepo,
        TerrainRepository $terrainRepo,
        EntityManagerInterface $em
    ): Response {
        $terrain = $terrainRepo->find($idTerrain);

        if (!$terrain) {
            return $this->json(['error' => 'Terrain non trouvé'], 404);
        }

        // Vérification propriété du terrain
        /** @var \App\Entity\User\User $agriculteur */
        $agriculteur = $this->getUser();
        if ($terrain->getCin() !== $agriculteur->getCin()) {
            return $this->json(['error' => 'Accès refusé'], 403);
        }

        $cinOuvrier = (int) $request->request->get('cin_ouvrier');
        $ouvrier    = $userRepo->findByCin($cinOuvrier);

        if (!$ouvrier || $ouvrier->getRole() !== 1) {
            return $this->json(['error' => 'Ouvrier invalide'], 400);
        }

        // Désassigner de l'ancien terrain si nécessaire
        $ancienTerrain = $ouvrier->getTerrain();
        if ($ancienTerrain && $ancienTerrain->getIdTerrain() !== $idTerrain) {
            $ancienTerrain->removeOuvrier($ouvrier);
        }

        // Assigner au nouveau terrain
        $ouvrier->setTerrain($terrain);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => sprintf(
                '%s %s a été assigné(e) au terrain "%s".',
                $ouvrier->getPrenom(),
                $ouvrier->getNom(),
                $terrain->getNomTerrain()
            ),
        ]);
    }

    // ==================== DÉSASSIGNER UN OUVRIER D'UN TERRAIN ====================

    #[Route('/ouvrier/{cin}/desassigner', name: 'app_desassigner_ouvrier', methods: ['POST'])]
    public function desassignerOuvrier(
        int $cin,
        UserRepository $userRepo,
        TerrainRepository $terrainRepo,
        EntityManagerInterface $em
    ): Response {
        $ouvrier = $userRepo->findByCin($cin);

        if (!$ouvrier) {
            return $this->json(['error' => 'Ouvrier non trouvé'], 404);
        }

        $terrain = $ouvrier->getTerrain();

        if (!$terrain) {
            return $this->json(['error' => 'Cet ouvrier n\'est pas assigné à un terrain'], 400);
        }

        // Vérification propriété du terrain
        /** @var \App\Entity\User\User $agriculteur */
        $agriculteur = $this->getUser();
        if ($terrain->getCin() !== $agriculteur->getCin()) {
            return $this->json(['error' => 'Accès refusé'], 403);
        }

        $ouvrier->setTerrain(null);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => sprintf(
                '%s %s a été désassigné(e) du terrain "%s".',
                $ouvrier->getPrenom(),
                $ouvrier->getNom(),
                $terrain->getNomTerrain()
            ),
        ]);
    }

    // ==================== AUTO-ASSIGNATION (corrigée) ====================

    #[Route('/auto-assigner', name: 'app_tache_auto_assigner', methods: ['POST'])]
    public function autoAssigner(
        Request $request,
        TacheRepository $tacheRepo,
        UserRepository $userRepo,
        TerrainRepository $terrainRepo,
        EntityManagerInterface $em
    ): Response {
        /** @var \App\Entity\User\User $agriculteur */
        $agriculteur = $this->getUser();

        // Récupérer les terrains de l'agriculteur
        $terrains = $terrainRepo->findByAgriculteur($agriculteur->getCin());

        if (empty($terrains)) {
            return $this->json([
                'success' => false,
                'message' => 'Vous n\'avez aucun terrain enregistré.',
            ]);
        }

        // Récupérer les tâches non assignées
        $tachesNonAssignees = $tacheRepo->findTachesAAssigner();

        if (empty($tachesNonAssignees)) {
            return $this->json([
                'success' => false,
                'message' => 'Aucune tâche à assigner.',
            ]);
        }

        $assignations = [];

        foreach ($tachesNonAssignees as $tache) {
            $debut = new \DateTime();
            $fin   = $tache->getDateEcheancee() ?? (new \DateTime())->modify('+7 days');

            // Chercher un ouvrier disponible parmi les terrains de l'agriculteur
            $ouvrierAssigne = false;

            foreach ($terrains as $terrain) {
                $ouvriers = $userRepo->findOuvriersByTerrain($terrain->getIdTerrain());
                shuffle($ouvriers);

                foreach ($ouvriers as $ouvrier) {
                    $tachesConflict = $tacheRepo->findTachesConflict($ouvrier, $debut, $fin);

                    if (empty($tachesConflict)) {
                        $tache->setAssignee($ouvrier);
                        $em->flush();

                        $assignations[] = [
                            'tache'   => $tache->getNomTache(),
                            'ouvrier' => $ouvrier->getPrenom() . ' ' . $ouvrier->getNom(),
                            'terrain' => $terrain->getNomTerrain(),
                        ];

                        $ouvrierAssigne = true;
                        break 2; // sortir des deux boucles
                    }
                }
            }

            if (!$ouvrierAssigne) {
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