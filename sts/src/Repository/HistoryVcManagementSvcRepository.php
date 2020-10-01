<?php

namespace App\Repository;

use App\Entity\HistoryVcManagementSvc;
use App\Repository\History\HistoryRepositoryI;
use App\Repository\History\HistoryRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method HistoryVcManagementSvc|null find($id, $lockMode = null, $lockVersion = null)
 * @method HistoryVcManagementSvc|null findOneBy(array $criteria, array $orderBy = null)
 * @method HistoryVcManagementSvc[]    findAll()
 * @method HistoryVcManagementSvc[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistoryVcManagementSvcRepository extends ServiceEntityRepository implements HistoryRepositoryI
{
    use HistoryRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoryVcManagementSvc::class);
    }

    protected function _getHistoricalRepository()
    {
        return $this;
    }
}
