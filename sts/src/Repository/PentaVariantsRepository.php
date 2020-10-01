<?php

namespace App\Repository;

use App\Entity\PentaVariants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PentaVariants|null find($id, $lockMode = null, $lockVersion = null)
 * @method PentaVariants|null findOneBy(array $criteria, array $orderBy = null)
 * @method PentaVariants[]    findAll()
 * @method PentaVariants[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PentaVariantsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PentaVariants::class);
    }


    public function getAllProperties($pentaId)
    {
        $qb = $this->createQueryBuilder('pv');

        $resultList = $qb
//            ->select('svc.subVehicleConfigurationName, svc.subVehicleConfigurationId, pv.pentaVariantName, pv.pentaVariantId')
            ->select('*')
//            ->leftJoin('App:PentaVariants', 'pv', 'WITH', 'svc.subVehicleConfigurationId = pv.subVehicleConfiguration')
//            ->where($qb->expr()->like('svc.subVehicleConfigurationName', ':regex'))
//            ->setParameter('regex', '%' . $regexExpression . '%')
//            ->setParameter('regex', '%B%')
            ->getQuery()
            ->getResult();

        return $resultList;
    }
}
