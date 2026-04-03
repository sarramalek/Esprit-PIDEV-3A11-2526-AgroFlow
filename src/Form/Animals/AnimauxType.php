<?php

namespace App\Form\Animals;

use App\Entity\Animals\Animaux;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
class AnimauxType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('espece', ChoiceType::class, [
            'choices'  => [
                'Chien' => 'Chien',
                'Chat' => 'Chat',
                'Vache' => 'Vache',
                'Chèvre' => 'Chèvre',
                'Mouton' => 'Mouton',
                'Cheval' => 'Cheval',
            ],
            'placeholder' => 'Sélectionnez l\'espèce',])
            
            ->add('date_naissance')
           ->add('sexe', ChoiceType::class, [
            'choices'  => [
                'MALE' => 'MALE',
                'FEMELLE' => 'FEMELLE',
            ],
        ])
            ->add('poids')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Animaux::class,
        ]);
    }
}
