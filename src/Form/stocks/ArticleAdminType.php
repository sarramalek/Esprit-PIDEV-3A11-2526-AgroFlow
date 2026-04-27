<?php

namespace App\Form\stocks;

use App\Entity\stocks\Article;
use App\Entity\stocks\Categorie;
use App\Entity\User\User;
use App\Repository\User\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Article>
 */
class ArticleAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'placeholder' => '--- Selectionner l\'agriculteur (CIN) ---',
                'choice_label' => function (User $user) {
                    return $user->getCin() . ' - ' . $user->getNom() . ' ' . $user->getPrenom();
                },
                'query_builder' => function (UserRepository $er) {
                    return $er->createQueryBuilder('u')->where('u.role = :r')->setParameter('r', 2);
                },
            ])
            ->add('nom', TextType::class)
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'placeholder' => '--- Choisir une categorie ---',
            ])
            ->add('unite_mesure', TextType::class)
            ->add('quantite_en_stock', NumberType::class, ['html5' => true])
            ->add('seuil_alerte', NumberType::class, ['html5' => true])
            ->add('devise', ChoiceType::class, [
                'label' => 'Devise d\'achat',
                'choices' => [
                    'Dinar Tunisien (TND)' => 'TND',
                    'Euro (EUR)' => 'EUR',
                    'Dollar (USD)' => 'USD',
                    'Livre Sterling (GBP)' => 'GBP',
                    'Dinar Algerien (DZD)' => 'DZD',
                    'Dirham Marocain (MAD)' => 'MAD',
                ],
            ])
            ->add('prix_achat_devise', NumberType::class, [
                'label' => 'Prix d\'achat original',
                'required' => false,
                'html5' => true,
                'attr' => ['placeholder' => '0.00']
            ])
            ->add('prix_unitaire', NumberType::class, [
                'label' => 'Prix Unitaire (TND)',
                'html5' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Article::class]);
    }
}
