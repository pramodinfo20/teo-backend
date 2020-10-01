<?php

namespace App\Repository;

use App\Entity\EcuSubConfigurationVehicleContainment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EcuSubConfigurationVehicleContainment|null find($id, $lockMode = null, $lockVersion = null)
 * @method EcuSubConfigurationVehicleContainment|null findOneBy(array $criteria, array $orderBy = null)
 * @method EcuSubConfigurationVehicleContainment[]    findAll()
 * @method EcuSubConfigurationVehicleContainment[]    findBy(array $criteria, array $orderBy = null, $limit = null,
 *         $offset = null)
 */
class EcuSubConfigurationVehicleContainmentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EcuSubConfigurationVehicleContainment::class);
    }

    public function findAllEcusByConfigurationById(int $id): array
    {
        return $this->createQueryBuilder('conf')
            ->select('identity(conf.subVehicleConfiguration) as subVehicleConfiguration, identity(conf.ceEcu) as ecu, ecus.ceEcuId, ecus.ecuName')
            ->join('App:ConfigurationEcus', 'ecus', 'WITH', 'ecus.ceEcuId = conf.ceEcu')
            ->where('conf.subVehicleConfiguration = :id')
            ->orderBy('ecus.ecuName', 'ASC')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }
}
