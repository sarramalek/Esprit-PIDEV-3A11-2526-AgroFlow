<?php
// src/Service/SmsService.php

namespace App\Service;

use Twilio\Rest\Client as TwilioClient;
use Twilio\Exceptions\RestException;
use Vonage\Client as VonageClient;
use Vonage\Client\Credentials\Basic;
use Vonage\SMS\Message\SMS;
use Psr\Log\LoggerInterface;

class SmsService
{
    private TwilioClient $twilioClient;
    private VonageClient $vonageClient;
    private LoggerInterface $logger;
    private string $twilioFrom;
    private string $vonageFrom;

    public function __construct(
        string $twilioSid,
        string $twilioToken,
        string $twilioFrom,
        string $vonageApiKey,
        string $vonageApiSecret,
        string $vonageFrom,
        LoggerInterface $logger
    ) {
        $this->twilioClient = new TwilioClient($twilioSid, $twilioToken);
        $this->vonageClient = new VonageClient(new Basic($vonageApiKey, $vonageApiSecret));
        $this->twilioFrom   = $twilioFrom;
        $this->vonageFrom   = $vonageFrom;
        $this->logger       = $logger;
    }

    /**
     * Génère un code aléatoire à 6 chiffres
     */
    public function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Envoie le code par SMS.
     * Twilio en priorité, Vonage en fallback si limite atteinte.
     */
    public function sendVerificationCode(string $phone): array
    {
        $code = $this->generateCode();

        try {
            // ✅ Tentative Twilio
            $this->twilioClient->messages->create($phone, [
                'from' => $this->twilioFrom,
                'body' => "Votre code de vérification : $code",
            ]);

            $this->logger->info("SMS Twilio envoyé à $phone");

            return [
                'success'  => true,
                'channel'  => 'twilio',
                'code'     => $code, // à stocker en session/DB
            ];

        } catch (RestException $e) {

            // 🚨 Limite Twilio (429) → Fallback Vonage
            if ($e->getStatusCode() === 429) {
                $this->logger->warning("Limite Twilio atteinte. Bascule sur Vonage pour $phone");
                return $this->sendViaVonage($phone, $code);
            }

            $this->logger->error("Erreur Twilio : " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fallback : envoi via Vonage
     */
    private function sendViaVonage(string $phone, string $code): array
    {
        $message = new SMS($phone, $this->vonageFrom, "Votre code de vérification : $code");

        $response = $this->vonageClient->sms()->send($message);
        $current  = $response->current();

        if ($current->getStatus() === 0) {
            $this->logger->info("SMS Vonage envoyé avec succès.");

            return [
                'success' => true,
                'channel' => 'vonage',
                'code'    => $code, // à stocker en session/DB
            ];
        }

        $this->logger->error("Échec Vonage : " . $current->getStatus());

        throw new \RuntimeException("Échec de l'envoi SMS via Vonage. Status: " . $current->getStatus());
    }
}