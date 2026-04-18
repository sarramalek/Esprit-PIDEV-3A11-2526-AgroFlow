<?php

namespace App\Controller\Materiels;

use App\Entity\Materiels\Maintenance;
use App\Form\Materiels\MaintenanceType;
use App\Repository\Materiels\MaintenanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/agriculteur/maintenances')]
class MaintenancesController extends AbstractController
{
    #[Route('', name: 'agri_maintenances_index', methods: ['GET'])]
    public function index(Request $request, MaintenanceRepository $repo): Response
    {
        $search = $request->query->get('search', '');
        $type   = $request->query->get('type', '');
        $sort   = $request->query->get('sort', 'dateMain');
        $dir    = $request->query->get('dir', 'DESC');

        $maintenances   = $repo->searchWithMaterielName($search, $type, $sort, $dir);
        $countByType    = $repo->countByTypePanne();
        $coutByMonth    = $repo->getCoutByMonth();
        $totalCout      = $repo->getTotalCout();
        $countByStatut  = $repo->countByStatut();
        $countByPriorite = $repo->countByPriorite();

        return $this->render('maintenances/index.html.twig', [
            'maintenances'    => $maintenances,
            'search'          => $search,
            'type'            => $type,
            'sort'            => $sort,
            'dir'             => $dir,
            'countByType'     => $countByType,
            'coutByMonth'     => $coutByMonth,
            'totalCout'       => $totalCout,
            'countByStatut'   => $countByStatut,
            'countByPriorite' => $countByPriorite,
        ]);
    }

    // ── API : Generate content via Google Gemini DIRECT ──
    #[Route('/api/gemini/generate', name: 'agri_maintenances_api_gemini_generate', methods: ['POST'])]
    public function generateWithGemini(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $prompt = $data['prompt'] ?? '';
        
        if (empty($prompt)) {
            return $this->json(['error' => 'Prompt manquant'], 400);
        }

        // Récupérer la configuration depuis les variables d'environnement
        $apiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');
        $model = $_ENV['GEMINI_MODEL'] ?? 'gemini-2.0-flash-exp';
        $apiUrl = $_ENV['GEMINI_API_URL'] ?? 'https://generativelanguage.googleapis.com/v1beta/models';
        $temperature = $_ENV['GEMINI_TEMPERATURE'] ?? 0.7;
        $maxTokens = $_ENV['GEMINI_MAX_TOKENS'] ?? 4000;
        
        if (empty($apiKey)) {
            return $this->json(['error' => 'Clé API Gemini non configurée. Ajoutez GEMINI_API_KEY dans .env.local'], 500);
        }

        // Construire l'URL de l'API
        $url = "{$apiUrl}/{$model}:generateContent?key={$apiKey}";

        // Préparer les données de la requête
        $requestData = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => (float)$temperature,
                'maxOutputTokens' => (int)$maxTokens,
                'topP' => 0.9,
                'topK' => 40
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ]
            ]
        ];

        // Configuration CURL
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($requestData)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return $this->json(['error' => 'Erreur CURL: ' . $error], 500);
        }
        
        curl_close($ch);

        // Vérifier la réponse
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMsg = $errorData['error']['message'] ?? $response;
            return $this->json(['error' => 'Erreur Gemini API: ' . $errorMsg], $httpCode);
        }

        $result = json_decode($response, true);
        $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (empty($text)) {
            return $this->json(['error' => 'Réponse vide de l\'API Gemini'], 500);
        }

        return $this->json(['text' => $text]);
    }

    // ── API : Generate maintenance schedule (page HTML) ──
    #[Route('/api/schedule/{id}', name: 'agri_maintenances_api_schedule', methods: ['GET'])]
    public function apiSchedule(int $id, MaintenanceRepository $repo): Response
    {
        $maintenance = $repo->findOneWithMaterielName($id);
        if (!$maintenance) {
            throw $this->createNotFoundException('Maintenance non trouvée.');
        }
        return $this->render('maintenances/api_schedule_html.html.twig', [
            'maintenance' => $maintenance,
        ]);
    }

    // ── API : Diagnostics (page HTML) ──
    #[Route('/api/diagnostics/{id}', name: 'agri_maintenances_api_diagnostics', methods: ['GET'])]
    public function apiDiagnostics(int $id, MaintenanceRepository $repo): Response
    {
        $maintenance = $repo->findOneWithMaterielName($id);
        if (!$maintenance) {
            throw $this->createNotFoundException('Maintenance non trouvée.');
        }
        return $this->render('maintenances/api_diagnostics_html.html.twig', [
            'maintenance' => $maintenance,
        ]);
    }

    // EXPORT EXCEL
    #[Route('/export/excel', name: 'agri_maintenances_export_excel', methods: ['GET'])]
    public function exportExcel(MaintenanceRepository $repo): StreamedResponse
    {
        $maintenances = $repo->findAllOrderedByDate();

        $response = new StreamedResponse(function () use ($maintenances) {
            $handle = fopen('php://output', 'w+');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['AGROFLOW — Rapport Maintenances'], ';');
            fputcsv($handle, ['Exporté le : ' . (new \DateTime())->format('d/m/Y H:i')], ';');
            fputcsv($handle, ['Nombre d\'enregistrements : ' . count($maintenances)], ';');
            fputcsv($handle, [], ';');
            fputcsv($handle, ['#', 'Type de panne', 'Coût (DT)', 'Date', 'Statut', 'Priorité', 'Kilométrage', 'Description', 'Recommandation', 'ID Matériel', 'Nom Matériel'], ';');

            $index = 1;
            $totalCout = 0.0;
            foreach ($maintenances as $m) {
                fputcsv($handle, [
                    $index++,
                    $m->getTypePanne(),
                    number_format($m->getCout(), 2, ',', ' '),
                    $m->getDateMain()?->format('d/m/Y') ?? '',
                    $m->getStatut() ?? '',
                    $m->getPriorite() ?? '',
                    $m->getKilometrage() ?? '',
                    $m->getDescription() ?? '',
                    $m->getRecommandation() ?? '',
                    $m->getIdM() ? '#' . $m->getIdM() : '',
                    $m->getNom() ?? '',
                ], ';');
                $totalCout += $m->getCout();
            }

            fputcsv($handle, [], ';');
            fputcsv($handle, ['', 'TOTAL', number_format($totalCout, 2, ',', ' ') . ' DT'], ';');
            fclose($handle);
        });

        $filename = 'maintenances_' . (new \DateTime())->format('Ymd_Hi') . '.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        return $response;
    }

    // EXPORT PDF
    #[Route('/export/pdf', name: 'agri_maintenances_export_pdf', methods: ['GET'])]
    public function exportPdf(MaintenanceRepository $repo): Response
    {
        return $this->render('maintenances/pdf.html.twig', [
            'maintenances' => $repo->findAllOrderedByDate(),
        ]);
    }

    // CREATE
    #[Route('/new', name: 'agri_maintenances_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $maintenance = new Maintenance();
        $form = $this->createForm(MaintenanceType::class, $maintenance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($maintenance);
            $em->flush();
            $this->addFlash('success', 'Maintenance ajoutée avec succès.');
            return $this->redirectToRoute('agri_maintenances_index');
        }

        return $this->render('maintenances/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // SHOW
    #[Route('/{id}', name: 'agri_maintenances_show', methods: ['GET'])]
    public function show(int $id, MaintenanceRepository $repo): Response
    {
        $maintenance = $repo->findOneWithMaterielName($id);
        if (!$maintenance) {
            throw $this->createNotFoundException('Maintenance non trouvée.');
        }
        return $this->render('maintenances/show.html.twig', [
            'maintenance' => $maintenance,
        ]);
    }

    // EDIT
    #[Route('/{id}/edit', name: 'agri_maintenances_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Maintenance $maintenance, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MaintenanceType::class, $maintenance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Maintenance mise à jour avec succès.');
            return $this->redirectToRoute('agri_maintenances_show', ['id' => $maintenance->getIdMain()]);
        }

        return $this->render('maintenances/edit.html.twig', [
            'form'        => $form->createView(),
            'maintenance' => $maintenance,
        ]);
    }

    // DELETE
    #[Route('/{id}/delete', name: 'agri_maintenances_delete', methods: ['POST'])]
    public function delete(Request $request, Maintenance $maintenance, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete_maintenance_' . $maintenance->getIdMain(), $token)) {
            $em->remove($maintenance);
            $em->flush();
            $this->addFlash('success', 'Maintenance supprimée avec succès.');
        } else {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
        }
        return $this->redirectToRoute('agri_maintenances_index');
    }
}