<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Exception;

class SmartVisionService
{
    private HttpClientInterface $httpClient;
    private string $geminiKey;

    public function __construct(
        HttpClientInterface $httpClient,
        #[Autowire('%env(GEMINI_API_KEY)%')] string $geminiKey
    ) {
        $this->httpClient = $httpClient;
        $this->geminiKey = $geminiKey;
    }

    /**
     * Analyse une image via Gemini 1.5 Flash pour extraire le nom et la catégorie.
     *
     * @param string $base64Image L'image encodée en base64 (sans le préfixe data:image/jpeg;base64,)
     * @param string $mimeType Le type mime de l'image
     * @return array Tableau associatif contenant 'nom' et 'categorie'
     */
    /**
     * @return array{nom:string|null, categorie:string|null}
     */
    public function analyzeImage(string $base64Image, string $mimeType = 'image/jpeg'): array
    {
        if (empty($this->geminiKey)) {
            throw new Exception("La clé d'API GEMINI_API_KEY est manquante dans le fichier .env");
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=' . trim($this->geminiKey);

        $prompt = "Tu es un expert en inventaire agricole. Analyse précisément cette image de produit :
        1. Identifie le NOM DU PRODUIT écrit sur l'emballage (ex: 'YaraMila', 'Roundup', 'Semences Maïs Pioneer'). Sois le plus précis possible sur le texte OCR.
        2. Déduis la CATÉGORIE technique (Semences, Pesticide, Engrais, Matériel, ou Récolte) en observant l'objet.
        
        Réponds UNIQUEMENT avec un JSON pur :
        {
          \"nom\": \"Nom précis extrait de l'image\",
          \"categorie\": \"Catégorie technique\"
        }";

        $response = $this->httpClient->request('POST', $url, [
            'json' => [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $base64Image
                                ]
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.2, // Faible créativité pour être précis sur l'OCR
                    'responseMimeType' => 'application/json' // Force le JSON si supporté
                ]
            ]
        ]);

        try {
            $content = $response->toArray();
        } catch (Exception $e) {
            $statusCode = $response->getStatusCode();
            if ($statusCode === 429) {
                throw new Exception("Quota Gemini épuisé. Veuillez réessayer plus tard ou utiliser une autre clé API.");
            }
            throw new Exception("Erreur lors de la communication avec Gemini : " . $e->getMessage());
        }
        
        if (!isset($content['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception("Réponse invalide de l'API Gemini : structure de réponse inattendue.");
        }

        $jsonText = $content['candidates'][0]['content']['parts'][0]['text'];
        
        // Nettoyage potentiel des backticks Markdown si Gemini les renvoie quand même
        $jsonText = str_replace(['```json', '```'], '', $jsonText);
        $result = json_decode(trim($jsonText), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Le format JSON retourné par Gemini est invalide : " . $jsonText);
        }

        return [
            'nom' => $result['nom'] ?? null,
            'categorie' => $result['categorie'] ?? null
        ];
    }
}
