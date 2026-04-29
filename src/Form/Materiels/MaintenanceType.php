<?php

namespace App\Form\Materiels;

use App\Entity\Materiels\Maintenance;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Materiels\Machine;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * @extends AbstractType<Maintenance>
 */
class MaintenanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typePanne', ChoiceType::class, [
                'label'    => false,
                'choices'  => [
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
                'placeholder' => '— Choisir un type —',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez sélectionner un type de panne.']),
                ],
            ])
            ->add('cout', NumberType::class, [
                'label'   => false,
                'scale'   => 2,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le coût est obligatoire.']),
                    new Assert\Positive(['message' => 'Le coût doit être positif.']),
                    new Assert\LessThanOrEqual([
                        'value'   => 999999.99,
                        'message' => 'Le coût ne peut pas dépasser 999 999,99 DT.',
                    ]),
                ],
            ])
            ->add('dateMain', DateType::class, [
                'label'  => false,
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date est obligatoire.']),
                    new Assert\LessThanOrEqual([
                        'value'   => 'today',
                        'message' => 'La date ne peut pas être dans le futur.',
                    ]),
                ],
            ])
            ->add('statut', ChoiceType::class, [
                'label'   => false,
                'choices' => [
                    'Planifié'   => 'planifie',
                    'En cours'   => 'en_cours',
                    'Terminé'    => 'termine',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez sélectionner un statut.']),
                ],
            ])
            ->add('priorite', ChoiceType::class, [
                'label'   => false,
                'choices' => [
                    'Faible'   => 'faible',
                    'Moyenne'  => 'moyenne',
                    'Haute'    => 'haute',
                    'Urgente'  => 'urgente',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez sélectionner une priorité.']),
                ],
            ])
            ->add('kilometrage', IntegerType::class, [
                'label'    => false,
                'required' => false,
                'attr'     => ['min' => 0, 'placeholder' => 'Ex: 12500'],
                'constraints' => [
                    new Assert\PositiveOrZero(['message' => 'Le kilométrage doit être positif ou zéro.']),
                    new Assert\LessThanOrEqual([
                        'value'   => 9999999,
                        'message' => 'Kilométrage trop élevé.',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label'    => false,
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'min'        => 5,
                        'max'        => 1000,
                        'minMessage' => 'La description doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[^<>{}]*$/',
                        'message' => 'La description ne doit pas contenir les caractères < > { }.',
                    ]),
                ],
            ])
            ->add('recommandation', TextareaType::class, [
                'label'    => false,
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max'        => 2000,
                        'maxMessage' => 'La recommandation ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            // ✅ Remplacer par
->add('idM', EntityType::class, [
    'class'        => Machine::class,
    'choice_label' => 'nom',
    'label'        => false,
    'required'     => false,
    'placeholder'  => '— Aucune machine —',
])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Maintenance::class,
        ]);
    }
}