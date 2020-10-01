<?php

namespace App\Repository;

use App\Entity\GlobalParameterValuesSetsMapping;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobalParameterValuesSetsMapping|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobalParameterValuesSetsMapping|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobalParameterValuesSetsMapping[]    findAll()
 * @method GlobalParameterValuesSetsMapping[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobalParameterValuesSetsMappingRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobalParameterValuesSetsMapping::class);
    }

    public function findHexByGlobalIdAndSubConfId(int $globalId, int $subConfId)
    {
        return $this->createQueryBuilder('g')
            ->select('gs.valueHex')
            ->join('App:GlobalParameterValuesSets', 'gs', 'WITH',
                'gs.globalParameterValuesSetId = g.globalParameterValuesSet')
            ->where('g.subVehicleConfiguration = :subConfId')
            ->andWhere('gs.globalParameter = :globalId')
            ->setParameters(['subConfId' => $subConfId, 'globalId' => $globalId])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
