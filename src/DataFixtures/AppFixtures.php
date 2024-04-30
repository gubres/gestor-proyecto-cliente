<?php

// src/DataFixtures/AppFixtures.php
namespace App\DataFixtures;

use App\Entity\Usuarios;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        // Crear usuario administrador
        $adminUser = new Usuarios();
        $adminUser->setEmail('admin@admin');
        $adminUser->setPassword($this->passwordHasher->hashPassword(
            $adminUser,
            'admin'
        ));
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setNombre('admin');
        $adminUser->setApellidos('admin');
        $adminUser->setIsActive(true);
        $adminUser->setVerified(true);
        $adminUser->setActualizadoEn(new \DateTime("now", new \DateTimeZone('Europe/Madrid')));
        $adminUser->setCreadoEn(new \DateTime("now", new \DateTimeZone('Europe/Madrid')));




        $manager->persist($adminUser);
        $manager->flush();
    }
}
