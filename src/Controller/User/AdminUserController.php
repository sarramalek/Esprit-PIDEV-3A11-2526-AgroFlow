<?php

namespace App\Controller\User;

use App\Entity\User\User;
use App\Form\User\UserFormType; // vérifié
use App\Repository\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users', name: 'admin_users')]
#[IsGranted('ROLE_ADMIN')]
class AdminUserController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface      $em,
        private readonly UserRepository              $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    // ── LIST ──────────────────────────────────────────────────────────────────
    #[Route('', name: '_list')]
    public function list(Request $request): Response
    {
        $search = $request->query->get('q', '');
        $role   = $request->query->get('role', '');

        $qb = $this->userRepository->createQueryBuilder('u')
            ->orderBy('u.dateCreationcpt', 'DESC');

        if ($search) {
            $qb->andWhere('u.nom LIKE :q OR u.prenom LIKE :q OR u.email LIKE :q')
               ->setParameter('q', "%$search%");
        }
        if ($role !== '') {
            $qb->andWhere('u.role = :role')
               ->setParameter('role', (int) $role);
        }

        $users = $qb->getQuery()->getResult();

        $stats = [
            'total'        => $this->userRepository->count([]),
            'admins'       => $this->userRepository->count(['role' => 3]),
            'agriculteurs' => $this->userRepository->count(['role' => 2]),
            'ouvriers'     => $this->userRepository->count(['role' => 1]),
        ];

        return $this->render('User/list.html.twig', [
            'users'  => $users,
            'stats'  => $stats,
            'search' => $search,
            'role'   => $role,
        ]);
    }

    // ── SHOW ──────────────────────────────────────────────────────────────────
    #[Route('/{cin}', name: '_show', requirements: ['cin' => '\d+'], methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('User/show.html.twig', [
            'user' => $user,
        ]);
    }

    // ── CREATE ────────────────────────────────────────────────────────────────
    #[Route('/new', name: '_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plain = $form->get('plainPassword')->getData();
            if ($plain) {
                $user->setMdp($this->passwordHasher->hashPassword($user, $plain));
            }
            $user->setDateCreationcpt(new \DateTime());
            $user->setDateDernierchg(new \DateTime());

            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès.');
            return $this->redirectToRoute('admin_users_list');
        }

        return $this->render('User/form.html.twig', [
            'form'  => $form->createView(),
            'user'  => $user,
            'mode'  => 'create',
        ]);
    }

    // ── EDIT ──────────────────────────────────────────────────────────────────
    #[Route('/{cin}/edit', name: '_edit', requirements: ['cin' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserFormType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plain = $form->get('plainPassword')->getData();
            if ($plain) {
                $user->setMdp($this->passwordHasher->hashPassword($user, $plain));
            }
            $user->setDateDernierchg(new \DateTime());

            $this->em->flush();

            $this->addFlash('success', 'Utilisateur mis à jour avec succès.');
            return $this->redirectToRoute('admin_users_list');
        }

        return $this->render('User/form.html.twig', [
            'form'  => $form->createView(),
            'user'  => $user,
            'mode'  => 'edit',
        ]);
    }

    // ── DELETE ────────────────────────────────────────────────────────────────
    #[Route('/{cin}/delete', name: '_delete', requirements: ['cin' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete_user_' . $user->getCin(), $request->request->get('_token'))) {
            $this->em->remove($user);
            $this->em->flush();
            $this->addFlash('success', 'Utilisateur supprimé.');
        }

        return $this->redirectToRoute('admin_users_list');
    }
}