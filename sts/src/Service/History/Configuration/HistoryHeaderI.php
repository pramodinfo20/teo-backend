<?php

namespace App\Service\History\Configuration;

use App\Entity\ConfigurationEcus;
use App\Entity\SubVehicleConfigurations;
use App\Model\Configuration\Header as HeaderModel;

interface HistoryHeaderI
{
    /**
     * Save a non-entity data from form with transactions
     *
     * @param HeaderModel              $header
     * @param ConfigurationEcus        $ecu
     * @param SubVehicleConfigurations $subConf
     *
     * @throws \Exception
     */
    public function save(
        HeaderModel $header,
        ConfigurationEcus $ecu,
        SubVehicleConfigurations $subConf
    ): void;
}