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

#[Route('/agri/plantes', name: 'agri_plantes')]
class AgriPlanteController extends AbstractController
{
    #[Route('', name: '', methods: ['GET'])]
    public function index(Request $req, PlanteRepository $repo): Response
    {
        $q       = $req->query->get('q', '');
        $plantes = $q ? $repo->search($q) : $repo->findAll();

        $total        = count($plantes);
        $eauValues    = array_filter(array_map(fn($p) => $p->getBesoinEau(), $plantes));
        $avgBesoinEau = count($eauValues)
            ? round(array_sum($eauValues) / count($eauValues), 2)
            : null;
        $cycleValues  = array_filter(array_map(fn($p) => $p->getCycleJours(), $plantes));
        $avgCycle     = count($cycleValues)
            ? round(array_sum($cycleValues) / count($cycleValues))
            : null;

        return $this->render('agri/plante/index.html.twig', [
            'plantes'       => $plantes,
            'q'             => $q,
            'total'         => $total,
            'avgBesoinEau'  => $avgBesoinEau,
            'avgCycleJours' => $avgCycle,
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
            return $this->redirectToRoute('agri_plantes');
        }

        return $this->render('agri/plante/form.html.twig', [
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
            $this->addFlash('success', 'Plante modifiée avec succès.');
            return $this->redirectToRoute('agri_plantes');
        }

        return $this->render('agri/plante/form.html.twig', [
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
        return $this->redirectToRoute('agri_plantes');
    }

    #[Route('/{id}', name: '_show', methods: ['GET'])]
    public function show(Plante $plante): Response
    {
        return $this->render('agri/plante/show.html.twig', [
            'plante' => $plante,
        ]);
    }

    #[Route('/langue/{locale}', name: '_changer_langue')]
    public function changerLangue(string $locale, Request $request): Response
    {
        $request->getSession()->set('_locale', $locale);

        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('agri_plantes'));
    }
}