<?php

namespace App\Repository;

use App\Entity\Vehicles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use function Doctrine\ORM\QueryBuilder;

/**
 * @method Vehicles|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vehicles|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vehicles[]    findAll()
 * @method Vehicles[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehiclesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Vehicles::class);
    }

    public function getVehiclesId(string $column, string $startValue, string $endValue): array
    {
        $queryBuilder = $this->createQueryBuilder('v');

        $startVehicleId = $this->getSingleVehicleId($column, $startValue);
        $endVehicleId = $this->getSingleVehicleId($column, $endValue);

        $result = $queryBuilder
            ->select('v.vehicleId, v.vin')
            ->addSelect('ccpr.approvalCode, ccpr.approvalDate')
            ->addSelect('IDENTITY(v.subVehicleConfiguration) AS subVehicleConfigurationId')
            ->addSelect('svc.subVehicleConfigurationName')
            ->addSelect('(svc.vehicleConfiguration) AS vehicleConfigurationId')
            ->addSelect('(vs.coc) AS seqNumber')
            ->addSelect('(vs.cocYear) AS year')
            ->join('App:VehiclesSales', 'vs', 'WITH', 'v.vehicleId = vs.vehicle')
            ->join('App:SubVehicleConfigurations', 'svc', 'WITH', 'v.subVehicleConfiguration = svc.subVehicleConfigurationId')
            ->leftJoin('App:CocParameterRelease', 'ccpr', 'WITH', 'ccpr.cprSubVehicleConfiguration = v.subVehicleConfiguration')
            ->orderBy('v.vehicleId')
            ->where($queryBuilder->expr()->between('v.vehicleId', $startVehicleId, $endVehicleId))
            ->getQuery()
            ->getResult();

        return $result;
    }

    private function getSingleVehicleId(string $columnName, string $columnValue)
    {
        $queryBuilder = $this->createQueryBuilder('v');

        $result = $queryBuilder
            ->select('v.vehicleId')
            ->where($queryBuilder->expr()->like('v.' . $columnName, $queryBuilder->expr()->literal($columnValue)))
            ->getQuery()
            ->getSingleScalarResult();

        return $result;
    }

    public function getVehiclesToPdfForOldConfig(array $vehicles)
    {
        $queryBuilder = $this->createQueryBuilder('v');

        $result = $queryBuilder
            ->select('v.vehicleId, v.vin, v.pentaKennwort')
            ->addSelect('svc.subVehicleConfigurationId, svc.subVehicleConfigurationName')
            ->addSelect('vc.vehicleConfigurationKey, vc.vehicleTypeName, vc.vehicleTypeYear')
            ->addSelect('vv.zielstaat, vv.vmax, vv.maxPower, vv.maxPower30min')
            ->addSelect('pv.pentaVariantName')
            ->addSelect('cc.configurationColorName')
            ->join('App:SubVehicleConfigurations', 'svc', 'WITH', 'v.subVehicleConfiguration = svc.subVehicleConfigurationId')
            ->join('App:VehicleConfigurations', 'vc', 'WITH', 'svc.vehicleConfiguration = vc.vehicleConfigurationId')
            ->join('App:VehicleVariants', 'vv', 'WITH', 'v.vehicleVariant = vv.vehicleVariantId')
            ->join('App:PentaVariants', 'pv', 'WITH', 'v.pentaVariant = pv.pentaVariantId')
            ->join('App:ConfigurationColors', 'cc', 'WITH', 'pv.configurationColor = cc.configurationColorId')
            ->where($queryBuilder->expr()->in('v.vehicleId', $vehicles))
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function getVehiclesPropertiesToPdfForOldConfig(string $vehicle)
    {
        $queryBuilder = $this->createQueryBuilder('v');

        $result = $queryBuilder
            ->select('vcp.vcPropertyId')
            ->addSelect('vcps.description')
            ->join('App:SubVehicleConfigurations', 'svc', 'WITH', 'v.subVehicleConfiguration = svc.subVehicleConfigurationId')
            ->join('App:VehicleConfigurations', 'vc', 'WITH', 'svc.vehicleConfiguration = vc.vehicleConfigurationId')
            ->leftJoin('App:VehicleConfigurationPropertiesMapping', 'vcpm', 'WITH', 'vc.vehicleConfigurationId = vcpm.vehicleConfiguration')
            ->leftJoin('App:AllowedSymbols', 'sa', 'WITH', 'vcpm.allowedSymbols = sa.allowedSymbolsId')
            ->leftJoin('App:VehicleConfigurationProperties', 'vcp', 'WITH', 'vcpm.vcProperty = vcp.vcPropertyId')
            ->leftJoin('App:VehicleConfigurationPropertiesSymbols', 'vcps', 'WITH', 'vcpm.vcProperty = vcps.vcProperty AND vcpm.allowedSymbols = vcps.allowedSymbols')
            ->where($queryBuilder->expr()->in('v.vehicleId', $vehicle))
            ->getQuery()
            ->getResult();

        return $result;

    }

    public function getTiresPressureToPdfForOldConfig(string $vehicle)
    {
        $queryBuilder = $this->createQueryBuilder('v');

        $result = $queryBuilder
            ->select('svp.specialVehiclePropertyName AS vehicleConfigurationPropertyName')
            ->addSelect('svpv.valueInteger AS description')
            ->leftJoin('App:SubVehicleConfigurations', 'svc', 'WITH', 'v.subVehicleConfiguration = svc.subVehicleConfigurationId')
            ->leftJoin('App:VehicleConfigurations', 'vc', 'WITH', 'svc.vehicleConfiguration = vc.vehicleConfigurationId')
            ->leftJoin('App:SpecialVehiclePropertiesMapping', 'svpm', 'WITH', 'svpm.subVehicleConfiguration = svc.subVehicleConfigurationId')
            ->leftJoin('App:SpecialVehicleProperties', 'svp', 'WITH', 'svp.specialVehiclePropertyId = svpm.specialVehicleProperty')
            ->leftJoin('App:SpecialVehiclePropertyValues', 'svpv', 'WITH', 'svpm.specialVehiclePropertyValue = svpv.svpvId AND svpm.specialVehicleProperty = svp.specialVehiclePropertyId')
            ->where($queryBuilder->expr()->in('v.vehicleId', $vehicle))
            ->andWhere('svp.specialVehiclePropertyName LIKE :Tire')
            ->setParameter('Tire', 'Tire%')
            ->setMaxResults(2)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function getCoCParameterToPdfForShortConfig(string $vehicle)
    {
        $queryBuilder = $this->createQueryBuilder('v');

        $result = $queryBuilder
            ->select('svc.subVehicleConfigurationId, cpvs.valueInteger, cp.cocParameterId, cp.cocParameterName')
            ->leftJoin('App:SubVehicleConfigurations', 'svc', 'WITH', 'v.subVehicleConfiguration = svc.subVehicleConfigurationId')
            ->leftJoin('App:CocParameterValuesSetsMapping', 'cpvsm', 'WITH', 'svc.subVehicleConfigurationId = cpvsm.subVehicleConfiguration')
            ->leftJoin('App:CocParameterValuesSets', 'cpvs', 'WITH', 'cpvsm.cocParameterValueSet = cpvs.cocParameterValuesSetId')
            ->leftJoin('App:CocParameters', 'cp', 'WITH', 'cpvs.cocParameter = cp.cocParameterId')
            ->where($queryBuilder->expr()->in('v.vehicleId', $vehicle))
            ->andWhere('cpvs.cocParameter in(23,27,28)')
            ->orderBy('cp.cocParameterId', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function getAdditionalComponentsToPdfForOldConfig(string $vehicle, int $id)
    {
        $queryBuilder = $this->createQueryBuilder('v');

        $result = $queryBuilder
            ->select('svpm.svpmId as svpmid, svp.specialVehiclePropertyName, svp.specialVehiclePropertyId, vt.variableTypeName, svpv.valueBool, svpv.valueString, svpv.valueInteger')
            ->join('App:SubVehicleConfigurations', 'svc', 'WITH', 'v.subVehicleConfiguration = svc.subVehicleConfigurationId')
            ->join('App:VehicleConfigurations', 'vc', 'WITH', 'svc.vehicleConfiguration = vc.vehicleConfigurationId')
            ->join('App:SpecialVehiclePropertiesMapping', 'svpm', 'WITH', 'svc.subVehicleConfigurationId = svpm.subVehicleConfiguration')
            ->join('App:SpecialVehicleProperties', 'svp', 'WITH', 'svpm.specialVehicleProperty = svp.specialVehiclePropertyId')
            ->join('App:SpecialVehiclePropertyValues', 'svpv', 'WITH', 'svpm.specialVehiclePropertyValue = svpv.svpvId')
            ->join('App:VariableTypes', 'vt', 'WITH', 'svp.variableType = vt.variableTypeId')
            ->where($queryBuilder->expr()->in('v.vehicleId', $vehicle))
            ->andWhere('svc.subVehicleConfigurationId = :id')
            ->andWhere('svpm.specialVehicleProperty in(1,2,5,6,8,12)')
            ->andWhere('svpv.valueBool != FALSE AND vt.variableTypeId = 7')
            ->orderBy('svp.specialVehiclePropertyId', 'ASC')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function getVehiclesToPdfForNewConfig(array $vehicles)
    {
        $queryBuilder = $this->createQueryBuilder('v');

        $result = $queryBuilder
            ->select('v.vehicleId, v.vin, v.pentaKennwort')
            ->addSelect('svc.subVehicleConfigurationId, svc.shortProductionDescription')
            ->addSelect('vc.vehicleConfigurationKey')
            ->join('App:SubVehicleConfigurations', 'svc', 'WITH', 'v.subVehicleConfiguration = svc.subVehicleConfigurationId')
            ->join('App:VehicleConfigurations', 'vc', 'WITH', 'svc.vehicleConfiguration = vc.vehicleConfigurationId')
            ->where($queryBuilder->expr()->in('v.vehicleId', $vehicles))
            ->orderBy('v.vehicleId', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function getVehiclesPropertiesToPdfForNewConfig(string $vehicle)
    {
        $queryBuilder = $this->createQueryBuilder('v');

        $result = $queryBuilder
            ->select('vcp.vcPropertyId')
            ->addSelect('vcps.germanDescription')
            ->join('App:SubVehicleConfigurations', 'svc', 'WITH', 'v.subVehicleConfiguration = svc.subVehicleConfigurationId')
            ->join('App:VehicleConfigurations', 'vc', 'WITH', 'svc.vehicleConfiguration = vc.vehicleConfigurationId')
            ->leftJoin('App:VehicleConfigurationPropertiesMapping', 'vcpm', 'WITH', 'vc.vehicleConfigurationId = vcpm.vehicleConfiguration')
            ->leftJoin('App:AllowedSymbols', 'sa', 'WITH', 'vcpm.allowedSymbols = sa.allowedSymbolsId')
            ->leftJoin('App:VehicleConfigurationProperties', 'vcp', 'WITH', 'vcpm.vcProperty = vcp.vcPropertyId')
            ->leftJoin('App:VehicleConfigurationPropertiesSymbols', 'vcps', 'WITH', 'sa.allowedSymbolsId = vcps.allowedSymbols AND vcpm.vcProperty = vcps.vcProperty')
            ->where($queryBuilder->expr()->in('v.vehicleId', $vehicle))
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function getTiresPressureToPdfForNewConfig(string $vehicle)
    {
        $queryBuilder = $this->createQueryBuilder('v');

        $result = $queryBuilder
            ->select('svp.specialVehiclePropertyName AS vehicleConfigurationPropertyName')
            ->addSelect('svpv.valueInteger AS germanDescription')
            ->leftJoin('App:SubVehicleConfigurations', 'svc', 'WITH', 'v.subVehicleConfiguration = svc.subVehicleConfigurationId')
            ->leftJoin('App:VehicleConfigurations', 'vc', 'WITH', 'svc.vehicleConfiguration = vc.vehicleConfigurationId')
            ->leftJoin('App:SpecialVehiclePropertiesMapping', 'svpm', 'WITH', 'svpm.vehicleConfiguration = vc.vehicleConfigurationId')
            ->leftJoin('App:SpecialVehicleProperties', 'svp', 'WITH', 'svp.specialVehiclePropertyId = svpm.specialVehicleProperty')
            ->leftJoin('App:SpecialVehiclePropertyValues', 'svpv', 'WITH', 'svpm.specialVehiclePropertyValue = svpv.svpvId AND svpm.specialVehicleProperty = svp.specialVehiclePropertyId')
            ->where($queryBuilder->expr()->in('v.vehicleId', $vehicle))
            ->andWhere('svp.specialVehiclePropertyName LIKE :Tire')
            ->setParameter('Tire', 'Tire%')
            ->setMaxResults(2)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function getVinBySubConfigurationId($subConfId)
    {
        $queryBuilder = $this->createQueryBuilder('v');

        $result = $queryBuilder
            ->select('v.vehicleId AS vehicleId, v.vin AS vin, (v.subVehicleConfiguration) AS subVehicleConfigurationId')
            ->addSelect('ccpr.approvalCode, ccpr.approvalDate')
            ->addSelect('(vs.coc) AS seqNumber')
            ->addSelect('(vs.cocYear) AS year')
            ->join('App:VehiclesSales', 'vs', 'WITH', 'v.vehicleId = vs.vehicle')
            ->innerJoin('App:SubVehicleConfigurations', 'svc', 'WITH', 'svc.subVehicleConfigurationId = v.subVehicleConfiguration')
            ->leftJoin('App:CocParameterRelease', 'ccpr', 'WITH', 'ccpr.cprSubVehicleConfiguration = v.subVehicleConfiguration')
            ->where($queryBuilder->expr()->in('v.subVehicleConfiguration', $subConfId))
            ->orderBy('v.vin', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function getVehicleInfo(int $vehicle)
    {
        return $this->createQueryBuilder('v')
            ->select('v.vehicleId as vehicle_id', 'v.vin as vin', 'v.pentaKennwort as pentaKennwort', 'IDENTITY(v.vehicleVariant) as vehicleVariantId')
            ->addSelect('vv.windchillVariantName as windchill_variant_name')
            ->innerJoin('App:VehicleVariants', 'vv', 'WITH', 'v.vehicleVariant = vv.vehicleVariantId')
            ->where('v.vehicleId = :vehicle')
            ->setParameter('vehicle', $vehicle)
            ->getQuery()
            ->getSingleResult();
    }

    public function getVehiclesFromSalesAndStart(string $start)
    {
        return $this->createQueryBuilder('v')
            ->select('v.vehicleId as vehicle_id', 'v.vin as vin', 'v.code as code', 'v.ikz as ikz', 'c.name as colorname')
            ->addSelect('CONCAT(vc.vehicleTypeName, vc.vehicleTypeYear) as type')
            ->innerJoin('App:VehiclesSales', 'vs', 'WITH', 'vc.vehicle = v.vehicleId')
            ->innerJoin('App:SubVehicleConfigurations', 'sc', 'WITH', 'sc.subVehicleConfigurationId = v.subVehicleConfiguration')
            ->innerJoin('App:VehicleConfigurations', 'vc', 'WITH', 'vc.vehicleConfigurationId = sc.vehicleConfiguration')
            ->leftJoin('App:Colors', 'c', 'WITH', 'v.color = c.colorId')
            ->where('vs.vorhaben = :start')
            ->setParameter('start', $start)
            ->getQuery()
            ->getResult();
    }

    public function getVinByDeliveryDate($startDate, $endDate)
    {
        $queryBuilder = $this->createQueryBuilder('v');

        $result = $queryBuilder
            ->select('v.vehicleId, v.vin')
            ->addSelect('ccpr.approvalCode, ccpr.approvalDate')
            ->addSelect('svc.subVehicleConfigurationId')
            ->addSelect('(svc.vehicleConfiguration) AS vehicleConfigurationId')
            ->addSelect('(vs.coc) AS seqNumber')
            ->addSelect('(vs.cocYear) AS year')
            ->join('App:VehiclesSales', 'vs', 'WITH', 'v.vehicleId = vs.vehicle')
            ->join('App:SubVehicleConfigurations', 'svc', 'WITH', 'v.subVehicleConfiguration = svc.subVehicleConfigurationId')
            ->leftJoin('App:CocParameterRelease', 'ccpr', 'WITH', 'ccpr.cprSubVehicleConfiguration = v.subVehicleConfiguration')
            ->where('vs.deliveryDate >= :startDate')
            ->andWhere('vs.deliveryDate <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('v.vin', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function getVehiclesByScope(int $start, int $stop, string $col)
    {
        return $this->createQueryBuilder('v')
            ->select('v.vehicleId as vehicle_id', 'v.vin as vin', 'v.code as code', 'v.ikz as ikz', 'c.name as colorname')
            ->addSelect('CONCAT(vc.vehicleTypeName, vc.vehicleTypeYear) as type')
            ->addSelect('vc.vehicleTypeName as divisionType, vc.vehicleTypeYear as divisionYear, vc.vehicleSeries as divisionSeries ')
            ->leftJoin('App:Colors', 'c', 'WITH', 'v.color = c.colorId')
            ->innerJoin('App:SubVehicleConfigurations', 'sc', 'WITH', 'sc.subVehicleConfigurationId = v.subVehicleConfiguration')
            ->innerJoin('App:VehicleConfigurations', 'vc', 'WITH', 'vc.vehicleConfigurationId = sc.vehicleConfiguration')
            ->where('v.vehicleId >= :start')
            ->andWhere('v.vehicleId <= :stop')
            ->setParameters([
                'start' => $start,
                'stop' => $stop
            ])
            ->orderBy('v.' . $col, 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getVehicleIdByCol($col, $val)
    {
        return $this->createQueryBuilder('v')
            ->select('v.vehicleId')
            ->where('v.' . $col . ' = :val')
            ->setParameter('val', $val)
            ->getQuery()
            ->getSingleResult();
    }

    public function getVehiclesByPentaKennwort($startValue, $endValue)
    {
        $queryBuilder = $this->createQueryBuilder('v');
        $result = $queryBuilder
            ->select('v.vehicleId AS vehicleId, v.vin AS vin, (v.subVehicleConfiguration) AS subVehicleConfigurationId')
            ->addSelect('svc.subVehicleConfigurationName')
            ->addSelect('ccpr.approvalCode, ccpr.approvalDate')
            ->addSelect('(svc.vehicleConfiguration) AS vehicleConfigurationId')
            ->addSelect('(vs.coc) AS seqNumber')
            ->addSelect('(vs.cocYear) AS year')
            ->join('App:VehiclesSales', 'vs', 'WITH', 'v.vehicleId = vs.vehicle')
            ->join('App:SubVehicleConfigurations', 'svc', 'WITH', 'v.subVehicleConfiguration = svc.subVehicleConfigurationId')
            ->leftJoin('App:CocParameterRelease', 'ccpr', 'WITH', 'ccpr.cprSubVehicleConfiguration = v.subVehicleConfiguration')
            ->orderBy('v.vehicleId')
            ->where('v.pentaKennwort >= :startVal')
            ->andWhere('v.pentaKennwort <= :endVal')
            ->setParameter('startVal', $startValue)
            ->setParameter('endVal', $endValue)
            ->orderBy('v.vin', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return mixed
     */
    public function findAllVins()
    {
        $queryBuilder = $this->createQueryBuilder('v');

        $result = $queryBuilder
            ->select('v.vin')
            ->orderBy('v.vin')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return mixed
     */
    public function findAllLicencePlates()
    {
        $queryBuilder = $this->createQueryBuilder('v');

        $result = $queryBuilder
            ->select('v.code')
            ->orderBy('v.code')
            ->where('v.code IS NOT NULL')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return mixed
     */
    public function findAllLocation()
    {
        $queryBuilder = $this->createQueryBuilder('v');

        $result = $queryBuilder
            ->select('d.name')
            ->join('App:Depots', 'd','WITH', 'v.depot = d.depotId')
            ->where('d.depotType = 1')
            ->distinct(true)
            ->orderBy('d.name')
            ->getQuery()
            ->getResult();

        return $result;
    }

}
