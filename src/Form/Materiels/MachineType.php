<?php
// src/Form/Materiels/MachineType.php

namespace App\Form\Materiels;

use App\Entity\Materiels\Machine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class MachineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ─────────────────────────────────────────────────────────────
            // NOM DE LA MACHINE
            // ─────────────────────────────────────────────────────────────
            ->add('nom', TextType::class, [
                'label' => 'Nom de la machine',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: Tracteur Fendt 724',
                    'maxlength' => 100,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(['message' => '❌ Le nom est obligatoire']),
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => '❌ Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => '❌ Le nom ne peut pas dépasser {{ limit }} caractères',
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9\s\-_\'àáâãäåçèéêëìíîïðòóôõöùúûüýÿ]+$/u',
                        'message' => '❌ Le nom contient des caractères non autorisés',
                    ]),
                ],
            ])

            // ─────────────────────────────────────────────────────────────
            // MARQUE
            // ─────────────────────────────────────────────────────────────
            ->add('marque', TextType::class, [
                'label' => 'Marque',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: John Deere, Fendt, Massey Ferguson...',
                    'maxlength' => 100,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(['message' => '❌ La marque est obligatoire']),
                    new Length([
                        'min' => 2, 
                        'max' => 100,
                        'minMessage' => '❌ La marque doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => '❌ La marque ne peut pas dépasser {{ limit }} caractères',
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9\s\-_\'àáâãäåçèéêëìíîïðòóôõöùúûüýÿ]+$/u',
                        'message' => '❌ La marque contient des caractères non autorisés',
                    ]),
                ],
            ])

            // ─────────────────────────────────────────────────────────────
            // MODÈLE
            // ─────────────────────────────────────────────────────────────
            ->add('modele', TextType::class, [
                'label' => 'Modèle',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: 724, 6155R, 8R...',
                    'maxlength' => 100,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(['message' => '❌ Le modèle est obligatoire']),
                    new Length([
                        'min' => 1, 
                        'max' => 100,
                        'minMessage' => '❌ Le modèle doit contenir au moins {{ limit }} caractère',
                        'maxMessage' => '❌ Le modèle ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
            ])

            // ─────────────────────────────────────────────────────────────
            // NUMÉRO DE SÉRIE
            // ─────────────────────────────────────────────────────────────
            ->add('numeroSerie', TextType::class, [
                'label' => 'Numéro de série',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Optionnel - Ex: SN-2024-001',
                    'maxlength' => 100,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Length([
                        'max' => 100,
                        'maxMessage' => '❌ Le numéro de série ne peut pas dépasser {{ limit }} caractères',
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9\-_\s]*$/',
                        'message' => '❌ Le numéro de série ne peut contenir que des lettres, chiffres, tirets et underscores',
                    ]),
                ],
            ])

            // ─────────────────────────────────────────────────────────────
            // ÉTAT
            // ─────────────────────────────────────────────────────────────
            ->add('etatM', ChoiceType::class, [
                'label' => 'État',
                'required' => true,
                'placeholder' => '-- Sélectionnez un état --',
                'choices' => [
                    '🌟 Neuf' => 'Neuf',
                    '👍 Bon' => 'Bon',
                    '🔄 Occasion' => 'Occasion',
                    '⚠️ En panne' => 'En panne',
                    '🔧 En maintenance' => 'En maintenance',
                    '❌ Hors service' => 'Hors service',
                ],
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => '❌ L\'état est obligatoire']),
                    new Choice([
                        'choices' => ['Neuf', 'Bon', 'Occasion', 'En panne', 'En maintenance', 'Hors service'],
                        'message' => '❌ Veuillez choisir un état valide',
                    ]),
                ],
            ])

            // ─────────────────────────────────────────────────────────────
            // DATE D'ACHAT
            // ─────────────────────────────────────────────────────────────
            ->add('dateAchat', DateType::class, [
                'label' => 'Date d\'achat',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                    'max' => date('Y-m-d'),
                ],
                'constraints' => [
                    new LessThanOrEqual([
                        'value' => 'today',
                        'message' => '❌ La date d\'achat ne peut pas être dans le futur',
                    ]),
                    new Callback([$this, 'validateDateAchat']),
                ],
            ])

            // ─────────────────────────────────────────────────────────────
            // KILOMÉTRAGE
            // ─────────────────────────────────────────────────────────────
            ->add('kilometrage', IntegerType::class, [
                'label' => 'Kilométrage actuel (km)',
                'required' => true,
                'attr' => [
                    'placeholder' => '0',
                    'min' => 0,
                    'max' => 9999999,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(['message' => '❌ Le kilométrage est obligatoire']),
                    new PositiveOrZero(['message' => '❌ Le kilométrage ne peut pas être négatif']),
                    new Range([
                        'min' => 0,
                        'max' => 9999999,
                        'minMessage' => '❌ Le kilométrage ne peut pas être négatif',
                        'maxMessage' => '❌ Le kilométrage ne peut pas dépasser {{ limit }} km',
                    ]),
                    new Type([
                        'type' => 'integer',
                        'message' => '❌ Le kilométrage doit être un nombre entier',
                    ]),
                ],
            ])

            // ─────────────────────────────────────────────────────────────
            // DATE DERNIÈRE VISITE
            // ─────────────────────────────────────────────────────────────
            ->add('dateLastVisite', DateType::class, [
                'label' => 'Date de la dernière visite',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                    'max' => date('Y-m-d'),
                ],
                'constraints' => [
                    new Callback([$this, 'validateDateLastVisite']),
                ],
            ])

            // ─────────────────────────────────────────────────────────────
            // KILOMÉTRAGE DERNIÈRE VISITE
            // ─────────────────────────────────────────────────────────────
            ->add('kmLastVisite', IntegerType::class, [
                'label' => 'Kilométrage lors de la dernière visite (km)',
                'required' => true,
                'attr' => [
                    'placeholder' => '0',
                    'min' => 0,
                    'max' => 9999999,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(['message' => '❌ Le kilométrage de la dernière visite est obligatoire']),
                    new PositiveOrZero(['message' => '❌ Le kilométrage ne peut pas être négatif']),
                    new Range([
                        'min' => 0,
                        'max' => 9999999,
                        'minMessage' => '❌ Le kilométrage ne peut pas être négatif',
                        'maxMessage' => '❌ Le kilométrage ne peut pas dépasser {{ limit }} km',
                    ]),
                    new Type([
                        'type' => 'integer',
                        'message' => '❌ Le kilométrage doit être un nombre entier',
                    ]),
                ],
            ])

            // ─────────────────────────────────────────────────────────────
            // PROCHAINE MAINTENANCE
            // ─────────────────────────────────────────────────────────────
            ->add('prochaineMaintenance', DateType::class, [
                'label' => 'Date de prochaine maintenance',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => date('Y-m-d'),
                ],
                'constraints' => [
                    new Callback([$this, 'validateProchaineMaintenance']),
                ],
            ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VALIDATIONS PERSONNALISÉES POUR LA DATE D'ACHAT
    // ─────────────────────────────────────────────────────────────────────────
    public function validateDateAchat(?\DateTimeInterface $value, ExecutionContextInterface $context): void
    {
        if ($value === null) {
            return;
        }

        $today = new \DateTime();
        $minDate = (new \DateTime())->modify('-100 years');

        if ($value > $today) {
            $context->buildViolation('❌ La date d\'achat ne peut pas être dans le futur')
                ->addViolation();
        }

        if ($value < $minDate) {
            $context->buildViolation('❌ La date d\'achat semble trop ancienne (plus de 100 ans)')
                ->addViolation();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VALIDATIONS PERSONNALISÉES POUR LA DATE DE DERNIÈRE VISITE
    // ─────────────────────────────────────────────────────────────────────────
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
        $today = new \DateTime('today');

        // Vérification 1: Pas dans le futur
        if ($value > $today) {
            $context->buildViolation('❌ La date de dernière visite ne peut pas être dans le futur')
                ->addViolation();
        }

        // Vérification 2: Ne peut pas être antérieure à la date d'achat
        if ($dateAchat instanceof \DateTimeInterface && $value < $dateAchat) {
            $context->buildViolation('❌ La date de dernière visite ne peut pas être antérieure à la date d\'achat')
                ->addViolation();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VALIDATIONS PERSONNALISÉES POUR LA PROCHAINE MAINTENANCE
    // ─────────────────────────────────────────────────────────────────────────
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
        $today = new \DateTime('today');

        // Vérification 1: Ne peut pas être dans le passé
        if ($value < $today) {
            $context->buildViolation('❌ La date de prochaine maintenance ne peut pas être dans le passé')
                ->addViolation();
        }

        // Vérification 2: Doit être postérieure à la dernière visite
        if ($dateLastVisite instanceof \DateTimeInterface && $value <= $dateLastVisite) {
            $context->buildViolation('❌ La date de prochaine maintenance doit être postérieure à la dernière visite')
                ->addViolation();
        }

        // Vérification 3: Doit être postérieure à la date d'achat
        if ($dateAchat instanceof \DateTimeInterface && $value <= $dateAchat) {
            $context->buildViolation('❌ La date de prochaine maintenance doit être postérieure à la date d\'achat')
                ->addViolation();
        }

        // Vérification 4: Alerte si trop éloignée (plus de 10 ans)
        $maxDate = (new \DateTime())->modify('+10 years');
        if ($value > $maxDate) {
            $context->buildViolation('⚠️ Attention: La date de prochaine maintenance est très éloignée (plus de 10 ans)')
                ->addViolation();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VALIDATION CROISÉE : KILOMÉTRAGE vs KILOMÉTRAGE DERNIÈRE VISITE
    // ─────────────────────────────────────────────────────────────────────────
    public function validateKmVisite(mixed $value, ExecutionContextInterface $context): void
    {
        $object = $context->getObject();
        
        if (!$object instanceof Machine) {
            return;
        }

        $kilometrage = $object->getKilometrage();
        $kmLastVisite = $object->getKmLastVisite();

        // Vérification 1: kmLastVisite ne peut pas dépasser kilométrage
        if ($kilometrage !== null && $kmLastVisite !== null && $kmLastVisite > $kilometrage) {
            $context->buildViolation('❌ Le kilométrage de la dernière visite ne peut pas être supérieur au kilométrage actuel')
                ->atPath('kmLastVisite')
                ->addViolation();
        }

        // Vérification 2: Alerte si plus de 50 000 km depuis dernière visite
        if ($kilometrage !== null && $kmLastVisite !== null && ($kilometrage - $kmLastVisite) > 50000) {
            $context->buildViolation('⚠️ Attention: Plus de 50 000 km depuis la dernière visite')
                ->atPath('kmLastVisite')
                ->addViolation();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VALIDATION DE COHÉRENCE DES DATES
    // ─────────────────────────────────────────────────────────────────────────
    public function validateDateConsistency(mixed $value, ExecutionContextInterface $context): void
    {
        $object = $context->getObject();
        
        if (!$object instanceof Machine) {
            return;
        }

        $dateAchat = $object->getDateAchat();
        $dateLastVisite = $object->getDateLastVisite();
        $prochaineMaintenance = $object->getProchaineMaintenance();

        // Vérification de la cohérence des dates
        if ($dateAchat && $dateLastVisite && $dateLastVisite < $dateAchat) {
            $context->buildViolation('❌ La date de dernière visite ne peut pas être antérieure à la date d\'achat')
                ->atPath('dateLastVisite')
                ->addViolation();
        }

        if ($dateLastVisite && $prochaineMaintenance && $prochaineMaintenance <= $dateLastVisite) {
            $context->buildViolation('❌ La date de prochaine maintenance doit être postérieure à la date de dernière visite')
                ->atPath('prochaineMaintenance')
                ->addViolation();
        }

        if ($dateAchat && $prochaineMaintenance && $prochaineMaintenance <= $dateAchat) {
            $context->buildViolation('❌ La date de prochaine maintenance doit être postérieure à la date d\'achat')
                ->atPath('prochaineMaintenance')
                ->addViolation();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VALIDATION DE L'EXISTENCE DE L'AGRICULTEUR
    // À APPELER DANS LE CONTRÔLEUR
    // ─────────────────────────────────────────────────────────────────────────
    // Cette validation doit être faite dans le contrôleur car elle nécessite
    // l'accès à la base de données pour vérifier l'existence de l'agriculteur

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Machine::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'machine_form',
            'constraints' => [
                new Callback([$this, 'validateKmVisite']),
                new Callback([$this, 'validateDateConsistency']),
            ],
            'validation_groups' => ['Default'],
        ]);
    }
}