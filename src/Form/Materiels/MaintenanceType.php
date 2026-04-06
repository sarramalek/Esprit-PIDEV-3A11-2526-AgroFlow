<?php

namespace App\Form\Materiels;

use App\Entity\Materiels\Maintenance;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Regex;

class MaintenanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            // ── Type de panne ──
            ->add('typePanne', ChoiceType::class, [
                'label'       => 'Type de panne',
                'placeholder' => '-- Sélectionner un type --',
                'choices'     => [
                    'Mécanique'         => 'Mécanique',
                    'Électricité'       => 'Électricité',
                    'Hydraulique'       => 'Hydraulique',
                    'Logicielle'        => 'Logicielle',
                    'Transmission'      => 'Transmission',
                    'Moteur'            => 'Moteur',
                    'Vidange & filtres' => 'Vidange & filtres',
                    'Révision générale' => 'Révision générale',
                    'Pneumatique'       => 'Pneumatique',
                    'Autre'             => 'Autre',
                ],
                'constraints' => [
                    new NotBlank(message: 'Veuillez sélectionner un type de panne.'),
                    new NotNull(message: 'Le type de panne est obligatoire.'),
                ],
                'attr' => ['class' => 'form-select'],
            ])

            // ── Coût ──
            ->add('cout', NumberType::class, [
                'label'   => 'Coût (DT)',
                'scale'   => 2,
                'html5'   => true,
                'constraints' => [
                    new NotBlank(message: 'Le coût est obligatoire.'),
                    new NotNull(message: 'Le coût ne peut pas être vide.'),
                    new Positive(message: 'Le coût doit être un nombre positif.'),
                    new GreaterThan([
                        'value'   => 0,
                        'message' => 'Le coût doit être supérieur à 0 DT.',
                    ]),
                    new LessThanOrEqual([
                        'value'   => 999999.99,
                        'message' => 'Le coût ne peut pas dépasser 999 999,99 DT.',
                    ]),
                ],
                'attr' => [
                    'class'       => 'form-control',
                    'placeholder' => 'Ex : 250.00',
                    'min'         => 0.01,
                    'max'         => 999999.99,
                    'step'        => '0.01',
                ],
            ])

            // ── Date ──
            ->add('dateMain', DateType::class, [
                'label'  => 'Date de maintenance',
                'widget' => 'single_text',
                'html5'  => true,
                'constraints' => [
                    new NotBlank(message: 'La date est obligatoire.'),
                    new NotNull(message: 'La date ne peut pas être vide.'),
                    new LessThanOrEqual([
                        'value'   => new \DateTime('today'),
                        'message' => 'La date ne peut pas être dans le futur.',
                    ]),
                    new GreaterThan([
                        'value'   => new \DateTime('1990-01-01'),
                        'message' => 'La date doit être postérieure au 01/01/1990.',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'max'   => (new \DateTime())->format('Y-m-d'),
                    'min'   => '1990-01-01',
                ],
            ])

            // ── Description ──
            ->add('description', TextareaType::class, [
                'label'    => 'Description',
                'required' => false,
                'constraints' => [
                    new Length([
                        'max'        => 1000,
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères.',
                        'min'        => 5,
                        'minMessage' => 'La description doit contenir au moins {{ limit }} caractères.',
                    ]),
                    new Regex([
                        'pattern' => '/^[^<>{}]*$/',
                        'message' => 'La description contient des caractères non autorisés.',
                    ]),
                ],
                'attr' => [
                    'class'       => 'form-control',
                    'placeholder' => "Décrivez l'intervention effectuée…",
                    'rows'        => 4,
                    'maxlength'   => 1000,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Maintenance::class,
        ]);
    }
}