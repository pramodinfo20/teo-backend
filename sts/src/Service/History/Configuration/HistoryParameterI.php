<?php

namespace App\Service\History\Configuration;

use App\Entity\EcuSwVersions;
use App\Entity\SubVehicleConfigurations;
use App\Model\Configuration\OdxCollection;
use Symfony\Component\Config\Definition\Exception\Exception;

interface HistoryParameterI
{
    /**
     * @param OdxCollection            $collection
     * @param EcuSwVersions            $sw
     * @param SubVehicleConfigurations $subConf
     * @param int                      $odx
     *
     * @throws Exception
     */
    public function save(
        OdxCollection $collection,
        EcuSwVersions $sw,
        SubVehicleConfigurations $subConf,
        int $odx
    ): void;
}