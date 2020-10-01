<?php

namespace App\Repository;

use App\Entity\EcuSwProperties;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EcuSwProperties|null find($id, $lockMode = null, $lockVersion = null)
 * @method EcuSwProperties|null findOneBy(array $criteria, array $orderBy = null)
 * @method EcuSwProperties[]    findAll()
 * @method EcuSwProperties[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EcuSwPropertiesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EcuSwProperties::class);
    }

    public function getPropertiesForEcuSwVersion(int $ecuSwVersion) {

        $queryBuilder = $this->createQueryBuilder('esp');

        $result = $queryBuilder
            ->select('esp.ecuSwPropertyId, esp.name, esp.value, espm.propertyOrder')
            ->join('App:EcuSwPropertiesMapping', 'espm', 'WITH', 'esp.ecuSwPropertyId = espm.ecuSwProperty')
            ->join('App:EcuSwVersions', 'esv', 'WITH', 'espm.ecuSwVersion = esv.ecuSwVersionId')
            ->where('esv.ecuSwVersionId = :ecuSwVersionId')
            ->setParameter('ecuSwVersionId', $ecuSwVersion)
            ->orderBy('espm.propertyOrder')
            ->getQuery()
            ->getResult();

            return $result;
    }

    public function getAllProperties() {
        $queryBuilder = $this->createQueryBuilder('esp');

        $result = $queryBuilder
            ->select('identity(espm.ecuSwProperty) as propertyId, esp.name, esp.value, identity(espm.ecuSwVersion) as ecuSwVersion')
            ->join('App:EcuSwPropertiesMapping', 'espm', 'WITH', 'esp.ecuSwPropertyId = espm.ecuSwProperty')
            ->orderBy('esp.name')
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function getAllPropertiesBySw(int $sw) {
        $queryBuilder = $this->createQueryBuilder('esp');

        $result = $queryBuilder
            ->select('esp.name, esp.value, identity(espm.ecuSwVersion) as ecuSwVersion')
            ->addSelect('esp.ecuSwPropertyId')
            ->leftJoin('App:EcuSwPropertiesMapping', 'espm', 'WITH',
                'esp.ecuSwPropertyId = espm.ecuSwProperty AND espm.ecuSwVersion = :sw')
            ->setParameter(':sw', $sw)
            ->orderBy('esp.name')
            ->getQuery()
            ->getResult();

        return $result;

    }


    // /**
    //  * @return EcuSwProperties[] Returns an array of EcuSwProperties objects
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
    public function findOneBySomeField($value): ?EcuSwProperties
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
