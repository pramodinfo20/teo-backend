<?php

namespace App\Repository;

use App\Entity\ResponsibilityCategories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ResponsibilityCategories|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResponsibilityCategories|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResponsibilityCategories[]    findAll()
 * @method ResponsibilityCategories[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResponsibilityCategoriesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ResponsibilityCategories::class);
    }

    public function getCategories()
    {
        $queryBuilder = $this->createQueryBuilder('rc');

        return $queryBuilder
            ->select('rc.rcId as id, rc.name')
            ->orderBy('rc.name')
            ->getQuery()
            ->getResult();
    }
}
