<?php

namespace App\Repository;

use App\Entity\CocParameters;
use App\Entity\SubVehicleConfigurations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method CocParameters|null find($id, $lockMode = null, $lockVersion = null)
 * @method CocParameters|null findOneBy(array $criteria, array $orderBy = null)
 * @method CocParameters[]    findAll()
 * @method CocParameters[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CocParametersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CocParameters::class);
    }

    public function getParametersBySubConfiguration(SubVehicleConfigurations $subConfiguration)
    {
        return $this->createQueryBuilder('cp')
            ->select('cp.cocParameterId as cocParameterId, IDENTITY(cp.variableType) as variableTypeId')
            ->addSelect('cp.cocParameterName, IDENTITY(cp.responsibleUser) as responsibleUserId')
            ->addSelect('vt.variableTypeName, u.fname as responsibleUserFname, u.lname as responsibleUserLname')
            ->addSelect('cpvs.cocParameterValuesSetId as cocParameterValueSetId')
            ->addSelect('cp.field, un.name, cp.description')
            ->addSelect('cpvs.valueString, cpvs.valueBool, cpvs.valueDouble, cpvs.valueInteger, cpvs.valueBiginteger')
            ->addSelect('cpvs.valueDate, cpvs.valueHex')
            ->leftJoin('App:CocParameterValuesSets', 'cpvs', 'WITH',
                'cpvs.cocParameter = cp.cocParameterId')
            ->leftJoin('App:CocParameterValuesSetsMapping', 'cpvsm', 'WITH',
                'cpvsm.cocParameterValueSet = cpvs.cocParameterValuesSetId')
            ->leftJoin('App:Users', 'u', 'WITH',
                'cp.responsibleUser = u.id')
            ->leftJoin('App:VariableTypes', 'vt', 'WITH',
                'cp.variableType = vt.variableTypeId')
            ->leftJoin('App:Units', 'un', 'WITH',
              'un.unitId = cp.unit')
            ->where('cpvsm.subVehicleConfiguration = :subConfiguration ')
            ->setParameters([
                'subConfiguration' => $subConfiguration,
            ])
            ->orderBy('cp.parameterOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
