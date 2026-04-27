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
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;


/**
 * @extends AbstractType<Abonnement>
 */
class AbonnementType extends AbstractType
{
    public function __construct(
        private readonly OffreRepository $offreRepository,
        private readonly UserRepository  $userRepository,
        private Security $security
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // ── Dropdown Offres ──────────────────────────────────────────────────
        $offres        = $this->offreRepository->findAll();
        /** @var array<string, int> $offresChoices */
        $offresChoices = [];
        foreach ($offres as $offre) {
            $label = '#' . $offre->getIdOffres() . ' — ' . $offre->getNomOffre() . ' (' . $offre->getPrix() . ' TND)';
            $offresChoices[$label] = $offre->getIdOffres();
        }

        // ── Dropdown Users (sauf utilisateur connecté) ───────────────────────
        $user = $this->security->getUser();
        $currentCin = ($user instanceof \App\Entity\User\User) ? $user->getCin() : null;

        $users        = $this->userRepository->findAll();
        /** @var array<string, int> $usersChoices */
        $usersChoices = [];
        foreach ($users as $user) {
            if ($user->getCin() === $currentCin) {
                continue;
            }
            $label = $user->getPrenom() . ' ' . $user->getNom() . ' — CIN: ' . $user->getCin();
            $usersChoices[$label] = $user->getCin();
        }

        $builder
            ->add('cin', ChoiceType::class, [
                'label'       => 'Utilisateur',
                'choices'     => $usersChoices,
                'placeholder' => '-- Sélectionner un utilisateur --',
                'required'    => false,
                'attr'        => ['style' => 'width:100%'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner un utilisateur.']),
                    new Choice([
                        'choices' => array_values($usersChoices),
                        'message' => 'Utilisateur invalide.',
                    ]),
                ],
            ])
            ->add('idOffre', ChoiceType::class, [
                'label'       => 'Offre',
                'choices'     => $offresChoices,
                'placeholder' => '-- Sélectionner une offre --',
                'required'    => false,
                'attr'        => ['style' => 'width:100%'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner une offre.']),
                    new Choice([
                        'choices' => array_values($offresChoices),
                        'message' => 'Offre invalide.',
                    ]),
                ],
            ])
            ->add('dateInscription', DateType::class, [
    'label'       => 'Date d\'inscription',
    'widget'      => 'single_text',
    'html5'       => false,
    'required'    => false,
    'empty_data'  => null,
    'constraints' => [
        new NotBlank(['message' => 'Veuillez entrer la date d\'inscription.']),
        new GreaterThanOrEqual([
            'value'   => new \DateTime('first day of January this year'),
            'message' => 'La date d\'inscription doit être dans l\'année en cours.',
        ]),
        new LessThanOrEqual([
            'value'   => new \DateTime('last day of December this year'),
            'message' => 'La date d\'inscription doit être dans l\'année en cours.',
        ]),
    ],
])
->add('dateExpiration', DateType::class, [
    'label'       => 'Date d\'expiration',
    'widget'      => 'single_text',
    'html5'       => false,
    'required'    => false,
    'empty_data'  => null,
    'constraints' => [
        new NotBlank(['message' => 'Veuillez entrer la date d\'expiration.']),
        new GreaterThan([
            'value'   => new \DateTime('today'),
            'message' => 'La date d\'expiration doit être dans le futur.',
        ]),
    ],
])
            ->add('situation', ChoiceType::class, [
                'label'       => 'Situation',
                'required'    => false,
                'choices'     => [
                    'Actif'   => 'actif',
                    'Inactif' => 'inactif',
                    'Expiré'  => 'expire',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner une situation.']),
                    new Choice([
                        'choices' => ['actif', 'inactif', 'expire'],
                        'message' => 'La situation sélectionnée est invalide.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'     => Abonnement::class,
            'error_bubbling' => false,
            'attr'           => ['novalidate' => 'novalidate'],
        ]);
    }
}