<?php

namespace App\Form;

use App\Entity\Tareas;
use App\Entity\Usuarios;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nombre', TextType::class, [
            'attr' => [
                'maxlength' => 50,
                'required' => 'required'
            ]
        ])
        ->add('apellidos', TextType::class, [
            'attr' => [
                'maxlength' => 100,
                'required' => 'required'
            ]

        ])
        ->add('email', EmailType::class, [
            'attr' => [
                'maxlength' => 180,
                'required' => 'required'
            ]

        ])
        ->add('plainPassword', PasswordType::class, [
            'mapped' => false,
            'attr' => [
                'autocomplete' => 'new-password',
                'minlength' => 6,
                'maxlength' => 40,
                'required' => 'required'
            ]

        ])
        ->add('agreeTerms', CheckboxType::class, [
            'mapped' => false,
            'required' => true, 
            'label' => 'Acepto los términos y condiciones', 
        ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Usuarios::class,
        ]);
    }
}
