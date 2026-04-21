<?php
// Standalone Telegram Send Script
$token = "8486321705:AAGakvOdcjM2hq_FcNQtVwa1eJxmGbFVAjM";
$chatId = "6221067209";

$message = "📊 <b>Test Bilan AgroFlow</b>\n\n";
$message .= "Bonjour Malek Marieme,\n\n";
$message .= "Ceci est un test manuel de votre bilan journalier.\n";
$message .= "✅ <b>Statut :</b> Système de notification corrigé et opérationnel.\n\n";
$message .= "📄 <i>AgroFlow Stabilization</i>";

$url = "https://api.telegram.org/bot{$token}/sendMessage";
$data = [
    'chat_id' => $chatId,
    'text' => $message,
    'parse_mode' => 'HTML'
];

$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\n",
        'content' => json_encode($data)
    ]
];

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) {
    echo "Erreur lors de l'envoi.";
} else {
    echo "Message envoyé avec succès !";
}
