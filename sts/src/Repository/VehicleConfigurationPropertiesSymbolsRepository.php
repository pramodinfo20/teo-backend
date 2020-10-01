<?php

namespace App\Repository;

use App\Entity\VehicleConfigurationPropertiesSymbols;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method VehicleConfigurationPropertiesSymbols|null find($id, $lockMode = null, $lockVersion = null)
 * @method VehicleConfigurationPropertiesSymbols|null findOneBy(array $criteria, array $orderBy = null)
 * @method VehicleConfigurationPropertiesSymbols[]    findAll()
 * @method VehicleConfigurationPropertiesSymbols[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehicleConfigurationPropertiesSymbolsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, VehicleConfigurationPropertiesSymbols::class);
    }


    public function getOptionsForProperty($property)
    {
        $qb = $this->createQueryBuilder('vcps');

        $queryResult = $qb
            ->select('vcps.description, asy.symbol')
            ->join('App:AllowedSymbols', 'asy', 'WITH', 'vcps.allowedSymbols = asy.allowedSymbolsId')
            ->where('vcps.vcProperty = :propertyId')
            ->setParameter('propertyId', $property)
            ->getQuery()
            ->getResult();

        return $queryResult;
    }
}
