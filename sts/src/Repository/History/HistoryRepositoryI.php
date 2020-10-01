<?php

namespace App\Repository\History;

interface HistoryRepositoryI
{
    public function getSearchResults(
        \DateTime $dateFrom = null,
        \DateTime $dateTo = null,
        int $userId = null,
        string $comment = null,
        int $event = null
    ) : array;

    public function getMetaDataById(int $id);
}