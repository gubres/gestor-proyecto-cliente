<?php

namespace App\Repository;

use App\Entity\Tareas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tareas>
 *
 * @method Tareas|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tareas|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tareas[]    findAll()
 * @method Tareas[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TareasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tareas::class);
    }
    public function findNotDeleted(): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.eliminado = :val')
            ->setParameter('val', false)
            ->getQuery()
            ->getResult();
    }

    public function findByDateRange(string $fechaInicio, string $fechaFin): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.creadoEn >= :fechaInicio')
            ->andWhere('t.creadoEn <= :fechaFin')
            ->setParameter('fechaInicio', $fechaInicio)
            ->setParameter('fechaFin', $fechaFin)
            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return Tareas[] Returns an array of Tareas objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Tareas
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
