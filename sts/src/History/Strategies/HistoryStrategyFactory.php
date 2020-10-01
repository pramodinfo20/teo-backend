<?php

namespace App\History\Strategies;

use App\Enum\HistoryTypes;
use App\Service\AbstractService;

final class HistoryStrategyFactory extends AbstractService
{
    public function getHistoryStrategy(int $type) : HistoryStrategy
    {
        try {
            switch ($type) {
                case HistoryTypes::ECU_PARAMETER_MANAGEMENT:
                    return new EcuParameterManagementStrategy($this->manager, $this->entityManager);
                    break;
                case HistoryTypes::VEHICLE_CONFIGURATION_SVC_MANAGEMENT:
                    return new VehicleConfigurationSvcManagementStrategy($this->manager, $this->entityManager);
                    break;
                case HistoryTypes::VEHICLE_CONFIGURATION_VC_MANAGEMENT:
                    return new VehicleConfigurationVcManagementStrategy($this->manager, $this->entityManager);
                    break;
                case HistoryTypes::COC_VALUES_SETS_ASSIGNMENT:
                    return new CocValuesSetsManagementStrategy($this->manager, $this->entityManager);
                    break;
                case HistoryTypes::SOFTWARE_MANAGEMENT:
                    return new SoftwareManagementStrategy($this->manager, $this->entityManager);
                    break;
                default:
                    throw new \Exception("Could not create history strategy");
                    break;
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}