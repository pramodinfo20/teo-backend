<?php

namespace App\Repository;

use App\Entity\SubVehicleConfigurations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SubVehicleConfigurations|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubVehicleConfigurations|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubVehicleConfigurations[]    findAll()
 * @method SubVehicleConfigurations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubVehicleConfigurationsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SubVehicleConfigurations::class);
    }

    public function findByTypeYearSeries(string $type, int $year, string $series, string $customerKey = null): array
    {
        if (is_null($customerKey)) {
            $rule = $this->createQueryBuilder('svc')->expr()->isNull('vc.vehicleCustomerKey');
            $parameters = [
                'type' => $type,
                'year' => $year,
                'series' => $series,
            ];
        } else {
            $rule = 'vc.vehicleCustomerKey = :customerKey';
            $parameters = [
                'type' => $type,
                'year' => $year,
                'series' => $series,
                'customerKey' => $customerKey
            ];
        }

        return $this->createQueryBuilder('svc')
            ->select('vc.vehicleConfigurationId, vc.vehicleConfigurationKey,
            svc.subVehicleConfigurationId, svc.subVehicleConfigurationName')
            ->addSelect('vc.vehicleTypeName, vc.vehicleTypeYear, vc.vehicleSeries')
            ->addSelect('COUNT(v.vehicleId) as vehiclesCount')
            ->addSelect('vc.draft as configuration_draft, svc.draft as sub_configuration_draft')
            ->join('App:VehicleConfigurations', 'vc', 'WITH',
                'vc.vehicleConfigurationId = svc.vehicleConfiguration')
            ->leftJoin('App:Vehicles', 'v', 'WITH',
                'svc.subVehicleConfigurationId = v.subVehicleConfiguration')
            ->where('vc.vehicleTypeName = :type')
            ->andWhere('vc.vehicleTypeYear = :year')
            ->andWhere('vc.vehicleSeries = :series')
            ->andWhere($rule)
            ->orderBy('vc.vehicleConfigurationKey', 'ASC')
            ->addOrderBy('vc.vehicleConfigurationId', 'ASC')
            ->addOrderBy('svc.subVehicleConfigurationName', 'ASC')
            ->setParameters($parameters)
            ->groupBy('svc.subVehicleConfigurationId')
            ->addGroupBy('vc.vehicleConfigurationId')
            ->getQuery()
            ->getResult();
    }

    public function findByTypeYearSeriesDraftDetection(
        string $type,
        int $year,
        string $series,
        string $customerKey = null)
    : array
    {
        if (is_null($customerKey)) {
            $rule = $this->createQueryBuilder('svc')->expr()->isNull('vc.vehicleCustomerKey');
            $parameters = [
                'type' => $type,
                'year' => $year,
                'series' => $series,
            ];
        } else {
            $rule = 'vc.vehicleCustomerKey = :customerKey';
            $parameters = [
                'type' => $type,
                'year' => $year,
                'series' => $series,
                'customerKey' => $customerKey
            ];
        }

        $parameters = array_merge($parameters, [
            ':d' => 'D',
            ':b' => 'B',
            'year16' => 16,
            'series3' => 3
        ]);

        return $this->createQueryBuilder('svc')
            ->select('vc.vehicleConfigurationId, vc.vehicleConfigurationKey,
            svc.subVehicleConfigurationId, svc.subVehicleConfigurationName')
            ->addSelect('vc.vehicleTypeName, vc.vehicleTypeYear, vc.vehicleSeries')
            ->addSelect('COUNT(v.vehicleId) as vehiclesCount')
            ->addSelect('CASE
                    WHEN vc.vehicleTypeName = :d OR vc.vehicleTypeName = :b THEN
                        CASE 
                            WHEN vc.vehicleTypeYear < :year16 THEN false
                            WHEN vc.vehicleTypeYear = :year16 THEN
                                CASE 
                                    WHEN vc.vehicleSeries < :series3 THEN false
                                    ELSE vc.draft
                                END
                            ELSE vc.draft
                         END
                         ELSE vc.draft
                 END as configuration_draft')
            ->addSelect('CASE
                    WHEN vc.vehicleTypeName = :d OR vc.vehicleTypeName = :b THEN
                        CASE 
                            WHEN vc.vehicleTypeYear < :year16 THEN false
                            WHEN vc.vehicleTypeYear = :year16 THEN
                                CASE 
                                    WHEN vc.vehicleSeries < :series3 THEN false
                                    ELSE svc.draft
                                END
                            ELSE svc.draft
                         END
                         ELSE svc.draft
                 END as sub_configuration_draft')
            ->join('App:VehicleConfigurations', 'vc', 'WITH',
                'vc.vehicleConfigurationId = svc.vehicleConfiguration')
            ->leftJoin('App:Vehicles', 'v', 'WITH',
                'svc.subVehicleConfigurationId = v.subVehicleConfiguration')
            ->where('vc.vehicleTypeName = :type')
            ->andWhere('vc.vehicleTypeYear = :year')
            ->andWhere('vc.vehicleSeries = :series')
            ->andWhere($rule)
            ->orderBy('vc.vehicleConfigurationKey', 'ASC')
            ->addOrderBy('vc.vehicleConfigurationId', 'ASC')
            ->addOrderBy('svc.subVehicleConfigurationName', 'ASC')
            ->setParameters($parameters)
            ->groupBy('svc.subVehicleConfigurationId')
            ->addGroupBy('vc.vehicleConfigurationId')
            ->getQuery()
            ->getResult();
    }

    public function findByConfigurationName(string $configuration): array
    {
        return $this->createQueryBuilder('svc')
            ->select('vc.vehicleConfigurationId, vc.vehicleConfigurationKey,
            svc.subVehicleConfigurationId, svc.subVehicleConfigurationName')
            ->addSelect('COUNT(v.vehicleId) as vehiclesCount')
            ->join('App:VehicleConfigurations', 'vc', 'WITH',
                'vc.vehicleConfigurationId = svc.vehicleConfiguration')
            ->leftJoin('App:Vehicles', 'v', 'WITH',
                'svc.subVehicleConfigurationId = v.subVehicleConfiguration')
            ->where('REGEX(svc.subVehicleConfigurationName, :regex) = true')
            ->orderBy('vc.vehicleConfigurationKey', 'ASC')
            ->addOrderBy('vc.vehicleConfigurationId', 'ASC')
            ->addOrderBy('svc.subVehicleConfigurationName', 'ASC')
            ->setParameter('regex', $configuration)
            ->groupBy('svc.subVehicleConfigurationId')
            ->addGroupBy('vc.vehicleConfigurationId')
            ->getQuery()
            ->getResult();

    }

    public function findByVinNumber(string $vinNumber): array
    {
        $result = $this->createQueryBuilder('svc')
            ->select('vc.vehicleConfigurationId, vc.vehicleConfigurationKey,
            svc.subVehicleConfigurationId, svc.subVehicleConfigurationName')
            ->addSelect('COUNT(v.vehicleId) as vehiclesCount')
            ->join('App:VehicleConfigurations', 'vc', 'WITH',
                'vc.vehicleConfigurationId = svc.vehicleConfiguration')
            ->leftJoin('App:Vehicles', 'v', 'WITH',
                'svc.subVehicleConfigurationId = v.subVehicleConfiguration')
            ->where('REGEX(v.vin, :regex) = true')
            ->orderBy('vc.vehicleConfigurationKey', 'ASC')
            ->addOrderBy('vc.vehicleConfigurationId', 'ASC')
            ->addOrderBy('svc.subVehicleConfigurationName', 'ASC')
            ->setParameter('regex', $vinNumber)
            ->groupBy('svc.subVehicleConfigurationId')
            ->addGroupBy('vc.vehicleConfigurationId')
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function findByLocation(string $location): array
    {
        $result = $this->createQueryBuilder('svc')
            ->select('vc.vehicleConfigurationId, vc.vehicleConfigurationKey,
            svc.subVehicleConfigurationId, svc.subVehicleConfigurationName')
            ->addSelect('COUNT(v.vehicleId) as vehiclesCount')

            ->join('App:VehicleConfigurations', 'vc', 'WITH',
                'vc.vehicleConfigurationId = svc.vehicleConfiguration')
            ->join('App:Vehicles', 'v', 'WITH',
                'svc.subVehicleConfigurationId = v.subVehicleConfiguration')
            ->join('App:Depots', 'd','WITH', 'v.depot = d.depotId')

            ->where('REGEX(d.name, :regex) = true')
            ->andWhere('d.depotType = 1')

            ->orderBy('vc.vehicleConfigurationKey', 'ASC')
            ->addOrderBy('vc.vehicleConfigurationId', 'ASC')
            ->addOrderBy('svc.subVehicleConfigurationName', 'ASC')

            ->setParameter('regex', $location)
            ->groupBy('svc.subVehicleConfigurationId')
            ->addGroupBy('vc.vehicleConfigurationId')
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function findByLicencePlate(string $licencePlate): array
    {
        $result = $this->createQueryBuilder('svc')
            ->select('vc.vehicleConfigurationId, vc.vehicleConfigurationKey,
            svc.subVehicleConfigurationId, svc.subVehicleConfigurationName')
            ->addSelect('COUNT(v.vehicleId) as vehiclesCount')
            ->join('App:VehicleConfigurations', 'vc', 'WITH',
                'vc.vehicleConfigurationId = svc.vehicleConfiguration')
            ->join('App:Vehicles', 'v', 'WITH',
                'svc.subVehicleConfigurationId = v.subVehicleConfiguration')

            ->where('REGEX(v.code, :regex) = true')

            ->orderBy('vc.vehicleConfigurationKey', 'ASC')
            ->addOrderBy('vc.vehicleConfigurationId', 'ASC')
            ->addOrderBy('svc.subVehicleConfigurationName', 'ASC')
            ->setParameter('regex', $licencePlate)
            ->groupBy('svc.subVehicleConfigurationId')
            ->addGroupBy('vc.vehicleConfigurationId')
            ->getQuery()
            ->getResult();
        return $result;
    }

    public function findAllConfigurations(): array
    {
        $queryBuilder = $this->createQueryBuilder('svc');

        return $queryBuilder
            ->select('vc.vehicleConfigurationId, vc.vehicleConfigurationKey,
            svc.subVehicleConfigurationId, svc.subVehicleConfigurationName')
            ->join('App:VehicleConfigurations', 'vc', 'WITH',
                'vc.vehicleConfigurationId = svc.vehicleConfiguration')
            ->orderBy('vc.vehicleConfigurationKey', 'ASC')
            ->addOrderBy('vc.vehicleConfigurationId', 'ASC')
            ->addOrderBy('svc.subVehicleConfigurationName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getTypeSeriesYear($id)
    {
        $qb = $this->createQueryBuilder('svc');

        $result = $qb
            ->select('vc.vehicleTypeName as type, vc.vehicleTypeYear as year, vc.vehicleSeries as series')
            ->addSelect("vc.vehicleCustomerKey as customer_key")
            ->leftJoin('App:VehicleConfigurations', 'vc', 'WITH', 'svc.vehicleConfiguration = vc.vehicleConfigurationId')
            ->where('svc.subVehicleConfigurationId = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult();

        return $result;
    }

    public function getAssignedParameters($id)
    {
        $qb = $this->createQueryBuilder('svc');

        $result = $qb
            ->select('asy.symbol, vcp.vehicleConfigurationPropertyName, vcps.description, vcp.vcPropertyId, vckl.positionInVehicleConfigurationKey')
            ->join('App:VehicleConfigurations', 'vc', 'WITH', 'svc.vehicleConfiguration = vc.vehicleConfigurationId')
            ->join('App:VehicleConfigurationPropertiesMapping', 'vcpm', 'WITH', 'svc.vehicleConfiguration = vcpm.vehicleConfiguration')
            ->join('App:AllowedSymbols', 'asy', 'WITH', 'vcpm.allowedSymbols = asy.allowedSymbolsId')
            ->join('App:VehicleConfigurationProperties', 'vcp', 'WITH', 'vcpm.vcProperty = vcp.vcPropertyId')
            ->join('App:VehicleConfigurationPropertiesSymbols', 'vcps', 'WITH', '(vcpm.vcProperty = vcps.vcProperty) AND (vcpm.allowedSymbols = vcps.allowedSymbols)')
            //todo: Remove Left Join, Replace with Inner Join
            ->leftjoin('App:VehicleConfigurationKeyLogic', 'vckl', 'WITH', 'vckl.vcProperty = vcp.vcPropertyId')
            ->where('svc.subVehicleConfigurationId = :id')
//            ->where('vcpm.vehicleConfiguration = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function getVehicleConfigurationKey($id)
    {
        $qb = $this->createQueryBuilder('svc');

        $result = $qb
            ->select('vc.vehicleConfigurationKey')
            ->join('App:VehicleConfigurations', 'vc', 'WITH', 'svc.vehicleConfiguration = vc.vehicleConfigurationId')
            ->where('svc.subVehicleConfigurationId = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult();

        return $result;
    }

    public function getReleaseState($id)
    {
        $qb = $this->createQueryBuilder('svc');

        $result = $qb
            ->select('vcs.vehicleConfigurationStateName as releaseStatus')
            ->join('App:VehicleConfigurationState', 'vcs', 'WITH', 'svc.vehicleConfigurationState = vcs.vehicleConfigurationStateId')
            ->where('svc.subVehicleConfigurationId = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult();

        return $result;
    }

    public function getDefProductionLocation(int $id): array
    {
        $qb = $this->createQueryBuilder('svc');

        $result = $qb
            ->select('dp.name, dp.address')
            ->join('App:VehicleConfigurations', 'vc', 'WITH', 'svc.vehicleConfiguration = vc.vehicleConfigurationId')
            ->join('App:Depots', 'dp', 'WITH', 'vc.defaultProductionLocation = dp.depotId')
            ->where('svc.subVehicleConfigurationId = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult();

        return $result;
    }

    public function getAdditionalFeatures(int $id)
    {
        $qb = $this->createQueryBuilder('svc');

        $result = $qb
            ->select('svpm.svpmId as svpmid, svp.specialVehiclePropertyName, vt.variableTypeName,  svpv.valueBool, svpv.valueString, svpv.valueInteger')
            ->addSelect("CASE
                WHEN svp.specialVehiclePropertyId = 8 THEN 1
                WHEN svp.specialVehiclePropertyId = 9 THEN 2
                WHEN svp.specialVehiclePropertyId = 10 THEN 3
                WHEN svp.specialVehiclePropertyId = 11 THEN 4
                WHEN svp.specialVehiclePropertyId = 12 THEN 5
                WHEN svp.specialVehiclePropertyId = 6 THEN 6
                WHEN svp.specialVehiclePropertyId = 7 THEN 7
            ELSE 0
            END as new_order")
            ->join('App:SpecialVehiclePropertiesMapping', 'svpm', 'WITH', 'svc.subVehicleConfigurationId = svpm.subVehicleConfiguration')
            ->join('App:SpecialVehicleProperties', 'svp', 'WITH', 'svpm.specialVehicleProperty = svp.specialVehiclePropertyId')
            ->join('App:SpecialVehiclePropertyValues', 'svpv', 'WITH', 'svpm.specialVehiclePropertyValue = svpv.svpvId')
            ->join('App:VariableTypes', 'vt', 'WITH', 'svp.variableType = vt.variableTypeId')
            ->where('svc.subVehicleConfigurationId = :id')
            ->andWhere('svpm.specialVehicleProperty in(6,7,8,9,10,11,12)')
            ->orderBy('new_order', 'ASC')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function getAdditionalComponents(int $id)
    {
        $qb = $this->createQueryBuilder('svc');

        $result = $qb
            ->select('svpm.svpmId as svpmid, svp.specialVehiclePropertyName, vt.variableTypeName,  svpv.valueBool, svpv.valueString, svpv.valueInteger, svpm.visibleOnReport')
            ->addSelect('IDENTITY(svpm.specialVehicleProperty) as mapping_order')
            ->join('App:SpecialVehiclePropertiesMapping', 'svpm', 'WITH', 'svc.subVehicleConfigurationId = svpm.subVehicleConfiguration')
            ->join('App:SpecialVehicleProperties', 'svp', 'WITH', 'svpm.specialVehicleProperty = svp.specialVehiclePropertyId')
            ->join('App:SpecialVehiclePropertyValues', 'svpv', 'WITH', 'svpm.specialVehiclePropertyValue = svpv.svpvId')
            ->join('App:VariableTypes', 'vt', 'WITH', 'svp.variableType = vt.variableTypeId')
            ->where('svc.subVehicleConfigurationId = :id')
            ->andWhere('svpm.specialVehicleProperty in(1,2,3,4,5)')
            ->orderBy('svp.specialVehiclePropertyId', 'ASC')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function getColorFromPenta(int $id)
    {
        $qb = $this->createQueryBuilder('svc');

        $result = $qb
            ->select('cc.configurationColorName')
            ->join('App:PentaVariants', 'pv', 'WITH', 'svc.subVehicleConfigurationId = pv.subVehicleConfiguration')
            ->join('App:ConfigurationColors', 'cc', 'WITH', 'pv.configurationColor = cc.configurationColorId')
            ->where('svc.subVehicleConfigurationId = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function getNumberOfCars(int $id)
    {
        return $this->createQueryBuilder('svc')
            ->select("COUNT(v.vehicleId) as number")
            ->join('App:Vehicles', 'v', 'WITH', 'svc.subVehicleConfigurationId = v.subVehicleConfiguration')
            ->where("svc.subVehicleConfigurationId = :id")
            ->setParameter('id', $id)
            ->groupBy('svc.subVehicleConfigurationId')
            ->getQuery()
            ->getOneOrNullResult();
    }


    public function getVehicleCount(int $id)
    {

        $sql = /** @lang SQL */
            "
WITH last_teo_status AS (
    SELECT DISTINCT ON (teo_vin)
        teo_vin AS vin,
        LAST_VALUE(processed_diagnose_status) OVER (PARTITION BY teo_vin ORDER BY diagnose_status_time ASC) AS last_status
    FROM
        processed_teo_status
)
(SELECT
    'Total' AS \"color\",
    COUNT(*) AS \"numberVehiclesCreated\",  --1.
    SUM((COALESCE(last_status, 'DEFECTIVE') <> 'DEFECTIVE')::INTEGER) AS \"vehicleEndOfLine\",  --3.
    SUM(COALESCE(delivery_status, FALSE)::INTEGER) AS \"qSApproved\", -- 4. also total delivered
    SUM((delivery_status AND delivery_date <= NOW() AND COALESCE(dp_division_id, 0) > 0)::INTEGER) + SUM((delivery_status AND division_id = 51)::INTEGER) AS \"totalDelivered\", --5a
    SUM((delivery_status AND delivery_date <= NOW() AND COALESCE(dp_division_id, 0) > 0)::INTEGER) AS \"deutschePostDelivered\", --5b
    SUM((delivery_status AND delivery_date <= NOW() AND division_id = 51)::INTEGER) AS \"3rdPartyCustomersDelivered\" --5c
FROM
    vehicles v
INNER JOIN
    penta_variants pv USING (penta_variant_id, sub_vehicle_configuration_id)
INNER JOIN
    sub_vehicle_configurations svc USING (sub_vehicle_configuration_id)
INNER JOIN
    configuration_colors cc USING (configuration_color_id)
INNER JOIN
    vehicles_sales vs USING (vehicle_id)
INNER JOIN
    depots de USING (depot_id)
INNER JOIN
    divisions di USING (division_id)
LEFT JOIN
    last_teo_status USING (vin)
WHERE
    sub_vehicle_configuration_id = :id
GROUP BY
    \"color\"
)
UNION
(SELECT
    cc.configuration_color_name AS \"color\",
    COUNT(*) AS \"numberVehiclesCreated\",  --1.
    SUM((COALESCE(last_status, 'DEFECTIVE') <> 'DEFECTIVE')::INTEGER) AS \"vehicleEndOfLine\",  --3.
    SUM(COALESCE(delivery_status, FALSE)::INTEGER) AS \"qSApproved\", -- 4. also total delivered
    SUM((delivery_status AND delivery_date <= NOW() AND COALESCE(dp_division_id, 0) > 0)::INTEGER) + SUM((delivery_status AND division_id = 51)::INTEGER) AS \"totalDelivered\", --5a
    SUM((delivery_status AND delivery_date <= NOW() AND COALESCE(dp_division_id, 0) > 0)::INTEGER) AS \"deutschePostDelivered\", --5b
    SUM((delivery_status AND delivery_date <= NOW() AND division_id = 51)::INTEGER) AS \"3rdPartyCustomersDelivered\" --5c
FROM
    vehicles v
INNER JOIN
    penta_variants pv USING (penta_variant_id, sub_vehicle_configuration_id)
INNER JOIN
    sub_vehicle_configurations svc USING (sub_vehicle_configuration_id)
INNER JOIN
    configuration_colors cc USING (configuration_color_id)
INNER JOIN
    vehicles_sales vs USING (vehicle_id)
INNER JOIN
    depots de USING (depot_id)
INNER JOIN
    divisions di USING (division_id)
LEFT JOIN
    last_teo_status USING (vin)
WHERE
    sub_vehicle_configuration_id = :id
GROUP BY
    \"color\"
)
ORDER BY \"numberVehiclesCreated\" DESC;
";

        $params = ['id' => $id];
        $result = $this->getEntityManager()->getConnection()->executeQuery($sql, $params)->fetchAll();

        return $result;
    }
}
