<?php

namespace App\Controller\User;

use App\Entity\User\Tache;
use App\Entity\User\User;
use App\Repository\User\TacheRepository;
use App\Repository\User\UserRepository;
use App\Repository\Terrain\TerrainRepository;
use App\Service\AssignationAutoService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\TacheIAService ; 

#[Route('/agriculteur/ouvriers')]
class Ouvrier_agriController extends AbstractController
{
    #[Route('', name: 'app_ouvrier_index', methods: ['GET'])]
public function index(
    Request $request,
    UserRepository $userRepo,
    TerrainRepository $terrainRepo
): Response {
    $agriculteur = $this->getUser();
    $terrains    = $terrainRepo->findByAgriculteur($agriculteur->getCin());
    $tousOuvriers = $userRepo->findOuvriersByAgriculteur($agriculteur->getCin());

    $parPage =4 ;

    // Pagination par terrain
    $ouvriersByTerrain = [];
    $paginationByTerrain = [];

    foreach ($terrains as $terrain) {
        $tid = $terrain->getId();

        // Ouvriers de ce terrain
        $ouvriersTerrain = array_values(array_filter(
            $tousOuvriers,
            fn($o) => $o->getTerrain() && $o->getTerrain()->getId() === $tid
        ));

        $total      = count($ouvriersTerrain);
        $totalPages = max(1, (int) ceil($total / $parPage));
        $page       = max(1, min((int) $request->query->get("page_terrain_$tid", 1), $totalPages));

        $ouvriersByTerrain[$tid]   = array_slice($ouvriersTerrain, ($page - 1) * $parPage, $parPage);
        $paginationByTerrain[$tid] = [
            'page'       => $page,
            'totalPages' => $totalPages,
            'total'      => $total,
        ];
    }

    return $this->render('User/ouvriers_index.html.twig', [
        'terrains'            => $terrains,
        'ouvriersByTerrain'   => $ouvriersByTerrain,
        'paginationByTerrain' => $paginationByTerrain,
    ]);
}

    #[Route('/nouveau', name: 'app_ouvrier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher, TerrainRepository $terrainRepo): Response
    {
        $agriculteur = $this->getUser();
        $terrains = $terrainRepo->findByAgriculteur($agriculteur->getCin());

        if ($request->isMethod('POST')) {
            $ouvrier = new User();
            $ouvrier->setCin((int) $request->request->get('cin'));
            $ouvrier->setNom($request->request->get('nom'));
            $ouvrier->setPrenom($request->request->get('prenom'));
            $ouvrier->setEmail($request->request->get('email'));
            $ouvrier->setTel($request->request->get('tel'));
            $ouvrier->setAdresse($request->request->get('adresse'));
            $ouvrier->setVille($request->request->get('ville'));
            $ouvrier->setRole(1);
            $ouvrier->setDateCreationcpt(new \DateTime());
            $ouvrier->setMdp($hasher->hashPassword($ouvrier, $request->request->get('mdp')));

            $idTerrain = $request->request->get('id_terrain');
            if ($idTerrain) {
                $terrain = $terrainRepo->find((int) $idTerrain);
                if ($terrain && $terrain->getCin() === $agriculteur->getCin()) {
                    $ouvrier->setTerrain($terrain);
                }
            }

            $em->persist($ouvrier);
            $em->flush();
            $this->addFlash('success', 'Ouvrier créé avec succès.');
            return $this->redirectToRoute('app_ouvrier_index');
        }

        return $this->render('User/ouvriers_new.html.twig', ['terrains' => $terrains]);
    }

    #[Route('/{cin}/modifier', name: 'app_ouvrier_edit', methods: ['GET', 'POST'])]
    public function edit(int $cin, Request $request, UserRepository $userRepo, TerrainRepository $terrainRepo, EntityManagerInterface $em): Response
    {
        $agriculteur = $this->getUser();
        $ouvrier = $userRepo->findByCin($cin);
        if (!$ouvrier || !$this->appartientAgriculteur($ouvrier, $agriculteur, $terrainRepo)) {
            throw $this->createAccessDeniedException();
        }

        $terrains = $terrainRepo->findByAgriculteur($agriculteur->getCin());

        if ($request->isMethod('POST')) {
            $ouvrier->setNom($request->request->get('nom'));
            $ouvrier->setPrenom($request->request->get('prenom'));
            $ouvrier->setEmail($request->request->get('email'));
            $ouvrier->setTel($request->request->get('tel'));
            $ouvrier->setAdresse($request->request->get('adresse'));
            $ouvrier->setVille($request->request->get('ville'));

            $idTerrain = $request->request->get('id_terrain');
            if ($idTerrain) {
                $terrain = $terrainRepo->find((int) $idTerrain);
                if ($terrain && $terrain->getCin() === $agriculteur->getCin()) {
                    $ouvrier->setTerrain($terrain);
                }
            } else {
                $ouvrier->setTerrain(null);
            }

            $em->flush();
            $this->addFlash('success', 'Ouvrier modifié avec succès.');
            return $this->redirectToRoute('app_ouvrier_index');
        }

        return $this->render('User/ouvriers_edit.html.twig', [
            'ouvrier'  => $ouvrier,
            'terrains' => $terrains,
        ]);
    }

    #[Route('/{cin}/supprimer', name: 'app_ouvrier_delete', methods: ['POST'])]
    public function deleteOuvrier(int $cin, UserRepository $userRepo, TerrainRepository $terrainRepo, EntityManagerInterface $em): Response
    {
        $agriculteur = $this->getUser();
        $ouvrier = $userRepo->findByCin($cin);
        if (!$ouvrier || !$this->appartientAgriculteur($ouvrier, $agriculteur, $terrainRepo)) {
            throw $this->createAccessDeniedException();
        }
        $em->remove($ouvrier);
        $em->flush();
        $this->addFlash('success', 'Ouvrier supprimé.');
        return $this->redirectToRoute('app_ouvrier_index');
    }

    #[Route('/{cin}/taches', name: 'app_ouvrier_taches', methods: ['GET'])]
    public function taches(int $cin, UserRepository $userRepo, TacheRepository $tacheRepo, TerrainRepository $terrainRepo): Response
    {
        $agriculteur = $this->getUser();
        $ouvrier = $userRepo->findByCin($cin);
        if (!$ouvrier || !$this->appartientAgriculteur($ouvrier, $agriculteur, $terrainRepo)) {
            throw $this->createAccessDeniedException();
        }
        $taches = $tacheRepo->findByAssignee($ouvrier);
        return $this->render('User/ouvriers_taches.html.twig', [
            'ouvrier' => $ouvrier,
            'taches'  => $taches,
        ]);
    }

    #[Route('/{cin}/taches/ajouter', name: 'app_ouvrier_tache_add', methods: ['POST'])]
    public function ajouterTache(int $cin, Request $request, UserRepository $userRepo, TerrainRepository $terrainRepo, EntityManagerInterface $em): Response
    {
        $agriculteur = $this->getUser();
        $ouvrier = $userRepo->findByCin($cin);
        if (!$ouvrier || !$this->appartientAgriculteur($ouvrier, $agriculteur, $terrainRepo)) {
            throw $this->createAccessDeniedException();
        }

        $tache = new Tache();
        $tache->setNomTache($request->request->get('nom_tache'));
        $tache->setDescription($request->request->get('description'));
        $tache->setEtat($request->request->get('etat', 'à faire'));
        $tache->setPriorite($request->request->get('priorite', 'normale'));
        $tache->setAssignee($ouvrier);

        $echeance = $request->request->get('date_echeancee');
        if ($echeance) {
            $tache->setDateEcheancee(new \DateTime($echeance));
        }

        $em->persist($tache);
        $em->flush();
        $this->addFlash('success', 'Tâche assignée avec succès.');
        return $this->redirectToRoute('app_ouvrier_taches', ['cin' => $cin]);
    }

    #[Route('/tache/{id}/etat', name: 'app_tache_update_etat', methods: ['POST'])]
    public function updateEtatTache(int $id, Request $request, TacheRepository $tacheRepo, TerrainRepository $terrainRepo, EntityManagerInterface $em): Response
    {
        $agriculteur = $this->getUser();
        $tache = $tacheRepo->find($id);
        if (!$tache) {
            return $this->json(['error' => 'Tâche introuvable'], 404);
        }
        $ouvrier = $tache->getAssignee();
        if (!$ouvrier || !$this->appartientAgriculteur($ouvrier, $agriculteur, $terrainRepo)) {
            return $this->json(['error' => 'Accès refusé'], 403);
        }
        $tache->setEtat($request->request->get('etat'));
        $em->flush();
        return $this->json(['success' => true]);
    }

    #[Route('/tache/{id}/supprimer', name: 'app_ouvrier_tache_delete', methods: ['POST'])]
    public function supprimerTache(int $id, TacheRepository $tacheRepo, TerrainRepository $terrainRepo, EntityManagerInterface $em): Response
    {
        $agriculteur = $this->getUser();
        $tache = $tacheRepo->find($id);
        if (!$tache) {
            throw $this->createNotFoundException();
        }
        $ouvrier = $tache->getAssignee();
        if (!$ouvrier || !$this->appartientAgriculteur($ouvrier, $agriculteur, $terrainRepo)) {
            throw $this->createAccessDeniedException();
        }
        $cinOuvrier = $ouvrier->getCin();
        $em->remove($tache);
        $em->flush();
        $this->addFlash('success', 'Tâche supprimée.');
        return $this->redirectToRoute('app_ouvrier_taches', ['cin' => $cinOuvrier]);
    }

    private function appartientAgriculteur(User $ouvrier, User $agriculteur, TerrainRepository $terrainRepo): bool
    {
        $terrain = $ouvrier->getTerrain();
        if ($terrain) {
            return $terrain->getCin() === $agriculteur->getCin();
        }
        $ouvriersAgriculteur = $terrainRepo->findCinsOuvriersAgriculteur($agriculteur->getCin());
        return in_array($ouvrier->getCin(), $ouvriersAgriculteur, true);
    }
    // Injectez le service dans le constructeur ou en paramètre de méthode
#[Route('/tache/assignation-auto', name: 'app_ouvrier_tache_auto', methods: ['POST'])]
public function assignationAuto(
    Request $request,
    TerrainRepository $terrainRepo,
    EntityManagerInterface $em,
    AssignationAutoService $assignationService
): Response {
    $agriculteur = $this->getUser();

    // Récupère la date saisie
    $echeanceStr = $request->request->get('date_echeancee');
    $echeance    = $echeanceStr ? new \DateTime($echeanceStr) : null;

    // Choisit automatiquement l'ouvrier
    $ouvrier = $assignationService->choisirOuvrier($agriculteur, $echeance);

    if (!$ouvrier) {
        $this->addFlash('danger', 'Aucun ouvrier disponible. Créez d\'abord des ouvriers.');
        return $this->redirectToRoute('app_ouvrier_index');
    }

    // Crée la tâche
    $tache = new Tache();
    $tache->setNomTache($request->request->get('nom_tache'));
    $tache->setDescription($request->request->get('description'));
    $tache->setEtat($request->request->get('etat', 'à faire'));
    $tache->setPriorite($request->request->get('priorite', 'normale'));
    $tache->setAssignee($ouvrier);

    if ($echeance) {
        $tache->setDateEcheancee($echeance);
    }

    $em->persist($tache);
    $em->flush();

    $this->addFlash('success', sprintf(
        'Tâche assignée automatiquement à %s %s.',
        $ouvrier->getPrenom(),
        $ouvrier->getNom()
    ));

    return $this->redirectToRoute('app_ouvrier_index');
}
#[Route('/tache/suggestion-ia', name: 'app_ouvrier_tache_ia_suggest', methods: ['GET'])]
public function suggestionIA(\App\Service\TacheIAService $iaService): Response
{
    $agriculteur = $this->getUser();
    try {
        $suggestion = $iaService->genererSuggestion($agriculteur->getCin());
        return $this->json($suggestion); // ex: { nom_tache, description, priorite, etat }
    } catch (\Exception $e) {
        return $this->json(['error' => $e->getMessage()], 500);
    }
}
    #[Route('/debug-ia', name: 'app_test_ia', methods: ['GET'])]
public function testIA(\App\Service\TacheIAService $iaService): Response
{
    try {
        $result = $iaService->genererSuggestion($this->getUser()->getCin());
        return $this->json(['ok' => true, 'result' => $result]);
    } catch (\Throwable $e) {
        return $this->json([
            'error' => $e->getMessage(),
            'class' => get_class($e),
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
        ]);
    }
}
}