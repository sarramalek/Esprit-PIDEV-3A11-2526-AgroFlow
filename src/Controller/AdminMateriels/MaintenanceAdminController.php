<?php
// src/Controller/AdminMateriels/MaintenanceAdminController.php

namespace App\Controller\AdminMateriels;

use App\Entity\Materiels\Maintenance;
use App\Entity\Materiels\Machine;
use App\Repository\Materiels\MaintenanceRepository;
use App\Repository\Materiels\MachineRepository;
use App\Service\GeminiRecommendationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

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
        $statut     = $request->query->get('statut', '');
        $priorite   = $request->query->get('priorite', '');

        if ($coutFilter === 'asc') {
            $sort = 'cout'; $dir = 'ASC';
        } elseif ($coutFilter === 'desc') {
            $sort = 'cout'; $dir = 'DESC';
        }

        $maintenances = $repo->searchWithMaterielName($search, $type, $sort, $dir, $idM, $statut, $priorite);
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
            'selectedStatut' => $statut,
            'selectedPriorite' => $priorite,
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
    // WIKIPEDIA-LIKE DOCUMENTATION
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/wiki', name: 'wiki', methods: ['GET'])]
    public function wikiDocumentation(Request $request): Response
    {
        $search = $request->query->get('search', '');
        $selectedTopic = $request->query->get('topic', '');
        
        $knowledgeBase = $this->getKnowledgeBase();
        
        $filteredTopics = $knowledgeBase;
        if (!empty($search)) {
            $filteredTopics = array_filter($knowledgeBase, function($topic) use ($search) {
                return stripos($topic['title'], $search) !== false || 
                       stripos($topic['content'], $search) !== false ||
                       stripos($topic['category'], $search) !== false;
            });
        }
        
        $currentTopic = null;
        if (!empty($selectedTopic) && isset($knowledgeBase[$selectedTopic])) {
            $currentTopic = $knowledgeBase[$selectedTopic];
        } elseif (!empty($filteredTopics)) {
            $firstKey = array_key_first($filteredTopics);
            $currentTopic = $filteredTopics[$firstKey];
            $selectedTopic = $firstKey;
        } else {
            $currentTopic = $knowledgeBase['moteur'] ?? null;
            $selectedTopic = 'moteur';
        }
        
        $relatedTopics = $this->getRelatedTopics($selectedTopic, $knowledgeBase);
        
        return $this->render('admins/maintenances/wiki.html.twig', [
            'knowledgeBase' => $knowledgeBase,
            'filteredTopics' => $filteredTopics,
            'currentTopic' => $currentTopic,
            'selectedTopic' => $selectedTopic,
            'search' => $search,
            'relatedTopics' => $relatedTopics,
        ]);
    }
    
    #[Route('/wiki/api/{topic}', name: 'wiki_api', methods: ['GET'])]
    public function wikiApi(string $topic): JsonResponse
    {
        $knowledgeBase = $this->getKnowledgeBase();
        
        if (!isset($knowledgeBase[$topic])) {
            return $this->json(['error' => 'Topic not found'], 404);
        }
        
        return $this->json([
            'title' => $knowledgeBase[$topic]['title'],
            'content' => $knowledgeBase[$topic]['content'],
            'category' => $knowledgeBase[$topic]['category'],
            'image' => $knowledgeBase[$topic]['image'],
            'wiki_link' => $knowledgeBase[$topic]['wiki_link'],
            'symptoms' => $knowledgeBase[$topic]['symptoms'] ?? [],
            'causes' => $knowledgeBase[$topic]['causes'] ?? [],
            'solutions' => $knowledgeBase[$topic]['solutions'] ?? [],
            'prevention' => $knowledgeBase[$topic]['prevention'] ?? '',
            'estimated_cost' => $knowledgeBase[$topic]['estimated_cost'] ?? 'N/A',
            'avg_duration' => $knowledgeBase[$topic]['avg_duration'] ?? 'N/A',
        ]);
    }
    
    /**
     * Base de connaissances complète avec IMAGES RÉELLES
     * Images libres de droit provenant de Unsplash
     */
    private function getKnowledgeBase(): array
    {
        return [
            'moteur' => [
                'title' => '🔧 Panne Moteur',
                'category' => 'Mécanique',
                'content' => 'Les pannes moteur sont parmi les plus critiques en maintenance industrielle et agricole. Un moteur peut présenter divers symptômes allant de la perte de puissance à l\'arrêt complet. Les causes courantes incluent l\'usure des composants, un manque d\'entretien, ou une contamination du carburant et de l\'huile.',
                'image' => 'https://images.unsplash.com/photo-1581093588401-fbb62a02e120?w=800&h=450&fit=crop',
                'wiki_link' => 'https://fr.wikipedia.org/wiki/Moteur',
                'symptoms' => [
                    '💢 Perte de puissance',
                    '🔥 Surchauffe moteur',
                    '💨 Fumée excessive (noire, bleue, blanche)',
                    '🔊 Bruits anormaux (cliquetis, cognements)',
                    '⚡ Difficulté au démarrage',
                    '🛢️ Consommation d\'huile excessive'
                ],
                'causes' => [
                    'Filtres obstrués (air, carburant, huile)',
                    'Injecteurs encrassés ou défectueux',
                    'Usure des segments ou pistons',
                    'Joint de culasse endommagé',
                    'Système de refroidissement défaillant',
                    'Carburant de mauvaise qualité'
                ],
                'solutions' => [
                    '🔧 Nettoyage ou remplacement des injecteurs',
                    '🛠️ Révision complète du moteur',
                    '🌡️ Vérification du système de refroidissement',
                    '💧 Changement d\'huile et filtres',
                    '🔩 Remplacement des bougies/prechauffages'
                ],
                'prevention' => 'Effectuez les vidanges aux intervalles recommandés, utilisez des carburants et lubrifiants de qualité, surveillez les températures et pressions, et respectez les heures de fonctionnement maximales.',
                'estimated_cost' => '500 - 5000 DT',
                'avg_duration' => '2 - 10 jours'
            ],
            'hydraulique' => [
                'title' => '💧 Panne Hydraulique',
                'category' => 'Hydraulique',
                'content' => 'Les systèmes hydrauliques sont essentiels pour les engins agricoles (relevage, direction, chargeur). Les pannes hydrauliques se manifestent souvent par une perte de puissance, des mouvements saccadés, ou des fuites visibles. La contamination de l\'huile est la cause principale des défaillances.',
                'image' => 'https://images.unsplash.com/photo-1581091226033-d5c48150dbaa?w=800&h=450&fit=crop',
                'wiki_link' => 'https://fr.wikipedia.org/wiki/Hydraulique',
                'symptoms' => [
                    '💧 Fuites d\'huile visibles',
                    '📉 Perte de puissance ou réponse lente',
                    '🔊 Bruits de pompe (grincements, claquements)',
                    '🌡️ Surchauffe de l\'huile',
                    '🔄 Mouvements saccadés ou irréguliers',
                    '⚙️ Impossibilité de lever une charge'
                ],
                'causes' => [
                    'Usure des joints ou flexibles',
                    'Huile contaminée ou de mauvais niveau',
                    'Filtre hydraulique obstrué',
                    'Pompe hydraulique défectueuse',
                    'Distributeur ou vérin endommagé',
                    'Surcharge du système'
                ],
                'solutions' => [
                    '🔧 Remplacement des joints et flexibles',
                    '🛢️ Vidange et remplacement de l\'huile',
                    '🧹 Nettoyage ou remplacement du filtre',
                    '🔄 Révision ou remplacement de la pompe',
                    '⚙️ Réparation du vérin ou distributeur'
                ],
                'prevention' => 'Contrôlez régulièrement le niveau et l\'état de l\'huile, remplacez les filtres selon préconisations, vérifiez l\'état des flexibles, et évitez les surcharges prolongées.',
                'estimated_cost' => '300 - 3000 DT',
                'avg_duration' => '1 - 5 jours'
            ],
            'electrique' => [
                'title' => '⚡ Panne Électrique',
                'category' => 'Électricité',
                'content' => 'Les pannes électriques sont fréquentes et peuvent être difficiles à diagnostiquer. Elles affectent le démarrage, l\'éclairage, les instruments de bord, ou les calculateurs électroniques. Un mauvais contact ou une batterie défaillante sont souvent en cause.',
                'image' => 'https://images.unsplash.com/photo-1562426509-9f954dc9fec3?w=800&h=450&fit=crop',
                'wiki_link' => 'https://fr.wikipedia.org/wiki/%C3%89lectricit%C3%A9_automobile',
                'symptoms' => [
                    '🔋 Batterie qui se décharge rapidement',
                    '💡 Voyants d\'alerte au tableau de bord',
                    '🔌 Démarrage difficile ou impossible',
                    '⚡ Coupures de courant intermittentes',
                    '🎛️ Capteurs ou afficheurs défaillants',
                    '😤 Odeur de brûlé'
                ],
                'causes' => [
                    'Batterie usée ou sulfatée',
                    'Alternateur défaillant (ne recharge pas)',
                    'Mauvais contacts ou oxydation',
                    'Fusible ou relais grillé',
                    'Faisceau électrique endommagé',
                    'Calculateur (ECU) défectueux'
                ],
                'solutions' => [
                    '🔋 Test et remplacement de la batterie',
                    '🔄 Révision ou remplacement de l\'alternateur',
                    '🧹 Nettoyage des connexions électriques',
                    '🔌 Remplacement des fusibles/relais défectueux',
                    '💻 Diagnostic électronique complet'
                ],
                'prevention' => 'Nettoyez régulièrement les bornes de batterie, vérifiez la tension de charge, protégez les connecteurs de l\'humidité, et effectuez un diagnostic électronique annuel.',
                'estimated_cost' => '100 - 2000 DT',
                'avg_duration' => '1 - 3 jours'
            ],
            'transmission' => [
                'title' => '🔄 Panne Transmission',
                'category' => 'Transmission',
                'content' => 'La transmission transmet la puissance du moteur aux roues. Une panne de transmission peut rendre le véhicule inutilisable. Les symptômes incluent des difficultés à passer les vitesses, des bruits anormaux, ou une perte totale de motricité.',
                'image' => 'https://images.unsplash.com/photo-1581092335871-5a5b5b2b7a6f?w=800&h=450&fit=crop',
                'wiki_link' => 'https://fr.wikipedia.org/wiki/Transmission_m%C3%A9canique',
                'symptoms' => [
                    '🎛️ Difficulté à passer les vitesses',
                    '🔊 Grincements ou claquements',
                    '🔄 Patinage (régime moteur qui monte sans accélération)',
                    '⚙️ À-coups lors du passage de vitesse',
                    '💧 Fuite d\'huile de transmission',
                    '🚫 Perte totale de motricité'
                ],
                'causes' => [
                    'Niveau d\'huile insuffisant',
                    'Embrayage usé (transmission manuelle)',
                    'Boîte de vitesses endommagée',
                    'Cardans ou joints homocinétiques usés',
                    'Pont ou différentiel défectueux',
                    'Surcharge ou utilisation intensive'
                ],
                'solutions' => [
                    '🛢️ Vidange et remplacement de l\'huile',
                    '🔧 Remplacement de l\'embrayage',
                    '🔄 Révision ou échange de la boîte',
                    '⚙️ Remplacement des cardans ou joints',
                    '🔩 Réparation du différentiel'
                ],
                'prevention' => 'Contrôlez le niveau d\'huile transmission régulièrement, évitez les à-coups brusques, ne surchargez pas l\'engin, et faites vidanger aux intervalles préconisés.',
                'estimated_cost' => '800 - 8000 DT',
                'avg_duration' => '2 - 8 jours'
            ],
            'pneumatique' => [
                'title' => '🎈 Panne Pneumatique',
                'category' => 'Pneumatique',
                'content' => 'Les systèmes pneumatiques utilisent l\'air comprimé pour le freinage, la suspension ou divers actionneurs. Les pannes se manifestent par des fuites d\'air, une pression insuffisante, ou des organes qui ne répondent plus.',
                'image' => 'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=800&h=450&fit=crop',
                'wiki_link' => 'https://fr.wikipedia.org/wiki/Pneumatique',
                'symptoms' => [
                    '💨 Bruit de fuite d\'air',
                    '📉 Manomètre indiquant une pression basse',
                    '🚫 Freins moins efficaces',
                    '🔊 Compresseur qui tourne en continu',
                    '⏱️ Temps de recharge trop long',
                    '🎛️ Actionneurs qui ne répondent pas'
                ],
                'causes' => [
                    'Joints ou flexibles usés/perforés',
                    'Compresseur défectueux',
                    'Sécheur d\'air obstrué',
                    'Clapets anti-retour défaillants',
                    'Réservoir d\'air percé ou corrodé',
                    'Condensation excessive dans le circuit'
                ],
                'solutions' => [
                    '🔍 Détection et réparation des fuites (eau savonneuse)',
                    '🔄 Remplacement du compresseur',
                    '💧 Vidange régulière des réservoirs',
                    '🔧 Nettoyage ou remplacement du sécheur',
                    '⚙️ Révision des vannes et clapets'
                ],
                'prevention' => 'Purgez quotidiennement les réservoirs, vérifiez l\'absence de fuites, contrôlez l\'état des flexibles, et assurez une lubrification adéquate du compresseur.',
                'estimated_cost' => '200 - 2500 DT',
                'avg_duration' => '1 - 4 jours'
            ],
            'vidange' => [
                'title' => '🛢️ Vidange & Filtres',
                'category' => 'Entretien',
                'content' => 'La vidange est une opération d\'entretien préventif essentielle qui consiste à remplacer l\'huile moteur et le filtre à huile. Une vidange régulière prolonge la durée de vie du moteur et prévient les pannes graves.',
                'image' => 'https://images.unsplash.com/photo-1581092335953-4e6a2f4d8b5c?w=800&h=450&fit=crop',
                'wiki_link' => 'https://fr.wikipedia.org/wiki/Vidange_(m%C3%A9canique)',
                'symptoms' => [
                    '⏰ Kilométrage ou temps dépassé',
                    '🟤 Huile noire et épaisse',
                    '🔊 Bruit moteur anormal',
                    '🌡️ Température moteur élevée',
                    '💡 Voyant de pression d\'huile allumé'
                ],
                'causes' => [
                    'Huile dégradée par la chaleur et l\'usage',
                    'Filtre colmaté par les impuretés',
                    'Utilisation d\'huile non adaptée',
                    'Surcharge ou usage intensif',
                    'Dépassement des intervalles préconisés'
                ],
                'solutions' => [
                    '🛢️ Vidanger l\'huile usagée',
                    '🔧 Remplacer le filtre à huile',
                    '💧 Remplir avec huile neuve adaptée',
                    '✅ Contrôler l\'absence de fuites',
                    '📝 Enregistrer l\'intervention'
                ],
                'prevention' => 'Respectez scrupuleusement les intervalles de vidange (heures ou kilométrage), utilisez l\'huile préconisée par le constructeur, et notez chaque intervention pour le suivi.',
                'estimated_cost' => '80 - 400 DT',
                'avg_duration' => '1 - 3 heures'
            ],
            'freinage' => [
                'title' => '🛑 Panne Freinage',
                'category' => 'Sécurité',
                'content' => 'Le système de freinage est critique pour la sécurité. Une défaillance peut avoir des conséquences graves. Les signes d\'usure incluent des bruits, des vibrations, ou une pédale qui s\'enfonce trop.',
                'image' => 'https://images.unsplash.com/photo-1562426509-9f954dc9fec3?w=800&h=450&fit=crop',
                'wiki_link' => 'https://fr.wikipedia.org/wiki/Freinage',
                'symptoms' => [
                    '🔊 Grincements ou couinements au freinage',
                    '📉 Freinage moins efficace',
                    '🦶 Pédale de frein qui pompe ou s\'enfonce',
                    '🔴 Voyant d\'usure allumé',
                    '🚗 Vibrations dans le volant au freinage',
                    '💧 Fuite de liquide de frein'
                ],
                'causes' => [
                    'Plaquettes ou mâchoires usées',
                    'Disques ou tambours voilés',
                    'Étage hydraulique endommagé',
                    'Liquide de frein absorbé (usé)',
                    'Défaut du système ABS',
                    'Fuite dans le circuit'
                ],
                'solutions' => [
                    '🔧 Remplacement des plaquettes/disques',
                    '🔄 Révision des étriers',
                    '💧 Purge et remplacement du liquide',
                    '🛢️ Réparation des fuites',
                    '💻 Diagnostic ABS'
                ],
                'prevention' => 'Surveillez l\'usure des plaquettes visuellement, changez le liquide de frein tous les 2 ans, ne roulez pas avec le voyant de frein allumé, et effectuez un contrôle annuel complet.',
                'estimated_cost' => '150 - 1500 DT',
                'avg_duration' => '1 - 2 jours'
            ]
        ];
    }
    
    private function getRelatedTopics(string $currentTopic, array $knowledgeBase): array
    {
        $related = [];
        $categories = [];
        
        foreach ($knowledgeBase as $key => $topic) {
            if ($key !== $currentTopic) {
                $categories[$topic['category']][] = $key;
            }
        }
        
        $currentCategory = $knowledgeBase[$currentTopic]['category'] ?? '';
        if (isset($categories[$currentCategory])) {
            foreach ($categories[$currentCategory] as $relatedKey) {
                $related[$relatedKey] = $knowledgeBase[$relatedKey];
            }
        }
        
        if (count($related) < 3) {
            foreach ($knowledgeBase as $key => $topic) {
                if ($key !== $currentTopic && !isset($related[$key])) {
                    $related[$key] = $topic;
                    if (count($related) >= 3) break;
                }
            }
        }
        
        return $related;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HISTORIQUE
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/historique', name: 'history', methods: ['GET'])]
    public function history(
        MaintenanceRepository $repo,
        MachineRepository $machineRepo,
        Request $request,
        GeminiRecommendationService $geminiService,
        LoggerInterface $logger
    ): Response {
        $idM      = $request->query->get('idM', '');
        $statut   = $request->query->get('statut', '');
        $priorite = $request->query->get('priorite', '');
        $year     = $request->query->get('year', '');

        $criteria = [];
        if ($idM !== '') {
            $criteria['idM'] = (int) $idM;
        }

        $maintenances = $repo->findBy($criteria, ['dateMain' => 'DESC']);

        if ($statut !== '') {
            $maintenances = array_filter($maintenances, fn(Maintenance $m) => $m->getStatut() === $statut);
        }

        if ($priorite !== '') {
            $maintenances = array_filter($maintenances, fn(Maintenance $m) => $m->getPriorite() === $priorite);
        }

        if ($year !== '') {
            $maintenances = array_filter($maintenances, fn(Maintenance $m) => $m->getDateMain()?->format('Y') === $year);
        }

        $maintenances = array_values($maintenances);

        foreach ($maintenances as $maintenance) {
            try {
                $existingReco = $maintenance->getRecommandation();
                if (empty($existingReco) || strlen($existingReco) < 10) {
                    $recommendation = $geminiService->generateRecommendation($maintenance);
                    $maintenance->setRecommandation($recommendation);
                    $logger->info('IA Gemini recommendation generated', [
                        'maintenance_id' => $maintenance->getIdMain(),
                        'type' => $maintenance->getTypePanne()
                    ]);
                }
            } catch (\Exception $e) {
                $logger->error('Failed to generate Gemini recommendation', [
                    'maintenance_id' => $maintenance->getIdMain(),
                    'error' => $e->getMessage()
                ]);
                $maintenance->setRecommandation($this->getFallbackRecommendation($maintenance));
            }
        }

        $grouped = [];
        foreach ($maintenances as $m) {
            $date = $m->getDateMain();
            if (!$date) continue;
            $grouped[$date->format('Y-m')][] = $m;
        }
        krsort($grouped);

        $totalCout = array_sum(array_map(fn(Maintenance $m) => (float) $m->getCout(), $maintenances));
        
        $years = array_unique(array_filter(array_map(fn(Maintenance $m) => $m->getDateMain()?->format('Y'), $maintenances)));
        rsort($years);

        $machines = $machineRepo->findAll();
        $urgentesCount = count(array_filter($maintenances, fn($m) => $m->getPriorite() === 'urgente'));

        return $this->render('admins/maintenances/history.html.twig', [
            'grouped' => $grouped,
            'totalCout' => $totalCout,
            'machines' => $machines,
            'years' => $years,
            'selectedIdM' => $idM,
            'selectedStatut' => $statut,
            'selectedPriorite' => $priorite,
            'selectedYear' => $year,
            'totalCount' => count($maintenances),
            'urgentesCount' => $urgentesCount,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DASHBOARD IA
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/ai/dashboard', name: 'ai_dashboard', methods: ['GET'])]
    public function aiDashboard(
        MachineRepository $machineRepo,
        GeminiRecommendationService $geminiService
    ): Response {
        $machines = $machineRepo->findAll();
        
        $healthScores = [];
        $allAlerts = [];
        $predictions = [];
        
        foreach ($machines as $machine) {
            $healthScores[$machine->getId()] = $geminiService->calculateHealthScore($machine);
            $alerts = $geminiService->generateSmartAlerts($machine);
            $allAlerts = array_merge($allAlerts, $alerts);
            $predictions[$machine->getId()] = $geminiService->predictFailure($machine);
        }
        
        $priorities = $geminiService->prioritizeInterventions($machines);
        $schedule = $geminiService->generateOptimizedSchedule($priorities, 30);
        
        $avgHealthScore = !empty($healthScores) ? array_sum(array_column($healthScores, 'score')) / count($healthScores) : 0;
        $criticalAlerts = count(array_filter($allAlerts, fn($a) => $a['type'] === 'critique'));
        $highRiskMachines = count(array_filter($predictions, fn($p) => $p['risque_panne'] === 'élevé'));
        
        return $this->render('admins/maintenances/ai_dashboard.html.twig', [
            'machines' => $machines,
            'healthScores' => $healthScores,
            'alerts' => $allAlerts,
            'predictions' => $predictions,
            'priorities' => $priorities,
            'schedule' => $schedule,
            'avgHealthScore' => round($avgHealthScore),
            'criticalAlerts' => $criticalAlerts,
            'highRiskMachines' => $highRiskMachines,
            'totalMachines' => count($machines)
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // API ROUTES
    // ─────────────────────────────────────────────────────────────────────────
    #[Route('/ai/predict/{id}', name: 'ai_predict', methods: ['GET'])]
    public function apiPredictMachine(
        int $id,
        MachineRepository $machineRepo,
        GeminiRecommendationService $geminiService
    ): JsonResponse {
        $machine = $machineRepo->find($id);
        if (!$machine) {
            return $this->json(['error' => 'Machine non trouvée'], 404);
        }
        
        $prediction = $geminiService->predictFailure($machine);
        $healthScore = $geminiService->calculateHealthScore($machine);
        $alerts = $geminiService->generateSmartAlerts($machine);
        
        return $this->json([
            'machine' => [
                'id' => $machine->getId(),
                'nom' => $machine->getNom(),
                'marque' => $machine->getMarque()
            ],
            'health_score' => $healthScore,
            'prediction' => $prediction,
            'alerts' => $alerts
        ]);
    }

    #[Route('/ai/schedule', name: 'ai_schedule', methods: ['GET'])]
    public function apiOptimizedSchedule(
        MachineRepository $machineRepo,
        GeminiRecommendationService $geminiService
    ): JsonResponse {
        $machines = $machineRepo->findAll();
        $priorities = $geminiService->prioritizeInterventions($machines);
        $schedule = $geminiService->generateOptimizedSchedule($priorities, 30);
        
        return $this->json([
            'total_machines' => count($machines),
            'interventions_planifiees' => count($schedule),
            'priorities' => $priorities,
            'planning' => $schedule
        ]);
    }

    #[Route('/ai/alerts', name: 'ai_alerts', methods: ['GET'])]
    public function apiGlobalAlerts(
        MachineRepository $machineRepo,
        GeminiRecommendationService $geminiService
    ): JsonResponse {
        $machines = $machineRepo->findAll();
        $allAlerts = [];
        
        foreach ($machines as $machine) {
            $alerts = $geminiService->generateSmartAlerts($machine);
            $allAlerts = array_merge($allAlerts, $alerts);
        }
        
        return $this->json([
            'total_alerts' => count($allAlerts),
            'critical_alerts' => count(array_filter($allAlerts, fn($a) => $a['type'] === 'critique')),
            'warning_alerts' => count(array_filter($allAlerts, fn($a) => $a['type'] === 'warning')),
            'alerts' => $allAlerts
        ]);
    }

    private function getFallbackRecommendation(Maintenance $m): string
    {
        $type = strtolower($m->getTypePanne());
        $priorite = $m->getPriorite();
        $km = $m->getKilometrage();
        
        $fallbacks = [
            'moteur' => '🔧 Révision moteur recommandée tous les 500h ou 10 000 km',
            'vidange' => '🛢️ Prochaine vidange dans 3 mois ou 3000 km',
            'électrique' => '⚡ Contrôle préventif du circuit électrique et batterie',
            'hydraulique' => '💧 Vérification du niveau et des flexibles hydrauliques',
            'transmission' => '⚙️ Inspection de la transmission et de l\'embrayage',
            'frein' => '🛑 Contrôle d\'usure des plaquettes et disques',
        ];
        
        foreach ($fallbacks as $key => $message) {
            if (str_contains($type, $key)) {
                return $message;
            }
        }
        
        if ($priorite === 'urgente') {
            return "⚠️ Intervention immédiate requise pour {$m->getTypePanne()}";
        }
        
        if ($km && $km > 10000) {
            return "📅 Révision programmée dans les 30 jours ou 2000 km";
        }
        
        return "🔍 Surveillance périodique recommandée pour {$m->getTypePanne()}";
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
    // CRUD OPERATIONS
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

        $maintenance->setStatut('planifie');
        $maintenance->setPriorite('moyenne');

        if ($request->isMethod('POST')) {
            $typePanne = trim($request->request->get('typePanne', ''));
            if (empty($typePanne)) {
                $errors['typePanne'] = 'Le type de panne est obligatoire.';
            } else {
                $maintenance->setTypePanne($typePanne);
            }

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

            $recommandation = trim($request->request->get('recommandation', ''));
            if (!empty($recommandation)) {
                if (strlen($recommandation) > 2000) {
                    $errors['recommandation'] = 'La recommandation ne peut pas dépasser 2000 caractères.';
                } else {
                    $maintenance->setRecommandation($recommandation);
                }
            }

            $statut = $request->request->get('statut', 'planifie');
            $allowedStatuts = ['planifie', 'en_cours', 'termine'];
            if (!in_array($statut, $allowedStatuts)) {
                $errors['statut'] = 'Statut invalide.';
            } else {
                $maintenance->setStatut($statut);
            }

            $priorite = $request->request->get('priorite', 'moyenne');
            $allowedPriorites = ['faible', 'moyenne', 'haute', 'urgente'];
            if (!in_array($priorite, $allowedPriorites)) {
                $errors['priorite'] = 'Priorité invalide.';
            } else {
                $maintenance->setPriorite($priorite);
            }

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

            if (empty($errors)) {
                $em->persist($maintenance);
                $em->flush();
                $this->addFlash('success', 'Maintenance ajoutée avec succès.');
                return $this->redirectToRoute('admin_maintenances_index');
            } else {
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
            $typePanne = trim($request->request->get('typePanne', ''));
            if (empty($typePanne)) {
                $errors['typePanne'] = 'Le type de panne est obligatoire.';
            } else {
                $maintenance->setTypePanne($typePanne);
            }

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

            $statut = $request->request->get('statut', 'planifie');
            $allowedStatuts = ['planifie', 'en_cours', 'termine'];
            if (!in_array($statut, $allowedStatuts)) {
                $errors['statut'] = 'Statut invalide.';
            } else {
                $maintenance->setStatut($statut);
            }

            $priorite = $request->request->get('priorite', 'moyenne');
            $allowedPriorites = ['faible', 'moyenne', 'haute', 'urgente'];
            if (!in_array($priorite, $allowedPriorites)) {
                $errors['priorite'] = 'Priorité invalide.';
            } else {
                $maintenance->setPriorite($priorite);
            }

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

            if (empty($errors)) {
                $em->flush();
                $this->addFlash('success', 'Maintenance mise à jour avec succès.');
                return $this->redirectToRoute('admin_maintenances_index');
            } else {
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
    
    #[Route('/ai/analyze/{id}', name: 'ai_analyze', methods: ['GET'])]
    public function aiAnalyzeMaintenance(
        int $id,
        MaintenanceRepository $repo,
        GeminiRecommendationService $geminiService
    ): JsonResponse {
        $maintenance = $repo->find($id);
        if (!$maintenance) {
            return $this->json(['error' => 'Maintenance non trouvée'], 404);
        }
        
        $recommendation = $geminiService->generateRecommendation($maintenance);
        $analysis = $this->generateDetailedAnalysis($maintenance);
        
        return $this->json([
            'recommendation' => $recommendation,
            'analysis' => $analysis['description'],
            'next_maintenance' => $analysis['next_maintenance'],
            'actions' => $analysis['actions'],
            'tips' => $analysis['tips']
        ]);
    }

    private function generateDetailedAnalysis(Maintenance $m): array
    {
        $type = strtolower($m->getTypePanne());
        $priorite = $m->getPriorite();
        $km = $m->getKilometrage();
        
        $analyses = [
            'moteur' => [
                'description' => 'Analyse moteur: Usure normale détectée. Surveillance recommandée des niveaux d\'huile et de liquide de refroidissement.',
                'next_maintenance' => $km ? 'Dans ' . min(5000, $km * 0.5) . ' km ou 6 mois' : 'Dans 6 mois',
                'actions' => ['Contrôle du niveau d\'huile', 'Vérification des bougies', 'Inspection des courroies'],
                'tips' => 'Utilisez une huile moteur de qualité premium pour prolonger la durée de vie.'
            ],
            'vidange' => [
                'description' => 'Maintenance vidange standard. Le cycle de vidange est respecté.',
                'next_maintenance' => $km ? 'Dans ' . min(5000, $km * 0.6) . ' km' : 'Dans 5000 km',
                'actions' => ['Changement d\'huile', 'Remplacement du filtre à huile', 'Contrôle du niveau'],
                'tips' => 'Respectez scrupuleusement les intervalles de vidange pour éviter l\'usure prématurée.'
            ],
            'électrique' => [
                'description' => 'Système électrique: Diagnostic recommandé. Risque de panne batterie/alternateur.',
                'next_maintenance' => 'Dans 30 jours',
                'actions' => ['Test de batterie', 'Vérification alternateur', 'Contrôle du faisceau'],
                'tips' => 'Un voyant batterie qui s\'allume indique souvent un problème d\'alternateur.'
            ],
            'hydraulique' => [
                'description' => 'Circuit hydraulique: Contrôle préventif nécessaire. Risque de fuite modéré.',
                'next_maintenance' => 'Dans 3 mois',
                'actions' => ['Contrôle niveau huile', 'Inspection flexibles', 'Test de pression'],
                'tips' => 'Surveillez les traces d\'huile sous la machine après utilisation.'
            ],
            'transmission' => [
                'description' => 'Transmission: Point de contrôle atteint. Révision recommandée.',
                'next_maintenance' => $km ? 'Dans ' . min(10000, $km) . ' km' : 'Dans 10000 km',
                'actions' => ['Vidange transmission', 'Réglage embrayage', 'Contrôle cardans'],
                'tips' => 'Une transmission qui patine ou accroche nécessite une intervention rapide.'
            ],
            'frein' => [
                'description' => 'Système de freinage: Usure dans les normes. Surveillance continue.',
                'next_maintenance' => $km ? 'Dans ' . min(5000, $km * 0.8) . ' km' : 'Dans 5000 km',
                'actions' => ['Contrôle plaquettes', 'Vérification disques', 'Test efficacité'],
                'tips' => 'Un grincement au freinage indique des plaquettes à remplacer.'
            ]
        ];
        
        $default = [
            'description' => "Maintenance {$m->getTypePanne()} analysée. Aucune anomalie majeure détectée.",
            'next_maintenance' => 'Dans 3 mois',
            'actions' => ['Maintenance préventive standard', 'Contrôle visuel', 'Test fonctionnel'],
            'tips' => 'Documentez chaque intervention pour un meilleur suivi.'
        ];
        
        $result = $analyses[$type] ?? $default;
        
        if ($priorite === 'urgente') {
            $result['description'] = '⚠️ URGENT: ' . $result['description'];
            $result['next_maintenance'] = 'IMMÉDIAT';
            $result['actions'][] = 'Intervention d\'urgence requise';
        }
        
        return $result;
    }
}