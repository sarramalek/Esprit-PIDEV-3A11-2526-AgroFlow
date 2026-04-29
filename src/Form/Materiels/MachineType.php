<?php
// src/Form/Materiels/MachineType.php

namespace App\Form\Materiels;

use App\Entity\Materiels\Machine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Choice;  // ← CORRECTION: Choice au lieu de ChoiceConstraint
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class MachineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ── Nom ────────────────────────────────────────────────────────
            ->add('nom', TextType::class, [
                'label' => 'Nom de la machine',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire']),
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Le nom doit faire au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
            ])

            // ── Marque ─────────────────────────────────────────────────────
            ->add('marque', TextType::class, [
                'label' => 'Marque',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'La marque est obligatoire']),
                    new Length([
                        'min' => 2, 
                        'max' => 255,
                        'minMessage' => 'La marque doit faire au moins {{ limit }} caractères',
                        'maxMessage' => 'La marque ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
            ])

            // ── Modèle ─────────────────────────────────────────────────────
            ->add('modele', TextType::class, [
                'label' => 'Modèle',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le modèle est obligatoire']),
                    new Length([
                        'min' => 1, 
                        'max' => 255,
                        'minMessage' => 'Le modèle doit faire au moins {{ limit }} caractère',
                        'maxMessage' => 'Le modèle ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
            ])

            // ── Numéro de série ────────────────────────────────────────────
            ->add('numeroSerie', TextType::class, [
                'label' => 'Numéro de série',
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Le numéro de série ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
            ])

            // ── État ───────────────────────────────────────────────────────
            ->add('etatM', ChoiceType::class, [
                'label' => 'État',
                'required' => true,
                'placeholder' => '-- Sélectionnez un état --',
                'choices' => [
                    'Neuf' => 'Neuf',
                    'Bon' => 'Bon',
                    'Occasion' => 'Occasion',
                    'En panne' => 'En panne',
                ],
                'constraints' => [
                    new NotBlank(['message' => "L'état est obligatoire"]),
                    new Choice([  // ← CORRECTION: Choice au lieu de ChoiceConstraint
                        'choices' => ['Neuf', 'Bon', 'Occasion', 'En panne'],
                        'message' => 'Veuillez choisir un état valide',
                    ]),
                ],
            ])

            // ── Date d'achat ───────────────────────────────────────────────
            ->add('dateAchat', DateType::class, [
                'label' => "Date d'achat",
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
                'constraints' => [
                    new LessThanOrEqual([
                        'value' => 'today',
                        'message' => "La date d'achat ne peut pas être dans le futur",
                    ]),
                ],
            ])

            // ── Kilométrage ────────────────────────────────────────────────
            ->add('kilometrage', IntegerType::class, [
                'label' => 'Kilométrage actuel (km)',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le kilométrage est obligatoire']),
                    new PositiveOrZero(['message' => 'Le kilométrage ne peut pas être négatif']),
                ],
            ])

            // ── Date dernière visite ───────────────────────────────────────
            ->add('dateLastVisite', DateType::class, [
                'label' => 'Date de la dernière visite',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
                'constraints' => [
                    new Callback([$this, 'validateDateLastVisite']),
                ],
            ])

            // ── Kilométrage dernière visite ────────────────────────────────
            ->add('kmLastVisite', IntegerType::class, [
                'label' => 'Kilométrage lors de la dernière visite (km)',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le kilométrage de la dernière visite est obligatoire']),
                    new PositiveOrZero(['message' => 'Le kilométrage ne peut pas être négatif']),
                ],
            ])

            // ── Prochaine maintenance ──────────────────────────────────────
            ->add('prochaineMaintenance', DateType::class, [
                'label' => 'Date de prochaine maintenance',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
                'constraints' => [
                    new Callback([$this, 'validateProchaineMaintenance']),
                ],
            ]);
    }

    // Validation personnalisée pour la date de dernière visite
    public function validateDateLastVisite(?\DateTimeInterface $value, ExecutionContextInterface $context): void
    {
        if ($value === null) {
            return;
        }

        $form = $context->getRoot();
        if (!$form instanceof FormInterface) {
            return;
        }
        $dateAchat = $form->get('dateAchat')->getData();

        if ($value > new \DateTime('today')) {
            $context->buildViolation('La date de dernière visite ne peut pas être dans le futur')
                ->addViolation();
        }

        if ($dateAchat instanceof \DateTimeInterface && $value < $dateAchat) {
            $context->buildViolation('La date de dernière visite ne peut pas être antérieure à la date d\'achat')
                ->addViolation();
        }
    }

    // Validation personnalisée pour la prochaine maintenance
    public function validateProchaineMaintenance(?\DateTimeInterface $value, ExecutionContextInterface $context): void
    {
        if ($value === null) {
            return;
        }

        $form = $context->getRoot();
        if (!$form instanceof FormInterface) {
            return;
        }
        $dateLastVisite = $form->get('dateLastVisite')->getData();
        $dateAchat = $form->get('dateAchat')->getData();

        if ($value < new \DateTime('today')) {
            $context->buildViolation('La date de prochaine maintenance ne peut pas être dans le passé')
                ->addViolation();
        }

        if ($dateLastVisite instanceof \DateTimeInterface && $value <= $dateLastVisite) {
            $context->buildViolation('La date de prochaine maintenance doit être postérieure à la dernière visite')
                ->addViolation();
        }

        if ($dateAchat instanceof \DateTimeInterface && $value <= $dateAchat) {
            $context->buildViolation('La date de prochaine maintenance doit être postérieure à la date d\'achat')
                ->addViolation();
        }
    }

    // Validation croisée entre kilométrage et kmLastVisite
    public function validateKmVisite(mixed $value, ExecutionContextInterface $context): void
    {
        $object = $context->getObject();
        
        if (!$object instanceof Machine) {
            return;
        }

        $kilometrage = $object->getKilometrage();
        $kmLastVisite = $object->getKmLastVisite();

        if ($kilometrage !== null && $kmLastVisite !== null && $kmLastVisite > $kilometrage) {
            $context->buildViolation('Le kilométrage de la dernière visite ne peut pas être supérieur au kilométrage actuel')
                ->atPath('kmLastVisite')
                ->addViolation();
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Machine::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'machine_form',
            'constraints' => [
                new Callback([$this, 'validateKmVisite']),
            ],
        ]);
    }
}