<?php

namespace App\Security;

use App\Entity\User\User;
use App\Repository\User\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    public function __construct(
        private RouterInterface       $router,
        private UserRepository        $userRepo,
        private TokenStorageInterface $tokenStorage,
    ) {}

    // ──────────────────────────────────────────────
    // 1. Construction du Passport (vérif email+mdp)
    // ──────────────────────────────────────────────
    public function authenticate(Request $request): Passport
    {
        $email    = $request->request->get('_username', '');
        $password = $request->request->get('_password', '');

        return new Passport(
            new UserBadge($email, fn($id) => $this->userRepo->findOneBy(['email' => $id])),
            new PasswordCredentials($password)
        );
    }

    // ──────────────────────────────────────────────
    // 2. Succès → 2FA ou dashboard selon rôle
    // ──────────────────────────────────────────────
    public function onAuthenticationSuccess(
        Request        $request,
        TokenInterface $token,
        string         $firewallName
    ): Response {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return new RedirectResponse($this->router->generate('app_login'));
        }

        $session = $request->getSession();

        // ── Compte banni ──
        if ((int) $user->getRole() === 0) {
            $this->tokenStorage->setToken(null);
            $session->invalidate();
            return new RedirectResponse($this->router->generate('app_bann'));
        }

        // ── 2FA activée ──
        if ($user->getTwoFactorEnabled()) {

            $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $session->set('2fa_pending_user_id', $user->getCin());
            $session->set('2fa_code',            $code);
            $session->set('2fa_expires_at',      time() + 300);

            // ── Email ──
            if ($user->getEmail()) {
                try {
                    $this->sendCodeByEmail($user->getEmail(), $code);
                    error_log('2FA EMAIL OK → ' . $user->getEmail());
                } catch (\Exception $e) {
                    error_log('2FA EMAIL FAILED: ' . $e->getMessage());
                }
            }

            // ── SMS ──
            if ($user->getTel()) {
                try {
                    $this->sendCodeBySms($user->getTel(), $code);
                    error_log('2FA SMS OK → ' . $user->getTel());
                } catch (\Exception $e) {
                    error_log('2FA SMS FAILED: ' . $e->getMessage());
                }
            }

            // ⚠️ Invalider le token → user pas encore connecté
            $this->tokenStorage->setToken(null);

            return new RedirectResponse($this->router->generate('app_2fa_verify'));
        }

        // ── Pas de 2FA → redirection selon rôle ──
        return new RedirectResponse(
            $this->router->generate($this->getRouteByRole($user))
        );
    }

    // ──────────────────────────────────────────────
    // 3. Échec d'authentification
    // ──────────────────────────────────────────────
    public function onAuthenticationFailure(
        Request                 $request,
        AuthenticationException $exception
    ): Response {
        $request->getSession()->set(
            \Symfony\Component\Security\Http\SecurityRequestAttributes::AUTHENTICATION_ERROR,
            $exception
        );
        return new RedirectResponse($this->router->generate('app_login'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->router->generate('app_login');
    }

    // ──────────────────────────────────────────────
    // Helpers privés
    // ──────────────────────────────────────────────
    private function getRouteByRole(User $user): string
    {
        return match((int) $user->getRole()) {
            0       => 'app_bann',
            1       => 'ouvrier_home',
            2       => 'agri_home',
            3       => 'admin_dashboard',
            default => 'ouvrier_home',
        };
    }

    /**
     * Envoi email via Resend REST API (pur curl, sans SDK, aucune dépendance)
     * 
     * ⚠️  IMPORTANT — deux cas selon ton compte Resend :
     *
     *  • Sans domaine vérifié (tests) :
     *      - from  → obligatoirement "onboarding@resend.dev"
     *      - to    → obligatoirement l'email avec lequel tu t'es inscrit sur resend.com
     *
     *  • Avec domaine vérifié (production) :
     *      - from  → "AgroFlow <noreply@ton-domaine.com>"
     *      - to    → n'importe quel email
     *
     *  Pour vérifier ton domaine : https://resend.com/domains
     */
    private function sendCodeByEmail(string $email, string $code): void
    {
        $apiKey = $_ENV['RESEND_API_KEY'] ?? getenv('RESEND_API_KEY');

        if (!$apiKey) {
            throw new \RuntimeException('RESEND_API_KEY manquante');
        }

        // ─── Change ces deux lignes selon ton cas (voir commentaire ci-dessus) ───
        $from      = $_ENV['RESEND_FROM_EMAIL'] ?? getenv('RESEND_FROM_EMAIL') ?: 'onboarding@resend.dev';
        $recipient = $email; // en mode test sans domaine → remplace par ton email Resend
        // ─────────────────────────────────────────────────────────────────────────

        $payload = json_encode([
            'from'    => 'AgroFlow <' . $from . '>',
            'to'      => [$recipient],
            'subject' => 'AgroFlow – Code de vérification 2FA',
            'html'    => "
                <div style='font-family:DM Sans,sans-serif;max-width:480px;margin:0 auto;
                            padding:32px;background:#FCF8E6;border-radius:12px;'>
                    <h2 style='color:#2D5A27;'>Vérification en deux étapes</h2>
                    <p style='color:#555;'>Votre code de connexion AgroFlow :</p>
                    <div style='background:white;border:2px solid #c8e6c0;border-radius:10px;
                                padding:20px;text-align:center;letter-spacing:0.3em;
                                font-size:32px;font-weight:700;color:#2D5A27;'>
                        {$code}
                    </div>
                    <p style='color:#888;font-size:13px;margin-top:20px;'>
                        Expire dans <strong>5 minutes</strong>.
                    </p>
                </div>
            ",
        ]);

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => $payload,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            throw new \RuntimeException('cURL error: ' . $curlErr);
        }

        if ($httpCode >= 400) {
            throw new \RuntimeException('Resend HTTP ' . $httpCode . ': ' . $response);
        }
    }

    /**
     * Envoi SMS via Twilio REST API (pur curl, sans SDK)
     * Twilio utilise HTTPS → fonctionne sur tous les plans Railway
     */
    private function sendCodeBySms(string $tel, string $code): void
    {
        $sid   = $_ENV['TWILIO_ACCOUNT_SID'] ?? getenv('TWILIO_ACCOUNT_SID');
        $token = $_ENV['TWILIO_AUTH_TOKEN']  ?? getenv('TWILIO_AUTH_TOKEN');
        $from  = $_ENV['TWILIO_FROM']        ?? getenv('TWILIO_FROM');

        if (!$sid || !$token || !$from) {
            throw new \RuntimeException('Variables Twilio manquantes (SID/TOKEN/FROM)');
        }

        if (!str_starts_with($tel, '+')) {
            $tel = '+216' . ltrim($tel, '0');
        }

        $ch = curl_init("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json");
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERPWD        => "{$sid}:{$token}",
            CURLOPT_POSTFIELDS     => http_build_query([
                'From' => $from,
                'To'   => $tel,
                'Body' => "AgroFlow – Code 2FA : {$code} (valable 5 min)",
            ]),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            throw new \RuntimeException('cURL error: ' . $curlErr);
        }

        if ($httpCode >= 400) {
            throw new \RuntimeException('Twilio HTTP ' . $httpCode . ': ' . $response);
        }
    }
}