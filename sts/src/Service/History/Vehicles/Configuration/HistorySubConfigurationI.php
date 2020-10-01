<?php

namespace App\Service\History\Vehicles\Configuration;

use App\Entity\SubVehicleConfigurations;
use App\Entity\Users;
use App\Model\LongKeyModel;
use App\Model\ShortKeyModel;
use App\Model\Vehicles\Configuration\ConfigurationSearch;

interface HistorySubConfigurationI
{
    /**
     * @param ShortKeyModel $shortKeyModel
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveShortKey(ShortKeyModel $shortKeyModel): ConfigurationSearch;

    /**
     * @param LongKeyModel $longKeyModel
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveLongKey(LongKeyModel $longKeyModel): ConfigurationSearch;

    /**
     * @param ShortKeyModel $shortKeyModel
     * @param Users         $user
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveEditionShortKey(ShortKeyModel $shortKeyModel, Users $user): ConfigurationSearch;

    /**
     * @param LongKeyModel $longKeyModel
     * @param Users        $user
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveEditionLongKey(LongKeyModel $longKeyModel, Users $user): ConfigurationSearch;

    /**
     * @param ShortKeyModel $shortKeyModel
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveFixShortKey(ShortKeyModel $shortKeyModel): ConfigurationSearch;

    /**
     * @param LongKeyModel $longKeyModel
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveFixLongKey(LongKeyModel $longKeyModel): ConfigurationSearch;

    /**
     * @param SubVehicleConfigurations $subconfiguration
     *
     * @return bool
     * @throws \Exception
     */
    public function deleteSubConfiguration(SubVehicleConfigurations $subconfiguration) : bool;

}