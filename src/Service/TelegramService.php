<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class TelegramService
{
    private $httpClient;
    private $token;
    private $chatId;

    public function __construct(
        HttpClientInterface $httpClient,
        #[Autowire('%env(TELEGRAM_TOKEN)%')] string $telegramToken
    ) {
        $this->httpClient = $httpClient;
        $this->token = $telegramToken;
    }

    public function notifier(string $message, ?string $chatId = null): void
    {
        if (!$chatId) {
            return;
        }

        $url = "https://api.telegram.org/bot{$this->token}/sendMessage";

        try {
            $response = $this->httpClient->request('POST', $url, [
                'json' => [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML'
                ]
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                $errorData = $response->toArray(false);
                throw new \Exception("Erreur Telegram ({$statusCode}) : " . ($errorData['description'] ?? 'Inconnue'));
            }
        } catch (\Exception $e) {
            // Vous pouvez logguer l'erreur ou la relancer pour la voir dans la console
            throw new \Exception("Echec de l'envoi Telegram : " . $e->getMessage());
        }
    }
}
