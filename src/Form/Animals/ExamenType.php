<?php

namespace App\Form\Animals;

use App\Entity\Animals\Examen; // Doit être au singulier
use App\Entity\Animals\Animaux;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExamenType extends AbstractType // Nom de classe sans "s"
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_examen', null, [
                'widget' => 'single_text',
            ])
            ->add('type_examen')
            ->add('diagnostic')
            ->add('traitement')
            ->add('animal', EntityType::class, [
                'class' => Animaux::class,
                'choice_label' => 'nom', // Affiche le nom de l'animal dans la liste déroulante
            ])
        ;
    }

   public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Examen::class, // <-- Remplace le point par ::
        ]);
    }
}