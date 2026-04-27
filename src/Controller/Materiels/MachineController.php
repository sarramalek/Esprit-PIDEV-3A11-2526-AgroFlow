<?php

namespace App\Controller\Materiels;

use App\Entity\Materiels\Machine;
use App\Entity\User\User;
use App\Form\Materiels\MachineType;
use App\Repository\Materiels\MachineRepository;
use App\Repository\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/agriculteur/materiels/machines', name: 'agri_')]
final class MachineController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    // ── Helper privé : récupère l'entité User complète depuis la BDD ─────────
    private function getFullUser(UserRepository $userRepo): ?User
    {
        $sessionUser = $this->getUser();
        if (!$sessionUser) {
            return null;
        }

        return $userRepo->findOneBy(['email' => $sessionUser->getUserIdentifier()]);
    }

    // ── Liste ────────────────────────────────────────────────────────────────
    #[Route('', name: 'machines', methods: ['GET'])]
    public function machineIndex(
        Request $request,
        MachineRepository $repo,
        UserRepository $userRepo
    ): Response {
        $user = $this->getFullUser($userRepo);

        if (!$user) {
            $this->addFlash('error', 'Veuillez vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        $machines = $repo->search([
            'cin'     => $user->getCin(),
            'search'  => trim($request->query->get('search',  '')),
            'etat'    => trim($request->query->get('etat',    '')),
            'sortBy'  => $request->query->get('sortBy',  'dateAchat'),
            'sortDir' => $request->query->get('sortDir', 'DESC'),
        ]);

        return $this->render('machines/index.html.twig', [
            'machines' => $machines,
        ]);
    }

    // ── Recherche Wikipedia ─────────────────────────────────────────────────
    #[Route('/wikipedia-search', name: 'wikipedia_search', methods: ['POST'])]
    public function wikipediaSearch(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Récupérer les paramètres
        $nom = trim($data['nom'] ?? '');
        $marque = trim($data['marque'] ?? '');
        $modele = trim($data['modele'] ?? '');
        $query = trim($data['query'] ?? '');
        
        // Construire la requête de recherche
        $searchTerms = [];
        
        if (!empty($query)) {
            // Recherche directe
            $searchTerms[] = $query;
        } else {
            // Recherche par nom d'abord
            if (!empty($nom)) {
                $searchTerms[] = $nom;
            }
            // Recherche par marque + nom
            if (!empty($marque) && !empty($nom)) {
                $searchTerms[] = $marque . ' ' . $nom;
            }
            // Recherche par marque seule
            if (!empty($marque)) {
                $searchTerms[] = $marque;
            }
            // Recherche par marque + modèle
            if (!empty($marque) && !empty($modele)) {
                $searchTerms[] = $marque . ' ' . $modele;
            }
            // Recherche par modèle
            if (!empty($modele)) {
                $searchTerms[] = $modele;
            }
        }
        
        // Supprimer les doublons et les termes vides
        $searchTerms = array_values(array_unique($searchTerms));
        
        // Tenter chaque terme de recherche
        foreach ($searchTerms as $term) {
            $result = $this->searchWikipediaTerm($term);
            if ($result['exists']) {
                return $this->json($result);
            }
        }
        
        // Si aucun résultat, retourner les suggestions
        $suggestions = $this->getSuggestions($nom, $marque, $modele);
        
        return $this->json([
            'exists' => false,
            'error' => 'Aucune information trouvée pour cette machine',
            'suggestions' => $suggestions
        ], Response::HTTP_NOT_FOUND);
    }
    
    /**
     * @return array<string, mixed>
     */
    private function searchWikipediaTerm(string $query): array
    {
        try {
            $query = trim($query);
            if (empty($query)) {
                return ['exists' => false];
            }
            
            // Nettoyer la requête pour l'URL
            $encodedQuery = urlencode($query);
            
            // Tentative 1: API REST Wikipedia
            $url = 'https://fr.wikipedia.org/api/rest_v1/page/summary/' . $encodedQuery;
            
            $response = $this->httpClient->request('GET', $url, [
                'timeout' => 10,
                'headers' => [
                    'User-Agent' => 'AgroFlow/1.0 (https://agroflow.com; contact@agroflow.com)'
                ]
            ]);
            
            if ($response->getStatusCode() === 200) {
                $content = $response->toArray();
                
                // Vérifier que c'est une page valide
                if (isset($content['title']) && !isset($content['missing']) && isset($content['extract'])) {
                    return [
                        'exists' => true,
                        'title' => $content['title'],
                        'description' => $content['extract'],
                        'image' => $content['originalimage']['source'] ?? $content['thumbnail']['source'] ?? null,
                        'url' => $content['content_urls']['desktop']['page'] ?? 'https://fr.wikipedia.org/wiki/' . $encodedQuery
                    ];
                }
            }
            
            // Tentative 2: API de recherche Wikipedia
            return $this->searchWikipediaApi($query);
            
        } catch (\Exception $e) {
            return ['exists' => false];
        }
    }
    
    /**
     * @return array<string, mixed>
     */
    private function searchWikipediaApi(string $query): array
    {
        try {
            $url = 'https://fr.wikipedia.org/w/api.php';
            
            $response = $this->httpClient->request('GET', $url, [
                'query' => [
                    'action' => 'query',
                    'list' => 'search',
                    'srsearch' => $query,
                    'format' => 'json',
                    'origin' => '*',
                    'srlimit' => 3
                ],
                'timeout' => 10,
                'headers' => [
                    'User-Agent' => 'AgroFlow/1.0'
                ]
            ]);
            
            $searchResult = $response->toArray();
            
            if (!empty($searchResult['query']['search'])) {
                $firstResult = $searchResult['query']['search'][0];
                $title = $firstResult['title'];
                $encodedTitle = urlencode($title);
                
                // Récupérer le résumé
                $summaryUrl = 'https://fr.wikipedia.org/api/rest_v1/page/summary/' . $encodedTitle;
                $summaryResponse = $this->httpClient->request('GET', $summaryUrl, [
                    'timeout' => 10,
                    'headers' => ['User-Agent' => 'AgroFlow/1.0']
                ]);
                $summary = $summaryResponse->toArray();
                
                // Suggestions
                $suggestions = [];
                foreach (array_slice($searchResult['query']['search'], 1, 2) as $result) {
                    $suggestions[] = $result['title'];
                }
                
                return [
                    'exists' => true,
                    'title' => $summary['title'] ?? $title,
                    'description' => $summary['extract'] ?? strip_tags($firstResult['snippet'] ?? 'Aucune description disponible'),
                    'image' => $summary['originalimage']['source'] ?? $summary['thumbnail']['source'] ?? null,
                    'url' => $summary['content_urls']['desktop']['page'] ?? 'https://fr.wikipedia.org/wiki/' . $encodedTitle,
                    'suggestions' => $suggestions
                ];
            }
            
            return ['exists' => false];
            
        } catch (\Exception $e) {
            return ['exists' => false];
        }
    }
    
    /**
     * @return array<int, string>
     */
    private function getSuggestions(string $nom, string $marque, string $modele): array
    {
        $suggestions = [];
        
        if (!empty($nom)) {
            $suggestions[] = $nom;
        }
        if (!empty($marque)) {
            $suggestions[] = $marque;
        }
        if (!empty($modele)) {
            $suggestions[] = $modele;
        }
        
        // Suggestions générales agricoles
        $agriculturalTerms = [
            'Tracteur agricole',
            'Machine agricole',
            'Matériel agricole',
            'Engin agricole'
        ];
        
        foreach ($agriculturalTerms as $term) {
            $suggestions[] = $term;
            if (!empty($marque)) {
                $suggestions[] = $marque . ' ' . $term;
            }
        }
        
        // Marques connues
        $knownBrands = ['John Deere', 'Massey Ferguson', 'New Holland', 'Fendt', 'Case IH', 'Claas', 'Kubota'];
        foreach ($knownBrands as $brand) {
            if (!empty($marque) && stripos($brand, $marque) === false) {
                $suggestions[] = $brand;
            }
        }
        
        return array_unique(array_slice($suggestions, 0, 8));
    }

    // ── Statistiques ────────────────────────────────────────────────────────
    #[Route('/statistiques', name: 'machine_statistiques', methods: ['GET'])]
    public function machineStatistiques(MachineRepository $repo): Response
    {
        $stats = $repo->getStatistiques();

        return $this->render('machines/statistiques.html.twig', [
            'stats'        => $stats,
            'etatLabels'   => array_keys($stats['parEtat']),
            'etatValues'   => array_values($stats['parEtat']),
            'marqueLabels' => array_keys($stats['parMarque']),
            'marqueValues' => array_values($stats['parMarque']),
        ]);
    }

    // ── Nouvelle machine ────────────────────────────────────────────────────
    #[Route('/new', name: 'machine_new', methods: ['GET', 'POST'])]
    public function machineNew(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepo
    ): Response {
        $user = $this->getFullUser($userRepo);

        if (!$user) {
            $this->addFlash('error', 'Veuillez vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        $machine = new Machine();
        $form    = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $machine->setAgriculteur($user);
            $em->persist($machine);
            $em->flush();

            $this->addFlash('success', '✅ Machine « ' . $machine->getNom() . ' » ajoutée.');
            return $this->redirectToRoute('agri_machines');
        }

        return $this->render('machines/new.html.twig', [
            'form'    => $form,
            'machine' => $machine,
        ]);
    }

    // ── Détail ──────────────────────────────────────────────────────────────
    #[Route('/{id}', name: 'machine_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function machineShow(Machine $machine): Response
    {
        return $this->render('machines/show.html.twig', [
            'machine'        => $machine,
            'nomAgriculteur' => $machine->getNomAgriculteur(),
        ]);
    }

    // ── Édition ─────────────────────────────────────────────────────────────
    #[Route('/{id}/edit', name: 'machine_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function machineEdit(
        Request $request,
        Machine $machine,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', '✅ Machine « ' . $machine->getNom() . ' » mise à jour.');
            return $this->redirectToRoute('agri_machines');
        }

        return $this->render('machines/edit.html.twig', [
            'form'    => $form,
            'machine' => $machine,
        ]);
    }

    // ── Suppression ─────────────────────────────────────────────────────────
    #[Route('/{id}/delete', name: 'machine_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function machineDelete(
        Request $request,
        Machine $machine,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete_machine_' . $machine->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($machine);
            $em->flush();
            $this->addFlash('success', '🗑️ Machine supprimée.');
        } else {
            $this->addFlash('error', '❌ Token CSRF invalide.');
        }

        return $this->redirectToRoute('agri_machines');
    }
}