<?php

namespace App\Form;

use App\Entity\User\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('_username', EmailType::class, [
                'label'       => false,
                'mapped'      => false,
                'attr'        => [
                    'placeholder'  => 'Votre email',
                    'autocomplete' => 'email',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre email.',
                    ]),
                    new Email([
                        'message' => 'Email invalide.',
                    ]),
                ],
            ])
            ->add('_password', PasswordType::class, [
                'label'       => false,
                'mapped'      => false,
                'attr'        => [
                    'placeholder'  => 'Votre mot de passe',
                    'autocomplete' => 'current-password',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre mot de passe.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_csrf_token',
            'csrf_token_id'   => 'authenticate',  // doit correspondre à security.yaml
        ]);
    }

    /**
     * Le prefix vide est OBLIGATOIRE pour que Symfony Security
     * puisse lire _username et _password directement dans la requête
     * sans prefix de formulaire (ex: login_form[_username])
     */
    public function getBlockPrefix(): string
    {
        return '';
    }
}
