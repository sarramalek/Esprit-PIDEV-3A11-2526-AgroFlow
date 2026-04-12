<?php

namespace App\Form\stocks;

use App\Entity\stocks\Article;
use App\Entity\stocks\Categorie;
use App\Entity\User\User;
use App\Repository\User\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'placeholder' => '--- Sélectionner l\'agriculteur (CIN) ---',
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
                'placeholder' => '--- Choisir une catégorie ---',
            ])
            ->add('unite_mesure', TextType::class)
            ->add('quantite_en_stock', NumberType::class, ['html5' => true])
            ->add('seuil_alerte', NumberType::class, ['html5' => true])
            ->add('prix_unitaire', NumberType::class, ['html5' => true]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Article::class]);
    }
}
