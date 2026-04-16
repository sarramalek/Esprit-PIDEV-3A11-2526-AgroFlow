<?php

namespace App\Form\Evenements;

use App\Entity\Evenements\categorieevenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class categorieevenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomCategorie', TextType::class, [
                'label'      => 'Nom de la catégorie *',
                'attr'       => [
                    'placeholder' => 'Ex: Conférence, Formation, Atelier...',
                    'class'       => 'form-control form-control-lg',
                    'minlength'   => 3,
                    'maxlength'   => 100,
                ],
                'label_attr' => ['class' => 'form-label fw-bold mt-2'],
            ])
            ->add('descriptionCategorie', TextareaType::class, [
                'label'      => 'Description *',
                'attr'       => [
                    'placeholder' => 'Décrivez cette catégorie d\'événement...',
                    'class'       => 'form-control',
                    'rows'        => 5,
                    'minlength'   => 10,
                    'maxlength'   => 500,
                ],
                'label_attr' => ['class' => 'form-label fw-bold mt-3'],
            ])
            ->add('enregistrer', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr'  => [
                    'class' => 'btn btn-success px-5 fw-bold mt-4',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => categorieevenement::class,
        ]);
    }
}