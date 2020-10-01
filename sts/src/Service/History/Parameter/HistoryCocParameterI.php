<?php

namespace App\Service\History\Parameter;

use App\Entity\CocParameterRelease;
use App\Entity\SubVehicleConfigurations;
use App\Model\Parameter\CocCollection;

interface HistoryCocParameterI
{
    /**
     * Save a non-entity data from form with transactions
     *
     * @param CocCollection    $cocCollection
     * @param SubVehicleConfigurations $subVehicleConfigurations
     *
     * @throws \Exception
     */
    public function save(CocCollection $cocCollection, SubVehicleConfigurations $subVehicleConfigurations): void;

    /**
     * Save  entity data from form with transactions
     *
     * @param CocParameterRelease    $cocParameterRelease
     *
     * @throws \Exception
     */
    public function saveCoCReleased(CocParameterRelease $cocParameterRelease): void;
}