<?php

namespace App\Controller;

use App\Repository\User\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminDashboardController extends AbstractController
{
    #[Route('/DashboardAdmin', name: 'admin_dashboard')]
    public function index(UserRepository $userRepo): Response
    {
        // ==================== STATS UTILISATEURS ====================
        $total        = count($userRepo->findAll());
        $agriculteurs = count($userRepo->findAllAgriculteurs());
        $ouvriers     = count($userRepo->findAllOuvriers());
        $admins       = count($userRepo->findAllAdmins());

        // ==================== INSCRIPTIONS 6 DERNIERS MOIS ====================
        $labels           = [];
        $dataAgriculteurs = [];
        $dataOuvriers     = [];

        for ($i = 5; $i >= 0; $i--) {
            $date     = new \DateTime("-$i months");
            $labels[] = $date->format('M Y');

            // Agriculteurs inscrits ce mois
            $dataAgriculteurs[] = $userRepo->countByRoleAndMonth(2, $date);

            // Ouvriers inscrits ce mois
            $dataOuvriers[] = $userRepo->countByRoleAndMonth(1, $date);
        }

        // ==================== NOUVEAUX CE MOIS ====================
        $now          = new \DateTime();
        $newThisMonth = $userRepo->countByMonth($now);

        return $this->render('/DashboardAdmin.html.twig', [
            'stats' => [
                'totalUsers'         => $total,
                'agriculteurs'       => $agriculteurs,
                'agriculteursPct'    => $total > 0 ? round($agriculteurs / $total * 100) : 0,
                'ouvriers'           => $ouvriers,
                'ouvriersPct'        => $total > 0 ? round($ouvriers / $total * 100) : 0,
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
            'inscriptionsOuvriers'     => $dataOuvriers,
            'revenus'                  => [3200, 3800, 2900, 4500, 4200, 5100], // à brancher sur Abonnement
        ]);
    }
    #[Route('/profile/update', name: 'profile_update', methods: ['POST'])]
public function profileUpdate(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): JsonResponse
{
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); // ← tous les rôles connectés

 

    $user = $this->getUser();

    if ($email = $request->request->get('email')) {
        $user->setEmail(trim($email));
    }
    if ($prenom = $request->request->get('prenom')) {
        $user->setPrenom(trim($prenom));
    }
    if ($nom = $request->request->get('nom')) {
        $user->setNom(trim($nom));
    }
    if ($cin = $request->request->get('cin')) {
        $user->setCin(trim($cin));
    }
    if ($tel = $request->request->get('tel')) {
        $user->setTel(trim($tel));
    }
    if ($ville = $request->request->get('ville')) {
        $user->setVille(trim($ville));
    }
    if ($pwd = $request->request->get('password')) {
        $user->setMdp($hasher->hashPassword($user, $pwd));
    }
    $photo = $request->request->get('photo'); // récupère l'URL Cloudinary
if ($photo) {
    $user->setImg($photo);
}
$em->flush();

    $em->flush();

    return new JsonResponse([
        'success' => true,
        'prenom'  => $user->getPrenom(),
        'nom'     => $user->getNom(),
        'email'   => $user->getEmail(),
            'photo'   => $user->getImg(), // ← important

    ]);
}

}