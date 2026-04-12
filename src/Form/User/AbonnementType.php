<?php

namespace App\Form\User;

use App\Entity\User\Abonnement;
use App\Repository\User\OffreRepository;
use App\Repository\User\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class AbonnementType extends AbstractType
{
    public function __construct(
        private readonly OffreRepository $offreRepository,
        private readonly UserRepository  $userRepository,
        private readonly Security        $security,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // ── Dropdown Offres ──────────────────────────────────────────────────
        $offres        = $this->offreRepository->findAll();
        $offresChoices = [];
        foreach ($offres as $offre) {
            $label = '#' . $offre->getIdOffres() . ' — ' . $offre->getNomOffre() . ' (' . $offre->getPrix() . ' TND)';
            $offresChoices[$label] = $offre->getIdOffres();
        }

        // ── Dropdown Users (sauf utilisateur connecté) ───────────────────────
        $currentCin   = $this->security->getUser()?->getCin();
        $users        = $this->userRepository->findAll();
        $usersChoices = [];
        foreach ($users as $user) {
            if ($user->getCin() === $currentCin) {
                continue; // exclure l'utilisateur connecté
            }
            $label = $user->getPrenom() . ' ' . $user->getNom() . ' — CIN: ' . $user->getCin();
            $usersChoices[$label] = $user->getCin();
        }

        $builder
            ->add('cin', ChoiceType::class, [
                'label'       => 'Utilisateur',
                'choices'     => $usersChoices,
                'placeholder' => '-- Sélectionner un utilisateur --',
                'required'    => true,
                'attr'        => ['style' => 'width:100%'],
            ])
            ->add('idOffre', ChoiceType::class, [
                'label'       => 'Offre',
                'choices'     => $offresChoices,
                'placeholder' => '-- Sélectionner une offre --',
                'required'    => true,
                'attr'        => ['style' => 'width:100%'],
            ])
            ->add('dateInscription', DateType::class, [
                'label'  => 'Date d\'inscription',
                'widget' => 'single_text',
            ])
            ->add('dateExpiration', DateType::class, [
                'label'  => 'Date d\'expiration',
                'widget' => 'single_text',
            ])
            ->add('situation', ChoiceType::class, [
                'label'   => 'Situation',
                'choices' => [
                    'Actif'   => 'actif',
                    'Inactif' => 'inactif',
                    'Expiré'  => 'expire',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Abonnement::class,
        ]);
    }
}