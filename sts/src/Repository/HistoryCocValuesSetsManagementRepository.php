<?php

namespace App\Repository;

use App\Entity\HistoryCocValuesSetsManagement;
use App\Repository\History\HistoryRepositoryI;
use App\Repository\History\HistoryRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method HistoryCocValuesSetsManagement|null find($id, $lockMode = null, $lockVersion = null)
 * @method HistoryCocValuesSetsManagement|null findOneBy(array $criteria, array $orderBy = null)
 * @method HistoryCocValuesSetsManagement[]    findAll()
 * @method HistoryCocValuesSetsManagement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistoryCocValuesSetsManagementRepository extends ServiceEntityRepository implements HistoryRepositoryI
{
    use HistoryRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoryCocValuesSetsManagement::class);
    }

    protected function _getHistoricalRepository()
    {
        return $this;
    }
}
