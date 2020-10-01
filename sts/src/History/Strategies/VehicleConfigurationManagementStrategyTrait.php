<?php


namespace App\History\Strategies;


use App\Entity\ConfigurationColors;
use App\Entity\ConfigurationEcus;
use App\Entity\Depots;
use App\Entity\ReleaseStatus;
use App\Entity\Users;
use App\Entity\VehicleConfigurationProperties;
use App\Entity\VehicleConfigurationPropertiesSymbols;
use Doctrine\Common\Collections\ArrayCollection;

trait VehicleConfigurationManagementStrategyTrait
{
    abstract protected function _getManager();

    /**
     * @return array
     */
    private function getAllOptions(): array
    {
        $vehicleCPSymbolsManager = $this->_getManager()->getRepository(VehicleConfigurationPropertiesSymbols::class);
        $vehiclePropertiesManager = $this->_getManager()->getRepository(VehicleConfigurationProperties::class);

        $options = new ArrayCollection($vehicleCPSymbolsManager->findAll());
        $properties = $vehiclePropertiesManager->findAll();

        $allOptionsMapped = [];
        foreach ($properties as $property) {
            $optionsByProperty = $options->filter(function ($option) use ($property)
            {
                return $option->getVcProperty()->getVcPropertyId() == $property->getVcPropertyId();
            });

            foreach ($optionsByProperty as $value) {
                $allOptionsMapped[$property->getVcPropertyId()][$value->getAllowedSymbols()->getAllowedSymbolsId()] =
                    " [ {$value->getAllowedSymbols()->getSymbol()} ] - " . "{$value->getDescription()}";
            }
        }

        return $allOptionsMapped;
    }

    /**
     * @return array
     */
    private function getStsProductionLocations(): array
    {
        $depots = $this->_getManager()->getRepository(Depots::class)->findBy(['depotType' => 1]);

        $stsProductionLocationsMapped = [];
        foreach ($depots as $depot) {
            $stsProductionLocationsMapped[$depot->getDepotId()] = $depot->getName();
        }

        return $stsProductionLocationsMapped;
    }

    /**
     * @return array
     */
    private function getConfigurationColors(): array
    {
        $colors = $this->_getManager()->getRepository(ConfigurationColors::class)->findAll();

        $configurationColorsMapped = [];
        foreach ($colors as $color) {
            $configurationColorsMapped[$color->getConfigurationColorId()] = $color->getConfigurationColorName();
        }

        return $configurationColorsMapped;
    }

    /**
     * @return array
     */
    private function getReleaseStates(): array
    {
        $releaseStates = $this->_getManager()->getRepository(ReleaseStatus::class)->findAll();

        $releaseStatesMapped = [];
        foreach ($releaseStates as $state) {
            $releaseStatesMapped[$state->getReleaseStatusId()] = $state->getReleaseStatusName();
        }

        return $releaseStatesMapped;
    }

    /**
     * @param int $beforeUserId
     * @param int $afterUserId
     *
     * @return array
     */
    private function getUserNameAndSurname(int $beforeUserId = null, int $afterUserId = null): array
    {
        $beforeUser = [];
        if (!is_null($beforeUserId)) {
            $beforeUser[] = $this->_getManager()->getRepository(Users::class)->find($beforeUserId);
        }

        $afterUser = [];
        if (!is_null($afterUserId)) {
            $afterUser[] = $this->_getManager()->getRepository(Users::class)->find($afterUserId);
        }

        $users = array_merge($beforeUser, $afterUser);

        $usersMapped = [];
        foreach ($users as $user) {
            $usersMapped[$user->getId()] = "$user";
        }

        return $usersMapped;
    }

    /**
     * @return array
     */
    private function getAllEcus() : array
    {
        $ecus = $this->_getManager()->getRepository(ConfigurationEcus::class)->findBy([], ['ecuName' => 'ASC']);

        $ecusMapped = [];
        foreach ($ecus as $ecu) {
            $ecusMapped[$ecu->getCeEcuId()] = $ecu->getEcuName();
        }

        return $ecusMapped;
    }
}