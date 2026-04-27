<?php

namespace App\Form\User;

use App\Entity\User\Tache;
use App\Entity\User\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;

class TacheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomTache', TextType::class, [
                'label'    => false,
                'required' => false,
                'attr'     => ['placeholder' => 'Nom de la tâche'],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom de la tâche est obligatoire.']),
                    new Length([
                        'min'        => 3,
                        'max'        => 100,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                    new Regex([
                        'pattern' => '/^[\p{L}0-9 \-_.,!?\'\"()]+$/u',
                        'message' => 'Le nom contient des caractères non autorisés.',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label'    => false,
                'required' => false,
                'attr'     => ['placeholder' => 'Description', 'rows' => 4],
                'constraints' => [
                    new NotBlank(['message' => 'La Description de la tâche est obligatoire.']),
                    new Length([
                        'max'        => 1000,
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('assignee', EntityType::class, [
                'label'        => false,
                'required'     => false,
                'class'        => User::class,
                'choice_label' => fn(User $u) => $u->getNom() . ' ' . $u->getPrenom(),
                'placeholder'  => '-- Choisir un ouvrier --',
                'constraints'  => [
                    new NotBlank(['message' => 'L ouvrier a assignée  est obligatoire.']),
                    new NotNull(['message' => 'Veuillez assigner la tâche à un ouvrier.']),
                ],
            ])
            ->add('etat', ChoiceType::class, [
                'label'       => false,
                'required'    => false,
                'placeholder' => '-- Choisir un état --',
                'choices'     => [
                    'À faire'  => 'à faire',
                    'En cours' => 'en cours',
                    'Terminée' => 'terminée',
                    'Annulée'  => 'annulée',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'L etat de la tâche est obligatoire.']),
                    new NotBlank(['message' => 'Veuillez choisir un état.']),
                ],
            ])
            ->add('priorite', ChoiceType::class, [
                'label'       => false,
                'required'    => false,
                'placeholder' => '-- Choisir une priorité --',
                'choices'     => [
                    'Haute'   => 'haute',
                    'Moyenne' => 'moyenne',
                    'Basse'   => 'basse',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'La prioritee de la tâche est obligatoire.']),
                    new NotBlank(['message' => 'Veuillez choisir une priorité.']),
                ],
            ])
            ->add('dateEcheancee', DateType::class, [
                'label'    => false,
                'widget'   => 'single_text',
                'required' => false,
                'html5'    => false,
                'attr'     => ['placeholder' => 'JJ/MM/AAAA'],
                'constraints' => [
                    new NotBlank(['message' => 'La date d echeance de la tâche est obligatoire.']),
                    new GreaterThanOrEqual([
                        'value'   => 'today',
                        'message' => "La date d'échéance doit être aujourd'hui ou dans le futur.",
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'     => Tache::class,
            'error_bubbling' => false,
            'attr'           => ['novalidate' => 'novalidate'],
        ]);
    }
}