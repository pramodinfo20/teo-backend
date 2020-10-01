<?php

namespace App\Repository;

use App\Entity\HistoryVcManagementVc;
use App\Repository\History\HistoryRepositoryI;
use App\Repository\History\HistoryRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method HistoryVcManagementVc|null find($id, $lockMode = null, $lockVersion = null)
 * @method HistoryVcManagementVc|null findOneBy(array $criteria, array $orderBy = null)
 * @method HistoryVcManagementVc[]    findAll()
 * @method HistoryVcManagementVc[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistoryVcManagementVcRepository extends ServiceEntityRepository implements HistoryRepositoryI
{
    use HistoryRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoryVcManagementVc::class);
    }

    protected function _getHistoricalRepository()
    {
        return $this;
    }
}
