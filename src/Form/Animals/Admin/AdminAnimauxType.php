<?php

namespace App\Form\Animals\Admin;

use App\Entity\Animals\Animaux;
use App\Entity\User\User;
use App\Repository\User\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminAnimauxType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => "Nom de l'animal",
                'attr'  => ['placeholder' => 'Entrez le nom...'],
            ])
            ->add('espece', ChoiceType::class, [
                'choices' => [
                    'Chien'  => 'Chien',
                    'Chat'   => 'Chat',
                    'Vache'  => 'Vache',
                    'Chèvre' => 'Chèvre',
                    'Mouton' => 'Mouton',
                    'Cheval' => 'Cheval',
                ],
                'label' => 'Espèce',
            ])
            ->add('date_naissance', DateType::class, [
                'widget' => 'single_text',
                'label'  => 'Date de Naissance',
            ])
            ->add('sexe', ChoiceType::class, [
                'choices' => [
                    'Mâle'   => 'MALE',
                    'Femelle' => 'FEMELLE',
                ],
                'label' => 'Sexe',
            ])
            ->add('poids', NumberType::class, [
                'label'    => 'Poids (kg)',
                'required' => false,
            ])
            ->add('user', EntityType::class, [
                'class'         => User::class,
                'query_builder' => function (UserRepository $ur) {
                    return $ur->createQueryBuilder('u')
                        ->where('u.role = :role')
                        ->setParameter('role', 2) // role 2 = Agriculteur
                        ->orderBy('u.nom', 'ASC');
                },
                'choice_label' => function (User $u) {
                    return $u->getPrenom() . ' ' . $u->getNom() . ' (' . $u->getEmail() . ')';
                },
                'label'       => 'Propriétaire (Agriculteur)',
                'placeholder' => '-- Sélectionner un agriculteur --',
                'required'    => false,
                'attr'        => ['class' => 'form-select'],
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
