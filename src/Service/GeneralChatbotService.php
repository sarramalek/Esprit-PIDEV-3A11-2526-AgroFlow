<?php
// src/Service/GeneralChatbotService.php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class GeneralChatbotService
{
    private const GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly MaintenanceChatbotService $maintenanceChatbotService,
        #[Autowire(env: 'GEMINI_API_KEY')]
        private readonly string $geminiApiKey,
    ) {}

    public function chat(string $message, array $history = [], ?int $machineId = null, ?string $machineName = null): array
    {
        if ($this->isAgriculturalMaintenance($message)) {
            return $this->maintenanceChatbotService->chat($message, $history, $machineId, $machineName);
        }
        
        return $this->generalChat($message, $history);
    }

    private function isAgriculturalMaintenance(string $message): bool
    {
        $keywords = [
            'tracteur', 'panne', 'moteur', 'vidange', 'filtre', 'hydraulique',
            'embrayage', 'batterie', 'pneu', 'moissonneuse', 'semoir',
            'pulvérisateur', 'labour', 'récolte', 'agricole', 'mecanique',
            'casse', 'fuite', 'bruit', 'chauffe', 'injecteur', 'turbo',
            'demarrer', 'démarre', 'caler', 'cale', 'fumée', 'fumee'
        ];
        
        $messageLower = mb_strtolower($message);
        
        foreach ($keywords as $keyword) {
            if (str_contains($messageLower, $keyword)) {
                return true;
            }
        }
        
        return false;
    }

    private function generalChat(string $message, array $history): array
    {
        $response = $this->callGemini($message, $history);
        
        return [
            'success' => true,
            'message' => $response,
            'detectedType' => null,
            'compatibleParts' => [],
            'hasHistory' => false,
            'historyCount' => 0,
        ];
    }

    private function callGemini(string $message, array $history): string
    {
        $systemPrompt = "Tu es AgroBot, un assistant intelligent pour les agriculteurs tunisiens.\nRéponds UNIQUEMENT en FRANÇAIS, sois amical et précis.\nUtilise des émojis comme 🌾 🔧 📦 💡.";
        
        $contents = [];
        foreach ($history as $msg) {
            $role = $msg['role'] === 'user' ? 'user' : 'model';
            $contents[] = ['role' => $role, 'parts' => [['text' => $msg['content']]]];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];
        
        try {
            $response = $this->httpClient->request('POST', self::GEMINI_API_URL . '?key=' . $this->geminiApiKey, [
                'json' => [
                    'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 1500,
                        'topP' => 0.95,
                        'topK' => 40,
                    ],
                ],
                'timeout' => 30,
            ]);
            
            $data = $response->toArray();
            
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return $data['candidates'][0]['content']['parts'][0]['text'];
            }
            
            return "❌ Désolé, je n'ai pas pu générer une réponse. Pouvez-vous reformuler ?";
                
        } catch (\Throwable $e) {
            error_log('[GeneralChatbot] Error: ' . $e->getMessage());
            return "❌ Désolé, je rencontre des difficultés techniques. Veuillez réessayer.";
        }
    }
}