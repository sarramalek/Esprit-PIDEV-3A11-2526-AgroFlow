<?php

namespace App\EventListener;

use App\Service\TwoFactorCodeSender;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[AsEventListener]
class TwoFactorListener
{
    public function __construct(
        private TwoFactorCodeSender $sender,
        private RouterInterface $router
    ) {
    }

    public function __invoke(LoginSuccessEvent $event): void
    {
        $user = $event->getAuthenticatedToken()->getUser();

        if (!$user->isTwoFactorEnabled()) {
            return;
        }

        $session = $event->getRequest()->getSession();
        $session->set('2fa_pending_user_id', $user->getId());

        $this->sender->send($user, $session);
        $event->getAuthenticatedToken()->setAttributes(['2fa_pending' => true]);

        $event->setResponse(new RedirectResponse($this->router->generate('app_2fa_verify')));
    }
}

