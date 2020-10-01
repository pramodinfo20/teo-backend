<?php

namespace App\Repository;

use App\Entity\CocParameterValuesSetsMapping;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CocParameterValuesSetsMapping|null find($id, $lockMode = null, $lockVersion = null)
 * @method CocParameterValuesSetsMapping|null findOneBy(array $criteria, array $orderBy = null)
 * @method CocParameterValuesSetsMapping[]    findAll()
 * @method CocParameterValuesSetsMapping[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CocParameterValuesSetsMappingRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CocParameterValuesSetsMapping::class);
    }

    public function findHexByCocIdAndSubConfId(int $cocId, int $subConfId)
    {
        return $this->createQueryBuilder('c')
            ->select('gs.valueHex')
            ->join('App:CocParameterValuesSets', 'cs', 'WITH',
                'cs.cocParameterValuesSetId = c.cocParameterValuesSet')
            ->where('c.subVehicleConfiguration = :subConfId')
            ->andWhere('cs.cocParameter = :cocId')
            ->setParameters(['subConfId' => $subConfId, 'cocId' => $cocId])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getCpnBySubVehConfId(int $subConfId)
    {
        $queryBuilder = $this->createQueryBuilder('cpvsm');

        $result = $queryBuilder
            ->select('(cpvsm.cocParameterValueSet) AS cocParameterValueSet')
            ->addSelect('(cp.cocParameterName) AS cocParameterName')
            ->addSelect('(cpvs.valueString) AS valueS')
            ->addSelect('(cpvs.valueBool) AS valueB')
            ->addSelect('(cpvs.valueDouble) AS valueD')
            ->addSelect('(cpvs.valueHex) AS valueH')
            ->addSelect('cpvs.valueInteger AS valueI')
            ->join('App:SubVehicleConfigurations', 'svc', 'WITH', 'cpvsm.subVehicleConfiguration = svc.subVehicleConfigurationId')
            ->join('App:CocParameterValuesSets', 'cpvs', 'WITH', 'cpvs.cocParameterValuesSetId = cpvsm.cocParameterValueSet')
            ->join('App:CocParameters', 'cp', 'WITH', 'cp.cocParameterId = cpvs.cocParameter')
            ->where($queryBuilder->expr()->in('cpvsm.subVehicleConfiguration', $subConfId))
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function getCpvsBySubVehConfId(int $subConfId)
    {
        $queryBuilder = $this->createQueryBuilder('cpvsm');

        $result = $queryBuilder
            ->select('(cpvs.valueString) AS valueS')
            ->addSelect('(cpvs.valueBool) AS valueB')
            ->addSelect('(cpvs.valueDouble) AS valueD')
            ->addSelect('(cpvs.valueHex) AS valueH')
            ->addSelect('cpvs.valueInteger AS valueI')
            ->join('App:CocParameterValuesSets', 'cpvs', 'WITH', 'cpvs.cocParameterValuesSetId = cpvsm.cocParameterValueSet')
            ->join('App:CocParameters', 'cp', 'WITH', 'cp.cocParameterId = cpvs.cocParameter')
            ->where($queryBuilder->expr()->in('cpvs.cocParameterValuesSetId', $subConfId))
            ->getQuery()
            ->getResult();

        return $result;
    }
}
