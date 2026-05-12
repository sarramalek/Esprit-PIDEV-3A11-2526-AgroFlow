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
        private TokenStorageInterface $tokenStorage,  // ← pour invalider le token
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
            // Invalider immédiatement
            $this->tokenStorage->setToken(null);
            $session->invalidate();
            return new RedirectResponse($this->router->generate('app_bann'));
        }

        // ── 2FA activée ──
        if ($user->getTwoFactorEnabled()) {

            // Générer le code OTP
            $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Stocker en session
            $session->set('2fa_pending_user_id', $user->getCin());
            $session->set('2fa_code',            $code);
            $session->set('2fa_expires_at',      time() + 300); // 5 min
// ── Envoyer le code par email ──
if ($user->getEmail()) {
    try {
        $this->sendCodeByEmail($user->getEmail(), $code);
    } catch (\Exception $e) {
        // email failed silently
    }
}

// ── Envoyer aussi par SMS si numéro présent ──
if ($user->getTel()) {
    try {
        $this->sendCodeBySms($user->getTel(), $code);
    } catch (\Exception $e) {
        // sms failed silently
    }
}
            // ⚠️ CRUCIAL : invalider le token Symfony
            // → l'utilisateur n'est PAS encore connecté
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

    private function sendCodeByEmail(string $email, string $code): void
    {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'maleksarra362@gmail.com';
            $mail->Password   = 'dwxqvewsbormytyn';
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465; // ← changed from 587
        $mail->Timeout    = 5;   // ← ADD THIS
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('maleksarra362@gmail.com', 'AgroFlow');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'AgroFlow – Code de vérification 2FA';
            $mail->Body    = "
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
            ";
            $mail->send();
        } catch (\Exception $e) {
            error_log('EMAIL ERROR: ' . $e->getMessage()); // ← ADD THIS
        throw $e;
        }
    }

    private function sendCodeBySms(string $tel, string $code): void
    {
        try {
            $twilio = new \Twilio\Rest\Client(
                $_ENV['TWILIO_ACCOUNT_SID'],
                $_ENV['TWILIO_AUTH_TOKEN']
            );
            if (!str_starts_with($tel, '+')) {
                $tel = '+216' . ltrim($tel, '0');
            }
            $twilio->messages->create($tel, [
                'from' => $_ENV['TWILIO_FROM'],
                'body' => "AgroFlow – Code 2FA : {$code} (valable 5 min)",
            ]);
        } catch (\Exception $e) {
            // silencieux
        }
    }
}