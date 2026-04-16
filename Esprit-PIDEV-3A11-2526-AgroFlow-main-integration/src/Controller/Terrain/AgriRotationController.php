<?php
namespace App\Controller\Terrain;

use App\Entity\Terrain\Rotation;
use App\Form\Terrain\RotationType;
use App\Repository\Terrain\RotationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/agri/rotations', name: 'agri_rotations')]
class AgriRotationController extends AbstractController
{
    #[Route('', name: '', methods: ['GET'])]
    public function index(Request $req, RotationRepository $repo): Response
    {
        $user = $this->getUser();
        $cin  = $user->getCin();

        $q         = $req->query->get('q', '');
        $rotations = $q
            ? $repo->searchByUserCin($q, $cin)
            : $repo->findByUserCin($cin);

        $total    = count($rotations);
        $actifs   = $repo->countByStatusAndCin(1, $cin);
        $inactifs = $repo->countByStatusAndCin(0, $cin);

        $durees = array_filter(array_map(function($r) {
            if ($r->getDateDebut() && $r->getDateFin()) {
                return $r->getDateFin()->diff($r->getDateDebut())->days;
            }
            return null;
        }, $rotations));
        $dureeMoyenne = count($durees)
            ? round(array_sum($durees) / count($durees))
            : null;

        return $this->render('agri/rotation/index.html.twig', [
            'rotations'    => $rotations,
            'q'            => $q,
            'total'        => $total,
            'actifs'       => $actifs,
            'inactifs'     => $inactifs,
            'dureeMoyenne' => $dureeMoyenne,
        ]);
    }

    #[Route('/new', name: '_new', methods: ['GET','POST'])]
    public function new(Request $req, EntityManagerInterface $em): Response
    {
        $rotation = new Rotation();
    $form     = $this->createForm(RotationType::class, $rotation, [
        'user_cin' => $this->getUser()->getCin(),  // ← CIN passé ici
    ]);
    $form->handleRequest($req);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->persist($rotation);
        $em->flush();
        $this->addFlash('success', 'Rotation créée avec succès.');
        return $this->redirectToRoute('agri_rotations');
    }

    return $this->render('agri/rotation/form.html.twig', [
        'form'  => $form->createView(),
        'title' => 'Nouvelle rotation',
        'edit'  => false,
    ]);
    }

    #[Route('/{id}/edit', name: '_edit', methods: ['GET','POST'])]
    public function edit(Rotation $rotation, Request $req, EntityManagerInterface $em): Response
    {
       $form = $this->createForm(RotationType::class, $rotation, [
        'user_cin' => $this->getUser()->getCin(),  // ← CIN passé ici
    ]);
    $form->handleRequest($req);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();
        $this->addFlash('success', 'Rotation modifiée avec succès.');
        return $this->redirectToRoute('agri_rotations');
    }

    return $this->render('agri/rotation/form.html.twig', [
        'form'     => $form->createView(),
        'title'    => 'Modifier la rotation',
        'rotation' => $rotation,
        'edit'     => true,
    ]);
    }

    #[Route('/{id}/delete', name: '_delete', methods: ['POST'])]
    public function delete(Rotation $rotation, Request $req, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$rotation->getId(), $req->request->get('_token'))) {
            $em->remove($rotation);
            $em->flush();
            $this->addFlash('success', 'Rotation supprimée.');
        }
        return $this->redirectToRoute('agri_rotations');
    }

    #[Route('/{id}', name: '_show', methods: ['GET'])]
    public function show(Rotation $rotation): Response
    {
        return $this->render('agri/rotation/show.html.twig', [
            'rotation' => $rotation,
        ]);
    }
}