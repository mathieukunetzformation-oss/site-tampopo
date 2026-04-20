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
}
