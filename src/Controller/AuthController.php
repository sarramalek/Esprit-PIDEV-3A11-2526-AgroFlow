<?php

namespace App\Controller;

use App\Entity\User\User;
use App\Form\auth\RegistrationFormType;
use App\Form\auth\LoginFormType;
use App\Repository\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Service\TwoFactorCodeSender;


use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Twilio\Rest\Client as TwilioClient;


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
    // Si déjà connecté normalement → rediriger
    if ($this->getUser() && !$session->get('2fa_pending_user_id')) {
        return $this->redirectByRole($this->getUser());
    }

    $error        = $authenticationUtils->getLastAuthenticationError();
    $lastUsername = $authenticationUtils->getLastUsername();

    // Symfony a authentifié l'utilisateur → on intercepte avant la session finale
    // On passe par un CustomAuthenticator (voir étape 3) qui positionne '2fa_pending_user_id'
    // Si 2FA en attente → rediriger vers la page de vérification
    if ($session->get('2fa_pending_user_id')) {
        return $this->redirectToRoute('app_2fa_verify');
    }

    $loginForm = $this->createForm(LoginFormType::class, ['_username' => $lastUsername]);

    return $this->render('auth/login.html.twig', [
        'loginForm' => $loginForm->createView(),
        'error'     => $error,
    ]);
}


 #[Route('/send-code', name: 'send_code', methods: ['POST'])]
    public function sendCode(
        Request $request,
        SmsService $smsService,
        SessionInterface $session
    ): JsonResponse {
        $phone = $request->request->get('phone');

        try {
            $result = $smsService->sendVerificationCode($phone);

            // 💾 Stocker le code en session pour vérification
            $session->set('verification_code', $result['code']);
            $session->set('verification_expires', time() + 600); // 10 min

            return $this->json([
                'success' => true,
                'channel' => $result['channel'], // 'twilio' ou 'vonage'
                'message' => 'Code envoyé par SMS.',
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Impossible d\'envoyer le SMS.',
            ], 500);
        }
    }

    #[Route('/verify-code', name: 'verify_code', methods: ['POST'])]
    public function verifyCode(
        Request $request,
        SessionInterface $session
    ): JsonResponse {
        $inputCode = $request->request->get('code');
        $savedCode = $session->get('verification_code');
        $expires   = $session->get('verification_expires');

        if (!$savedCode || time() > $expires) {
            return $this->json(['success' => false, 'message' => 'Code expiré.'], 400);
        }

        if ($inputCode !== $savedCode) {
            return $this->json(['success' => false, 'message' => 'Code incorrect.'], 400);
        }

        // ✅ Code valide → nettoyer la session
        $session->remove('verification_code');
        $session->remove('verification_expires');

        return $this->json(['success' => true, 'message' => 'Code vérifié avec succès.']);
    }
// Dans AuthController.php — ajouter ces routes :

#[Route('/2fa/verify', name: 'app_2fa_verify')]
public function twoFactorVerify(
    Request $request,
    SessionInterface $session,
    UserRepository $userRepo,
    EntityManagerInterface $em
): Response {
    $userId = $session->get('2fa_pending_user_id');
    if (!$userId) {
        return $this->redirectToRoute('app_login');
    }

    $error = null;

    if ($request->isMethod('POST')) {
        $entered   = trim($request->request->get('code', ''));
        $stored    = $session->get('2fa_code');
        $expiresAt = $session->get('2fa_expires_at');

        if (time() > $expiresAt) {
            $session->remove('2fa_pending_user_id');
            $session->remove('2fa_code');
            $error = 'Le code a expiré. Veuillez vous reconnecter.';
        } elseif ($entered !== $stored) {
            $error = 'Code incorrect. Vérifiez votre email ou SMS.';
        } else {
            // Code valide → connecter l'utilisateur manuellement
            $user = $userRepo->find($userId);
            $session->remove('2fa_pending_user_id');
            $session->remove('2fa_code');
            $session->remove('2fa_expires_at');

            // Authentification manuelle via le token
            $token = new \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken(
                $user, 'main', $user->getRoles()
            );
            $this->container->get('security.token_storage')->setToken($token);

            return $this->redirectByRole($user);
        }
    }

    return $this->render('auth/2fa_verify.html.twig', ['error' => $error]);
}

#[Route('/2fa/resend', name: 'app_2fa_resend')]
public function twoFactorResend(
    SessionInterface $session,
    UserRepository $userRepo,
    TwoFactorCodeSender $sender
): Response {
    $userId = $session->get('2fa_pending_user_id');
    if (!$userId) return $this->redirectToRoute('app_login');

    $user = $userRepo->find($userId);
    $sender->send($user, $session);
    $this->addFlash('success', 'Nouveau code envoyé.');
    return $this->redirectToRoute('app_2fa_verify');
}

#[Route('/profil/2fa/toggle', name: 'app_2fa_toggle', methods: ['POST'])]
public function toggle2fa(
    Request $request,
    EntityManagerInterface $em
): Response {
    $user = $this->getUser();
    if (!$user) return $this->json(['success' => false], 401);

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
    EntityManagerInterface $em,
): Response {
    $user = new User();
    $form = $this->createForm(RegistrationFormType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        $user->setRole(2); // Agriculteur par défaut

        $user->setMdp(
            $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData())
        );

        $user->setDateCreationcpt(new \DateTime());
        $user->setDateDernierchg(new \DateTime());

        $em->persist($user);
        $em->flush();

        return $this->redirectByRole($user);
    }

    return $this->render('auth/register.html.twig', [
        'registrationForm' => $form->createView(),
    ]);
}

    // ==================== LOGOUT ====================
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
    }
// ==================== PAGE COMPTE BANNI ====================
#[Route('/banni', name: 'app_bann')]
public function banni(
    \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage,
    \Symfony\Component\HttpFoundation\RequestStack $requestStack
): Response
{
    // Déconnecter l'utilisateur
    $tokenStorage->setToken(null);

    // Invalider la session
    $session = $requestStack->getSession();
    if ($session) {
        $session->invalidate();
    }

    return $this->render('auth/banni.html.twig');
}

    // ==================== REDIRECTION PAR ROLE ====================
    private function redirectByRole($user): Response
    {
        return match((int)$user->getRole()) {
            0       => $this->redirectToRoute('app_bann'),
            1       => $this->redirectToRoute('ouvrier_home'),   // ← corrigé
            2       => $this->redirectToRoute('agri_home'),
            3       => $this->redirectToRoute('admin_dashboard'),
            default => $this->redirectToRoute('ouvrier_home'),   // ← corrigé
        };
    }
    // ── CHECK SESSION (appelé par JS toutes les 2s) ───────────────────────────
#[Route('/check-session', name: 'app_check_session')]
public function checkSession(): Response
{
    if (!$this->getUser()) {
        return new Response('Unauthorized', 401);
    }
    return new Response('OK', 200);
}
//------------------------------------------------------------------------
#[Route('/api/terrains/{cinAgriculteur}', name: 'api_terrains_by_agriculteur')]
public function terrainsByAgriculteur(
    int $cinAgriculteur,
    \App\Repository\Terrain\TerrainRepository $terrainRepo
): Response {
    $terrains = $terrainRepo->findByAgriculteur($cinAgriculteur);

    $data = array_map(fn($t) => [
        'id'  => $t->getId(),
        'nom' => $t->getNomTerrain(),
    ], $terrains);

    return $this->json($data);
}





//--------------------API RESET PASSWOR - TWILIO - -------------------------------
 // ──────────────────────────────────────────────────────────────
    // ÉTAPE 1 : Saisie de l'email + choix du canal (email ou SMS)
    // ──────────────────────────────────────────────────────────────
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
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $session->set('reset_code',       $code);
            $session->set('reset_email',      $email);
            $session->set('reset_canal',      $canal);
            $session->set('reset_expires_at', time() + 600);

           if ($canal === 'sms') {
    $sent = false;

    // ── Tentative 1 : Twilio ──
    try {
        $twilio = new TwilioClient(
            $_ENV['TWILIO_ACCOUNT_SID'],
            $_ENV['TWILIO_AUTH_TOKEN']
        );
        $tel = $user->getTel();
        if (!str_starts_with($tel, '+')) {
            $tel = '+216' . ltrim($tel, '0');
        }
        $twilio->messages->create($tel, [
            'from' => $_ENV['TWILIO_FROM'],
            'body' => "AgroFlow – Votre code : $code (valable 10 min)"
        ]);
        $sent = true;

    } catch (\Twilio\Exceptions\RestException $e) {

        // ── Fallback : Vonage si Twilio dépasse la limite (429) ──
        if ($e->getStatusCode() === 429) {
            try {
                $vonage = new \Vonage\Client(
                    new \Vonage\Client\Credentials\Basic(
                        $_ENV['VONAGE_API_KEY'],
                        $_ENV['VONAGE_API_SECRET']
                    )
                );
                $tel = $user->getTel();
                if (!str_starts_with($tel, '+')) {
                    $tel = '+216' . ltrim($tel, '0');
                }
                $message  = new \Vonage\SMS\Message\SMS(
                    $tel,
                    $_ENV['VONAGE_FROM'],
                    "AgroFlow – Votre code : $code (valable 10 min)"
                );
                $response = $vonage->sms()->send($message);

                if ($response->current()->getStatus() === 0) {
                    $sent = true;
                }
            } catch (\Exception $vonageEx) {
                $error = 'SMS indisponible. Veuillez choisir l\'envoi par email.';
                goto renderForm;
            }
        } else {
            $error = 'Impossible d\'envoyer le SMS : ' . $e->getMessage();
            goto renderForm;
        }
    }

    if (!$sent) {
        $error = 'L\'envoi du SMS a échoué. Veuillez réessayer.';
        goto renderForm;
    }
            } else {
                // ── PHPMailer ──
                try {
                    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'maleksarra362@gmail.com';
                    $mail->Password   = 'dwxqvewsbormytyn'; // ← ici
                    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->CharSet    = 'UTF-8';

                    $mail->setFrom('maleksarra362@gmail.com', 'AgroFlow');
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = 'AgroFlow – Code de réinitialisation';
                    $mail->Body    = "
                        <div style='font-family:DM Sans,sans-serif;max-width:480px;margin:0 auto;padding:32px;background:#FCF8E6;border-radius:12px;'>
                            <h2 style='color:#2D5A27;margin-bottom:8px;'>Réinitialisation du mot de passe</h2>
                            <p style='color:#555;margin-bottom:24px;'>Utilisez ce code pour réinitialiser votre mot de passe AgroFlow :</p>
                            <div style='background:white;border:2px solid #c8e6c0;border-radius:10px;padding:20px;text-align:center;letter-spacing:0.3em;font-size:32px;font-weight:700;color:#2D5A27;'>
                                $code
                            </div>
                            <p style='color:#888;font-size:13px;margin-top:20px;'>Ce code expire dans <strong>10 minutes</strong>.</p>
                        </div>
                    ";

                    $mail->send();

                } catch (\Exception $e) {
                    $error = 'Erreur email : ' . $e->getMessage();
                    goto renderForm;
                }
            }

            return $this->redirectToRoute('app_reset_verify');
        }
    }

    renderForm:
    return $this->render('Auth/reset_password_request.html.twig', [
        'error' => $error,
    ]);
}
    // ──────────────────────────────────────────────────────────────
    // ÉTAPE 2 : Saisie du code de vérification
    // ──────────────────────────────────────────────────────────────
    #[Route('/reset-password/verify', name: 'app_reset_verify')]
    public function verify(
        Request $request,
        SessionInterface $session
    ): Response {
        // Sécurité : ne pas accéder directement sans avoir fait l'étape 1
        if (!$session->get('reset_email')) {
            return $this->redirectToRoute('app_reset_password');
        }

        $error = null;
        $canal = $session->get('reset_canal', 'email');

        if ($request->isMethod('POST')) {
            $entered    = trim($request->request->get('code', ''));
            $stored     = $session->get('reset_code');
            $expiresAt  = $session->get('reset_expires_at');

            if (time() > $expiresAt) {
                $session->remove('reset_code');
                $session->remove('reset_email');
                $session->remove('reset_canal');
                $session->remove('reset_expires_at');
                $error = 'Le code a expiré. Veuillez recommencer.';
            } elseif ($entered !== $stored) {
                $error = 'Code incorrect. Vérifiez et réessayez.';
            } else {
                // Code valide → passe à l'étape 3
                $session->set('reset_verified', true);
                return $this->redirectToRoute('app_reset_new_password');
            }
        }

        return $this->render('Auth/reset_password_verify.html.twig', [
            'error' => $error,
            'canal' => $canal,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // ÉTAPE 3 : Saisie du nouveau mot de passe
    // ──────────────────────────────────────────────────────────────
    #[Route('/reset-password/new', name: 'app_reset_new_password')]
    public function newPassword(
        Request $request,
        SessionInterface $session,
        UserRepository $userRepo,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        // Sécurité : code doit avoir été vérifié
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
                $email = $session->get('reset_email');
                $user  = $userRepo->findOneBy(['email' => $email]);

                if ($user) {
                    $user->setMdp($hasher->hashPassword($user, $pwd1));
                    $em->flush();
                }

                // Nettoie la session
                foreach (['reset_code','reset_email','reset_canal','reset_expires_at','reset_verified'] as $key) {
                    $session->remove($key);
                }

                $this->addFlash('success', 'Mot de passe réinitialisé avec succès !');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('Auth/reset_password_new.html.twig', [
            'error' => $error,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // BONUS : Renvoyer le code
    // ──────────────────────────────────────────────────────────────
    #[Route('/reset-password/resend', name: 'app_reset_resend')]
    public function resend(
        SessionInterface $session,
        UserRepository $userRepo,
        MailerInterface $mailer
    ): Response {
        $email = $session->get('reset_email');
        $canal = $session->get('reset_canal', 'email');

        if (!$email) {
            return $this->redirectToRoute('app_reset_password');
        }

        $user = $userRepo->findOneBy(['email' => $email]);
        if (!$user) {
            return $this->redirectToRoute('app_reset_password');
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $session->set('reset_code',       $code);
        $session->set('reset_expires_at', time() + 600);
if ($canal === 'sms') {
    $tel = $user->getTel();
    if (!str_starts_with($tel, '+')) {
        $tel = '+216' . ltrim($tel, '0');
    }

    $smsSent = false;

    // ── Tentative 1 : Twilio ──
    
    // ── Tentative 2 : Vonage (fallback) ──
    if (!$smsSent) {
        try {
            $vonage = new \Vonage\Client(
                new \Vonage\Client\Credentials\Basic(
                    $_ENV['VONAGE_API_KEY'],
                    $_ENV['VONAGE_API_SECRET']
                )
            );
            $response = $vonage->sms()->send(
                new \Vonage\SMS\Message\SMS(
                    $tel,
                    $_ENV['VONAGE_FROM'],
                    "AgroFlow – Votre code : $code (valable 10 min)"
                )
            );
            if ($response->current()->getStatus() === 0) {
                $smsSent = true;
            }
        } catch (\Exception $vonageEx) {
            $smsSent = false;
        }
    }

    if (!$smsSent) {
        $error = 'Impossible d\'envoyer le SMS. Veuillez choisir l\'envoi par email.';
        return $this->render('Auth/reset_password_request.html.twig', [
        'error' => $error,
    ]);
    }
    } else {
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'maleksarra362@gmail.com';
        $mail->Password   = 'dwxqvewsbormytyn'; // ← ici
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('maleksarra362@gmail.com', 'AgroFlow');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'AgroFlow – Nouveau code';
        $mail->Body    = "
            <div style='font-family:DM Sans,sans-serif;padding:32px;'>
                <h2 style='color:#2D5A27;'>Nouveau code</h2>
                <div style='font-size:32px;font-weight:700;color:#2D5A27;letter-spacing:0.3em;padding:20px;background:white;border:2px solid #c8e6c0;border-radius:10px;text-align:center;'>$code</div>
                <p style='color:#888;font-size:13px;margin-top:16px;'>Valable 10 minutes.</p>
            </div>
        ";

        $mail->send();
    } catch (\Exception $e) {
        // log silencieux
    }
}

        $this->addFlash('success', 'Un nouveau code a été envoyé.');
        return $this->redirectToRoute('app_reset_verify');
    }
}