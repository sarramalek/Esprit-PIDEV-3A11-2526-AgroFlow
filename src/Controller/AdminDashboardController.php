<?php

namespace App\Controller;

use App\Repository\User\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminDashboardController extends AbstractController
{
    #[Route('/DashboardAdmin', name: 'admin_dashboard')]
    public function index(UserRepository $userRepo): Response
    {
        // ==================== STATS UTILISATEURS ====================
        $total        = count($userRepo->findAll());
        $agriculteurs = count($userRepo->findAllAgriculteurs());
        $admins       = count($userRepo->findAllAdmins());

        // ==================== INSCRIPTIONS 6 DERNIERS MOIS ====================
        $labels           = [];
        $dataAgriculteurs = [];

        for ($i = 5; $i >= 0; $i--) {
            $date     = new \DateTime("-$i months");
            $labels[] = $date->format('M Y');

            // Agriculteurs inscrits ce mois
            $dataAgriculteurs[] = $userRepo->countByRoleAndMonth(2, $date);
        }

        // ==================== NOUVEAUX CE MOIS ====================
        $now          = new \DateTime();
        $newThisMonth = $userRepo->countByMonth($now);

        return $this->render('/DashboardAdmin.html.twig', [
            'stats' => [
                'totalUsers'         => $total,
                'agriculteurs'       => $agriculteurs,
                'agriculteursPct'    => $total > 0 ? round($agriculteurs / $total * 100) : 0,
                'admins'             => $admins,
                'adminsPct'          => $total > 0 ? round($admins / $total * 100) : 0,
                'newThisMonth'       => $newThisMonth,

                // À brancher sur votre entité Abonnement quand elle sera prête
                'abonnementsActifs'  => 0,
                'abonnementsExpires' => 0,
                'abonnementsAnnules' => 0,
                'abonnementsPct'     => 0,
            ],
            'recentUsers'              => $userRepo->findRecentUsers(5),
            'inscriptionsLabels'       => $labels,
            'inscriptionsAgriculteurs' => $dataAgriculteurs,
            'revenus'                  => [3200, 3800, 2900, 4500, 4200, 5100], // à brancher sur Abonnement
        ]);
    }
}