<?php

namespace App\Repository\History;

trait HistoryRepositoryTrait
{
    abstract protected function _getHistoricalRepository();

    /**
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param int    $userId
     * @param string $comment
     * @param int    $event
     * @param int    $fk
     *
     * @return array Returns an array of History objects
     */
    public function getSearchResults(
        \DateTime $dateFrom = null,
        \DateTime $dateTo = null,
        int $userId = null,
        string $comment = null,
        int $event = null,
        int $fk = null
    ): array
    {
        $whereDate = '1 = 1';
        $whereUser = '1 = 1';
        $whereComment = '1 = 1';
        $whereEvent = '1 = 1';
        $whereFk = ' 1 = 1 ';
        $parameters = [];

        if (!is_null($dateFrom) && !is_null($dateTo)) {
            $dateFrom = $dateFrom->setTime(0,0,0)->format('Y-m-d  H:m:s.uP');
            $dateTo = $dateTo->setTime(23,59,59)->format('Y-m-d  H:m:s.uP');
            $whereDate = "h.createdAt >= :dateFrom AND h.createdAt <= :dateTo";
            $parameters = array_merge($parameters, [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo
            ]);
        } else if (is_null($dateFrom) && !is_null($dateTo)) {
            $dateTo = $dateTo->setTime(23,59,59)->format('Y-m-d H:m:s.uP');
            $whereDate = "h.createdAt <= :dateTo";
            $parameters = array_merge($parameters, [
                'dateTo' => $dateTo
            ]);
        } else if (!is_null($dateFrom) && is_null($dateTo)) {
            $dateFrom = $dateFrom->setTime(0,0,0)->format('Y-m-d H:m:s.uP');
            $whereDate = "h.createdAt >= :dateFrom";
            $parameters = array_merge($parameters, [
                'dateFrom' => $dateFrom
            ]);
        }

        if (!is_null($userId)) {
            $whereUser = "h.createdBy = :user";
            $parameters = array_merge($parameters, ['user' => $userId]);
        }

        if (!is_null($comment)) {
            $whereComment = "h.comment = :comment";
            $parameters = array_merge($parameters, ['comment' => $comment]);
        }

        if (!is_null($event)) {
            $whereEvent = "h.event = :event";
            $parameters = array_merge($parameters, ['event' => $event]);
        }

        if (!is_null($fk)) {
            $whereFk = "h.fkId = :fk";
            $parameters = array_merge($parameters, ['fk' => $fk]);
        }

        return $this->_getHistoricalRepository()->createQueryBuilder('h')
            ->andWhere($whereDate)
            ->andWhere($whereUser)
            ->andWhere($whereComment)
            ->andWhere($whereEvent)
            ->andWhere($whereFk)
            ->setParameters($parameters)
            ->orderBy('h.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getMetaDataById(int $id)
    {
        return $this->_getHistoricalRepository()->createQueryBuilder('h')
            ->andWhere('h.hId = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult();
    }
}
