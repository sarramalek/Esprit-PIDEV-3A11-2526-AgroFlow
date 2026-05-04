<?php

namespace App\Command;

use App\Entity\stocks\Article;
use App\Entity\stocks\MouvementStock;
use App\Service\TelegramService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:daily-stock-summary',
    description: 'Envoie un bilan journalier des stocks via Telegram',
)]
class DailyStockSummaryCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private TelegramService $telegramService;

    public function __construct(EntityManagerInterface $entityManager, TelegramService $telegramService)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->telegramService = $telegramService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Génération du bilan journalier des stocks par Agriculteur');

        $todayStart = new \DateTime('today 00:00:00');
        $todayEnd = new \DateTime('today 23:59:59');

        // Récupérer les agriculteurs (role = 2)
        // Note : On utilise le repository User (namespace à vérifier, on suppose App\Entity\User\User)
        $agriculteurs = $this->entityManager->getRepository(\App\Entity\User\User::class)->findBy(['role' => 2]);

        $dateStr = (new \DateTime())->format('d/m/Y');

        $sentCount = 0;

        foreach ($agriculteurs as $agri) {
            // CORRECTION : On utilise la colonne dédiée au Chat ID Telegram
            $chatId = $agri->getTelegramChatId();
            
            if (!$chatId) {
                continue; // Pas d'ID Telegram renseigné, on passe au suivant
            }

            // Récupérer les mouvements du jour pour cet agriculteur
            $mouvements = $this->entityManager->getRepository(MouvementStock::class)
                ->createQueryBuilder('m')
                ->join('m.article', 'a')
                ->where('a.user = :agri')
                ->andWhere('m.dateMouvement BETWEEN :start AND :end')
                ->setParameter('agri', $agri)
                ->setParameter('start', $todayStart)
                ->setParameter('end', $todayEnd)
                ->getQuery()
                ->getResult();

            // S'il n'y a aucun mouvement, on peut choisir d'envoyer ou non le bilan
            // Ici, on envoie quand même pour confirmer que tout va bien, ou on peut skip.
            // On va inclure les alertes même s'il n'y a pas eu de mouvement.

            $entreesCount = 0;
            $entreesQuantite = 0;
            $sortiesCount = 0;
            $sortiesQuantite = 0;

            foreach ($mouvements as $m) {
                if (strtoupper($m->getType()) === 'ENTREE') {
                    $entreesCount++;
                    $entreesQuantite += $m->getQuantite();
                } elseif (strtoupper($m->getType()) === 'SORTIE') {
                    $sortiesCount++;
                    $sortiesQuantite += $m->getQuantite();
                }
            }

            // Récupérer les articles en alerte pour cet agriculteur
            $articles = $this->entityManager->getRepository(Article::class)->findBy(['user' => $agri]);
            $alertesCount = 0;
            foreach ($articles as $article) {
                if ($article->getQuantiteEnStock() <= $article->getSeuilAlerte()) {
                    $alertesCount++;
                }
            }

            $message = "📊 <b>Bilan AgroFlow - {$dateStr}</b>\n\n";
            $message .= "Bonjour {$agri->getNom()},\n\n";
            $message .= "✅ <b>Entrées :</b> +{$entreesQuantite} unités ({$entreesCount} mouvements)\n";
            $message .= "❌ <b>Sorties :</b> -{$sortiesQuantite} unités ({$sortiesCount} mouvements)\n\n";
            
            if ($alertesCount > 0) {
                $message .= "⚠️ <b>Alertes :</b> {$alertesCount} articles sont sous le seuil critique !\n\n";
            } else {
                $message .= "✅ <b>Alertes :</b> Aucun article en rupture.\n\n";
            }
            
            $message .= "📄 <i>Rapport complet disponible sur votre tableau de bord.</i>";

            // Envoi au Telegram de l'agriculteur (champ tel)
            $this->telegramService->notifier($message, $chatId);
            $sentCount++;
        }

        $io->success("Le bilan a été envoyé avec succès à {$sentCount} agriculteur(s) via Telegram.");

        return Command::SUCCESS;
    }
}
