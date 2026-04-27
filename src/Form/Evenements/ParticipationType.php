<?php

namespace App\Form\Evenements;

use App\Entity\Evenements\Evenement;
use App\Entity\Evenements\Participation;
use App\Entity\User\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Participation>
 */
class ParticipationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('evenement', EntityType::class, [
                'label' => 'Événement *',
                'class' => Evenement::class,
                'choice_label' => 'titre',
                'placeholder' => 'Sélectionnez un événement...',
                'attr' => ['class' => 'form-select'],
                'label_attr' => ['class' => 'form-label fw-bold mt-3'],
            ])
            ->add('utilisateur', EntityType::class, [
                'label' => 'Utilisateur *',
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return sprintf('%s %s (CIN: %d)', $user->getPrenom(), $user->getNom(), $user->getCin());
                },
                'placeholder' => 'Sélectionnez un utilisateur...',
                'attr' => ['class' => 'form-select'],
                'label_attr' => ['class' => 'form-label fw-bold mt-3'],
            ])
            ->add('dateInscription', DateType::class, [
                'label' => 'Date d’inscription *',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label fw-bold mt-3'],
            ])
            ->add('statutParticipation', ChoiceType::class, [
                'label' => 'Statut *',
                'choices' => [
                    'Inscrit' => 'Inscrit',
                    'Confirmé' => 'Confirmé',
                    'Annulé' => 'Annulé',
                ],
                'placeholder' => 'Sélectionnez un statut...',
                'attr' => ['class' => 'form-select'],
                'label_attr' => ['class' => 'form-label fw-bold mt-3'],
            ])
            ->add('presence', ChoiceType::class, [
                'label' => 'Présence *',
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'placeholder' => 'Choisir...',
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
            'data_class' => Participation::class,
        ]);
    }
}