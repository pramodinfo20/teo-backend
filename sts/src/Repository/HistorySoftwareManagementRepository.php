<?php

namespace App\Repository;

use App\Entity\HistorySoftwareManagement;
use App\Repository\History\HistoryRepositoryI;
use App\Repository\History\HistoryRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method HistorySoftwareManagement|null find($id, $lockMode = null, $lockVersion = null)
 * @method HistorySoftwareManagement|null findOneBy(array $criteria, array $orderBy = null)
 * @method HistorySoftwareManagement[]    findAll()
 * @method HistorySoftwareManagement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistorySoftwareManagementRepository extends ServiceEntityRepository implements HistoryRepositoryI
{
    use HistoryRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistorySoftwareManagement::class);
    }

    protected function _getHistoricalRepository()
    {
        return $this;
    }
}
