<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LocaleController extends AbstractController
{
    private const ALLOWED_LOCALES = ['fr', 'en', 'es'];

    #[Route('/set-locale/{locale}', name: 'app_set_locale', methods: ['GET'])]
    public function setLocale(Request $request, string $locale): Response
    {
        if (!in_array($locale, self::ALLOWED_LOCALES, true)) {
            $locale = 'fr';
        }

        $session = $request->getSession();
        if ($session) {
            $session->set('_locale', $locale);
        }

        $response = new RedirectResponse($request->headers->get('referer', $this->generateUrl('agri_home')));
        $response->headers->setCookie(new Cookie('locale', $locale, new \DateTime('+1 year')));

        return $response;
    }
}
