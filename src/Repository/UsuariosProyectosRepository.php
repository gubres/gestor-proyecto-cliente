<?php

namespace App\Repository;

use App\Entity\UsuariosProyectos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<UsuariosProyectos>
 *
 * @method UsuariosProyectos|null find($id, $lockMode = null, $lockVersion = null)
 * @method UsuariosProyectos|null findOneBy(array $criteria, array $orderBy = null)
 * @method UsuariosProyectos[]    findAll()
 * @method UsuariosProyectos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsuariosProyectosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsuariosProyectos::class);
    }


    //    /**
    //     * @return Usuarios[] Returns an array of Usuarios objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Usuarios
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
