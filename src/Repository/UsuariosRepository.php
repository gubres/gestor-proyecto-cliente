<?php

namespace App\Repository;

use App\Entity\Usuarios;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\ORM\Query\Expr\Join;
use DateTime;



/**
 * @extends ServiceEntityRepository<Usuarios>
 *
 * @method Usuarios|null find($id, $lockMode = null, $lockVersion = null)
 * @method Usuarios|null findOneBy(array $criteria, array $orderBy = null)
 * @method Usuarios[]    findAll()
 * @method Usuarios[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

class UsuariosRepository extends ServiceEntityRepository

{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Usuarios::class);
    }

    public function findProyectosCompartidos(Usuarios $usuario, ?DateTime $startDate, ?DateTime $endDate)
{
    $qb = $this->createQueryBuilder('u')
        ->innerJoin('u.usuariosProyectos', 'up')
        ->innerJoin('up.proyecto', 'p')
        ->innerJoin('p.usuariosProyectos', 'up2')
        ->innerJoin('up2.usuario', 'u2')
        ->innerJoin('p.tareas', 't')
        ->where('u2.id = :usuarioId')
        ->andWhere('t.finalizada = false')  // Filtra tareas no finalizadas
        ->setParameter('usuarioId', $usuario->getId());

   // Asegurarse de que la fecha de inicio es considerada y solo incluir tareas desde esa fecha en adelante
   if ($startDate) {
    $qb->andWhere('t.creado_en >= :start')
       ->setParameter('start', $startDate->format('Y-m-d 00:00:00'));
}

// Si se proporciona una fecha de fin, limitar las tareas hasta esa fecha
if ($endDate) {
    $qb->andWhere('t.creado_en <= :end')
       ->setParameter('end', $endDate->format('Y-m-d 23:59:59'));
}

    $qb->groupBy('u.id');

    return $qb->getQuery()->getResult();
}

}