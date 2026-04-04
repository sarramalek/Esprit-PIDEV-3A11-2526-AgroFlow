<?php

namespace App\Form\stocks;

use App\Entity\stocks\Article;
use App\Entity\stocks\Categorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du produit',
                'attr' => ['placeholder' => 'Ex: Engrais Azoté']
            ])
            ->add('quantite_en_stock', NumberType::class, [
                'label' => 'Quantité en stock',
                'attr' => ['step' => '0.1']
            ])
            ->add('seuil_alerte', NumberType::class, [
                'label' => 'Seuil d\'alerte',
            ])
            ->add('unite_mesure', TextType::class, [
                'label' => 'Unité (Kg, Litre, Sac...)',
                'attr' => ['placeholder' => 'Ex: Kg']
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'label' => 'Catégorie',
                'placeholder' => 'Sélectionnez une catégorie',
                'required' => false, // car ton entité autorise le nullable: true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
