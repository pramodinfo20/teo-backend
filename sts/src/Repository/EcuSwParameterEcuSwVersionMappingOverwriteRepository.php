<?php

namespace App\Repository;

use App\Entity\EcuSwParameterEcuSwVersionMappingOverwrite;
use App\Entity\EcuSwVersions;
use App\Entity\SubVehicleConfigurations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EcuSwParameterEcuSwVersionMappingOverwrite|null find($id, $lockMode = null, $lockVersion = null)
 * @method EcuSwParameterEcuSwVersionMappingOverwrite|null findOneBy(array $criteria, array $orderBy = null)
 * @method EcuSwParameterEcuSwVersionMappingOverwrite[]    findAll()
 * @method EcuSwParameterEcuSwVersionMappingOverwrite[]    findBy(array $criteria, array $orderBy = null, $limit =
 *         null, $offset = null)
 */
class EcuSwParameterEcuSwVersionMappingOverwriteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EcuSwParameterEcuSwVersionMappingOverwrite::class);
    }

    function getOverwrittenParametersForOdx2BySwIdAndSubconfId(EcuSwVersions $sw, SubVehicleConfigurations $subconf)
    {
        return $this->createQueryBuilder('mo')
            ->select('IDENTITY(vs.ecuSwParameter) as overwritten_parameter_id')
            ->addSelect('vs.valueBool as overwritten_value_bool')
            ->addSelect(' vs.valueInteger as overwritten_value_integer, vs.valueUnsigned as overwritten_value_unsigned')
            ->addSelect('vs.valueString as overwritten_value_string, vs.valueHex as overwritten_value_hex')
            ->addSelect('vs.ecuSwParameterValueSetId as overwritten_value_set_id')
            ->join('App:EcuSwParameterValuesSets', 'vs', 'WITH',
                'mo.ecuSwParameterValueSet = vs.ecuSwParameterValueSetId')
            ->leftjoin('App:Odx1Parameters', 'odx', 'WITH',
                'vs.ecuSwParameter = odx.opEcuSwParameter')
            /* --- Remove parameters that are only available in odx1 --- */
            ->where("COALESCE(odx.isAlsoOdx2, 'no') = 'no'  OR odx.isAlsoOdx2 = true")
            ->andWhere('mo.subVehicleConfiguration = :subconf')
            ->andWhere('mo.ecuSwVersion = :sw')
            ->setParameters([
                'subconf' => $subconf->getSubVehicleConfigurationId(),
                'sw' => $sw->getEcuSwVersionId()
            ])
            ->getQuery()
            ->getResult();
    }

    function getOverwrittenParametersForOdx1BySwIdAndSubconfId(EcuSwVersions $sw, SubVehicleConfigurations $subconf)
    {
        return $this->createQueryBuilder('mo')
            ->select('IDENTITY(vs.ecuSwParameter) as overwritten_parameter_id')
            ->addSelect('vs.valueBool as overwritten_value_bool')
            ->addSelect(' vs.valueInteger as overwritten_value_integer, vs.valueUnsigned as overwritten_value_unsigned')
            ->addSelect('vs.valueString as overwritten_value_string, vs.valueHex as overwritten_value_hex')
            ->addSelect('vs.ecuSwParameterValueSetId as overwritten_value_set_id')
            ->join('App:EcuSwParameterValuesSets', 'vs', 'WITH',
                'mo.ecuSwParameterValueSet = vs.ecuSwParameterValueSetId')
            ->join('App:Odx1Parameters', 'odx', 'WITH',
                'vs.ecuSwParameter = odx.opEcuSwParameter')
            ->andWhere('mo.subVehicleConfiguration = :subconf')
            ->andWhere('mo.ecuSwVersion = :sw')
            ->setParameters([
                'subconf' => $subconf->getSubVehicleConfigurationId(),
                'sw' => $sw->getEcuSwVersionId()
            ])
            ->getQuery()
            ->getResult();
    }
}
