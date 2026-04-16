<?php

namespace App\Controller\User;

use App\Repository\User\TacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ouvrier', name: 'ouvrier_')]
#[IsGranted('ROLE_OUVRIER')]
class OuvrierController extends AbstractController
{
    // ── Home ─────────────────────────────────────────────────────────────────
    #[Route('/', name: 'home')]
    public function home(TacheRepository $tacheRepo): Response
    {
        /** @var \App\Entity\User\User $user */
        $user = $this->getUser();

        $taches   = $tacheRepo->findByAssignee($user);
        $enCours  = array_filter($taches, fn($t) => $t->getEtat() === 'en cours');
        $recentes = array_slice($taches, 0, 5);

        $tachesData = array_map(fn($t) => [
            'id'          => $t->getIdTache(),
            'titre'       => $t->getNomTache(),
            'description' => $t->getDescription(),
            'statut'      => $this->mapStatut($t->getEtat()),
            'priorite'    => $t->getPriorite(),
            'dateDebut'   => null,
            'dateFin'     => $t->getDateEcheancee(),
        ], $recentes);

        return $this->render('home_ouvrier.html.twig', [
            'taches_total'         => count($taches),
            'taches_en_cours'      => count($enCours),
            'participations_total' => 0,
            'taches_recentes'      => $tachesData,
            'evenements_avenir'    => [],
        ]);
    }

    // ── Tâches ───────────────────────────────────────────────────────────────
    #[Route('/taches', name: 'taches')]
    public function taches(TacheRepository $tacheRepo): Response
    {
        /** @var \App\Entity\User\User $user */
        $user   = $this->getUser();
        $taches = $tacheRepo->findByAssignee($user);

        $tachesData = array_map(fn($t) => [
            'id'          => $t->getIdTache(),
            'titre'       => $t->getNomTache(),
            'description' => $t->getDescription(),
            'statut'      => $this->mapStatut($t->getEtat()),
            'priorite'    => $t->getPriorite(),
            'dateDebut'   => null,
            'dateFin'     => $t->getDateEcheancee(),
        ], $taches);

        return $this->render('User/ouvrier_tache.html.twig', [
            'taches' => $tachesData,
        ]);
    }

    // ── Changer statut (AJAX) ─────────────────────────────────────────────────
    #[Route('/tache/{id}/statut/{statut}', name: 'tache_statut')]
    public function changerStatut(
        int $id,
        string $statut,
        TacheRepository $tacheRepo,
        EntityManagerInterface $em
    ): Response {
        $tache = $tacheRepo->find($id);

        if (!$tache) {
            return $this->json(['success' => false, 'message' => 'Tâche non trouvée'], 404);
        }

        // Vérifier que la tâche appartient bien à l'ouvrier connecté
        /** @var \App\Entity\User\User $user */
        $user = $this->getUser();
        if ($tache->getAssignee()?->getCin() !== $user->getCin()) {
            return $this->json(['success' => false, 'message' => 'Accès refusé'], 403);
        }

        // Normaliser l'état actuel depuis la DB
        $statutActuel = $this->mapStatut($tache->getEtat());

        // Transitions autorisées uniquement dans un sens (valeurs normalisées)
        $transitionsAutorisees = [
            'a_faire'  => ['en_cours'],
            'en_cours' => ['terminee'],
        ];

        if (!in_array($statut, $transitionsAutorisees[$statutActuel] ?? [])) {
            return $this->json(['success' => false, 'message' => 'Transition non autorisée'], 400);
        }

        // Convertir le statut normalisé vers la valeur DB
        $etat = match($statut) {
            'en_cours' => 'en cours',
            'terminee' => 'terminée',
            'a_faire'  => 'à faire',
            default    => null,
        };

        if (!$etat) {
            return $this->json(['success' => false, 'message' => 'Statut invalide'], 400);
        }

        $tache->setEtat($etat);
        $em->flush();

        return $this->json(['success' => true]);
    }

    // ── Événements ───────────────────────────────────────────────────────────
    #[Route('/evenements', name: 'evenements')]
    public function evenements(): Response
    {
        return $this->render('Events/ouvrier_evenements.html.twig', [
            'evenements' => [],
        ]);
    }

    // ── Helper : map etat DB → statut normalisé ───────────────────────────────
    private function mapStatut(string $etat): string
    {
        return match($etat) {
            'en cours' => 'en_cours',
            'terminée' => 'terminee',
            default    => 'a_faire',
        };
    }
}