<?php

namespace App\Service\History\Vehicles\Configuration;

use App\Entity\VehicleConfigurations;
use App\Model\LongKeyModel;
use App\Model\ShortKeyModel;
use App\Model\Vehicles\Configuration\ConfigurationSearch;

interface HistoryConfigurationsI
{
    /**
     * @param ShortKeyModel         $shortKeyModel
     * @param VehicleConfigurations $configuration
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveFixShortKey(
        ShortKeyModel $shortKeyModel,
        VehicleConfigurations $configuration
    ) : ConfigurationSearch;

    /**
     * @param LongKeyModel          $longKeyModel
     * @param VehicleConfigurations $configuration
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveFixLongKey(
        LongKeyModel $longKeyModel,
        VehicleConfigurations $configuration
    ) : ConfigurationSearch;

    /**
     * @param ShortKeyModel         $shortKeyModel
     * @param VehicleConfigurations $configuration
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveEditionShortKey(
        ShortKeyModel $shortKeyModel,
        VehicleConfigurations $configuration
    ) : ConfigurationSearch;

    /**
     * @param LongKeyModel          $longKeyModel
     * @param VehicleConfigurations $configuration
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveEditionLongKey(
        LongKeyModel $longKeyModel,
        VehicleConfigurations $configuration
    ) : ConfigurationSearch;

    /**
     * @param VehicleConfigurations $configuration
     *
     * @return bool
     * @throws \Exception
     */
    public function deleteConfiguration(VehicleConfigurations $configuration) : bool;
}