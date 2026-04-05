<?php
// src/Form/Terrain/AgriTerrainType.php
namespace App\Form\Terrain;

use App\Entity\Terrain\Terrain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\{NotBlank, Length, Positive, LessThan, Range};

class AgriTerrainType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomTerrain', TextType::class, [
                'label' => 'Nom du terrain',
                'attr'  => ['placeholder' => 'Ex: Parcelle Nord'],
                'constraints' => [
                    new NotBlank(message: 'Le nom est obligatoire.'),
                    new Length(min: 2, max: 100),
                ],
            ])
            ->add('surface', NumberType::class, [
                'label' => 'Surface (ha)',
                'attr'  => ['placeholder' => '2.5'],
                'constraints' => [
                    new NotBlank(),
                    new Positive(message: 'La surface doit être positive.'),
                    new LessThan(value: 10000),
                ],
            ])
            ->add('typeSol', ChoiceType::class, [
                'label'       => 'Type de sol',
                'placeholder' => '— Choisir —',
                'choices'     => [
                    'Argileux' => 'Argileux',
                    'Sableux'  => 'Sableux',
                    'Limoneux' => 'Limoneux',
                    'Calcaire' => 'Calcaire',
                    'Humifère' => 'Humifère',
                ],
                'constraints' => [new NotBlank()],
            ])
            ->add('localisation', TextType::class, [
                'label' => 'Localisation',
                'attr'  => ['placeholder' => 'Ex: Nabeul, Béja...'],
                'constraints' => [
                    new NotBlank(),
                    new Length(min: 2, max: 150),
                ],
            ])
            ->add('pH', NumberType::class, [
                'label'    => 'pH du sol',
                'scale'    => 2,
                'attr'     => ['placeholder' => '0 – 14'],
                'constraints' => [
                    new NotBlank(),
                    new Range(min: 0, max: 14,
                        notInRangeMessage: 'Le pH doit être entre {{ min }} et {{ max }}.'
                    ),
                ],
            ]);
        // ← PAS de champ cin : injecté dans le controller
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Terrain::class]);
    }
}