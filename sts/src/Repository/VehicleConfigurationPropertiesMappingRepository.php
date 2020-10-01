<?php

namespace App\Repository;

use App\Entity\VehicleConfigurationPropertiesMapping;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method VehicleConfigurationPropertiesMapping|null find($id, $lockMode = null, $lockVersion = null)
 * @method VehicleConfigurationPropertiesMapping|null findOneBy(array $criteria, array $orderBy = null)
 * @method VehicleConfigurationPropertiesMapping[]    findAll()
 * @method VehicleConfigurationPropertiesMapping[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehicleConfigurationPropertiesMappingRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, VehicleConfigurationPropertiesMapping::class);
    }

}
