<?php

namespace App\Form\Materiels;

use App\Entity\Materiels\Machine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class MachineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la machine *',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: Tracteur Fendt 300',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le nom de la machine est obligatoire'
                    ]),
                    new Length([
                        'min' => 3,
                        'max' => 100,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom doit contenir au maximum {{ limit }} caractères',
                    ])
                ]
            ])
            ->add('marque', TextType::class, [
                'label' => 'Marque *',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: Fendt, John Deere, New Holland',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'La marque est obligatoire'
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'La marque doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'La marque doit contenir au maximum {{ limit }} caractères',
                    ])
                ]
            ])
            ->add('modele', TextType::class, [
                'label' => 'Modèle',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: 300 Vario',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'Le modèle doit contenir au maximum {{ limit }} caractères',
                    ])
                ]
            ])
            ->add('kilometrage', NumberType::class, [
                'label' => 'Kilométrage (km)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: 15000',
                    'class' => 'form-control',
                    'step' => '1',
                    'min' => '0'
                ],
                'constraints' => [
                    new PositiveOrZero([
                        'message' => 'Le kilométrage doit être un nombre positif ou zéro'
                    ]),
                    new Range([
                        'min' => 0,
                        'max' => 1000000,
                        'notInRangeMessage' => 'Le kilométrage doit être compris entre {{ min }} et {{ max }} km',
                    ])
                ]
            ])
            ->add('dateAchat', DateType::class, [
                'label' => 'Date d\'achat',
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'form-control js-datepicker',
                    'placeholder' => 'Sélectionnez une date'
                ],
                'constraints' => [
                    new LessThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date d\'achat ne peut pas être dans le futur'
                    ])
                ]
            ])
            ->add('prochaineMaintenance', DateType::class, [
                'label' => 'Prochaine maintenance',
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'form-control js-datepicker',
                    'placeholder' => 'Sélectionnez une date'
                ]
            ])
            ->add('etatM', ChoiceType::class, [
                'label' => 'État de la machine *',
                'choices' => [
                    'Neuf' => 'Neuf',
                    'Bon' => 'Bon',
                    'Occasion' => 'Occasion',
                    'En panne' => 'En panne',
                ],
                'placeholder' => 'Sélectionnez un état',
                'required' => true,
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'L\'état de la machine est obligatoire'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Machine::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'machine_item',
        ]);
    }
}