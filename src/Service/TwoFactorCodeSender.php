<?php
// src/Service/TwoFactorCodeSender.php
namespace App\Service;

use App\Entity\User\User;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twilio\Rest\Client as TwilioClient;

class TwoFactorCodeSender
{
    public function send(User $user, SessionInterface $session): void
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $session->set('2fa_code',       $code);
        $session->set('2fa_expires_at', time() + 300); // 5 min

        // Choisir canal : SMS si tel dispo, sinon email
        if ($user->getTel()) {
            $this->sendSms($user->getTel(), $code);
        } else {
            $this->sendEmail($user->getEmail(), $code);
        }
    }

    private function sendSms(string $tel, string $code): void
    {
        try {
        $twilio = new TwilioClient(
            $_ENV['TWILIO_ACCOUNT_SID'],
            $_ENV['TWILIO_AUTH_TOKEN']
        );
        $twilio->messages->create($tel, [
            'from' => $_ENV['TWILIO_FROM'],
            'body' => "AgroFlow – Votre code : $code (valable 10 min)"
        ]);
        $smsSent = true;

    } catch (\Exception $e) {
        // Twilio a échoué (429 ou autre) → on tente Vonage
        $smsSent = false;
    }

    // ── Tentative 2 : Vonage (fallback) ──
    if (!$smsSent) {
        try {
            $vonage = new \Vonage\Client(
                new \Vonage\Client\Credentials\Basic(
                    $_ENV['VONAGE_API_KEY'],
                    $_ENV['VONAGE_API_SECRET']
                )
            );
            $response = $vonage->sms()->send(
                new \Vonage\SMS\Message\SMS(
                    $tel,
                    $_ENV['VONAGE_FROM'],
                    "AgroFlow – Votre code : $code (valable 10 min)"
                )
            );
            if ($response->current()->getStatus() === 0) {
                $smsSent = true;
            }
        } catch (\Exception $vonageEx) {
            $smsSent = false;
        }
    }

    if (!$smsSent) {
        $error = 'Impossible d\'envoyer le SMS. Veuillez choisir l\'envoi par email.';
    
    }
    }

    private function sendEmail(string $email, string $code): void
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'maleksarra362@gmail.com';
        $mail->Password   = 'dwxqvewsbormytyn';
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom('maleksarra362@gmail.com', 'AgroFlow');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'AgroFlow – Code de connexion';
        $mail->Body    = "
            <div style='font-family:DM Sans,sans-serif;max-width:480px;margin:0 auto;padding:32px;background:#FCF8E6;border-radius:12px;'>
                <h2 style='color:#2D5A27;'>Vérification en deux étapes</h2>
                <p style='color:#555;'>Votre code de connexion AgroFlow :</p>
                <div style='background:white;border:2px solid #c8e6c0;border-radius:10px;padding:20px;text-align:center;letter-spacing:0.3em;font-size:32px;font-weight:700;color:#2D5A27;'>$code</div>
                <p style='color:#888;font-size:13px;margin-top:20px;'>Valable <strong>5 minutes</strong>. Si vous n'avez pas tenté de vous connecter, ignorez ce message.</p>
            </div>";
        $mail->send();
    }
}