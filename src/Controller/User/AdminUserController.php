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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

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




// ── PDF un user ──────────────────────────────────────────────────────────────
#[Route('/{cin}/pdf', name: '_pdf', requirements: ['cin' => '\d+'], methods: ['GET'])]
public function pdf(User $user): Response
{
    $options = new \Dompdf\Options();
    $options->set('defaultFont', 'Arial');
    $dompdf = new \Dompdf\Dompdf($options);

    $roleLabel = match((int)$user->getRole()) {
        3 => 'Administrateur',
        2 => 'Agriculteur',
        1 => 'Ouvrier',
        default => 'Inconnu',
    };

    $roleColor = match((int)$user->getRole()) {
        3 => '#B45309',
        2 => '#065F46',
        1 => '#1D4ED8',
        default => '#666',
    };

    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; color: #2C3E50; margin: 0; padding: 0; }
            .header { background: #2D5A27; color: white; padding: 30px 40px; }
            .logo { font-size: 22px; font-weight: 900; letter-spacing: 2px; }
            .logo span { color: #a8d5a2; }
            .header h1 { margin: 8px 0 0; font-size: 20px; font-weight: 600; }
            .header p { margin: 4px 0 0; font-size: 12px; opacity: 0.7; }
            .content { padding: 30px 40px; }
            .avatar { width: 70px; height: 70px; border-radius: 16px; background: #2D5A27; color: white; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; margin: 0 auto 20px; text-align: center; line-height: 70px; }
            .name { text-align: center; font-size: 22px; font-weight: 700; color: #2C3E50; margin-bottom: 6px; }
            .role-badge { text-align: center; margin-bottom: 24px; }
            .badge { display: inline-block; padding: 4px 16px; border-radius: 20px; font-size: 12px; font-weight: 700; color: white; background: ' . $roleColor . '; }
            .section-title { font-size: 11px; font-weight: 700; color: #999; text-transform: uppercase; letter-spacing: 1px; margin: 20px 0 10px; border-bottom: 1px solid #eee; padding-bottom: 6px; }
            .row { display: flex; justify-content: space-between; margin-bottom: 10px; }
            .label { color: #888; font-size: 13px; }
            .value { font-weight: 600; font-size: 13px; color: #2C3E50; }
            .footer { margin-top: 40px; padding: 16px 40px; background: #f9f9f9; border-top: 1px solid #eee; font-size: 11px; color: #aaa; text-align: center; }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="logo">AGRO<span>FLOW</span></div>
            <h1>Fiche Utilisateur</h1>
            <p>Généré le ' . (new \DateTime())->format('d/m/Y à H:i') . '</p>
        </div>
        <div class="content">
            <div class="avatar">' . strtoupper(substr($user->getPrenom() ?? 'U', 0, 1) . substr($user->getNom() ?? 'U', 0, 1)) . '</div>
            <div class="name">' . htmlspecialchars($user->getPrenom() . ' ' . $user->getNom()) . '</div>
            <div class="role-badge"><span class="badge">' . $roleLabel . '</span></div>

            <div class="section-title">Informations personnelles</div>
            <div class="row"><span class="label">CIN</span><span class="value">' . $user->getCin() . '</span></div>
            <div class="row"><span class="label">Email</span><span class="value">' . htmlspecialchars($user->getEmail() ?? '—') . '</span></div>
            <div class="row"><span class="label">Téléphone</span><span class="value">' . ($user->getTel() ?? '—') . '</span></div>
            <div class="row"><span class="label">Adresse</span><span class="value">' . htmlspecialchars($user->getAdresse() ?? '—') . '</span></div>
            <div class="row"><span class="label">Ville</span><span class="value">' . htmlspecialchars($user->getVille() ?? '—') . '</span></div>
            <div class="row"><span class="label">Date de naissance</span><span class="value">' . ($user->getDateNaiss() ? $user->getDateNaiss()->format('d/m/Y') : '—') . '</span></div>

            <div class="section-title">Informations compte</div>
            <div class="row"><span class="label">Rôle</span><span class="value">' . $roleLabel . '</span></div>
            <div class="row"><span class="label">Membre depuis</span><span class="value">' . ($user->getDateCreationcpt() ? $user->getDateCreationcpt()->format('d/m/Y') : '—') . '</span></div>
            <div class="row"><span class="label">Dernière modification</span><span class="value">' . ($user->getDateDernierchg() ? $user->getDateDernierchg()->format('d/m/Y') : '—') . '</span></div>
        </div>
        <div class="footer">AgroFlow &mdash; Plateforme de gestion agricole &mdash; Document généré automatiquement</div>
    </body>
    </html>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return new Response(
        $dompdf->output(),
        200,
        [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="user-' . $user->getCin() . '.pdf"',
        ]
    );
}

// ── EXCEL tous les users ──────────────────────────────────────────────────────
#[Route('/export/excel', name: '_excel', methods: ['GET'])]
public function excel(): StreamedResponse
{
    $users = $this->userRepository->findAll();

    $spreadsheet = new Spreadsheet();
    $sheet       = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Utilisateurs');

    // En-têtes
    $headers = ['CIN', 'Nom', 'Prénom', 'Email', 'Téléphone', 'Ville', 'Adresse', 'Rôle', 'Date inscription', 'Dernière modif'];
    foreach ($headers as $col => $header) {
        $cell = chr(65 + $col) . '1';
        $sheet->setCellValue($cell, $header);
        $sheet->getStyle($cell)->getFont()->setBold(true);
        $sheet->getStyle($cell)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('2D5A27');
        $sheet->getStyle($cell)->getFont()->getColor()->setRGB('FFFFFF');
    }

    // Données
    foreach ($users as $row => $user) {
        $roleLabel = match((int)$user->getRole()) {
            3 => 'Administrateur',
            2 => 'Agriculteur',
            1 => 'Ouvrier',
            default => 'Inconnu',
        };
        $r = $row + 2;
        $sheet->setCellValue("A$r", $user->getCin());
        $sheet->setCellValue("B$r", $user->getNom());
        $sheet->setCellValue("C$r", $user->getPrenom());
        $sheet->setCellValue("D$r", $user->getEmail());
        $sheet->setCellValue("E$r", $user->getTel() ?? '—');
        $sheet->setCellValue("F$r", $user->getVille() ?? '—');
        $sheet->setCellValue("G$r", $user->getAdresse() ?? '—');
        $sheet->setCellValue("H$r", $roleLabel);
        $sheet->setCellValue("I$r", $user->getDateCreationcpt()?->format('d/m/Y') ?? '—');
        $sheet->setCellValue("J$r", $user->getDateDernierchg()?->format('d/m/Y') ?? '—');

        // Zebra striping
        if ($row % 2 === 0) {
            $sheet->getStyle("A$r:J$r")->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('F4F6F9');
        }
    }

    // Auto-width
    foreach (range('A', 'J') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $writer = new Xlsx($spreadsheet);

    $response = new StreamedResponse(function () use ($writer) {
        $writer->save('php://output');
    });

    $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->headers->set('Content-Disposition', 'attachment; filename="utilisateurs-' . date('Y-m-d') . '.xlsx"');

    return $response;
}
}