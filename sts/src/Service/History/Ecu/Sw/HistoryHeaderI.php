<?php

namespace App\Service\History\Ecu\Sw;

use App\Model\Header as HeaderModel;

interface HistoryHeaderI
{
    /**
     * Save a non-entity data from form with transactions
     *
     * @param HeaderModel $header
     *
     * @throws \Exception
     */
    public function save(HeaderModel $header): void;
}