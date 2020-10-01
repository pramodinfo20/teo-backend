<?php


namespace App\Service\Vehicles\Configuration;

use App\Entity\ConfigurationEcus;
use App\Entity\EcuSubConfigurationVehicleContainment;
use App\Entity\EcuSwVersionSubVehicleConfigurationMapping;
use App\Entity\PentaVariants;
use App\Entity\SpecialVehiclePropertiesMapping;
use App\Entity\SubVehicleConfigurations;
use App\Entity\VehicleConfigurationPropertiesMapping;
use App\Model\ShortKeyModel;

class ShortKey extends VehicleConfigurationBase
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
        $additionalComponents = $this->manager->getRepository(SubVehicleConfigurations::class)->getAdditionalComponents($this->subVehConfId);
        $colorFromPenta = $this->manager->getRepository(SubVehicleConfigurations::class)->getColorFromPenta($this->subVehConfId);
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

        // Parts and features corresponding to the configuration key
        $featuresCorrespondingToKey = [];
        foreach ($parameters as $parameter) {
            $temp['Key'] = $parameter['vehicleConfigurationPropertyName'];
            $temp['Description'] = "[ {$parameter['symbol']} ] - " . $parameter['description'];
            $temp['PropertyId'] = $parameter['vcPropertyId'];

            array_push($featuresCorrespondingToKey, $temp);
        }

        $mappedAdditionalComponents = [];
        foreach ($additionalComponents as $value) {
            $mappedAdditionalComponents[(int)$value['mapping_order']] = $value;
        }

        $mappedAdditionalFeatures = [];
        foreach ($additionalFeatures as $value) {
            $mappedAdditionalFeatures[(int)$value['new_order']] = $value;
        }


        // Additional key components
        $additionalKeyComponents = [
            'espPart' => [
                'value' => array_key_exists(1, $mappedAdditionalComponents) ? ($mappedAdditionalComponents[1]['valueBool'] ? 'true' : 'false') : 'null',
                'visibleOnReport' => array_key_exists(1, $mappedAdditionalComponents) ? ($mappedAdditionalComponents[1]['visibleOnReport'] ? 'true' : 'false') : 'null'
            ],
            'rotatingBacon' => [
                'value' => array_key_exists(2, $mappedAdditionalComponents) ? ($mappedAdditionalComponents[2]['valueBool'] ? 'true' : 'false') : 'null',
                'visibleOnReport' => array_key_exists(2, $mappedAdditionalComponents) ? ($mappedAdditionalComponents[2]['visibleOnReport'] ? 'true' : 'false') : 'null'
            ],
            'partAtCoDriverPosition' => [
                'value' => array_key_exists(3, $mappedAdditionalComponents) ? $mappedAdditionalComponents[3]['valueString'] : 'null',
                'visibleOnReport' => array_key_exists(3, $mappedAdditionalComponents) ? ($mappedAdditionalComponents[3]['visibleOnReport'] ? 'true' : 'false') : 'null'
            ],
            'typeOfBattery' => [
                'value' => array_key_exists(4, $mappedAdditionalComponents) ? $mappedAdditionalComponents[4]['valueString'] : 'null',
                'visibleOnReport' => array_key_exists(4, $mappedAdditionalComponents) ? ($mappedAdditionalComponents[4]['visibleOnReport'] ? 'true' : 'false') : 'null'
            ],
            'radio' => [
                'value' => array_key_exists(5, $mappedAdditionalComponents) ? ($mappedAdditionalComponents[5]['valueBool'] ? 'true' : 'false') : 'null',
                'visibleOnReport' => array_key_exists(5, $mappedAdditionalComponents) ? ($mappedAdditionalComponents[5]['visibleOnReport'] ? 'true' : 'false') : 'null'
            ]
        ];

        // Additional key features
        $additionalKeyFeatures = [
            'standardColor' => (!(empty($colorFromPenta))) ? (array_key_exists('configurationColorName', $colorFromPenta[0]) ?
                $colorFromPenta[0]['configurationColorName'] : 'null') : 'null',
            'isDeutschePostConfiguration' => array_key_exists(6, $mappedAdditionalFeatures) ?
                ($mappedAdditionalFeatures[6]['valueBool'] ? 'true' : 'false') : 'null',
            'targetState' => array_key_exists(7, $mappedAdditionalFeatures) ?
                $mappedAdditionalFeatures[7]['valueString'] : 'null',
            'stdPlaceOfProduction' => $productionLocation['name'],
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
                $subConfiguration->getReleasedByUser()->getFname() . " " . $subConfiguration->getReleasedByUser()
                    ->getLname() :
                null,
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
                'isNewNameConvention' => false,
                'type' => $typeYearSeries['type'],
                'year' => $typeYearSeries['year'],
                'series' => $typeYearSeries['series'],
                'configurationState' => $releaseState['releaseStatus'],
                'periodOfProduction' => null,
                'periodOfDelivery' => null,
                'releaseState' => $rState,
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

    public function getModelToEdit(): ShortKeyModel
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

        $shortKeyModel = new ShortKeyModel();

        $shortKeyModel->setSubConfigurationId($this->subVehConfId);
        $shortKeyModel->setReleaseState($this->subConfiguration->getReleaseStatus()->getReleaseStatusId());
        $shortKeyModel->setReleasedByUser(
            (is_null($this->subConfiguration->getReleasedByUser())) ? null : $this->subConfiguration->getReleasedByUser()->getId());
        $shortKeyModel->setReleaseDate(
            (is_null($this->subConfiguration->getReleaseDate())) ? null : $this->subConfiguration->getReleaseDate()->format("Y-m-d H:i:s"));

        /* Vehicle Configuration Keys */
        $shortKeyModel->setVehicleConfigurationKey($this->subConfiguration->getVehicleConfiguration()->getVehicleConfigurationKey());
        $shortKeyModel->setPentaNumber($penta->getPentaVariantName());

        /* Key */
        $shortKeyModel->setType($this->subConfiguration->getVehicleConfiguration()->getVehicleTypeName());
        $shortKeyModel->setYear($this->subConfiguration->getVehicleConfiguration()->getVehicleTypeYear());
        $shortKeyModel->setSeries($this->subConfiguration->getVehicleConfiguration()->getVehicleSeries());
        $shortKeyModel->setLayout($cProperties[ShortKeyModel::LAYOUT]);
        $shortKeyModel->setFeature($cProperties[ShortKeyModel::FEATURE]);
        $shortKeyModel->setBattery($cProperties[ShortKeyModel::BATTERY]);

        /* Additional Key Component */
        $shortKeyModel->setEspPart($sProperties[ShortKeyModel::ESP_PART]['value']);
        $shortKeyModel->setEspPartReport($sProperties[ShortKeyModel::ESP_PART]['report']);
        $shortKeyModel->setRotatingBacon($sProperties[ShortKeyModel::ROTATING_BACON]['value']);
        $shortKeyModel->setRotatingBaconReport($sProperties[ShortKeyModel::ROTATING_BACON]['report']);
        $shortKeyModel->setPartAtCoDriverPosition($sProperties[ShortKeyModel::PART_AT_CO_DRIVER_POSITION]['value']);
        $shortKeyModel->setPartAtCoDriverPositionReport($sProperties[ShortKeyModel::PART_AT_CO_DRIVER_POSITION]['report']);
        $shortKeyModel->setTypeOfBattery($sProperties[ShortKeyModel::TYPE_OF_BATTERY]['value']);
        $shortKeyModel->setTypeOfBatteryReport($sProperties[ShortKeyModel::TYPE_OF_BATTERY]['report']);
        $shortKeyModel->setRadio($sProperties[ShortKeyModel::RADIO]['value']);
        $shortKeyModel->setRadioReport($sProperties[ShortKeyModel::RADIO]['report']);

        /* Additional key features */
        $shortKeyModel->setStandardColor((!is_null($penta)) ?
            $penta->getConfigurationColor()->getConfigurationColorId() : null);
        $shortKeyModel->setIsDeutschePostConfiguration($sProperties[ShortKeyModel::IS_DEUTSCHE_POST_CONFIGURATION]['value']);
        $shortKeyModel->setTargetState((!is_null($this->subConfiguration->getVehicleConfiguration()->getDefaultProductionLocation())) ?
            $this->subConfiguration->getVehicleConfiguration()->getDefaultProductionLocation()->getDepotId() : null);
        $shortKeyModel->setStsPlaceOfProduction((!is_null($this->subConfiguration->getVehicleConfiguration()->getDefaultProductionLocation())) ?
            $this->subConfiguration->getVehicleConfiguration()->getDefaultProductionLocation()->getDepotId() : null);
        $shortKeyModel->setEspFunctionality($sProperties[ShortKeyModel::ESP_FUNCTIONALITY]['value']);
        $shortKeyModel->setTirePressFront($sProperties[ShortKeyModel::TIRE_PRES_FRONT]['value']);
        $shortKeyModel->setTirePressRear($sProperties[ShortKeyModel::TIRE_PRES_REAR]['value']);
        $shortKeyModel->setComment($sProperties[ShortKeyModel::COMMENT]['value']);
        $shortKeyModel->setTestSoftwareVersion($sProperties[ShortKeyModel::TEST_SOFTWARE_VERSION]['value']);

        $shortKeyModel->setEcus($ecus);
        $shortKeyModel->setCans($cans);

        return $shortKeyModel;
    }
}