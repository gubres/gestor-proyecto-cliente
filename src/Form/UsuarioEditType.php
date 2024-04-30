<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Usuarios;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UsuarioEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control', // Añade aquí las clases que necesitas
                    'placeholder' => 'Introduce tu nombre'
                ]
            ])
            ->add('apellidos', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control', // Añade aquí las clases que necesitas
                    'placeholder' => 'Introduce tus apellidos'
                ]
            ])
            ->add('email', EmailType::class, [
                'disabled' => true, // Hace que el campo de email sea no editable
                'attr' => [
                    'class' => 'form-control mb-3', // Una clase para campos deshabilitados
                    'readonly' => true
                ]
            ])
            ->add('newPassword', PasswordType::class, [
                'mapped' => false,
                'required' => false, // opcional
                'label' => 'Nueva contraseña (opcional)',
                'attr' => [
                    'class' => 'form-control mb-3',
                    'autocomplete' => 'new-password'
                ]
            ]);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Usuarios::class,
        ]);
    }
}
