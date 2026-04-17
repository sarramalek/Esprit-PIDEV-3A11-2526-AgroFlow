<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    public function __construct(private MailerInterface $mailer) {}

    public function envoyerCredentialsOuvrier(string $emailDest, string $nom, string $prenom, string $telephone): void
    {
        $email = (new Email())
            ->from('maleksarra362@gmail.com')
            ->to($emailDest)
            ->subject('🌿 Vos identifiants de connexion - Gestion Agricole')
            ->html("
                <div style='font-family: Segoe UI, sans-serif; max-width: 500px; margin: auto; border: 1px solid #ddd; border-radius: 12px; overflow: hidden;'>
                    <div style='background: linear-gradient(135deg, #2d6a4f, #52b788); padding: 1.5rem 2rem;'>
                        <h2 style='color: white; margin: 0;'>🌿 Bienvenue, {$prenom} {$nom} !</h2>
                    </div>
                    <div style='padding: 2rem;'>
                        <p>Bienvenue, {$prenom} {$nom} au sein de notre plateforme ! Voici vos identifiants de connexion :</p>
                        <table style='width:100%; border-collapse: collapse; margin: 1rem 0;'>
                            <tr style='background:#f4f4f4;'>
                                <td style='padding:.6rem 1rem; font-weight:600;'>📧 Email</td>
                                <td style='padding:.6rem 1rem;'>{$emailDest}</td>
                            </tr>
                            <tr>
                                <td style='padding:.6rem 1rem; font-weight:600;'>🔑 Mot de passe</td>
                                <td style='padding:.6rem 1rem;'>{$telephone}</td>
                            </tr>
                        </table>
                        <p style='color:#888; font-size:.85rem;'>Pensez à changer votre mot de passe après votre première connexion.</p>
                    </div>
                </div>
            ");

        $this->mailer->send($email);
    }
}