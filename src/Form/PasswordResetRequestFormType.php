<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PasswordResetRequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Correo Electrónico',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ingrese su correo electrónico'],
                'help' => 'Introduzca el email asociado a su cuenta.',
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enviar enlace de recuperación',
                'attr' => ['class' => 'btn btn-primary mt-3'],
            ]);
    }

   
}
