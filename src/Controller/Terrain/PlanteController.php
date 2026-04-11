<?php
namespace App\Controller\Terrain;

use App\Entity\Terrain\Plante;
use App\Form\Terrain\PlanteType;
use App\Repository\Terrain\PlanteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/plantes', name: 'admin_plantes')]
class PlanteController extends AbstractController
{
    #[Route('', name: '', methods: ['GET'])]
    public function index(Request $req, PlanteRepository $repo): Response
    {
        $q       = $req->query->get('q', '');
        $plantes = $q ? $repo->search($q) : $repo->findAll();

        return $this->render('Plante/index.html.twig', [
            'plantes'       => $plantes,
            'q'             => $q,
            'total'         => count($repo->findAll()),
            'avgBesoinEau'  => $repo->avgBesoinEau(),
            'avgCycleJours' => $repo->avgCycleJours(),
        ]);
    }

    #[Route('/new', name: '_new', methods: ['GET','POST'])]
    public function new(Request $req, EntityManagerInterface $em): Response
    {
        $plante = new Plante();
        $form   = $this->createForm(PlanteType::class, $plante);
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($plante);
            $em->flush();
            $this->addFlash('success', 'Plante créée avec succès.');
            return $this->redirectToRoute('admin_plantes');
        }

        return $this->render('Plante/form.html.twig', [
            'form'  => $form->createView(),
            'title' => 'Nouvelle plante',
            'edit'  => false,
        ]);
    }

    #[Route('/{id}/edit', name: '_edit', methods: ['GET','POST'])]
    public function edit(Plante $plante, Request $req, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PlanteType::class, $plante);
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Plante modifiée.');
            return $this->redirectToRoute('admin_plantes');
        }

        return $this->render('Plante/form.html.twig', [
            'form'   => $form->createView(),
            'title'  => 'Modifier la plante',
            'plante' => $plante,
            'edit'   => true,
        ]);
    }

    #[Route('/{id}/delete', name: '_delete', methods: ['POST'])]
    public function delete(Plante $plante, Request $req, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$plante->getId(), $req->request->get('_token'))) {
            $em->remove($plante);
            $em->flush();
            $this->addFlash('success', 'Plante supprimée.');
        }
        return $this->redirectToRoute('admin_plantes');
    }

    #[Route('/{id}', name: '_show', methods: ['GET'])]
    public function show(Plante $plante): Response
    {
        return $this->render('Plante/show.html.twig', [
            'plante' => $plante,
        ]);
    }
}