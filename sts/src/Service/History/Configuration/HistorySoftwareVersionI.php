<?php

namespace App\Service\History\Configuration;

use App\Entity\ConfigurationEcus;
use App\Entity\EcuSwVersions;
use App\Entity\SubVehicleConfigurations;

interface HistorySoftwareVersionI
{
    /**
     * Assign Sw to SubConfiguration
     *
     * @param SubVehicleConfigurations $subconfiguration
     * @param EcuSwVersions            $sw
     * @param ConfigurationEcus        $ecu
     * @param bool                     $primary
     *
     * @return void
     */
    public function assignSw(
        SubVehicleConfigurations $subconfiguration,
        EcuSwVersions $sw,
        ConfigurationEcus $ecu,
        bool $primary
    ): void;

    /**
     * Remove Sw assignment
     *
     * @param SubVehicleConfigurations $subconfiguration
     * @param ConfigurationEcus        $ecu
     * @param bool                     $primary
     *
     * @return EcuSwVersions|null
     */
    public function removeSwAssignment(
        SubVehicleConfigurations $subconfiguration,
        ConfigurationEcus $ecu,
        bool $primary
    ): ?EcuSwVersions;
}