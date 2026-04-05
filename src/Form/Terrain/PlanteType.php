<?php
namespace App\Form\Terrain;

use App\Entity\Terrain\Plante;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Range;

class PlanteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomP', TextType::class, [
                'label' => 'Nom de la plante',
                'attr'  => ['class' => 'form-input', 'placeholder' => 'ex: Blé dur'],
                'constraints' => [
                    new NotBlank(message: 'Le nom de la plante est obligatoire.'),
                    new Length(
                        min: 2, max: 100,
                        minMessage: 'Minimum 2 caractères.',
                        maxMessage: 'Maximum 100 caractères.'
                    ),
                ],
            ])
            ->add('variete', TextType::class, [
                'label'    => 'Variété',
                'required' => false,
                'attr'     => ['class' => 'form-input', 'placeholder' => 'ex: cerise'],
                'constraints' => [
                    new Length(max: 100, maxMessage: 'Maximum 100 caractères.'),
                ],
            ])
            ->add('besoinEau', NumberType::class, [
                'label'    => 'Besoin en eau (L/j)',
                'required' => true,
                'attr'     => ['class' => 'form-input', 'step' => '0.1', 'placeholder' => 'ex: 5.5'],
                'constraints' => [
                    new NotBlank(message: 'Le besoin en eau est obligatoire.'),
                    new Range(
                        min: 0, max: 10000,
                        notInRangeMessage: 'Le besoin en eau doit être entre {{ min }} et {{ max }} L/j.'
                    ),
                ],
            ])
            ->add('cycleJours', IntegerType::class, [
                'label'    => 'Cycle (jours)',
                'required' => true,
                'attr'     => ['class' => 'form-input', 'placeholder' => 'ex: 150'],
                'constraints' => [
                    new NotBlank(message: 'Le cycle en jours est obligatoire.'),
                    new Positive(message: 'Le cycle doit être un nombre positif.'),
                    new Range(
                        min: 1, max: 3650,
                        notInRangeMessage: 'Le cycle doit être entre {{ min }} et {{ max }} jours.'
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Plante::class]);
    }
}