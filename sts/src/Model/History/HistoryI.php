<?php

namespace App\Model\History;

interface HistoryI
{
    public function getHistoryEvent() : ?int;
    public function setHistoryEvent(int $historyEvent = null);
}