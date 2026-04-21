<?php

namespace App\Form\Animals;

use App\Entity\Animals\Animaux;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnimauxType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'animal',
                'attr' => ['placeholder' => 'Entrez le nom...']
            ])
            ->add('espece', ChoiceType::class, [
                'choices' => [
                    'Chien' => 'Chien',
                    'Chat' => 'Chat',
                    'Vache' => 'Vache',
                    'Chèvre' => 'Chèvre',
                    'Mouton' => 'Mouton',
                    'Cheval' => 'Cheval'
                ],
                'label' => 'Espèce'
            ])
            ->add('date_naissance', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de Naissance'
            ])
            ->add('sexe', ChoiceType::class, [
                'choices' => [
                    'Mâle' => 'MALE',
                    'Femelle' => 'FEMELLE'
                ],
                'label' => 'Sexe'
            ])
            ->add('poids', NumberType::class, [
                'label' => 'Poids (kg)',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Animaux::class,
        ]);
    }
}
