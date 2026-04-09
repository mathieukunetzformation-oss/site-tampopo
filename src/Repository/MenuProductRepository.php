<?php

namespace App\Repository;

use App\Entity\MenuProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MenuProduct>
 */
class MenuProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuProduct::class);
    }

    public function findWithPhotos()
    {
        return $this->createQueryBuilder('p')
            ->where('p.photo IS NOT NULL')
            ->andWhere('p.photo != :empty')
            ->setParameter('empty', '')
            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return MenuProduct[] Returns an array of MenuProduct objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?MenuProduct
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
