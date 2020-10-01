<?php

namespace App\Repository;

use App\Entity\EcuSwPropertiesMapping;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EcuSwPropertiesMapping|null find($id, $lockMode = null, $lockVersion = null)
 * @method EcuSwPropertiesMapping|null findOneBy(array $criteria, array $orderBy = null)
 * @method EcuSwPropertiesMapping[]    findAll()
 * @method EcuSwPropertiesMapping[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EcuSwPropertiesMappingRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EcuSwPropertiesMapping::class);
    }

    public function getLastOrderForSelectedEcuSw(int $ecuSwVerId)
    {
        $queryBuilder = $this->createQueryBuilder('espm');

        $result = $queryBuilder
            ->select('MAX(espm.propertyOrder) as lastOrder')
            ->where('espm.ecuSwVersion = :ecuSwVerId')
            ->setParameter('ecuSwVerId', $ecuSwVerId)
            ->getQuery()
            ->getSingleScalarResult();

        return $result;
    }

    public function getEcuSwPropMappingIdList(int $ecuSwVerId) {
        $queryBuilder = $this->createQueryBuilder('espm');

        $result = $queryBuilder
            ->select('espm.ecuSwPropertiesMappingId')
            ->where('espm.ecuSwVersion = :ecuSwVerId')
            ->setParameter('ecuSwVerId', $ecuSwVerId)
            ->getQuery()
            ->getScalarResult();

        return $result;
    }

    public function getEcuSwPropMappingEcuSwList(int $ecuSwVerId) {
        $queryBuilder = $this->createQueryBuilder('espm');

        $result = $queryBuilder
            ->select('IDENTITY(espm.ecuSwProperty) AS ecuSwProperty, esp.name, esp.value')
            ->join('App:EcuSwProperties', 'esp', Join::WITH, 'espm.ecuSwProperty = esp.ecuSwPropertyId')
            ->where('espm.ecuSwVersion = :ecuSwVerId')
            ->setParameter('ecuSwVerId', $ecuSwVerId)
            ->getQuery()
            ->getScalarResult();

        return $result;
    }

    // /**
    //  * @return EcuSwPropertiesMapping[] Returns an array of EcuSwPropertiesMapping objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EcuSwPropertiesMapping
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
