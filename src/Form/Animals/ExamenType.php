<?php

namespace App\Form\Animals;

use App\Entity\Animals\Animaux;
use App\Entity\Animals\Examen;
use App\Repository\Animals\AnimauxRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExamenType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];

        $builder
            ->add('date_examen', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de l\'examen',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('type_examen', ChoiceType::class, [
                'choices' => [
                    'Vaccin' => 'Vaccin',
                    'Radio' => 'Radio',
                    'Scanner' => 'Scanner',
                    'Consultation' => 'Consultation',
                ],
                'label' => 'Type d\'examen',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('diagnostic', ChoiceType::class, [
                'choices' => [
                    'En bonne santé' => 'En bonne santé',
                    'Infection' => 'Infection',
                    'Fracture' => 'Fracture',
                    'Urgence' => 'Urgence',
                ],
                'label' => 'Diagnostic',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('traitement', ChoiceType::class, [
                'choices' => [
                    'Repos' => 'Repos',
                    'Antibiotiques' => 'Antibiotiques',
                    'Observation' => 'Observation',
                    'Chirurgie' => 'Chirurgie',
                ],
                'label' => 'Traitement',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('animal', EntityType::class, [
                'class' => Animaux::class,
                'query_builder' => function (AnimauxRepository $er) use ($user) {
                    $qb = $er->createQueryBuilder('a')->orderBy('a.nom', 'ASC');
                    // Si user est défini (agriculteur), on filtre ; sinon (admin) on voit tout
                    if ($user !== null) {
                        $qb->where('a.user = :user')->setParameter('user', $user);
                    }
                    return $qb;
                },
                'choice_label' => function (Animaux $a) {
                    return $a->getNom() . ' (' . $a->getEspece() . ')';
                },
                'label' => 'Animal concerné',
                'attr'  => ['class' => 'form-select'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Examen::class,
            'user' => null,
        ]);
    }
}