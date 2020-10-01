<?php

namespace App\History\Creators;

use App\Enum\HistoryTypes;
use App\Model\History\HistoryType;

final class HistoryTypeStaticCreator
{
    public static function getHistoryType(int $type) : HistoryType
    {
        $object = new HistoryType();
        $object->setType($type);

        try {
            switch ($type) {
                case HistoryTypes::ECU_PARAMETER_MANAGEMENT:
                    $object->setEcuParameterManagement(true);
                    break;
                case HistoryTypes::VEHICLE_CONFIGURATION_SVC_MANAGEMENT:
                    $object->setVehicleConfigurationSvcManagement(true);
                    break;
                case HistoryTypes::VEHICLE_CONFIGURATION_VC_MANAGEMENT:
                    $object->setVehicleConfigurationVcManagement(true);
                    break;
                case HistoryTypes::COC_VALUES_SETS_ASSIGNMENT:
                    $object->setCocValuesSetsAssignment(true);
                    break;
                case HistoryTypes::SOFTWARE_MANAGEMENT:
                    $object->setSoftwareManagement(true);
                    break;
                default:
                    throw new \Exception("Could not create HistoryType object");
                    break;
            }
        } catch (\Exception $exception) {
            throw $exception;
        }

        return $object;
    }
}