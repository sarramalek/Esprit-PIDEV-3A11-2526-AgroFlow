<?php
// src/Controller/Materiels/ChatbotController.php

namespace App\Controller\Materiels;

use App\Service\GeneralChatbotService;
use App\Repository\Materiels\MachineRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/agriculteur/chatbot')]
class ChatbotController extends AbstractController
{
    public function __construct(
        private readonly GeneralChatbotService $generalChatbotService,
        private readonly MachineRepository $machineRepository,
        private readonly HttpClientInterface $httpClient,
        #[Autowire(env: 'GEMINI_API_KEY')]
        private readonly string $geminiApiKey,
    ) {}

    #[Route('', name: 'agri_chatbot_index', methods: ['GET'])]
    public function index(): Response
    {
        $machines = $this->machineRepository->findAll();
        return $this->render('maintenances/chatbot.html.twig', [
            'machines' => $machines,
        ]);
    }

    #[Route('/api/machines', name: 'agri_chatbot_api_machines', methods: ['GET'])]
    public function getMachines(): JsonResponse
    {
        $machines = $this->machineRepository->findAll();
        
        $data = array_map(function($machine) {
            return [
                'id' => $machine->getId(),
                'nom' => $machine->getNom(),
                'marque' => $machine->getMarque(),
                'modele' => $machine->getModele(),
                'kilometrage' => $machine->getKilometrage(),
            ];
        }, $machines);
        
        return $this->json(['success' => true, 'machines' => $data]);
    }

    #[Route('/api/chat/general', name: 'agri_chatbot_api_general', methods: ['POST'])]
    public function generalChat(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!is_array($data)) {
                return $this->json(['success' => false, 'error' => 'Format invalide'], 400);
            }

            $message = trim($data['message'] ?? '');
            $history = is_array($data['history'] ?? null) ? $data['history'] : [];
            $machineId = isset($data['machineId']) && !empty($data['machineId']) ? (int) $data['machineId'] : null;
            $machineName = $data['machineName'] ?? null;

            if (empty($message)) {
                return $this->json(['success' => false, 'error' => 'Message vide'], 400);
            }

            $result = $this->generalChatbotService->chat($message, $history, $machineId, $machineName);
            return $this->json($result);

        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error'   => 'Erreur interne : ' . $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/api/nearby-shops', name: 'agri_chatbot_api_nearby_shops', methods: ['GET'])]
    public function getNearbyShops(Request $request): JsonResponse
    {
        $lat    = $request->query->get('lat');
        $lng    = $request->query->get('lng');
        $radius = (int) $request->query->get('radius', 10);

        $centerLat = $lat !== null ? (float) $lat : 36.8065;
        $centerLng = $lng !== null ? (float) $lng : 10.1815;

        $shops = $this->searchOpenStreetMap($centerLat, $centerLng, $radius);

        return $this->json([
            'success'  => true,
            'center'   => ['lat' => $centerLat, 'lng' => $centerLng],
            'radiusKm' => $radius,
            'shops'    => $shops,
            'total'    => count($shops),
        ]);
    }

    private function searchOpenStreetMap(float $lat, float $lng, int $radius): array
    {
        $radiusM = $radius * 1000;

        $query = sprintf(
            '[out:json][timeout:20];('
            . 'node["shop"="car_repair"](around:%d,%f,%f);'
            . 'node["shop"="agricultural"](around:%d,%f,%f);'
            . 'node["amenity"="vehicle_repair"](around:%d,%f,%f);'
            . 'node["shop"="car_parts"](around:%d,%f,%f);'
            . ');out body;',
            $radiusM, $lat, $lng,
            $radiusM, $lat, $lng,
            $radiusM, $lat, $lng,
            $radiusM, $lat, $lng
        );

        try {
            $response = $this->httpClient->request('GET', 'https://overpass-api.de/api/interpreter', [
                'query'   => ['data' => $query],
                'timeout' => 20,
                'headers' => ['User-Agent' => 'AgroBot/1.0 (contact@agroflow.tn)'],
            ]);

            $data  = $response->toArray();
            $shops = [];

            foreach ($data['elements'] ?? [] as $element) {
                if (!isset($element['lat'], $element['lon'], $element['tags'])) {
                    continue;
                }

                $tags = $element['tags'];
                $type = $this->getShopType($tags);

                $shops[] = [
                    'id'        => $element['id'],
                    'name'      => $tags['name'] ?? $tags['shop'] ?? $tags['amenity'] ?? 'Professionnel',
                    'lat'       => $element['lat'],
                    'lng'       => $element['lon'],
                    'address'   => trim(
                        ($tags['addr:housenumber'] ?? '')
                        . ' ' . ($tags['addr:street'] ?? '')
                        . ', ' . ($tags['addr:city'] ?? '')
                    ) ?: 'Adresse non disponible',
                    'phone'     => $tags['phone'] ?? $tags['contact:phone'] ?? null,
                    'website'   => $tags['website'] ?? $tags['contact:website'] ?? null,
                    'type'      => $type,
                    'typeLabel' => $this->getTypeLabel($type),
                    'color'     => $this->getTypeColor($type),
                    'icon'      => $this->getTypeIcon($type),
                    'distance'  => $this->calculateDistance($lat, $lng, $element['lat'], $element['lon']),
                ];
            }

            usort($shops, static fn($a, $b) => $a['distance'] <=> $b['distance']);

            return empty($shops)
                ? $this->getFallbackShops($lat, $lng)
                : array_slice($shops, 0, 20);

        } catch (\Throwable $e) {
            error_log('[Overpass] Error: ' . $e->getMessage());
            return $this->getFallbackShops($lat, $lng);
        }
    }

    private function getShopType(array $tags): string
    {
        $shop    = $tags['shop']   ?? null;
        $amenity = $tags['amenity'] ?? null;

        return match(true) {
            $shop === 'car_repair'      => 'garage',
            $shop === 'agricultural'    => 'agricole',
            $shop === 'car_parts'       => 'pieces',
            $amenity === 'vehicle_repair' => 'garage',
            default                     => 'autre',
        };
    }

    private function getTypeLabel(string $type): string
    {
        return match($type) {
            'garage'   => 'Garage / Atelier',
            'agricole' => 'Magasin agricole',
            'pieces'   => 'Fournisseur de pièces',
            default    => "Point d'intérêt",
        };
    }

    private function getTypeColor(string $type): string
    {
        return match($type) {
            'garage'   => '#e74c3c',
            'agricole' => '#27ae60',
            'pieces'   => '#f39c12',
            default    => '#3498db',
        };
    }

    private function getTypeIcon(string $type): string
    {
        return match($type) {
            'garage'   => 'wrench',
            'agricole' => 'leaf',
            'pieces'   => 'box',
            default    => 'map-pin',
        };
    }

    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R    = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return round($R * 2 * atan2(sqrt($a), sqrt(1 - $a)), 1);
    }

    private function getFallbackShops(float $lat, float $lng): array
    {
        return [
            [
                'id'        => 1,
                'name'      => 'Garage Agricole Tunis',
                'lat'       => $lat + 0.02,
                'lng'       => $lng + 0.01,
                'address'   => 'Zone Industrielle, Tunis',
                'phone'     => '+216 71 123 456',
                'website'   => null,
                'type'      => 'garage',
                'typeLabel' => 'Garage / Atelier',
                'color'     => '#e74c3c',
                'icon'      => 'wrench',
                'distance'  => 2.5,
            ],
            [
                'id'        => 2,
                'name'      => 'Pièces Agricoles SA',
                'lat'       => $lat - 0.015,
                'lng'       => $lng + 0.02,
                'address'   => 'Avenue Habib Bourguiba, Tunis',
                'phone'     => '+216 71 789 012',
                'website'   => null,
                'type'      => 'pieces',
                'typeLabel' => 'Fournisseur de pièces',
                'color'     => '#f39c12',
                'icon'      => 'box',
                'distance'  => 3.2,
            ],
            [
                'id'        => 3,
                'name'      => 'Coopérative Agricole Centrale',
                'lat'       => $lat + 0.01,
                'lng'       => $lng - 0.018,
                'address'   => 'Route de la Marsa, Tunis',
                'phone'     => '+216 71 345 678',
                'website'   => null,
                'type'      => 'agricole',
                'typeLabel' => 'Magasin agricole',
                'color'     => '#27ae60',
                'icon'      => 'leaf',
                'distance'  => 1.8,
            ],
        ];
    }
}