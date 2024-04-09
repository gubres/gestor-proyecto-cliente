<?php

namespace App\Form;

use App\Entity\Clientes;
use App\Entity\Proyectos;
use App\Entity\Usuarios;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProyectosType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre')
            ->add('estado')
            ->add('cliente', EntityType::class, [
                'class' => Clientes::class,
                'choice_label' => 'id',
            ])
            ->add('usuariosProyectos', EntityType::class, [
                'class' => Usuarios::class, // Utiliza la clase Usuarios
                'choice_label' => 'email', // Por ejemplo, utiliza el campo 'email' como label
                'multiple' => true,
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Proyectos::class,
        ]);
    }
}
