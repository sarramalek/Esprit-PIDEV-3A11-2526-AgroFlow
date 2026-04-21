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
            ->add('idM', IntegerType::class, [
                'label'    => false,
                'required' => false,
                'attr'     => ['min' => 1],
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