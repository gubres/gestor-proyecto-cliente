<?php

namespace App\Form;

use App\Entity\Proyectos;
use App\Entity\Clientes;
use App\Entity\Usuarios;
use App\Repository\UsuariosRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;


class ProyectosType extends AbstractType
{

    private $usuariosRepository;

    public function __construct(UsuariosRepository $usuariosRepository)
    {
        $this->usuariosRepository = $usuariosRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Nombre') 
            ->add('Cliente', EntityType::class, [
                'class' => Clientes::class,
                'choice_label' => 'nombre', // Usar la propiedad 'nombre' del cliente como etiqueta
            ])
            ->add('Estado', ChoiceType::class, [
                'choices' => [
                    'Selecciona el estado' => null, // Opción predeterminada
                    'Activo' => 'Activo',
                    'Inactivo' => 'Inactivo',
                ],
            ])
            ->add('usuarios', EntityType::class, [
                'class' => Usuarios::class,
                'choice_label' => 'email',
                'multiple' => true,
                'expanded' => false,
                'mapped' => false,
            ]);

            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Proyectos::class,
        ]);
    }
}