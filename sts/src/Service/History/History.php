<?php

namespace App\Service\History;

use App\History\Strategies\HistoryStrategyI;
use App\Model\History\Search\HistorySelector;
use App\Service\AbstractService;

class History extends AbstractService
{
    /**
    *  Get Search results.
    *
    * @param Historyselector $selector
    * @param HistoryStrategyI $strategy
    *
    * @return array
    */
    public function getSearchResults(HistorySelector $selector, HistoryStrategyI $strategy): array
    {
        $filters = $selector->getFilters();

        $createdFrom = (in_array(0, $filters)) ? $selector->getCreatedFrom() : null;
        $createdTo = (in_array(0, $filters)) ? $selector->getCreatedTo() : null;
        $createdBy = (in_array(1, $filters)) ? $selector->getCreatedBy() : null;
        $comment = (in_array(2, $filters)) ? $selector->getComment() : null;
        $event = (in_array(3, $filters)) ? $selector->getEvent() : null;
        $fk = (in_array(4, $filters)) ? null :  $selector->getFk();

        return $this->manager->getRepository($strategy->getTableName())->getSearchResults(
                $createdFrom, $createdTo, $createdBy, $comment, $event, $fk
            );
    }
}