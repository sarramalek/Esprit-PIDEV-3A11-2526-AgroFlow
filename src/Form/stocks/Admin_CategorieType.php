<?php

namespace App\Form\stocks;

use App\Entity\stocks\Categorie;
use App\Entity\User\User;
use App\Repository\User\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategorieAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la catégorie',
                'attr' => ['placeholder' => 'Ex: Engrais, Semences...']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 3]
            ])
            ->add('agriculteur', EntityType::class, [
                'class' => User::class,
                'label' => 'Agriculteur propriétaire',
                'placeholder' => '--- Sélectionner l\'agriculteur (CIN) ---',
                'choice_label' => function (User $user) {
                    // On utilise la même logique d'affichage que pour l'Article
                    return $user->getCin() . ' - ' . $user->getNom() . ' ' . $user->getPrenom();
                },
                'query_builder' => function (UserRepository $er) {
                    // Correction ici : on utilise 'role' au singulier comme dans ton exemple
                    return $er->createQueryBuilder('u')
                        ->where('u.role = :r')
                        ->setParameter('r', 2)
                        ->orderBy('u.nom', 'ASC');
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categorie::class,
        ]);
    }
}
