<?php

namespace App\Controller\User;

use App\Repository\User\TacheRepository;
use App\Repository\User\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/agriculteur/tache')]
class TacheFrontController extends AbstractController
{
    // ==================== FRONT OFFICE ====================
    #[Route('/front', name: 'app_tache_front', methods: ['GET'])]
    public function front(UserRepository $userRepo): Response
    {
        return $this->render('User/FrontTache.html.twig', [
            'ouvriers' => $userRepo->findAllOuvriers(),
        ]);
    }

    // ==================== AJAX : taches par ouvrier ====================
   #[Route('/front/agriculteur/{cin}', name: 'app_tache_by_ouvrier', methods: ['GET'])]
public function tachesByOuvrier(int $cin, UserRepository $userRepo, TacheRepository $tacheRepo): Response
{
    $ouvrier = $userRepo->findByCin($cin);

    if (!$ouvrier) {
        return $this->json(['error' => 'Ouvrier non trouvé'], 404);
    }

    $taches = $tacheRepo->findByAssignee($ouvrier);

    $data = array_map(fn($t) => [
        'id'          => $t->getIdTache(),
        'nom'         => $t->getNomTache(),
        'description' => $t->getDescription(),
        'etat'        => $t->getEtat(),
        'priorite'    => $t->getPriorite(),
        'echeance'    => $t->getDateEcheancee() ? $t->getDateEcheancee()->format('d/m/Y') : null,
        'enRetard'    => $t->getDateEcheancee()
                         && $t->getDateEcheancee() < new \DateTime()
                         && $t->getEtat() !== 'terminée',
        'csrfToken'   => $this->container->get('security.csrf.token_manager')
                             ->getToken('delete' . $t->getIdTache())
                             ->getValue(),
    ], $taches);

    return $this->json($data);
}
    
}