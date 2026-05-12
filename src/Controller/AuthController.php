<?php

namespace App\Controller;

use App\Entity\User\User;
use App\Form\RegistrationFormType;
use App\Form\LoginFormType;
use App\Repository\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use App\Service\SmsService;

class AuthController extends AbstractController
{
    // ==================== LOGIN ====================
    #[Route('/login', name: 'app_login')]
    public function login(
        AuthenticationUtils $authenticationUtils,
        Request $request,
        SessionInterface $session,
        UserRepository $userRepo,
        UserPasswordHasherInterface $hasher
    ): Response {
        $currentUser = $this->getUser();
        if ($currentUser instanceof User && !$session->get('2fa_pending_user_id')) {
            return $this->redirectByRole($currentUser);
        }

        $error        = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($session->get('2fa_pending_user_id')) {
            return $this->redirectToRoute('app_2fa_verify');
        }

        $loginForm = $this->createForm(LoginFormType::class, ['_username' => $lastUsername]);

        return $this->render('Auth/login.html.twig', [
            'loginForm' => $loginForm->createView(),
            'error'     => $error,
        ]);
    }

    // ==================== SEND/VERIFY CODE (inscription) ====================
    #[Route('/send-code', name: 'send_code', methods: ['POST'])]
    public function sendCode(
        Request $request,
        SmsService $smsService,
        SessionInterface $session
    ): JsonResponse {
        $phone = $request->request->get('phone');
        try {
            $result = $smsService->sendVerificationCode($phone);
            $session->set('verification_code',   $result['code']);
            $session->set('verification_expires', time() + 600);
            return $this->json(['success' => true, 'channel' => $result['channel'], 'message' => 'Code envoyé par SMS.']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Impossible d\'envoyer le SMS.'], 500);
        }
    }

    #[Route('/verify-code', name: 'verify_code', methods: ['POST'])]
    public function verifyCode(Request $request, SessionInterface $session): JsonResponse
    {
        $inputCode = $request->request->get('code');
        $savedCode = $session->get('verification_code');
        $expires   = $session->get('verification_expires');

        if (!$savedCode || time() > $expires) {
            return $this->json(['success' => false, 'message' => 'Code expiré.'], 400);
        }
        if ($inputCode !== $savedCode) {
            return $this->json(['success' => false, 'message' => 'Code incorrect.'], 400);
        }

        $session->remove('verification_code');
        $session->remove('verification_expires');
        return $this->json(['success' => true, 'message' => 'Code vérifié avec succès.']);
    }

    // ==================== 2FA ====================
    #[Route('/2fa/verify', name: 'app_2fa_verify')]
    public function twoFactorVerify(
        Request $request,
        SessionInterface $session,
        UserRepository $userRepo,
        EntityManagerInterface $em
    ): Response {
        $userCin = $session->get('2fa_pending_user_id');
        if (!$userCin) {
            return $this->redirectToRoute('app_login');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $entered   = trim($request->request->get('code', ''));
            $stored    = $session->get('2fa_code');
            $expiresAt = $session->get('2fa_expires_at');

            error_log('CIN: ' . $userCin);
            error_log('STORED: "' . $stored . '"');
            error_log('ENTERED: "' . $entered . '"');
            error_log('EXPIRED: ' . (time() > $expiresAt ? 'OUI' : 'NON'));

            if (!$stored) {
                $error = 'Session expirée. Veuillez vous reconnecter.';
                $session->remove('2fa_pending_user_id');
            } elseif (time() > $expiresAt) {
                $session->remove('2fa_pending_user_id');
                $session->remove('2fa_code');
                $error = 'Le code a expiré. Veuillez vous reconnecter.';
            } elseif ($entered !== $stored) {
                $error = 'Code incorrect. Vérifiez votre email ou SMS.';
            } else {
                $user = $userRepo->findOneBy(['cin' => $userCin]);
                if (!$user instanceof User) {
                    $session->remove('2fa_pending_user_id');
                    return $this->redirectToRoute('app_login');
                }

                $session->remove('2fa_pending_user_id');
                $session->remove('2fa_code');
                $session->remove('2fa_expires_at');

                $token = new \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken(
                    $user, 'main', $user->getRoles()
                );
                $this->container->get('security.token_storage')->setToken($token);

                return $this->redirectByRole($user);
            }
        }

        return $this->render('Auth/2fa_verify.html.twig', ['error' => $error]);
    }

    #[Route('/2fa/resend', name: 'app_2fa_resend')]
    public function twoFactorResend(SessionInterface $session, UserRepository $userRepo): Response
    {
        $userCin = $session->get('2fa_pending_user_id');
        if (!$userCin) return $this->redirectToRoute('app_login');

        $user = $userRepo->findOneBy(['cin' => $userCin]);
        if (!$user instanceof User) return $this->redirectToRoute('app_login');

        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $session->set('2fa_code',       $code);
        $session->set('2fa_expires_at', time() + 300);

        error_log('2FA RESEND CODE: ' . $code . ' pour CIN: ' . $userCin);

        if ($user->getTel()) {
            $this->sendSms($user->getTel(), "AgroFlow – Code 2FA : {$code} (valable 5 min)");
        }

        $this->addFlash('success', 'Nouveau code envoyé.');
        return $this->redirectToRoute('app_2fa_verify');
    }

    #[Route('/profil/2fa/toggle', name: 'app_2fa_toggle', methods: ['POST'])]
    public function toggle2fa(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) return $this->json(['success' => false], 401);

        $enabled = (int) $request->request->get('enabled', 0);
        $user->setTwoFactorEnabled($enabled ? 1 : 0);
        $em->flush();

        return $this->json(['success' => true, 'enabled' => $user->getTwoFactorEnabled()]);
    }

    // ==================== REGISTER ====================
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRole(2);
            $user->setMdp($passwordHasher->hashPassword($user, $form->get('plainPassword')->getData()));
            $user->setDateCreationcpt(new \DateTime());
            $user->setDateDernierchg(new \DateTime());
            $em->persist($user);
            $em->flush();
            return $this->redirectByRole($user);
        }

        return $this->render('Auth/register.html.twig', ['registrationForm' => $form->createView()]);
    }

    // ==================== LOGOUT ====================
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void {}

    // ==================== PAGE COMPTE BANNI ====================
    #[Route('/banni', name: 'app_bann')]
    public function banni(
        \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage,
        \Symfony\Component\HttpFoundation\RequestStack $requestStack
    ): Response {
        $tokenStorage->setToken(null);
        $requestStack->getSession()->invalidate();
        return $this->render('Auth/banni.html.twig');
    }

    // ==================== CHECK SESSION ====================
    #[Route('/check-session', name: 'app_check_session')]
    public function checkSession(): Response
    {
        return $this->getUser()
            ? new Response('OK', 200)
            : new Response('Unauthorized', 401);
    }

    // ==================== API TERRAINS ====================
    #[Route('/api/terrains/{cinAgriculteur}', name: 'api_terrains_by_agriculteur')]
    public function terrainsByAgriculteur(
        int $cinAgriculteur,
        \App\Repository\Terrain\TerrainRepository $terrainRepo
    ): Response {
        $terrains = $terrainRepo->findByAgriculteur($cinAgriculteur);
        $data = array_map(fn($t) => ['id' => $t->getId(), 'nom' => $t->getNomTerrain()], $terrains);
        return $this->json($data);
    }

    // ==================== RESET PASSWORD ====================
    #[Route('/reset-password', name: 'app_reset_password')]
    public function request(
        Request $request,
        UserRepository $userRepo,
        SessionInterface $session
    ): Response {
        $error = null;

        if ($request->isMethod('POST')) {
            $email = trim($request->request->get('email', ''));
            $canal = $request->request->get('canal', 'email');
            $user  = $userRepo->findOneBy(['email' => $email]);

            if (!$user) {
                $error = 'Aucun compte trouvé avec cet email.';
            } elseif ($canal === 'sms' && !$user->getTel()) {
                $error = 'Aucun numéro de téléphone associé à ce compte.';
            } else {
                $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $session->set('reset_code',       $code);
                $session->set('reset_email',      $email);
                $session->set('reset_canal',      $canal);
                $session->set('reset_expires_at', time() + 600);

                if ($canal === 'sms') {
                    $sent = $this->sendSms(
                        $user->getTel(),
                        "AgroFlow – Votre code : {$code} (valable 10 min)"
                    );
                    if (!$sent) {
                        $error = 'Impossible d\'envoyer le SMS. Veuillez choisir l\'envoi par email.';
                        goto renderForm;
                    }
                } else {
                    $sent = $this->sendEmail(
                        $email,
                        'AgroFlow – Code de réinitialisation',
                        $this->resetEmailHtml($code)
                    );
                    if (!$sent) {
                        $error = 'Erreur lors de l\'envoi de l\'email.';
                        goto renderForm;
                    }
                }

                return $this->redirectToRoute('app_reset_verify');
            }
        }

        renderForm:
        return $this->render('Auth/reset_password_request.html.twig', ['error' => $error]);
    }

    #[Route('/reset-password/verify', name: 'app_reset_verify')]
    public function verify(Request $request, SessionInterface $session): Response
    {
        if (!$session->get('reset_email')) {
            return $this->redirectToRoute('app_reset_password');
        }

        $error = null;
        $canal = $session->get('reset_canal', 'email');

        if ($request->isMethod('POST')) {
            $entered   = trim($request->request->get('code', ''));
            $stored    = $session->get('reset_code');
            $expiresAt = $session->get('reset_expires_at');

            if (time() > $expiresAt) {
                foreach (['reset_code','reset_email','reset_canal','reset_expires_at'] as $k) $session->remove($k);
                $error = 'Le code a expiré. Veuillez recommencer.';
            } elseif ($entered !== $stored) {
                $error = 'Code incorrect. Vérifiez et réessayez.';
            } else {
                $session->set('reset_verified', true);
                return $this->redirectToRoute('app_reset_new_password');
            }
        }

        return $this->render('Auth/reset_password_verify.html.twig', ['error' => $error, 'canal' => $canal]);
    }

    #[Route('/reset-password/new', name: 'app_reset_new_password')]
    public function newPassword(
        Request $request,
        SessionInterface $session,
        UserRepository $userRepo,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        if (!$session->get('reset_verified')) {
            return $this->redirectToRoute('app_reset_password');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $pwd1 = $request->request->get('password');
            $pwd2 = $request->request->get('password_confirm');

            if (strlen($pwd1) < 6) {
                $error = 'Le mot de passe doit contenir au moins 6 caractères.';
            } elseif ($pwd1 !== $pwd2) {
                $error = 'Les mots de passe ne correspondent pas.';
            } else {
                $user = $userRepo->findOneBy(['email' => $session->get('reset_email')]);
                if ($user) {
                    $user->setMdp($hasher->hashPassword($user, $pwd1));
                    $em->flush();
                }
                foreach (['reset_code','reset_email','reset_canal','reset_expires_at','reset_verified'] as $k) {
                    $session->remove($k);
                }
                $this->addFlash('success', 'Mot de passe réinitialisé avec succès !');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('Auth/reset_password_new.html.twig', ['error' => $error]);
    }

    #[Route('/reset-password/resend', name: 'app_reset_resend')]
    public function resend(SessionInterface $session, UserRepository $userRepo, MailerInterface $mailer): Response
    {
        $email = $session->get('reset_email');
        $canal = $session->get('reset_canal', 'email');

        if (!$email) return $this->redirectToRoute('app_reset_password');

        $user = $userRepo->findOneBy(['email' => $email]);
        if (!$user) return $this->redirectToRoute('app_reset_password');

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $session->set('reset_code',       $code);
        $session->set('reset_expires_at', time() + 600);

        if ($canal === 'sms') {
            $this->sendSms($user->getTel(), "AgroFlow – Votre code : {$code} (valable 10 min)");
        } else {
            $this->sendEmail($email, 'AgroFlow – Nouveau code', $this->resetEmailHtml($code));
        }

        $this->addFlash('success', 'Un nouveau code a été envoyé.');
        return $this->redirectToRoute('app_reset_verify');
    }

    // ==================== HELPERS PRIVÉS ====================

    private function redirectByRole(User $user): Response
    {
        return match((int)$user->getRole()) {
            0       => $this->redirectToRoute('app_bann'),
            1       => $this->redirectToRoute('ouvrier_home'),
            2       => $this->redirectToRoute('agri_home'),
            3       => $this->redirectToRoute('admin_dashboard'),
            default => $this->redirectToRoute('ouvrier_home'),
        };
    }

    /**
     * Envoi SMS via Twilio REST API (curl, sans SDK)
     * Retourne true si succès, false sinon
     */
    private function sendSms(string $tel, string $body): bool
    {
        try {
            $sid   = $_ENV['TWILIO_ACCOUNT_SID'] ?? getenv('TWILIO_ACCOUNT_SID');
            $token = $_ENV['TWILIO_AUTH_TOKEN']  ?? getenv('TWILIO_AUTH_TOKEN');
            $from  = $_ENV['TWILIO_FROM']        ?? getenv('TWILIO_FROM');

            if (!$sid || !$token || !$from) {
                error_log('SMS ERROR: Variables Twilio manquantes');
                return false;
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
                    'Body' => $body,
                ]),
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr  = curl_error($ch);
            curl_close($ch);

            if ($curlErr) {
                error_log('SMS CURL ERROR: ' . $curlErr);
                return false;
            }

            if ($httpCode >= 400) {
                error_log('SMS HTTP ERROR ' . $httpCode . ': ' . $response);
                return false;
            }

            error_log('SMS OK → ' . $tel);
            return true;

        } catch (\Exception $e) {
            error_log('SMS EXCEPTION: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoi email via Resend REST API (curl, sans SDK)
     * Retourne true si succès, false sinon
     */
    private function sendEmail(string $to, string $subject, string $html): bool
    {
        try {
            $apiKey = $_ENV['RESEND_API_KEY'] ?? getenv('RESEND_API_KEY');
            $from   = $_ENV['RESEND_FROM_EMAIL'] ?? getenv('RESEND_FROM_EMAIL') ?: 'onboarding@resend.dev';

            if (!$apiKey) {
                error_log('EMAIL ERROR: RESEND_API_KEY manquante');
                return false;
            }

            $payload = json_encode([
                'from'    => 'AgroFlow <' . $from . '>',
                'to'      => [$to],
                'subject' => $subject,
                'html'    => $html,
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
                error_log('EMAIL CURL ERROR: ' . $curlErr);
                return false;
            }

            if ($httpCode >= 400) {
                error_log('EMAIL HTTP ERROR ' . $httpCode . ': ' . $response);
                return false;
            }

            error_log('EMAIL OK → ' . $to);
            return true;

        } catch (\Exception $e) {
            error_log('EMAIL EXCEPTION: ' . $e->getMessage());
            return false;
        }
    }

    private function resetEmailHtml(string $code): string
    {
        return "
            <div style='font-family:DM Sans,sans-serif;max-width:480px;margin:0 auto;
                        padding:32px;background:#FCF8E6;border-radius:12px;'>
                <h2 style='color:#2D5A27;margin-bottom:8px;'>Réinitialisation du mot de passe</h2>
                <p style='color:#555;margin-bottom:24px;'>Utilisez ce code pour réinitialiser votre mot de passe AgroFlow :</p>
                <div style='background:white;border:2px solid #c8e6c0;border-radius:10px;
                            padding:20px;text-align:center;letter-spacing:0.3em;
                            font-size:32px;font-weight:700;color:#2D5A27;'>
                    {$code}
                </div>
                <p style='color:#888;font-size:13px;margin-top:20px;'>
                    Ce code expire dans <strong>10 minutes</strong>.
                </p>
            </div>
        ";
    }
}