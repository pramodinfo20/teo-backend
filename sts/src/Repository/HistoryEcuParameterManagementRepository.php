<?php

namespace App\Repository;

use App\Entity\HistoryEcuParameterManagement;
use App\Repository\History\HistoryRepositoryI;
use App\Repository\History\HistoryRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method HistoryEcuParameterManagement|null find($id, $lockMode = null, $lockVersion = null)
 * @method HistoryEcuParameterManagement|null findOneBy(array $criteria, array $orderBy = null)
 * @method HistoryEcuParameterManagement[]    findAll()
 * @method HistoryEcuParameterManagement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistoryEcuParameterManagementRepository extends ServiceEntityRepository implements HistoryRepositoryI
{
    use HistoryRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoryEcuParameterManagement::class);
    }

    protected function _getHistoricalRepository()
    {
        return $this;
    }
}
