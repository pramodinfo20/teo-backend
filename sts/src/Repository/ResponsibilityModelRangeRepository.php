<?php

namespace App\Repository;

use App\Entity\ResponsibilityModelRange;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ResponsibilityModelRange|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResponsibilityModelRange|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResponsibilityModelRange[]    findAll()
 * @method ResponsibilityModelRange[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResponsibilityModelRangeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResponsibilityModelRange::class);
    }

    public function checkIfUserOrgAlreadyAssignedForModelRange(int $userOrg, string $modelRange, bool $isOrganization)
    {
        $column = $isOrganization ? 'ra.stsOs' : 'ra.assignedUser';

        $qb = $this->createQueryBuilder('rmr');

        $result = $qb
            ->join('App:ResponsibilityAssignments', 'ra', 'WITH', 'ra.raId = rmr.respAssignments')
            ->where('rmr.name = :modelRange')
            ->andWhere($column . ' = :userId')
            ->setParameter('modelRange', $modelRange)
            ->setParameter('userId', $userOrg)
            ->getQuery()
            ->getScalarResult();

        return (bool)$result;
    }

    // /**
    //  * @return ResponsibilityModelRange[] Returns an array of ResponsibilityModelRange objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ResponsibilityModelRange
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
