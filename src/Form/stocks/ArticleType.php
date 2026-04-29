<?php

namespace App\Form\stocks;

use App\Entity\stocks\Article;
use App\Entity\stocks\Categorie;
use App\Entity\User\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Repository\stocks\CategorieRepository;

/**
 * @extends AbstractType<Article>
 */
class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // On récupère l'agriculteur passé depuis le contrôleur
        $agriculteur = $options['agriculteur'];

        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du produit',
                'attr' => ['placeholder' => 'Ex: Engrais Azoté']
            ])
            ->add('prix_unitaire', NumberType::class, [
                'label' => 'Prix Unitaire (TND)',
                'attr' => ['placeholder' => 'Prix final en Dinars', 'step' => '0.001'],
                'required' => false,
            ])
            ->add('devise', ChoiceType::class, [
                'label' => 'Devise d\'achat',
                'choices' => [
                    'Dinar Tunisien (TND)' => 'TND',
                    'Euro (EUR)' => 'EUR',
                    'Dollar US (USD)' => 'USD',
                    'Livre Sterling (GBP)' => 'GBP',
                    'Dinar Algérien (DZD)' => 'DZD',
                    'Dirham Marocain (MAD)' => 'MAD',
                ],
                'preferred_choices' => ['TND'],
            ])
            ->add('prix_achat_devise', NumberType::class, [
                'label' => 'Prix d\'achat (Devise etrangere)',
                'attr' => ['placeholder' => 'Saisir le prix original'],
                'required' => false,
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
                'label' => 'Ma Categorie',
                'placeholder' => 'Sélectionnez une catégorie',
                'required' => true,
                // --- FILTRE DES CATÉGORIES ICI ---
                'query_builder' => function (CategorieRepository $repo) use ($agriculteur) {
                    return $repo->createQueryBuilder('c')
                        ->where('c.agriculteur = :user') // On filtre par l'agriculteur
                        ->setParameter('user', $agriculteur)
                        ->orderBy('c.nom', 'ASC');
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
            'agriculteur' => null, // On définit l'option par défaut
        ]);

        // On force à ce que l'agriculteur soit passé
        $resolver->setRequired('agriculteur');
    }
}
