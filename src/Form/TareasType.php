<?php

namespace App\Form;

use App\Entity\Proyectos;
use App\Entity\Tareas;
use App\Entity\Usuarios;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class TareasType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', null, [
                'attr' => ['class' => 'form-control'], // Agrega la clase 'form-control' al campo nombre
            ])
            ->add('finalizada', ChoiceType::class, [
                'choices' => [
                    'Sí' => true,
                    'No' => false,
                ],
                'expanded' => true,
                'multiple' => false,
                'attr' => ['class' => 'form-control'],
            ])
            
            ->add('creado_en', DateTimeType::class, [
                'widget' => 'single_text', // campo input de fecha
                'attr' => ['class' => 'form-control']
            ])
            ->add('finalizado_en', DateTimeType::class, [
                'widget' => 'single_text',
                'required' => false, // No requerido, dependiendo de si la tarea ya ha sido finalizada o no
                'attr' => ['class' => 'form-control']
            ])

            ->add('prioridad', ChoiceType::class, [
                'choices' => [
                    'Selecciona la prioridad' => null, // Opción predeterminada
                    'ALTA' => 'ALTA',
                    'MEDIA' => 'MEDIA',
                    'BAJA' => 'BAJA',
                ],
        ])

        ->add('proyecto', EntityType::class, [
            'class' => Proyectos::class,
            'choice_label' => 'nombre', // Suponiendo que tienes un atributo 'nombre' en tu entidad Proyectos
            'placeholder' => 'Selecciona un proyecto', // Opción predeterminada
        ])
        ->add('usuario', EntityType::class, [
            'class' => Usuarios::class,
            'choice_label' => 'nombre', // Suponiendo que tienes un atributo 'nombre' en tu entidad Usuarios
            'multiple' => true,
        ])
    ;
}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tareas::class,
        ]);
    }
}
