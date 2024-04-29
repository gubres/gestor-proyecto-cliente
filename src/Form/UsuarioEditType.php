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
            ->add('nombre', TextType::class, ['required' => false])
            ->add('apellidos', TextType::class, ['required' => false])
            ->add('email', EmailType::class, [
                'disabled' => true // Hace que el campo de email sea no editable
            ])
            ->add('newPassword', PasswordType::class, [
                'mapped' => false,
                'required' => false, //  opcional 
                'label' => 'Nueva contraseña (opcional)',
                'attr' => ['autocomplete' => 'new-password'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Usuarios::class,
        ]);
    }
}
