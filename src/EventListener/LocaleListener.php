<?php
namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class LocaleListener
{
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $locale  = $request->getSession()->get('_locale');
        if ($locale) {
            $request->setLocale($locale);
        }
    }
}