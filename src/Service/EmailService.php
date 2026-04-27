<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Email;
use App\Entity\stocks\Article;
use App\Entity\User\User;

class EmailService
{
    private MailerInterface $mailer;
    private LoggerInterface $logger;

    public function __construct(MailerInterface $mailer, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function envoyerMailAlerte(Article $article, ?User $destinataire = null): bool
    {
        $destinataire = $destinataire ?? $article->getUser();
        if (!$destinataire) {
            $this->logger->error('EmailService: aucun destinataire défini pour l\'alerte', [
                'article_id' => $article->getId(),
            ]);
            return false;
        }

        $emailDestinataire = $destinataire->getEmail();
        if (!$emailDestinataire) {
            $this->logger->error('EmailService: destinataire sans email', [
                'article_id' => $article->getId(),
                'user_id' => $destinataire->getCin(),
            ]);
            return false;
        }

        $this->logger->info('EmailService: tentative d\'envoi de mail d\'alerte', [
            'article_id' => $article->getId(),
            'user_id' => $destinataire->getCin(),
            'email' => $emailDestinataire,
        ]);

        $html = '<div style="font-family: Arial, sans-serif; border: 1px solid #eee; padding: 20px; border-radius: 8px;">'
            . '<h2 style="color: #d9534f;">Attention, Stock Critique !</h2>'
            . '<p>Bonjour <strong>' . htmlspecialchars($destinataire->getNom() ?? 'Utilisateur') . '</strong>,</p>'
            . '<p>Le stock de l\'article <strong>' . htmlspecialchars($article->getNom()) . '</strong> '
            . 'est maintenant critique.</p>'
            . '<ul>'
            . '<li>Stock actuel : <strong>' . $article->getQuantiteEnStock() . '</strong> ' . htmlspecialchars($article->getUniteMesure()) . '</li>'
            . '<li>Seuil d\'alerte : <strong>' . $article->getSeuilAlerte() . '</strong> ' . htmlspecialchars($article->getUniteMesure()) . '</li>'
            . '</ul>'
            . '<p>Merci de reconstituer votre stock dès que possible.</p>'
            . '<hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">'
            . '<p style="font-size: 12px; color: #999;">Ceci est un email automatique généré par AgroFlow.</p>'
            . '</div>';

        try {
            $email = (new Email())
                ->from('noreply@agroflow.com')
                ->to($emailDestinataire)
                ->subject('Alerte de stock critique - ' . $article->getNom())
                ->html($html);

            $this->mailer->send($email);
            $this->logger->info('EmailService: email d\'alerte envoyé avec succès', [
                'article_id' => $article->getId(),
                'user_id' => $destinataire->getCin(),
            ]);
            return true;
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('EmailService: échec de l\'envoi de l\'email', [
                'article_id' => $article->getId(),
                'user_id' => $destinataire->getCin(),
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
