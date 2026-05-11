<?php
// src/EventListener/TwoFactorListener.php
namespace App\EventListener;

use App\Entity\User\User;
use App\Service\TwoFactorCodeSender;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

#[AsEventListener]
class TwoFactorListener
{
    public function __construct(
        private TwoFactorCodeSender $sender,
        private RouterInterface $router
    ) {}

    public function __invoke(LoginSuccessEvent $event): void
    {
        $user = $event->getAuthenticatedToken()->getUser();

        if (!$user instanceof User) {
            return;
        }

        if (!$user->isTwoFactorEnabled()) {
            return; // pas de 2FA, connexion normale
        }

        $session = $event->getRequest()->getSession();
        $session->set('2fa_pending_user_id', $user->getCin());

        // Envoyer le code
        $this->sender->send($user, $session);

        // Déconnecter temporairement (on reconnecter après le code)
        $event->getAuthenticatedToken()->setAttributes(['2fa_pending' => true]);

        // Forcer la redirection vers la page 2FA
        $event->setResponse(new RedirectResponse($this->router->generate('app_2fa_verify')));
    }
}