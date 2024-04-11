<?php

namespace App\Form;

use App\Entity\Proyectos;
use App\Entity\Tareas;
use App\Entity\Usuarios;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TareasType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre')
            ->add('finalizada')
            ->add('creado_en', null, [
                'widget' => 'single_text',
            ])
            ->add('prioridad')
            ->add('proyecto', EntityType::class, [
                'class' => Proyectos::class,
                'choice_label' => 'id',
            ])
            ->add('usuario', EntityType::class, [
                'class' => Usuarios::class,
                'choice_label' => 'id',
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
