<?php

namespace App\Repository;

use App\Entity\VehicleConfigurations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method VehicleConfigurations|null find($id, $lockMode = null, $lockVersion = null)
 * @method VehicleConfigurations|null findOneBy(array $criteria, array $orderBy = null)
 * @method VehicleConfigurations[]    findAll()
 * @method VehicleConfigurations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehicleConfigurationsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, VehicleConfigurations::class);
    }


    public function findVehicleTypes(): array
    {
        $queryBuilder = $this->createQueryBuilder('vc');

        return $queryBuilder
            ->select('vc.vehicleTypeName as vehicleConfigurationKey')
            ->orderBy('vehicleConfigurationKey', 'ASC')
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    public function findVehicleYears(string $type): array
    {
        $queryBuilder = $this->createQueryBuilder('vc');

        return $queryBuilder
            ->select("CASE
            WHEN vc.vehicleTypeYear < 10 THEN CONCAT('0', vc.vehicleTypeYear)
            ELSE CONCAT('', vc.vehicleTypeYear)
            END as vehicleConfigurationKey")
            ->where('vc.vehicleTypeName = :type')
            ->setParameter('type', $type)
            ->orderBy('vehicleConfigurationKey', 'ASC')
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    public function findVehicleSeries(string $type, int $year): array
    {
        $queryBuilder = $this->createQueryBuilder('vc');
        return $queryBuilder
            ->select("vc.vehicleSeries as vehicleConfigurationKey")
            ->addSelect("CASE WHEN vc.vehicleCustomerKey IS NOT NULL THEN vc.vehicleCustomerKey ELSE '' END as vehicleCustomerKey")
            ->addSelect('CASE WHEN vc.vehicleCustomerKey IS NULL THEN 1 ELSE 0 END as HIDDEN customerKeyIsNull')
            ->where("vc.vehicleTypeName = :type")
            ->andWhere('vc.vehicleTypeYear = :year')
            ->setParameter('type', $type)
            ->setParameter('year', $year)
            ->orderBy('vehicleConfigurationKey', 'ASC')
            ->addOrderBy('customerKeyIsNull', 'DESC')
            ->addOrderBy('vehicleCustomerKey', 'ASC')
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    public function findDuplicatedShortKey(string $key, int $confId)
    {
        return $this->createQueryBuilder('vc')
            ->select("vc.vehicleConfigurationId")
            ->where("vc.vehicleConfigurationKey = :key")
            ->andWhere("vc.vehicleConfigurationId != :id")
            ->setParameter("key", $key)
            ->setParameter("id", $confId)
            ->getQuery()
            ->getResult();
    }

    public function findDuplicatedInvalidShortKey(string $key)
    {
        return $this->createQueryBuilder('vc')
            ->select("vc.vehicleConfigurationId")
            ->where("vc.vehicleConfigurationKey = :key")
            ->andWhere("vc.draft = :t")
            ->setParameter("key", $key)
            ->setParameter("t", true)
            ->getQuery()
            ->getResult();
    }

    public function findDuplicatedLongKey(string $key, string $customerKey = null)
    {
        if (is_null($customerKey)) {
            $rule = $this->createQueryBuilder('vc')->expr()->isNotNull('vc.vehicleCustomerKey');
            $parameters = [
                'key' => $key,
            ];
        } else {
            $rule = 'vc.vehicleCustomerKey != :customerKey OR '.$this->createQueryBuilder('vc')->expr()->isNull('vc.vehicleCustomerKey');
            $parameters = [
                'key' => $key,
                'customerKey' => $customerKey,
            ];
        }

        return $this->createQueryBuilder('vc')
            ->select("vc.vehicleConfigurationId")
            ->where("vc.vehicleConfigurationKey = :key")
            ->andWhere($rule)
            ->setParameters($parameters)
            ->getQuery()
            ->getResult();
    }


    public function findDuplicatedInvalidLongKey(string $key, string $customerKey = null)
    {
        if (is_null($customerKey)) {
            $rule = $this->createQueryBuilder('vc')->expr()->isNull('vc.vehicleCustomerKey');
            $parameters = [
                'key' => $key,
                't' => true
            ];
        } else {
            $rule = 'vc.vehicleCustomerKey = :customerKey';
            $parameters = [
                'key' => $key,
                'customerKey' => $customerKey,
                't' => true
            ];
        }

         return $this->createQueryBuilder('vc')
            ->select("vc.vehicleConfigurationId")
            ->where("vc.vehicleConfigurationKey = :key")
            ->andWhere("vc.draft = :t")
            ->andWhere($rule)
            ->setParameters($parameters)
            ->getQuery()
            ->getResult();
    }

    public function findDuplicatedInvalidLongKeyToFix(int $confId, string $key, string $customerKey = null)
    {
        if (is_null($customerKey)) {
            $rule = $this->createQueryBuilder('vc')->expr()->isNull('vc.vehicleCustomerKey');
            $parameters = [
                'key' => $key,
                'configuration' => $confId,
            ];
        } else {
            $rule = 'vc.vehicleCustomerKey = :customerKey';
            $parameters = [
                'key' => $key,
                'configuration' => $confId,
                'customerKey' => $customerKey,
            ];
        }

        return $this->createQueryBuilder('vc')
            ->select("vc.vehicleConfigurationId")
            ->where("vc.vehicleConfigurationKey = :key")
//            ->andWhere("vc.draft = :t")
            ->andWhere('vc.vehicleConfigurationId != :configuration')
            ->andWhere($rule)
            ->setParameters($parameters)
            ->getQuery()
            ->getResult();
    }

    public function getVehicleConfigurationsInDevelopmentStatus(): array
    {
        $queryBuilder = $this->createQueryBuilder('vc');

        return $queryBuilder
            ->select('vc.vehicleTypeName, vc.vehicleTypeYear, vc.vehicleConfigurationKey, svc.subVehicleConfigurationName')
            ->addSelect("CASE 
                    WHEN vc.vehicleCustomerKey IS NOT NULL THEN CONCAT(vc.vehicleSeries, '_', vc.vehicleCustomerKey)
                    ELSE vc.vehicleSeries 
                END as vehicleSeries")
            ->addSelect('vc.vehicleConfigurationId, svc.subVehicleConfigurationId')
            ->join('App:SubVehicleConfigurations', 'svc', 'WITH', 'svc.vehicleConfiguration = vc.vehicleConfigurationId')
            ->join('App:VehicleConfigurationState', 'vcs', 'WITH', 'vcs.vehicleConfigurationStateId = svc.vehicleConfigurationState')
            ->where("vcs.vehicleConfigurationStateName = 'Under development'")
            ->orderBy('vc.vehicleTypeName', 'ASC')
            ->addOrderBy('vc.vehicleTypeYear', 'ASC')
            ->addOrderBy('vehicleSeries', 'ASC')
            ->addOrderBy('vc.vehicleConfigurationKey', 'ASC')
            ->addOrderBy('svc.subVehicleConfigurationName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getAssignedParameters($id) {
        $qb = $this->createQueryBuilder('vc');

        $result = $qb
            ->select('asy.symbol, vcp.vehicleConfigurationPropertyName, vcps.description, vcp.vcPropertyId, vckl.positionInVehicleConfigurationKey')
            ->join('App:VehicleConfigurationPropertiesMapping', 'vcpm', 'WITH', 'vc.vehicleConfigurationId = vcpm.vehicleConfiguration')
            ->join('App:AllowedSymbols', 'asy', 'WITH', 'vcpm.allowedSymbols = asy.allowedSymbolsId')
            ->join('App:VehicleConfigurationProperties', 'vcp', 'WITH', 'vcpm.vcProperty = vcp.vcPropertyId')
            ->join('App:VehicleConfigurationPropertiesSymbols', 'vcps', 'WITH', '(vcpm.vcProperty = vcps.vcProperty) AND (vcpm.allowedSymbols = vcps.allowedSymbols)')
            //todo: Remove Left Join, Replace with Inner Join
            ->leftjoin('App:VehicleConfigurationKeyLogic', 'vckl', 'WITH', 'vckl.vcProperty = vcp.vcPropertyId')
            ->where('vc.vehicleConfigurationId = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function getTypeSeriesYear($id) {
        $qb = $this->createQueryBuilder('vc');

        $result = $qb
            ->select('vc.vehicleTypeName as type, vc.vehicleTypeYear as year, vc.vehicleSeries as series')
            ->addSelect("vc.vehicleCustomerKey as customer_key")
            ->where('vc.vehicleConfigurationId = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult();

        return $result;
    }

    public function findOneVConfigurationByKeyAndCustomerKey(string $key, string $customerKey = null) {
        if (is_null($customerKey)) {
            $rule = $this->createQueryBuilder('vc')->expr()->isNull('vc.vehicleCustomerKey');
            $parameters = [
                'key' => $key,
            ];
        } else {
            $rule = 'vc.vehicleCustomerKey = :customerKey';
            $parameters = [
                'key' => $key,
                'customerKey' => $customerKey,
            ];
        }

        return $this->createQueryBuilder('vc')
            ->select("vc")
            ->where("vc.vehicleConfigurationKey = :key")
            ->andWhere($rule)
            ->setParameters($parameters)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getNumberOfCars(int $id) {
        return $this->createQueryBuilder('vc')
            ->select("COUNT(v.vehicleId) as number")
            ->join('App:SubVehicleConfigurations', 'sc', 'WITH', 'sc.vehicleConfiguration = vc.vehicleConfigurationId')
            ->join('App:Vehicles', 'v', 'WITH', 'sc.subVehicleConfigurationId = v.subVehicleConfiguration')
            ->where("vc.vehicleConfigurationId = :id")
            ->setParameter('id', $id)
            ->groupBy('vc.vehicleConfigurationId')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
