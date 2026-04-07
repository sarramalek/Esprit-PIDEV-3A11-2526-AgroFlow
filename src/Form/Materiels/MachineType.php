<?php

namespace App\Form\Materiels;

use App\Entity\Materiels\Machine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class MachineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la machine',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom de la machine est obligatoire']),
                    new Assert\Length([
                        'min'        => 3,
                        'max'        => 100,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ0-9\s\-]+$/',
                        'message' => 'Le nom ne peut contenir que des lettres, chiffres, espaces et tirets',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Ex: John Deere 5075E',
                    'minlength'   => 3,
                    'maxlength'   => 100,
                ],
            ])

            ->add('marque', TextType::class, [
                'label' => 'Marque',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La marque est obligatoire']),
                    new Assert\Length([
                        'min'        => 2,
                        'max'        => 50,
                        'minMessage' => 'La marque doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'La marque ne peut pas dépasser {{ limit }} caractères',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/',
                        'message' => 'La marque ne peut contenir que des lettres, espaces et tirets',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'John Deere, Massey Ferguson...',
                    'minlength'   => 2,
                    'maxlength'   => 50,
                ],
            ])

            ->add('modele', TextType::class, [
                'label' => 'Modèle',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le modèle est obligatoire']),
                    new Assert\Length([
                        'min'        => 2,
                        'max'        => 50,
                        'minMessage' => 'Le modèle doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le modèle ne peut pas dépasser {{ limit }} caractères',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ0-9\s\-]+$/',
                        'message' => 'Le modèle ne peut contenir que des lettres, chiffres, espaces et tirets',
                    ]),
                ],
                'attr' => [
                    'placeholder' => '5075E, 5713...',
                    'minlength'   => 2,
                    'maxlength'   => 50,
                ],
            ])

            ->add('numeroSerie', TextType::class, [
                'label' => 'Numéro de série',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le numéro de série est obligatoire']),
                    new Assert\Length([
                        'min'        => 5,
                        'max'        => 30,
                        'minMessage' => 'Le numéro de série doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le numéro de série ne peut pas dépasser {{ limit }} caractères',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z0-9\-]+$/',
                        'message' => 'Le numéro de série ne peut contenir que des lettres, chiffres et tirets (sans espaces)',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'JD123456789',
                    'minlength'   => 5,
                    'maxlength'   => 30,
                ],
            ])

            ->add('etatM', ChoiceType::class, [
                'label' => 'État',
                'choices' => [
                    'Neuf'        => 'Neuf',
                    'Bon'         => 'Bon',
                    'En occasion' => 'En occasion',
                    'En panne'    => 'En panne',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez sélectionner l\'état de la machine']),
                    new Assert\Choice([
                        'choices' => ['Neuf', 'Bon', 'En occasion', 'En panne'],
                        'message' => 'Veuillez choisir un état valide',
                    ]),
                ],
                'placeholder' => 'Sélectionnez l\'état',
            ])

            ->add('dateAchat', DateType::class, [
                'label'  => 'Date d\'achat',
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date d\'achat est obligatoire']),
                    new Assert\Type([
                        'type'    => \DateTimeInterface::class,
                        'message' => 'Veuillez entrer une date valide',
                    ]),
                    new Assert\GreaterThanOrEqual([
                        'value'   => '1900-01-01',
                        'message' => 'La date d\'achat ne peut pas être antérieure à 1900',
                    ]),
                    new Assert\LessThanOrEqual([
                        'value'   => 'today',
                        'message' => 'La date d\'achat ne peut pas être dans le futur',
                    ]),
                ],
                'attr' => [
                    'min' => '1900-01-01',
                    'max' => (new \DateTime())->format('Y-m-d'),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Machine::class,
        ]);
    }
}