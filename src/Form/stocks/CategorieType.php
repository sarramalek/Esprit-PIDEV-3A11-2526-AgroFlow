<?php

namespace App\Form\stocks;

use App\Entity\stocks\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Categorie>
 */
class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la catégorie',
                'attr' => [
                    'placeholder' => 'Ex: Engrais, Semences, Outillage...',
                    'class' => 'form-control-modern',
                    'style' => 'width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #ddd; margin-top: 5px;'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description (Optionnel)',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Décrivez brièvement le type de produits dans cette catégorie...',
                    'style' => 'width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #ddd; margin-top: 5px;'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categorie::class,
        ]);
    }
}
