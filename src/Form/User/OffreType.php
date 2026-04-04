<?php

namespace App\Form\User;

use App\Entity\User\Offre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class OffreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomOffre', TextType::class, [
                'label'       => false,
                'attr'        => ['placeholder' => "Nom de l'offre"],
                'constraints' => [new NotBlank(['message' => 'Veuillez entrer un nom.'])],
            ])
            ->add('description', TextareaType::class, [
                'label'    => false,
                'required' => false,
                'attr'     => ['placeholder' => 'Description de l\'offre', 'rows' => 4],
            ])
            ->add('prix', MoneyType::class, [
                'label'       => false,
                'currency'    => 'TND',
                'attr'        => ['placeholder' => 'Prix en TND'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un prix.']),
                    new Positive(['message' => 'Le prix doit être positif.']),
                ],
            ])
            ->add('dureeOffre', IntegerType::class, [
                'label'       => false,
                'attr'        => ['placeholder' => 'Durée en jours'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer une durée.']),
                    new Positive(['message' => 'La durée doit être positive.']),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Offre::class,
        ]);
    }
}