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
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Range;

class OffreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomOffre', TextType::class, [
                'label'          => false,
                'required'       => false,
                'error_bubbling' => false,
                'attr'           => ['placeholder' => "Nom de l'offre"],
                'constraints'    => [
                    new Length([
                        'min'        => 3,
                        'max'        => 100,
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label'          => false,
                'required'       => false,
                'error_bubbling' => false,
                'attr'           => ['placeholder' => "Description de l'offre", 'rows' => 4],
                'constraints'    => [
                    new Length([
                        'min'        => 3,
                        'max'        => 1000,
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('prix', MoneyType::class, [
                'label'          => false,
                'currency'       => 'TND',
                'required'       => false,
                'error_bubbling' => false,
                'attr'           => ['placeholder' => 'Prix en TND'],
                'constraints'    => [
                    new NotBlank(['message' => 'Veuillez entrer un prix.']),
                    new Positive(['message' => 'Le prix doit être un nombre positif.']),
                    new LessThanOrEqual([
                        'value'   => 99999.99,
                        'message' => 'Le prix ne peut pas dépasser {{ compared_value }} TND.',
                    ]),
                ],
            ])
            ->add('dureeOffre', IntegerType::class, [
                'label'          => false,
                'required'       => false,
                'error_bubbling' => false,
                'attr'           => ['placeholder' => 'Durée en jours'],
                'constraints'    => [
                    new NotBlank(['message' => 'Veuillez entrer une durée.']),
                    new Positive(['message' => 'La durée doit être un nombre positif.']),
                    new Range([
                        'min'               => 1,
                        'max'               => 3650,
                        'notInRangeMessage' => 'La durée doit être comprise entre {{ min }} et {{ max }} jours (10 ans max).',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'     => Offre::class,
            'error_bubbling' => false,
            'attr'           => ['novalidate' => 'novalidate'],
        ]);
    }
}