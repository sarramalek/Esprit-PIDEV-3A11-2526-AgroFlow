<?php

namespace App\Form\stocks;

use App\Entity\stocks\Article;
use App\Entity\stocks\MouvementStock;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MouvementStockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // 1. Sélection de l'article concerné
            ->add('article', EntityType::class, [
                'class' => Article::class,
                'choice_label' => 'nom',
                'label' => 'Article concerné',
                'placeholder' => 'Choisir un article...',
                'attr' => ['class' => 'form-select']
            ])

            // 2. Type de mouvement (Entrée ou Sortie)
            ->add('type', ChoiceType::class, [
                'label' => 'Type de mouvement',
                'choices'  => [
                    '📈 Entrée de stock' => 'ENTREE',
                    '📉 Sortie de stock' => 'SORTIE',
                ],
                'attr' => ['class' => 'form-select']
            ])

            // 3. Quantité
            ->add('quantite', NumberType::class, [
                'label' => 'Quantité mouvementée',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 100'
                ]
            ])

            // 4. Date du mouvement (Crucial pour tes rapports par mois)
            ->add('date_mouvement', DateTimeType::class, [
                'label' => 'Date de l\'opération',
                'widget' => 'single_text',
                'data' => new \DateTime(), // Date actuelle par défaut
                'attr' => ['class' => 'form-control']
            ])

            // 5. Motif / Commentaire
            ->add('motif', TextType::class, [
                'label' => 'Motif ou Référence',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Livraison fournisseur, Vente directe, Perte...'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MouvementStock::class,
        ]);
    }
}
