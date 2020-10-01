<?php

namespace App\Model\History\Traits;

trait HistoryEvent
{
    /**
     * @var int
     */
    private $historyEvent;

    /**
     * @return int
     */
    public function getHistoryEvent(): ?int
    {
        return $this->historyEvent;
    }

    /**
     * @param int $historyEvent
     *
     */
    public function setHistoryEvent(int $historyEvent = null)
    {
        $this->historyEvent = $historyEvent;
    }
}