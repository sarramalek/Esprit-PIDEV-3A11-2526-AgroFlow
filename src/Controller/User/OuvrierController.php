<?php

namespace App\Controller\User;

use App\Service\TaskTranslator;
use App\Repository\User\TacheRepository;
use App\Repository\Animals\AnimauxRepository;
use App\Repository\Materiels\MachineRepository;
use App\Repository\Terrain\TerrainRepository;
use App\Repository\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\Terrain\PlanteRepository;
use App\Repository\Materiels\MaintenanceRepository;

#[Route('/ouvrier', name: 'ouvrier_')]
#[IsGranted('ROLE_OUVRIER')]
class OuvrierController extends AbstractController
{
    private const KEYWORDS_ANIMAL = [
        'vaccination', 'vaccin', 'alimentation', 'bétail', 'animal', 'animaux',
        'vétérinaire', 'veto', 'traite', 'vermifugation', 'enclos', 'pesée',
        'troupeau', 'volaille', 'ovin', 'bovin', 'caprin', 'soin animal',
        'nourrissage', 'abreuvement', 'désinfection enclos', 'brebis', 'vache',
        'mouton', 'chèvre', 'poulet', 'dinde', 'lapin',
    ];

    private const KEYWORDS_MAINTENANCE = [
        'graissage', 'tracteur', 'vidange', 'révision', 'maintenance',
        'réparation', 'filtre', 'huile', 'pneu', 'pompe', 'panne',
        'diagnostic', 'moteur', 'électricité', 'hydraulique',
        'transmission', 'pneumatique', 'machine', 'équipement',
        'moissonneuse', 'charrue', 'semoir', 'pulvérisateur',
        'nettoyage machine', 'contrôle machine', 'vérification machine',
    ];

    private const KEYWORDS_TERRAIN = [
        'labour', 'irrigation', 'désherbage', 'fertilisation', 'semis', 'récolte',
        'taille', 'plantation', 'traitement phytosanitaire', 'terrain', 'culture',
        'plante', 'sol', 'engrais', 'herbicide', 'pesticide', 'arrosage',
        'serre', 'champ', 'parcelle', 'compost', 'buttage', 'binage',
    ];

    private const KEYWORDS_PLANTE = [
        'plante', 'semis', 'plantation', 'récolte', 'culture', 'graine',
        'tomate', 'blé', 'maïs', 'carotte', 'pomme de terre', 'laitue',
        'haricot', 'poivron', 'aubergine', 'courgette', 'salade', 'épinard',
        'olivier', 'vigne', 'orge', 'sorgho', 'tournesol', 'fève',
        'piment', 'melon', 'pastèque', 'fenouil', 'persil', 'coriandre',
    ];

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
            'categorie'   => null,
            'detail_url'  => null,
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
    public function taches(
        TacheRepository       $tacheRepo,
        AnimauxRepository     $animauxRepo,
        MachineRepository     $machineRepo,
        TerrainRepository     $terrainRepo,
        UserRepository        $userRepo,
        PlanteRepository      $plantesRepo,
        MaintenanceRepository $maintenanceRepo,
        TaskTranslator        $taskTranslator,
        Request               $request
    ): Response {
        /** @var \App\Entity\User\User $user */
        $user   = $this->getUser();
        $taches = $tacheRepo->findByAssignee($user);
        $locale = $request->getSession()->get('_locale', 'fr');

        $agriculteurCin = $user->getTerrain()->getCin();
        $animaux      = [];
        $terrains     = [];
        $plantes      = [];
        $maintenances = [];

        if ($agriculteurCin) {
            $agriculteur  = $userRepo->findByCin($agriculteurCin);
            $animaux      = $agriculteur
                ? $animauxRepo->searchDashboard(null, 'id', 'DESC', $agriculteur)
                : [];
            $terrains     = $terrainRepo->findByAgriculteur($agriculteurCin);
            $plantes      = $plantesRepo->findByAgriculteur($agriculteurCin);
            $maintenances = $maintenanceRepo->findByAgriculteurCin($agriculteurCin);
        }

        $tachesData = array_map(function ($t) use (
            $animaux, $terrains, $plantes, $maintenances,
            $taskTranslator, $locale
        ) {
            $nom           = strtolower($t->getNomTache() . ' ' . $t->getDescription());
            $categorie     = $this->detectCategorie($nom);
            $isPlante      = $this->isPlanteTask($nom);
            $isMaintenance = $this->isMaintenanceTask($nom);
            $detailUrl     = $this->resolveDetailUrl($categorie, $nom, $animaux, $terrains, $plantes, $isPlante, $maintenances);
            $detailLabel   = $this->resolveDetailLabel($categorie, $isPlante);

            return [
                'id'             => $t->getIdTache(),
                'titre'          => $taskTranslator->translate($t->getNomTache() ?? '', $locale),
                'description'    => $taskTranslator->translate($t->getDescription() ?? '', $locale),
                'statut'         => $this->mapStatut($t->getEtat()),
                'priorite'       => $t->getPriorite(),
                'dateDebut'      => null,
                'dateFin'        => $t->getDateEcheancee(),
                'categorie'      => $categorie,
                'is_plante'      => $isPlante,
                'is_maintenance' => $isMaintenance,
                'detail_url'     => $detailUrl,
                'detail_label'   => $detailLabel,
            ];
        }, $taches);

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

        /** @var \App\Entity\User\User $user */
        $user = $this->getUser();
        if ($tache->getAssignee()?->getCin() !== $user->getCin()) {
            return $this->json(['success' => false, 'message' => 'Accès refusé'], 403);
        }

        $statutActuel = $this->mapStatut($tache->getEtat());

        $transitionsAutorisees = [
            'a_faire'  => ['en_cours'],
            'en_cours' => ['terminee'],
        ];

        if (!in_array($statut, $transitionsAutorisees[$statutActuel] ?? [])) {
            return $this->json(['success' => false, 'message' => 'Transition non autorisée'], 400);
        }

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

    // ── Détection catégorie ───────────────────────────────────────────────────
    private function detectCategorie(string $texte): ?string
    {
        foreach (self::KEYWORDS_ANIMAL as $kw) {
            if (str_contains($texte, $kw)) return 'animal';
        }
        foreach (self::KEYWORDS_MAINTENANCE as $kw) {
            if (str_contains($texte, $kw)) return 'maintenance';
        }
        foreach (self::KEYWORDS_TERRAIN as $kw) {
            if (str_contains($texte, $kw)) return 'terrain';
        }
        return null;
    }

    private function isPlanteTask(string $texte): bool
    {
        foreach (self::KEYWORDS_PLANTE as $kw) {
            if (str_contains($texte, $kw)) return true;
        }
        return false;
    }

    private function isMaintenanceTask(string $texte): bool
    {
        foreach (self::KEYWORDS_MAINTENANCE as $kw) {
            if (str_contains($texte, $kw)) return true;
        }
        return false;
    }

    // ── Résolution URL détail ─────────────────────────────────────────────────
    private function resolveDetailUrl(
        ?string $categorie,
        string  $texte,
        array   $animaux,
        array   $terrains,
        array   $plantes,
        bool    $isPlante,
        array   $maintenances = []
    ): ?string {
        if (!$categorie) return null;

        switch ($categorie) {
            case 'animal':
                foreach ($animaux as $animal) {
                    $nomAnimal = strtolower($animal->getNom() ?? '');
                    if ($nomAnimal && str_contains($texte, $nomAnimal)) {
                        return $this->generateUrl('app_animaux_show', ['id' => $animal->getId()]);
                    }
                }
                return !empty($animaux) ? $this->generateUrl('app_animaux_index') : null;

            case 'maintenance':
                foreach ($maintenances as $maintenance) {
                    $typePanne = strtolower($maintenance->getTypePanne() ?? '');
                    if ($typePanne && str_contains($texte, $typePanne)) {
                        return $this->generateUrl('agri_maintenances_show', ['id' => $maintenance->getIdMain()]);
                    }
                }
                return !empty($maintenances) ? $this->generateUrl('agri_maintenances_index') : null;

            case 'terrain':
                if ($isPlante) {
                    foreach ($plantes as $plante) {
                        $nomPlante = strtolower($plante->getNomP() ?? '');
                        if ($nomPlante && str_contains($texte, $nomPlante)) {
                            return $this->generateUrl('agri_plantes_show', ['id' => $plante->getId()]);
                        }
                    }
                    if (!empty($plantes)) return $this->generateUrl('agri_plantes');
                }
                foreach ($terrains as $terrain) {
                    $nomTerrain = strtolower($terrain->getNomTerrain() ?? '');
                    if ($nomTerrain && str_contains($texte, $nomTerrain)) {
                        return $this->generateUrl('agri_terrains_show', ['id' => $terrain->getId()]);
                    }
                }
                return !empty($terrains)
                    ? $this->generateUrl('agri_terrains_show', ['id' => $terrains[0]->getId()])
                    : null;
        }

        return null;
    }

    private function resolveDetailLabel(?string $categorie, bool $isPlante): ?string
    {
        return match(true) {
            $categorie === 'animal'               => "Voir l'animal",
            $categorie === 'maintenance'          => 'Voir la maintenance',
            $categorie === 'terrain' && $isPlante => 'Voir la plante',
            $categorie === 'terrain'              => 'Voir le terrain',
            default                               => null,
        };
    }

    private function mapStatut(string $etat): string
    {
        return match($etat) {
            'en cours' => 'en_cours',
            'terminée' => 'terminee',
            default    => 'a_faire',
        };
    }

    // ── Changer langue ───────────────────────────────────────────────────────
    #[Route('/langue/{locale}', name: 'changer_langue')]
    public function changerLangue(string $locale, Request $request): Response
    {
        if (!in_array($locale, ['fr', 'en', 'ar'])) {
            $locale = 'fr';
        }
        $request->getSession()->set('_locale', $locale);
        $referer = $request->headers->get('referer', $this->generateUrl('ouvrier_taches'));
        return $this->redirect($referer);
    }
}