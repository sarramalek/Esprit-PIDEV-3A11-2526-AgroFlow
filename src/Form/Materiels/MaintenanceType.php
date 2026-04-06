<?php

namespace App\Form\Materiels;

use App\Entity\Materiels\Maintenance;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class MaintenanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typePanne', ChoiceType::class, [
                'label'       => 'Type de Panne *',
                'placeholder' => '-- Sélectionnez un type --',
                'choices'     => [
                    'Panne mécanique'   => 'mecanique',
                    'Panne électrique'  => 'electrique',
                    'Panne hydraulique' => 'hydraulique',
                    'Panne logicielle'  => 'logicielle',
                    'Autre'             => 'autre',
                ],
                'constraints' => [new NotBlank(['message' => 'Le type de panne est obligatoire.'])],
                'attr' => ['class' => 'form-select'],
            ])

            ->add('cout', NumberType::class, [
                'label' => 'Coût (DT) *',
                'scale' => 2,
                'constraints' => [
                    new NotBlank(['message' => 'Le coût est obligatoire.']),
                    new Positive(['message' => 'Le coût doit être positif.']),
                ],
                'attr' => [
                    'class'       => 'form-control',
                    'placeholder' => '0.00',
                    'step'        => '0.01',
                ],
            ])

            ->add('dateMain', DateType::class, [
                'label'  => 'Date de Maintenance *',
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'La date de maintenance est obligatoire.']),
                ],
                'attr' => ['class' => 'form-control'],
            ])

            ->add('description', TextareaType::class, [
                'label' => 'Description *',
                'constraints' => [
                    new NotBlank(['message' => 'La description est obligatoire.']),
                    new Length([
                        'min' => 10,
                        'max' => 500,
                        'minMessage' => 'La description doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
                'attr' => [
                    'class'       => 'form-control',
                    'rows'        => 5,
                    'placeholder' => 'Décrivez la panne et les travaux effectués...',
                ],
            ])

            ->add('idM', NumberType::class, [
                'label' => 'ID Matériel *',
                'constraints' => [
                    new NotBlank(['message' => 'L\'ID du matériel est obligatoire.']),
                    new Positive(['message' => 'L\'ID doit être un nombre positif.']),
                ],
                'attr' => [
                    'class'       => 'form-control',
                    'placeholder' => 'Entrez l\'ID du matériel',
                ],
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