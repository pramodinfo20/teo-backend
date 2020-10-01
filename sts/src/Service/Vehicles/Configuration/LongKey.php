<?php


namespace App\Service\Vehicles\Configuration;

use App\Entity\ConfigurationEcus;
use App\Entity\EcuSubConfigurationVehicleContainment;
use App\Entity\EcuSwVersionSubVehicleConfigurationMapping;
use App\Entity\PentaVariants;
use App\Entity\SpecialVehiclePropertiesMapping;
use App\Entity\SubVehicleConfigurations;
use App\Entity\VehicleConfigurationPropertiesMapping;
use App\Model\LongKeyModel;


/**
 * Class LongKey
 *
 * @package App\Service\Vehicles\Configuration
 */
class LongKey extends VehicleConfigurationBase
{

    public function getParametersToView()
    {
        $parameters = $this->manager->getRepository(SubVehicleConfigurations::class)->getAssignedParameters($this->subVehConfId);
        $vehConfKey = $this->manager->getRepository(SubVehicleConfigurations::class)->getVehicleConfigurationKey($this->subVehConfId);
        $typeYearSeries = $this->manager->getRepository(SubVehicleConfigurations::class)->getTypeSeriesYear($this->subVehConfId);
        $releaseState = $this->manager->getRepository(SubVehicleConfigurations::class)->getReleaseState($this->subVehConfId);
        $allEcus = $this->manager->getRepository(ConfigurationEcus::class)->findBy([], ['ecuName' => 'ASC']);
        $assignedSubConfigurations = $this->manager->getRepository(EcuSwVersionSubVehicleConfigurationMapping::class)
            ->findBy(['subVehicleConfiguration' => $this->subVehConfId]);
        $ecuToSubConfiguration = $this->manager->getRepository(EcuSubConfigurationVehicleContainment::class)
            ->findBy(['subVehicleConfiguration' => $this->subVehConfId]);
        $subConfiguration = $this->manager->getRepository(SubVehicleConfigurations::class)
            ->findOneBy(['subVehicleConfigurationId' => $this->subVehConfId]);
        $pentaVariants = $this->manager->getRepository(PentaVariants::class)
            ->findOneBy(['subVehicleConfiguration' => $this->subVehConfId]);
        $productionLocation = $this->manager->getRepository(SubVehicleConfigurations::class)->getDefProductionLocation($this->subVehConfId);
        $additionalFeatures = $this->manager->getRepository(SubVehicleConfigurations::class)->getAdditionalFeatures($this->subVehConfId);
        $countOfAllVehicles = $this->manager->getRepository(SubVehicleConfigurations::class)->getVehicleCount($this->subVehConfId);
        $vinMethod =  $this->subConfiguration->getVehicleConfiguration()->getOldVehicleVariant()->getVinMethod();
        $chargerControllable = $this->subConfiguration->getVehicleConfiguration()->getOldVehicleVariant()
            ->getChargerControllable();

        $ecusWithAssignedConf = [];
        foreach ($allEcus as $key => $ecu) {
            $mappedPrimarySw = null;
            $mappedAlternativeSw = null;
            $mappedEcu = null;
            $canForwarding = null;

            foreach ($assignedSubConfigurations as $mapping) {
                if ($mapping->getEcuSwVersion()->getCeEcu()->getCeEcuId() == $ecu->getCeEcuId()) {

                    if ($mapping->getIsPrimarySw()) {
                        $mappedPrimarySw = $mapping->getEcuSwVersion();
                    } else {
                        $mappedAlternativeSw = $mapping->getEcuSwVErsion();
                    }
                }
            }

            foreach ($ecuToSubConfiguration as $ecuSubConfiguration) {
                if ($ecuSubConfiguration->getCeEcu()->getCeEcuId() == $ecu->getCeEcuId()) {
                    $mappedEcu = $ecuSubConfiguration->getCeEcu();
                    $canForwarding = $ecuSubConfiguration->getCanForwarding();
                }
            }

            array_push($ecusWithAssignedConf, [
                'ecuId' => $ecu->getCeEcuId(),
                'ecuName' => $ecu->getEcuName(),
                'primary' => [
                    'ecuSwVersionId' => (!is_null($mappedPrimarySw)) ? $mappedPrimarySw->getEcuSwVersionId() : null,
                    'swVersion' => (!is_null($mappedPrimarySw)) ? $mappedPrimarySw->getSwVersion() : null
                ],
                'alternative' => [
                    'ecuSwVersionId' => (!is_null($mappedAlternativeSw)) ? $mappedAlternativeSw->getEcuSwVersionId() : null,
                    'swVersion' => (!is_null($mappedAlternativeSw)) ? $mappedAlternativeSw->getSwVersion() : null
                ],
                'mappedEcu' => (!is_null($mappedEcu)) ? true : false,
                'canForwarding' => $canForwarding
            ]);
        }

        usort($parameters, function ($item1, $item2)
        {
            return $item1['positionInVehicleConfigurationKey'] <=> $item2['positionInVehicleConfigurationKey'];
        });

        // Parts and features corresponding to the configuration key
        $featuresCorrespondingToKey = [];
        foreach ($parameters as $parameter) {
            $temp['Key'] = $parameter['vehicleConfigurationPropertyName'];
            $temp['Description'] = "[ {$parameter['symbol']} ] - " . $parameter['description'];
            $temp['PropertyId'] = $parameter['vcPropertyId'];

            array_push($featuresCorrespondingToKey, $temp);
        }

        $mappedAdditionalFeatures = [];
        foreach ($additionalFeatures as $value) {
            $mappedAdditionalFeatures[(int)$value['new_order']] = $value;
        }

        // Additional key components
        $additionalKeyComponents = [];

        // Additional key features
        $additionalKeyFeatures = [
            'isDeutschePostConfiguration' => array_key_exists(6, $mappedAdditionalFeatures) ?
                ($mappedAdditionalFeatures[6]['valueBool'] ? 'true' : 'false') : 'null',
            'stdPlaceOfProduction' => array_key_exists('name', $productionLocation) ?
                $productionLocation['name'] : 'null',
            'espFunctionality' => array_key_exists(1, $mappedAdditionalFeatures) ?
                ($mappedAdditionalFeatures[1]['valueBool'] ? 'true' : 'false') : 'null',
            'tirePresFront' => array_key_exists(2, $mappedAdditionalFeatures) ?
                $mappedAdditionalFeatures[2]['valueInteger'] . " (kPa)" : 'null',
            'tirePresRear' => array_key_exists(3, $mappedAdditionalFeatures) ?
                $mappedAdditionalFeatures[3]['valueInteger'] . " (kPa)" : 'null',
            'comment' => array_key_exists(4, $mappedAdditionalFeatures) ?
                $mappedAdditionalFeatures[4]['valueString'] : 'null',
            'testSoftwareVersion' => array_key_exists(5, $mappedAdditionalFeatures) ?
                ($mappedAdditionalFeatures[5]['valueBool'] ? 'true' : 'false') : 'null',
        ];

        $rState = [
            'userName' => (!is_null($subConfiguration->getReleasedByUser())) ?
                $subConfiguration->getReleasedByUser()->getFname() . " " . $subConfiguration->getReleasedByUser()->getLname() : null,
            'date' => (!is_null($subConfiguration->getReleaseDate())) ?
                $subConfiguration->getReleaseDate()->format('Y-m-d H:i:s') : null,
            'releaseState' => (!is_null($subConfiguration->getReleaseStatus())) ?
                $subConfiguration->getReleaseStatus()->getReleaseStatusName() : null
        ];

        return [
            [
                'VehicleConfigurationKey' => $vehConfKey['vehicleConfigurationKey'],
                'subVehConfigId' => $this->subVehConfId,
                'pentaName' => isset($pentaVariants) ? $pentaVariants->getPentaVariantName() : 'null',
                'isNewNameConvention' => true,
                'type' => $typeYearSeries['type'],
                'year' => $typeYearSeries['year'],
                'series' => $typeYearSeries['series'],
                'customerKey' => $typeYearSeries['customer_key'],
                'configurationState' => $releaseState['releaseStatus'],
                'periodOfProduction' => null,
                'periodOfDelivery' => null,
                'releaseState' => $rState,
                'shortProductionDescription' => $subConfiguration->getShortProductionDescription(),
                'configurationId' => null,
                'vinMethod' => $vinMethod,
                'chargerControllable' => $chargerControllable
            ],
            $featuresCorrespondingToKey,
            $additionalKeyComponents,
            $additionalKeyFeatures,
            $ecusWithAssignedConf,
            $countOfAllVehicles,
        ];
    }

    public function getModelToEdit(): LongKeyModel
    {
        $propertiesMappingManager = $this->manager->getRepository(VehicleConfigurationPropertiesMapping::class);
        $specialPropertiesMappingManager = $this->manager->getRepository(SpecialVehiclePropertiesMapping::class);
        $pentaManager = $this->manager->getRepository(PentaVariants::class);


        $properties = $propertiesMappingManager->findBy(['vehicleConfiguration' =>
            $this->subConfiguration->getVehicleConfiguration()->getVehicleConfigurationId()]);
        $specialProperties = $specialPropertiesMappingManager->findBy(['subVehicleConfiguration' => $this->subVehConfId]);
        $penta = $pentaManager->findOneBy(['subVehicleConfiguration' => $this->subVehConfId]);

        $cProperties = [];
        foreach ($properties as $property) {
            $cProperties[$property->getVcProperty()->getVcPropertyId()] = $property->getAllowedSymbols()->getAllowedSymbolsId();
        }

        $sProperties = [];
        foreach ($specialProperties as $property) {
            $sProperties[$property->getSpecialVehicleProperty()->getSpecialVehiclePropertyId()]['value'] = $property->getSpecialVehiclePropertyValue()->getValueBool()
                ?? $property->getSpecialVehiclePropertyValue()->getValueInteger() ?? $property->getSpecialVehiclePropertyValue()->getValueString();
            $sProperties[$property->getSpecialVehicleProperty()->getSpecialVehiclePropertyId()]['report'] = $property->getVisibleOnReport();
        }

        //todo: Change it later. Dummy method to make empty keys nullable.
        for ($i = 0; $i < 100; ++$i) {
            if (!array_key_exists($i, $cProperties)) {
                $cProperties[$i] = null;
            }

            if (!array_key_exists($i, $sProperties)) {
                $sProperties[$i]['value'] = null;
                $sProperties[$i]['report'] = null;
            }
        }

        $ecuToSubConfiguration = $this->manager->getRepository(EcuSubConfigurationVehicleContainment::class)
            ->findBy(['subVehicleConfiguration' => $this->subVehConfId]);

        $ecus = [];
        $cans = [];

        foreach ($ecuToSubConfiguration as $value) {
            array_push($ecus, $value->getCeEcu()->getCeEcuId());

            if ($value->getCanForwarding()) {
                array_push($cans, $value->getCeEcu()->getCeEcuId());
            }
        }

        $longKeyModel = new LongKeyModel();

        $longKeyModel->setSubConfigurationId($this->subVehConfId);
        $longKeyModel->setReleaseState($this->subConfiguration->getReleaseStatus()->getReleaseStatusId());
        $longKeyModel->setReleasedByUser(
            (is_null($this->subConfiguration->getReleasedByUser())) ? null : $this->subConfiguration->getReleasedByUser()->getId());
        $longKeyModel->setReleaseDate(
            (is_null($this->subConfiguration->getReleaseDate())) ? null : $this->subConfiguration->getReleaseDate()->format("Y-m-d H:i:s"));

        /* Vehicle Configuration Keys */
        $typeYearSeries = $this->subConfiguration->getVehicleConfiguration()->getVehicleTypeName()."".
            $this->subConfiguration->getVehicleConfiguration()->getVehicleTypeYear()."".
            $this->subConfiguration->getVehicleConfiguration()->getVehicleSeries();
        $typeDesignation = null;

        if (is_null($this->subConfiguration->getVehicleConfiguration()->getVehicleCustomerKey())) {
            $typeDesignation = $typeYearSeries;
        } else {
            $typeDesignation =$typeYearSeries."_".$this->subConfiguration->getVehicleConfiguration()
                    ->getVehicleCustomerKey();
        }

        $longKeyModel->setTypeDesignation($typeDesignation);
        $longKeyModel->setVehicleConfigurationKey($this->subConfiguration->getVehicleConfiguration()
            ->getVehicleConfigurationKey());
        $longKeyModel->setPentaNumber($penta->getPentaVariantName());
        $longKeyModel->setShortProductionDescription($this->subConfiguration->getShortProductionDescription());

        /* Key */
        $longKeyModel->setType($this->subConfiguration->getVehicleConfiguration()->getVehicleTypeName());
        $longKeyModel->setYear($this->subConfiguration->getVehicleConfiguration()->getVehicleTypeYear());
        $longKeyModel->setSeries($this->subConfiguration->getVehicleConfiguration()->getVehicleSeries());
        $longKeyModel->setCustomerKey($this->subConfiguration->getVehicleConfiguration()->getVehicleCustomerKey());
        $longKeyModel->setDevStatus($cProperties[LongKeyModel::DEV_STATUS]);
        $longKeyModel->setBody($cProperties[LongKeyModel::BODY]);
        $longKeyModel->setNumberDrive($cProperties[LongKeyModel::NUMBER_DRIVE]);
        $longKeyModel->setEngineType($cProperties[LongKeyModel::ENGINE_TYPE]);
        $longKeyModel->setStageOfCompletion($cProperties[LongKeyModel::STAGE_OF_COMPLETION]);
        $longKeyModel->setBodyLength($cProperties[LongKeyModel::BODY_LENGTH]);
        $longKeyModel->setFrontAxle($cProperties[LongKeyModel::FRONT_AXLE]);
        $longKeyModel->setRearAxle($cProperties[LongKeyModel::REAR_AXLE]);
        $longKeyModel->setZgg($cProperties[LongKeyModel::ZGG]);
        $longKeyModel->setTypeOfFuel($cProperties[LongKeyModel::TYPE_OF_FUEL]);
        $longKeyModel->setTractionBattery($cProperties[LongKeyModel::TRACTION_BATTERY]);
        $longKeyModel->setChargingSystem($cProperties[LongKeyModel::CHARGING_SYSTEM]);
        $longKeyModel->setVMax($cProperties[LongKeyModel::VMAX]);
        $longKeyModel->setSeats($cProperties[LongKeyModel::SEATS]);
        $longKeyModel->setTrailerHitch($cProperties[LongKeyModel::TRAILER_HITCH]);
        $longKeyModel->setSuperstructures($cProperties[LongKeyModel::SUPERSTRUCTURES]);
        $longKeyModel->setEnergySupplySuperStructure($cProperties[LongKeyModel::ENERGY_SUPPLY_SUPERSTRUCTURE]);
        $longKeyModel->setSteering($cProperties[LongKeyModel::STEERING]);
        $longKeyModel->setRearWindow($cProperties[LongKeyModel::REAR_WINDOW]);
        $longKeyModel->setAirConditioning($cProperties[LongKeyModel::AIR_CONDITIONING]);
        $longKeyModel->setpassengerAirbag($cProperties[LongKeyModel::PASSENGER_AIRBAG]);
        $longKeyModel->setKeyless($cProperties[LongKeyModel::KEYLESS]);
        $longKeyModel->setSpecialApplicationArea($cProperties[LongKeyModel::SPECIAL_APPLICATION_AREA]);
        $longKeyModel->setRadio($cProperties[LongKeyModel::RADIO]);
        $longKeyModel->setSoundGenerator($cProperties[LongKeyModel::SOUND_GENERATOR]);
        $longKeyModel->setCountryCode($cProperties[LongKeyModel::COUNTRY_CODE]);
        $longKeyModel->setColor($cProperties[LongKeyModel::COLOR]);
        $longKeyModel->setWheeling($cProperties[LongKeyModel::WHEELING]);

        /* Additional key features */
        $longKeyModel->setIsDeutschePostConfiguration($sProperties[LongKeyModel::IS_DEUTSCHE_POST_CONFIGURATION]['value']);
        $longKeyModel->setStsPlaceOfProduction((!is_null($this->subConfiguration->getVehicleConfiguration()->getDefaultProductionLocation())) ?
            $this->subConfiguration->getVehicleConfiguration()->getDefaultProductionLocation()->getDepotId() : null);
        $longKeyModel->setEspFunctionality($sProperties[LongKeyModel::ESP_FUNCTIONALITY]['value']);
        $longKeyModel->setTirePressFront($sProperties[LongKeyModel::TIRE_PRES_FRONT]['value']);
        $longKeyModel->setTirePressRear($sProperties[LongKeyModel::TIRE_PRES_REAR]['value']);
        $longKeyModel->setComment($sProperties[LongKeyModel::COMMENT]['value']);
        $longKeyModel->setTestSoftwareVersion($sProperties[LongKeyModel::TEST_SOFTWARE_VERSION]['value']);

        $longKeyModel->setEcus($ecus);
        $longKeyModel->setCans($cans);

        return $longKeyModel;
    }
}