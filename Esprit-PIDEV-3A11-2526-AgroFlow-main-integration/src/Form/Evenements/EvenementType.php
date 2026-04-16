<?php

namespace App\Form\Evenements;

use App\Entity\Evenements\categorieevenement;
use App\Entity\Evenements\Evenement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de l\'événement *',
                'attr' => [
                    'placeholder' => 'Ex: Formation sur l\'irrigation',
                    'class' => 'form-control form-control-lg',
                    'minlength' => 5,
                    'maxlength' => 255,
                ],
                'label_attr' => ['class' => 'form-label fw-bold mt-2'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description *',
                'attr' => [
                    'placeholder' => 'Décrivez l\'événement en détail...',
                    'class' => 'form-control',
                    'rows' => 5,
                    'minlength' => 10,
                ],
                'label_attr' => ['class' => 'form-label fw-bold mt-3'],
            ])
            ->add('typeEvenement', ChoiceType::class, [
                'label' => 'Type d\'événement *',
                'choices' => [
                    'Formation' => 'Formation',
                    'Intervention agricole' => 'Intervention agricole',
                    'Foire' => 'Foire',
                    'Réunion' => 'Réunion',
                    'Alerte saisonnière' => 'Alerte saisonnière',
                ],
                'placeholder' => 'Sélectionnez un type...',
                'attr' => ['class' => 'form-select'],
                'label_attr' => ['class' => 'form-label fw-bold mt-3'],
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date de début *',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label fw-bold mt-3'],
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Date de fin *',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label fw-bold mt-3'],
            ])
            ->add('lieu', TextType::class, [
                'label' => 'Lieu *',
                'attr' => [
                    'placeholder' => 'Ex: Ferme principale, Salle de réunion...',
                    'class' => 'form-control',
                ],
                'label_attr' => ['class' => 'form-label fw-bold mt-3'],
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut *',
                'choices' => [
                    'Planifié' => 'Planifié',
                    'Annulé' => 'Annulé',
                    'Terminé' => 'Terminé',
                ],
                'placeholder' => 'Sélectionnez un statut...',
                'attr' => ['class' => 'form-select'],
                'label_attr' => ['class' => 'form-label fw-bold mt-3'],
            ])
            ->add('categorie', EntityType::class, [
                'label' => 'Catégorie *',
                'class' => categorieevenement::class,
                'choice_label' => 'nomCategorie',
                'placeholder' => 'Sélectionnez une catégorie...',
                'attr' => ['class' => 'form-select'],
                'label_attr' => ['class' => 'form-label fw-bold mt-3'],
            ])
            ->add('enregistrer', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn btn-success px-5 fw-bold mt-4'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}