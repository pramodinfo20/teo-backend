<?php

namespace App\Repository;

use App\Entity\ConfigurationEcus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ConfigurationEcus|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConfigurationEcus|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConfigurationEcus[]    findAll()
 * @method ConfigurationEcus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfigurationEcusRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ConfigurationEcus::class);
    }

    public function getEcuList()
    {
        $queryBuilder = $this->createQueryBuilder('ce');

        return $queryBuilder
            ->select('ce.ceEcuId as id, ce.ecuName as name')
            ->getQuery()
            ->getResult();
    }

    public function getEcuSwList()
    {
        $queryBuilder = $this->createQueryBuilder('ce');

        $result = $queryBuilder
            ->select('ce.ceEcuId, ce.ecuName')
            ->addSelect('esv.ecuSwVersionId, esv.swVersion')
            ->addSelect('esv.suffixIfIsSubEcuSwVersion as suffix')
            ->join('App:EcuSwVersions', 'esv', Join::WITH, 'ce.ceEcuId = esv.ceEcu')
            ->where('esv.releaseStatus != :status')
            ->setParameter('status', 2)
            ->getQuery()
            ->getResult();

        return $result;
    }
}
