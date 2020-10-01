<?php

namespace App\Repository;

use App\Entity\CocParameters;
use App\Entity\EcuSwParameters;
use App\Entity\GlobalParameters;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EcuSwParameters|null find($id, $lockMode = null, $lockVersion = null)
 * @method EcuSwParameters|null findOneBy(array $criteria, array $orderBy = null)
 * @method EcuSwParameters[]    findAll()
 * @method EcuSwParameters[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EcuSwParametersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EcuSwParameters::class);
    }

    public function findGlobalsUsageInEcuParameters(GlobalParameters $globalParameter): array
    {
        return $this->createQueryBuilder('p')
            ->select('e.ecuName, v.swVersion')
            ->leftJoin('App:ConfigurationEcus', 'e', 'WITH', 'e.ceEcuId = p.ceEcu')
            ->leftJoin('App:EcuSwParameterEcuSwVersionMapping', 'm', 'WITH', 'm.ecuSwParameter = p.ecuSwParameterId')
            ->leftJoin('App:EcuSwVersions', 'v', 'WITH', 'v.ecuSwVersionId = m.ecuSwVersion')
            ->groupBy('e.ceEcuId, v.ecuSwVersionId')
            ->where('p.linkedToGlobalParameter = :parameter')
            ->setParameter('parameter', $globalParameter)
            ->orderBy('e.ecuName')
            ->getQuery()
            ->getResult();
    }

    public function findCoCsUsageInEcuParameters(CocParameters $cocParameter): array
    {
        return $this->createQueryBuilder('p')
            ->select('e.ecuName, v.swVersion')
            ->leftJoin('App:ConfigurationEcus', 'e', 'WITH', 'e.ceEcuId = p.ceEcu')
            ->leftJoin('App:EcuSwParameterEcuSwVersionMapping', 'm', 'WITH', 'm.ecuSwParameter = p.ecuSwParameterId')
            ->leftJoin('App:EcuSwVersions', 'v', 'WITH', 'v.ecuSwVersionId = m.ecuSwVersion')
            ->groupBy('e.ceEcuId, v.ecuSwVersionId')
            ->where('p.linkedToCocParameter = :parameter')
            ->setParameter('parameter', $cocParameter)
            ->orderBy('e.ecuName')
            ->getQuery()
            ->getResult();
    }
}