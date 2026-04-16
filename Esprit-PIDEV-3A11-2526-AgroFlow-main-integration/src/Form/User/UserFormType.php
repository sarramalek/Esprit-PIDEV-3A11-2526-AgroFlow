<?php

namespace App\Form\User;

use App\Entity\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $builder
            ->add('cin', TextType::class, [
                'label' => 'CIN',
                'attr'  => ['placeholder' => '8 chiffres'],
                'constraints' => [
                    new NotBlank(message: 'Le CIN est obligatoire.'),
                    new Length(min: 8, max: 8, exactMessage: 'Le CIN doit contenir exactement 8 chiffres.'),
                ],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr'  => ['placeholder' => 'Nom de famille'],
                'constraints' => [new NotBlank(message: 'Le nom est obligatoire.')],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr'  => ['placeholder' => 'Prénom'],
                'constraints' => [new NotBlank(message: 'Le prénom est obligatoire.')],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr'  => ['placeholder' => 'exemple@email.com'],
                'constraints' => [
                    new NotBlank(message: "L'email est obligatoire."),
                    new Email(message: "L'email n'est pas valide."),
                ],
            ])
            ->add('tel', TextType::class, [
                'label'    => 'Téléphone',
                'required' => false,
                'attr'     => ['placeholder' => '8 chiffres'],
            ])
            ->add('adresse', TextType::class, [
                'label'    => 'Adresse',
                'required' => false,
                'attr'     => ['placeholder' => 'Adresse complète'],
            ])
            ->add('ville', TextType::class, [
                'label'    => 'Ville',
                'required' => false,
                'attr'     => ['placeholder' => 'Ville'],
            ])
            ->add('dateNaiss', BirthdayType::class, [
                'label'    => 'Date de naissance',
                'required' => false,
                'widget'   => 'single_text',
            ])
            ->add('role', ChoiceType::class, [
                'label'   => 'Rôle',
                'choices' => [
                    'Ouvrier'        => 1,
                    'Agriculteur'    => 2,
                    'Administrateur' => 3,
                ],
                'constraints' => [new NotBlank(message: 'Le rôle est obligatoire.')],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type'           => PasswordType::class,
                'mapped'         => false,
                'required'       => !$isEdit,
                'first_options'  => [
                    'label' => 'Mot de passe',
                    'attr'  => ['placeholder' => $isEdit ? 'Laisser vide pour ne pas changer' : 'Mot de passe'],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr'  => ['placeholder' => 'Confirmer'],
                ],
                'constraints' => $isEdit ? [] : [
                    new NotBlank(message: 'Le mot de passe est obligatoire.'),
                    new Length(min: 6, minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit'    => false,
        ]);
    }
}
