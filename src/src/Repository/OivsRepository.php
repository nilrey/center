<?php

namespace App\Repository;

use App\Entity\Oivs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Oivs|null find($id, $lockMode = null, $lockVersion = null)
 * @method Oivs|null findOneBy(array $criteria, array $orderBy = null)
 * @method Oivs[]    findAll()
 * @method Oivs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OivsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Oivs::class);
    }

    // /**
    //  * @return Oivs[] Returns an array of Oivs objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Oivs
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
