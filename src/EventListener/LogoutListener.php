<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [LogoutEvent::class => 'onLogout'];
    }

    public function onLogout(LogoutEvent $event): void
{
    $session = $event->getRequest()->getSession();
    
    // Supprimer toutes les clés de check abonnements
    foreach ($session->all() as $key => $value) {
        if (str_starts_with($key, 'abonnements_check_')) {
            $session->remove($key);
        }
    }
    
    $session->invalidate();
}
}