<?php
namespace App\Controller\Terrain;

use App\Entity\Terrain\Terrain;
use App\Form\Terrain\AgriTerrainType;
use App\Repository\Terrain\TerrainRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agri/terrains', name: 'agri_terrains')]
#[IsGranted('ROLE_AGRICULTEUR')]   // ← corrigé ici
class AgriTerrainController extends AbstractController
{
    #[Route('', name: '', methods: ['GET'])]
    public function index(TerrainRepository $repo): Response
    {
        $cin      = $this->getUser()->getCin();
        $terrains = $repo->findBy(['cin' => $cin]);

        $totalTerrains = count($terrains);
        $surfaceTotale = array_sum(array_map(fn($t) => $t->getSurface() ?? 0, $terrains));
        $pHValues      = array_filter(array_map(fn($t) => $t->getPH(), $terrains));
        $pHMoyen       = count($pHValues)
            ? round(array_sum($pHValues) / count($pHValues), 2)
            : null;

        $typeSolStats = [];
        foreach ($terrains as $t) {
            $type = $t->getTypeSol() ?? 'Inconnu';
            $typeSolStats[$type] = ($typeSolStats[$type] ?? 0) + 1;
        }

        return $this->render('agri/terrain/index.html.twig', [
            'terrains'      => $terrains,
            'totalTerrains' => $totalTerrains,
            'surfaceTotale' => $surfaceTotale,
            'pHMoyen'       => $pHMoyen,
            'typeSolStats'  => $typeSolStats,
        ]);
    }

    #[Route('/new', name: '_new', methods: ['GET', 'POST'])]
    public function new(Request $req, EntityManagerInterface $em): Response
    {
        $terrain = new Terrain();
        $terrain->setCin($this->getUser()->getCin()); // CIN injecté automatiquement

        $form = $this->createForm(AgriTerrainType::class, $terrain);
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($terrain);
            $em->flush();
            $this->addFlash('success', 'Terrain ajouté avec succès !');
            return $this->redirectToRoute('agri_terrains');
        }

        return $this->render('agri/terrain/form.html.twig', [
            'form'  => $form->createView(),
            'title' => 'Nouveau terrain',
            'edit'  => false,
        ]);
    }

    #[Route('/{id}/edit', name: '_edit', methods: ['GET', 'POST'])]
    public function edit(Terrain $terrain, Request $req, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('TERRAIN_OWNER', $terrain);

        $form = $this->createForm(AgriTerrainType::class, $terrain);
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Terrain modifié avec succès.');
            return $this->redirectToRoute('agri_terrains');
        }

        return $this->render('agri/terrain/form.html.twig', [
            'form'    => $form->createView(),
            'title'   => 'Modifier le terrain',
            'terrain' => $terrain,
            'edit'    => true,
        ]);
    }

    #[Route('/{id}/delete', name: '_delete', methods: ['POST'])]
    public function delete(Terrain $terrain, Request $req, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('TERRAIN_OWNER', $terrain);

        if ($this->isCsrfTokenValid('delete'.$terrain->getId(), $req->request->get('_token'))) {
            $em->remove($terrain);
            $em->flush();
            $this->addFlash('success', 'Terrain supprimé.');
        }
        return $this->redirectToRoute('agri_terrains');
    }

    #[Route('/{id}', name: '_show', methods: ['GET'])]
    public function show(Terrain $terrain): Response
    {
        $this->denyAccessUnlessGranted('TERRAIN_OWNER', $terrain);

        return $this->render('agri/terrain/show.html.twig', [
            'terrain' => $terrain,
        ]);
    }
}