<?php
namespace App\Controller\Terrain;

use App\Entity\Terrain\Terrain;
use App\Form\Terrain\TerrainType;
use App\Repository\Terrain\TerrainRepository;
use App\Repository\User\UserRepository;          // ← AJOUTER
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/terrains', name: 'admin_terrains')]
class TerrainController extends AbstractController
{
    #[Route('', name: '', methods: ['GET'])]
    public function index(TerrainRepository $repo, UserRepository $userRepo): Response
    {
        $terrains = $repo->findAll();

        $usersRaw  = $userRepo->findAllForSelect();
        $cinToName = [];
        foreach ($usersRaw as $u) {
            $cinToName[(string)$u['cin']] = $u['nom'] . ' ' . $u['prenom'];
        }

        $totalTerrains = count($terrains);
        $surfaceTotale = array_sum(array_map(fn($t) => $t->getSurface(), $terrains));
        $pHValues      = array_filter(array_map(fn($t) => $t->getPH(), $terrains));
        $pHMoyen       = count($pHValues) ? round(array_sum($pHValues) / count($pHValues), 2) : null;

        $typeSolStats = [];
        foreach ($terrains as $t) {
            $type = $t->getTypeSol();
            $typeSolStats[$type] = ($typeSolStats[$type] ?? 0) + 1;
        }

        return $this->render('Terrain/index.html.twig', [
            'terrains'      => $terrains,
            'cinToName'     => $cinToName,
            'totalTerrains' => $totalTerrains,
            'surfaceTotale' => $surfaceTotale,
            'pHMoyen'       => $pHMoyen,
            'typeSolStats'  => $typeSolStats,
        ]);
    }

    #[Route('/new', name: '_new', methods: ['GET','POST'])]
    public function new(Request $req, EntityManagerInterface $em): Response
    {
        $terrain = new Terrain();
        $form = $this->createForm(TerrainType::class, $terrain);
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($terrain);
            $em->flush();
            $this->addFlash('success', 'Terrain créé avec succès.');
            return $this->redirectToRoute('admin_terrains');
        }

        return $this->render('terrain/form.html.twig', [
            'form'  => $form->createView(),
            'title' => 'Nouveau terrain',
            'edit'  => false,
        ]);
    }

    #[Route('/{id}/edit', name: '_edit', methods: ['GET','POST'])]
    public function edit(Terrain $terrain, Request $req, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(TerrainType::class, $terrain);
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Terrain modifié avec succès.');
            return $this->redirectToRoute('admin_terrains');
        }

        return $this->render('terrain/form.html.twig', [
            'form'    => $form->createView(),
            'title'   => 'Modifier le terrain',
            'terrain' => $terrain,
            'edit'    => true,
        ]);
    }

    #[Route('/{id}/delete', name: '_delete', methods: ['POST'])]
    public function delete(Terrain $terrain, Request $req, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$terrain->getId(), $req->request->get('_token'))) {
            $em->remove($terrain);
            $em->flush();
            $this->addFlash('success', 'Terrain supprimé.');
        }
        return $this->redirectToRoute('admin_terrains');
    }

    #[Route('/{id}', name: '_show', methods: ['GET'])]
    public function show(Terrain $terrain, UserRepository $userRepo): Response
    {
        $usersRaw  = $userRepo->findAllForSelect();
        $cinToName = [];
        foreach ($usersRaw as $u) {
            $cinToName[(string)$u['cin']] = $u['nom'] . ' ' . $u['prenom'];
        }

        return $this->render('terrain/show.html.twig', [
            'terrain'   => $terrain,
            'cinToName' => $cinToName,
        ]);
    }
}