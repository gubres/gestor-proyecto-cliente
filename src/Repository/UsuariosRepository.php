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





    

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Usuarios) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
   
    }

        // Método para encontrar todos los correos electrónicos de los usuarios
        public function findAllEmails(): array
        {dump("findAllEmails method called");

            $qb = $this->createQueryBuilder('u')
                ->select('u.email');
                dump("findAllEmails method called");
            
            return $qb->getQuery()->getResult();
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


       


    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->findOneBy(['email' => $identifier]);

        if (!$user) {
            throw new UnsupportedUserException(sprintf('User with email "%s" not found.', $identifier));
        }

        return $user;
    }


    


  

}
