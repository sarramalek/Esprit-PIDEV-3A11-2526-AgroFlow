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
    'error'     => $error,   // ← obligatoire
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

            // Redirection selon le rôle après inscription
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
        // Géré automatiquement par Symfony Security
    }

    // ==================== REDIRECTION PAR ROLE ====================
    private function redirectByRole($user): Response
{
    $role = $user->getRole();

    return match((int)$role) {
        1 => $this->redirectToRoute('ouvrier_dashboard'),     // Employé/Ouvrier
        2 => $this->redirectToRoute('agriculteur_dashboard'), // Agriculteur
        3 => $this->redirectToRoute('admin_dashboard'),       // Admin
        default => $this->redirectToRoute('app_home'),        // Visiteur
    };
}
}