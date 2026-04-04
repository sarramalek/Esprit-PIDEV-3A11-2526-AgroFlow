<?php

namespace App\Form;

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
use Symfony\Component\Validator\Constraints\NotBlank;

class TacheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomTache', TextType::class, [
                'label'       => false,
                'attr'        => ['placeholder' => 'Nom de la tâche'],
                'constraints' => [new NotBlank(['message' => 'Veuillez entrer un nom.'])],
            ])
            ->add('description', TextareaType::class, [
                'label'    => false,
                'required' => false,
                'attr'     => ['placeholder' => 'Description', 'rows' => 4],
            ])
            ->add('assignee', EntityType::class, [
                'label'        => false,
                'class'        => User::class,
                'choice_label' => fn(User $u) => $u->getNom() . ' ' . $u->getPrenom(),
                'placeholder'  => '-- Choisir un ouvrier --',
            ])
            ->add('etat', ChoiceType::class, [
                'label'       => false,
                'placeholder' => '-- Choisir un état --',
                'choices'     => [
                    'À faire'    => 'à faire',
                    'En cours'   => 'en cours',
                    'Terminée'   => 'terminée',
                    'Annulée'    => 'annulée',
                ],
                'constraints' => [new NotBlank(['message' => 'Veuillez choisir un état.'])],
            ])
            ->add('priorite', ChoiceType::class, [
                'label'       => false,
                'placeholder' => '-- Choisir une priorité --',
                'choices'     => [
                    'Haute'   => 'haute',
                    'Moyenne' => 'moyenne',
                    'Basse'   => 'basse',
                ],
                'constraints' => [new NotBlank(['message' => 'Veuillez choisir une priorité.'])],
            ])
            ->add('dateEcheancee', DateType::class, [
                'label'    => false,
                'widget'   => 'single_text',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tache::class,
        ]);
    }
}