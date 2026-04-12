<?php

namespace App\Controller\User;

use App\Entity\User\Tache;
use App\Entity\User\User;
use App\Repository\User\TacheRepository;
use App\Repository\User\UserRepository;
use App\Repository\Terrain\TerrainRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/agriculteur/ouvriers')]
class Ouvrier_agriController extends AbstractController
{
    #[Route('', name: 'app_ouvrier_index', methods: ['GET'])]
    public function index(UserRepository $userRepo, TerrainRepository $terrainRepo): Response
    {
        $agriculteur = $this->getUser();
        $terrains = $terrainRepo->findByAgriculteur($agriculteur->getCin());
        $ouvriers = $userRepo->findOuvriersByAgriculteur($agriculteur->getCin());
        return $this->render('User/ouvriers_index.html.twig', [
            'ouvriers' => $ouvriers,
            'terrains' => $terrains,
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
            'ouvrier' => $ouvrier,
            'terrains' => $terrains,
        ]);
    }

    // ✅ SUPPRIMER UN OUVRIER — route corrigée
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
            'taches' => $taches,
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

    // ✅ SUPPRIMER UNE TÂCHE — nom de route corrigé
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
}