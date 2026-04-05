<?php

namespace App\Form\stocks;

use App\Entity\stocks\MouvementStock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MouvementStockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Le type de mouvement : Entrée ou Sortie
            ->add('type', ChoiceType::class, [
                'label' => 'Type de mouvement',
                'choices'  => [
                    '📈 Entrée de stock' => 'ENTREE',
                    '📉 Sortie de stock' => 'SORTIE',
                ],
                'attr' => ['class' => 'form-control']
            ])

            // La quantité (doit être un nombre)
            ->add('quantite', NumberType::class, [
                'label' => 'Quantité',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 10.5'
                ]
            ])

            // Le motif (optionnel)
            ->add('motif', TextType::class, [
                'label' => 'Motif (Raison)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Livraison fournisseur, Perte, Vente...'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // On lie officiellement le formulaire à l'entité
            'data_class' => MouvementStock::class,
        ]);
    }
}
