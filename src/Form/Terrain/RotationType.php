<?php
namespace App\Form\Terrain;

use App\Entity\Terrain\Plante;
use App\Entity\Terrain\Rotation;
use App\Entity\Terrain\Terrain;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThan;

class RotationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('terrain', EntityType::class, [
                'class'        => Terrain::class,
                'choice_label' => 'nomTerrain',
                'label'        => 'Terrain',
                'placeholder'  => '— Choisir un terrain —',
                'attr'         => ['class' => 'form-input'],
                'constraints'  => [
                    new NotNull(message: 'Veuillez sélectionner un terrain.'),
                ],
            ])
            ->add('plante', EntityType::class, [
                'class'        => Plante::class,
                'choice_label' => 'nomP',
                'label'        => 'Plante',
                'placeholder'  => '— Choisir une plante —',
                'attr'         => ['class' => 'form-input'],
                'constraints'  => [
                    new NotNull(message: 'Veuillez sélectionner une plante.'),
                ],
            ])
            ->add('dateDebut', DateType::class, [
                'label'       => 'Date de début',
                'widget'      => 'single_text',
                'attr'        => ['class' => 'form-input'],
                'constraints' => [
                    new NotNull(message: 'La date de début est obligatoire.'),
                ],
            ])
            ->add('dateFin', DateType::class, [
                'label'       => 'Date de fin',
                'widget'      => 'single_text',
                'attr'        => ['class' => 'form-input'],
                'constraints' => [
                    new NotNull(message: 'La date de fin est obligatoire.'),
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label'   => 'Statut',
                'attr'    => ['class' => 'form-input'],
                'choices' => [
                    'Actif'   => 1,
                    'Inactif' => 0,
                ],
            ]);

        // Validation croisée : dateFin > dateDebut
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $rotation = $event->getData();
            $form     = $event->getForm();

            if ($rotation->getDateDebut() && $rotation->getDateFin()) {
                if ($rotation->getDateFin() <= $rotation->getDateDebut()) {
                    $form->get('dateFin')->addError(
                        new \Symfony\Component\Form\FormError(
                            'La date de fin doit être après la date de début.'
                        )
                    );
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Rotation::class]);
    }
}