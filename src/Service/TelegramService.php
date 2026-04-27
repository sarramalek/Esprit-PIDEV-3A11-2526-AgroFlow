<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class TelegramService
{
    private HttpClientInterface $httpClient;
    private string $token;

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
            return; // Si aucun ID n'est fourni, on ne peut pas envoyer le message
        }

        $url = "https://api.telegram.org/bot{$this->token}/sendMessage";

        $this->httpClient->request('POST', $url, [
            'json' => [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML'
            ]
        ]);
    }
}
