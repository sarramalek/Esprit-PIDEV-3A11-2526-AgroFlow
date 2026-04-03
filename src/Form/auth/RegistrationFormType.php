<?php

namespace App\Form;

use App\Entity\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


class RegistrationFormType extends AbstractType
{
    // Villes et leurs adresses/quartiers
    private const VILLES_ADRESSES = [
        'Tunis' => [
            'Centre-ville', 'La Marsa', 'Le Bardo', 'Carthage',
            'Ariana', 'La Goulette', 'Sidi Bou Saïd', 'Menzah',
        ],
        'Sfax' => [
            'Centre-ville', 'Sakiet Ezzit', 'Sakiet Eddaier',
            'Thyna', 'El Ain', 'Chihia',
        ],
        'Sousse' => [
            'Centre-ville', 'Hammam Sousse', 'Akouda',
            'Kalaa Kebira', 'Msaken', 'Kantaoui',
        ],
        'Nabeul' => [
            'Centre-ville', 'Hammamet', 'Kelibia',
            'Korba', 'Menzel Temime', 'Dar Chaabane',
        ],
        'Bizerte' => [
            'Centre-ville', 'Menzel Bourguiba', 'Mateur',
            'Ras Jebel', 'Zarzouna', 'Utique',
        ],
        'Gabès' => [
            'Centre-ville', 'Mareth', 'El Hamma',
            'Matmata', 'Chenini', 'Ghannouch',
        ],
        'Kairouan' => [
            'Centre-ville', 'Sbikha', 'Haffouz',
            'El Alaa', 'Nasrallah', 'Oueslatia',
        ],
        'Monastir' => [
            'Centre-ville', 'Ksar Hellal', 'Moknine',
            'Jemmal', 'Sahline', 'Bembla',
        ],
        'Gafsa' => [
            'Centre-ville', 'Metlaoui', 'El Ksar',
            'Redeyef', 'Moulares', 'Sened',
        ],
        'Médenine' => [
            'Centre-ville', 'Djerba', 'Zarzis',
            'Ben Gardane', 'Beni Khedache', 'Sidi Makhlouf',
        ],
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Préparer les choix de villes
        $villeChoices = array_combine(
            array_keys(self::VILLES_ADRESSES),
            array_keys(self::VILLES_ADRESSES)
        );

        $builder
        
            // Rôle (bouton radio stylisé)
            ->add('role', ChoiceType::class, [
                'label'    => false,
                'expanded' => true,   // rendu en boutons radio
                'multiple' => false,
                'choices'  => [
                    'Ouvrier'     => 1,
                    'Agriculteur' => 2,
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez choisir un rôle.']),
                ],
            ])
            ->add('cin', IntegerType::class, [
    'label' => false,
    'attr'  => ['placeholder' => 'Numéro CIN (8 chiffres)'],
    'constraints' => [
        new NotBlank(['message' => 'Veuillez entrer votre CIN.']),
        new Length([
            'min'        => 8,
            'max'        => 8,
            'exactMessage' => 'Le CIN doit contenir exactement {{ limit }} chiffres.',
        ]),
        new Positive(['message' => 'Le CIN doit être un nombre positif.']),
    ],
])
            // Date de naissance
            ->add('dateNaiss', BirthdayType::class, [
                'label'  => false,
                'widget' => 'single_text',   // input date HTML5
                'html5'  => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre date de naissance.']),
                    new LessThanOrEqual([
                        'value'   => new \DateTime('-18 years'),
                        'message' => 'Vous devez avoir au moins 18 ans.',
                    ]),
                    new GreaterThanOrEqual([
                        'value'   => new \DateTime('-100 years'),
                        'message' => 'Date de naissance invalide.',
                    ]),
                ],
            ])
            // Nom
            ->add('nom', TextType::class, [
                'label' => false,
                'attr'  => ['placeholder' => 'Nom'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre nom.']),
                    new Length(['max' => 255]),
                ],
            ])
            // Prénom
            ->add('prenom', TextType::class, [
                'label' => false,
                'attr'  => ['placeholder' => 'Prénom'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre prénom.']),
                    new Length(['max' => 255]),
                ],
            ])
            // Email
            ->add('email', EmailType::class, [
                'label' => false,
                'attr'  => ['placeholder' => 'Email'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre email.']),
                    new Email(['message' => 'Email invalide.']),
                ],
            ])
            // Ville (liste déroulante)
            ->add('ville', ChoiceType::class, [
                'label'       => false,
                'placeholder' => '-- Choisissez une ville --',
                'choices'     => $villeChoices,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez choisir une ville.']),
                ],
            ])
            // Adresse (initialement vide, remplie dynamiquement via JS + FormEvents)
            ->add('adresse', ChoiceType::class, [
                'label'       => false,
                'placeholder' => '-- Choisissez un quartier --',
                'choices'     => [],   // vide par défaut, rempli par JS ou FormEvent
                'required'    => false,
            ])
            // Mot de passe
            ->add('plainPassword', PasswordType::class, [
                'label'  => false,
                'mapped' => false,
                'attr'   => ['placeholder' => 'Mot de passe'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un mot de passe.']),
                    new Length([
                        'min'        => 8,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                        'max'        => 4096,
                    ]),
                ],
            ])
        ;

        // FormEvent : peupler les adresses selon la ville soumise
        $addAdresseField = function ($form, ?string $ville) {
            $adresses = self::VILLES_ADRESSES[$ville] ?? [];
            $choices  = array_combine($adresses, $adresses);

            $form->add('adresse', ChoiceType::class, [
                'label'       => false,
                'placeholder' => '-- Choisissez un quartier --',
                'choices'     => $choices,
                'required'    => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez choisir un quartier.']),
                ],
            ]);
        };

        // Avant soumission (pour affichage initial)
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($addAdresseField) {
            $user = $event->getData();
            $ville = $user instanceof User ? $user->getVille() : null;
            $addAdresseField($event->getForm(), $ville);
        });

        // Après soumission (pour valider les adresses de la ville choisie)
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($addAdresseField) {
            $data  = $event->getData();
            $ville = $data['ville'] ?? null;
            $addAdresseField($event->getForm(), $ville);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}