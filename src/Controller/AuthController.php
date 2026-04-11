<?php

namespace App\Controller;

use App\Entity\User\User;
use App\Form\RegistrationFormType;
use App\Form\LoginFormType;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{
    // ==================== LOGIN ====================
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectByRole($this->getUser());
        }

        $error        = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $loginForm = $this->createForm(LoginFormType::class, [
            '_username' => $lastUsername,
        ]);

        return $this->render('auth/login.html.twig', [
            'loginForm' => $loginForm->createView(),
            'error'     => $error,
        ]);
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
            // Hash du mot de passe
            $user->setMdp(
                $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );

            // Date de création
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

    // ==================== REDIRECTION PAR ROLE ====================
    private function redirectByRole($user): Response
    {
        return match((int)$user->getRole()) {
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
}