<?php

namespace App\Repository;

use App\Entity\PentaNumbers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PentaNumbers|null find($id, $lockMode = null, $lockVersion = null)
 * @method PentaNumbers|null findOneBy(array $criteria, array $orderBy = null)
 * @method PentaNumbers[]    findAll()
 * @method PentaNumbers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PentaNumbersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PentaNumbers::class);
    }

    public function findMainConfigurationPentaNumber(int $vehicleVariantId)
    {
        return $this->createQueryBuilder('pn')
                    ->select('pn.pentaNumberId')
                    ->where('pn.vehicleVariant = :vehicleVariant')
                    ->andWhere('pn.pentaNumberId = pn.pentaConfig')
                    ->setParameter('vehicleVariant', $vehicleVariantId)
                    ->getQuery()
                    ->setMaxResults(1)
                    ->getSingleResult();
    }

}
