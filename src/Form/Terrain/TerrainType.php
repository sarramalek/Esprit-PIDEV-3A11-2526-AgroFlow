<?php
namespace App\Form\Terrain;

use App\Repository\User\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Terrain\Terrain;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\Range;

class TerrainType extends AbstractType
{
    public function __construct(private UserRepository $userRepository) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $users = $this->userRepository->findAllForSelect();
        $choices = [];
        foreach ($users as $user) {
            $label = $user['nom'] . ' ' . $user['prenom'] . ' — CIN: ' . $user['cin'];
            $choices[$label] = (string) $user['cin'];
        }

        $builder
            ->add('nomTerrain', TextType::class, [
                'label' => 'Nom du terrain',
                'attr'  => ['class' => 'form-input', 'placeholder' => 'Ex: Terrain Nord'],
                'constraints' => [
                    new NotBlank(message: 'Le nom du terrain est obligatoire.'),
                    new Length(
                        min: 2, max: 100,
                        minMessage: 'Minimum 2 caractères.',
                        maxMessage: 'Maximum 100 caractères.'
                    ),
                ],
            ])
            ->add('cin', ChoiceType::class, [
                'label'       => 'Propriétaire',
                'choices'     => $choices,
                'placeholder' => '— Sélectionner un propriétaire —',
                'required'    => true,
                'attr'        => ['class' => 'form-input'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez sélectionner un propriétaire.'),
                ],
            ])
            ->add('surface', NumberType::class, [
                'label' => 'Surface (ha)',
                'attr'  => ['class' => 'form-input', 'placeholder' => 'Ex: 2.5'],
                'constraints' => [
                    new NotBlank(message: 'La surface est obligatoire.'),
                    new Positive(message: 'La surface doit être un nombre positif.'),
                    new LessThan(value: 10000, message: 'La surface ne peut pas dépasser 10 000 ha.'),
                ],
            ])
            ->add('typeSol', ChoiceType::class, [
                'label'       => 'Type de sol',
                'attr'        => ['class' => 'form-input'],
                'placeholder' => '— Choisir un type —',
                'choices'     => [
                    'Argileux' => 'Argileux',
                    'Sableux'  => 'Sableux',
                    'Limoneux' => 'Limoneux',
                    'Calcaire' => 'Calcaire',
                    'Humifère' => 'Humifère',
                ],
                'constraints' => [
                    new NotBlank(message: 'Le type de sol est obligatoire.'),
                ],
            ])
            ->add('localisation', TextType::class, [
                'label' => 'Localisation',
                'attr'  => ['class' => 'form-input', 'placeholder' => 'Ex: Nabeul, Béja...'],
                'constraints' => [
                    new NotBlank(message: 'La localisation est obligatoire.'),
                    new Length(min: 2, max: 150),
                ],
            ])
            ->add('pH', NumberType::class, [
                'label'    => 'pH',
                'required' => true,
                'attr'     => ['class' => 'form-input', 'placeholder' => 'Entre 0 et 14'],
                'scale'    => 2,
                'constraints' => [
                    new NotBlank(message: 'Le pH est obligatoire.'),
                    new Range(
                        min: 0,
                        max: 14,
                        notInRangeMessage: 'Le pH doit être entre {{ min }} et {{ max }}.'
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Terrain::class]);
    }
}