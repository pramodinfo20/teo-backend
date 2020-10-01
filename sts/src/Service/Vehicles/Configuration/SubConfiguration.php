<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 5/27/19
 * Time: 2:01 PM
 */

namespace App\Service\Vehicles\Configuration;

use App\Converter\Vin\VinBatchConverter;
use App\Entity\AllowedSymbols;
use App\Entity\CocParameterRelease;
use App\Entity\Colors;
use App\Entity\ConfigurationColors;
use App\Entity\ConfigurationEcus;
use App\Entity\Depots;
use App\Entity\EcuSubConfigurationVehicleContainment;
use App\Entity\EcuSwParameterEcuSwVersionMapping;
use App\Entity\EcuSwParameterEcuSwVersionMappingOverwrite;
use App\Entity\EcuSwVersionSubVehicleConfigurationMapping;
use App\Entity\GlobalParameterValuesSetsMapping;
use App\Entity\OdxSourceTypes;
use App\Entity\PentaNumbers;
use App\Entity\PentaVariants;
use App\Entity\ReleaseStatus;
use App\Entity\SpecialVehicleProperties;
use App\Entity\SpecialVehiclePropertiesMapping;
use App\Entity\SpecialVehiclePropertyValues;
use App\Entity\SubVehicleConfigurations;
use App\Entity\Users;
use App\Entity\VehicleConfigurationProperties;
use App\Entity\VehicleConfigurationPropertiesMapping;
use App\Entity\VehicleConfigurationPropertiesSymbols;
use App\Entity\VehicleConfigurations;
use App\Entity\VehicleConfigurationState;
use App\Entity\VehicleVariants;
use App\ErrorHandler\UndefinedIndex;
use App\Model\Vehicles\Configuration\ConfigurationSearch;
use App\Model\LongKeyModel;
use App\Model\ShortKeyModel;
use App\Model\ConfigurationI;
use App\Service\History\Vehicles\Configuration\HistorySubConfigurationI;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Enum\VariableTypes;
use App\Enum\Entity\ReleaseStatus as ReleaseStatusEnum;

class SubConfiguration implements HistorySubConfigurationI
{
    use UndefinedIndex;

    const VEHICLE_UNDER_DEVELOPMENT_STATE = 1;
    const UNDER_DEVELOPMENT_STATUS = 1;
    const NEW_EBOM = null;
    const ODX_FILE_SOURCE_ID = 1;
    const SHORT_KEY = 1;
    const LONG_KEY = 2;

    /**
     * @var ObjectManager
     */
    private $manager;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;


    /**
     * Parameter constructor.
     *
     * @param ObjectManager          $manager
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ObjectManager $manager, EntityManagerInterface $entityManager)
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
    }

    /**
     * @param SubVehicleConfigurations $subConfiguration
     * @param bool                     $createNew
     *
     * @return LongKeyModel|ShortKeyModel
     */
    public function getSubConfiguration(SubVehicleConfigurations $subConfiguration, bool $createNew = false)
    {
        if ($createNew)
            return ($this->detectSubConfigurationVersion($subConfiguration->getSubVehicleConfigurationName())
                == self::SHORT_KEY) ? new ShortKeyModel() : new LongKeyModel();

        if ($this->detectSubConfigurationVersion($subConfiguration->getSubVehicleConfigurationName())
            == self::SHORT_KEY) {
            $shortKey = new ShortKey($this->manager, $subConfiguration);
            return $shortKey->getModelToEdit();
        } else {
            $longKey = new LongKey($this->manager, $subConfiguration);
            return $longKey->getModelToEdit();
        }
    }

    /**
     * @param string $name
     *
     * @return int
     */
    public function detectSubConfigurationVersion(string $name): int
    {
        $layouts = array('EPOS', 'BPOS', 'EBOX', 'BBOX', 'BOXA', 'POST', 'PICK', 'PURE', 'PVS-', 'YAPT', 'PVSZ', 'PVSA');
        $type = substr($name, 0, 1);
        $year = (int)substr($name, 1, 2);
        $series = (int)substr($name, 3, 2);
        $layout = (string)substr($name, 5, 4);

        // var_dump($layout);
        // var_dump($series);

        // forced to use the LONG_KEY for all new configurations
        if ($type == "D") {
            if ($year == 17) {
                if ($series < 2) {
                    return self::SHORT_KEY;
                } else {
                    return self::LONG_KEY;
                }
            } elseif ($year < 17) {
                return self::SHORT_KEY;
            } else {
                return self::LONG_KEY;
            }
        } elseif ($type < "D" && (in_array($layout, $layouts) || $series == 0)) {
            return self::SHORT_KEY;
        } elseif ($type == "E" && (in_array($layout, $layouts) || $series == 0)) {
            return self::SHORT_KEY;
        }
        /*elseif ($type == "E") {
            if ($year >= 17 && $year <= 19) {
                return self::SHORT_KEY;
            } else {
                return self::LONG_KEY;
            }
        }*/ else {
            return self::LONG_KEY;
        }
    }

    /**
     * @param string $name
     *
     * @return int
     */
    public function detectSubConfigurationVersionForPDF(string $name): int
    {
        $type = substr($name, 0,1);
        $year = (int)substr($name, 1, 2);
        $series = (int)substr($name, 3,2);

        if ($type == "D") {
            if ($year == 17) {
                if ($series < 2) {
                  return self::SHORT_KEY;
                } else {
                  return self::LONG_KEY;
                }
            } elseif ($year < 17) {
              return self::SHORT_KEY;
            } else {
              return self::LONG_KEY;
            }
        } elseif ($type < "D") {
            return self::SHORT_KEY;
        } elseif ($type == 'E') {
            if ($year >= 17 && $year <= 19) {
                return self::SHORT_KEY;
            } else {
                return self::LONG_KEY;
            }
        } else {
            return self::LONG_KEY;
        }
    }

    /**
     * @param SubVehicleConfigurations $subConfiguration
     *
     * @return int
     */
    public function getConfigurationType(SubVehicleConfigurations $subConfiguration): int
    {
        if ($this->detectSubConfigurationVersion($subConfiguration->getSubVehicleConfigurationName())
            == self::SHORT_KEY) {
            return SELF::SHORT_KEY;
        } else {
            return SELF::LONG_KEY;
        }
    }


    /**
     * @param SubVehicleConfigurations $subConfiguration
     *
     * @return array
     */
    public function getParametersToView(SubVehicleConfigurations $subConfiguration): array
    {
        if ($this->detectSubConfigurationVersion($subConfiguration->getSubVehicleConfigurationName())
            == self::SHORT_KEY) {
            $shortKey = new ShortKey($this->manager, $subConfiguration);
            return $shortKey->getParametersToView($subConfiguration);
        } else {
            $longKey = new LongKey($this->manager, $subConfiguration);
            return $longKey->getParametersToView($subConfiguration);
        }
    }


    /**
     * @param int $propertyId
     *
     * @return array
     */
    public function getOptionsForProperty(int $propertyId): array
    {
        $vehicleCPSymbolsManager = $this->manager->getRepository(VehicleConfigurationPropertiesSymbols::class);

        $options = $vehicleCPSymbolsManager->findBy(['vcProperty' => $propertyId]);

        $rOptions = [];

        foreach ($options as $key => $value) {
            $rOptions[" [ {$value->getAllowedSymbols()->getSymbol()} ] - "
            . $value->getDescription()] = $value->getAllowedSymbols()->getAllowedSymbolsId();
        }

        return $rOptions;
    }


    /**
     * @param bool $reverseArray
     *
     * @return array
     */
    public function getAllOptions(bool $reverseArray = false): array
    {
        $vehicleCPSymbolsManager = $this->manager->getRepository(VehicleConfigurationPropertiesSymbols::class);
        $vehiclePropertiesManager = $this->manager->getRepository(VehicleConfigurationProperties::class);

        $options = new ArrayCollection($vehicleCPSymbolsManager->findAll());
        $properties = $vehiclePropertiesManager->findAll();

        $allOptions = [];
        foreach ($properties as $property) {
            $optionsByProperty = $options->filter(function ($option) use ($property)
            {
                return $option->getVcProperty()->getVcPropertyId() == $property->getVcPropertyId();
            });

            if ($reverseArray) {
                foreach ($optionsByProperty as $value) {
                    $allOptions[$property->getVcPropertyId()][$value->getAllowedSymbols()->getAllowedSymbolsId()] =
                        " [ {$value->getAllowedSymbols()->getSymbol()} ] - " . "{$value->getDescription()}";
                }
            } else {
                foreach ($optionsByProperty as $value) {
                    $allOptions[$property->getVcPropertyId()][" [ {$value->getAllowedSymbols()->getSymbol()} ] - " .
                    "{$value->getDescription()}"] = $value->getAllowedSymbols()->getAllowedSymbolsId();
                }
            }
        }

        return $allOptions;
    }

    /**
     * @param SubVehicleConfigurations $subVehicleConfiguration
     *
     * @return bool
     */
    public function checkAssignedSoftwares(SubVehicleConfigurations $subVehicleConfiguration) : bool
    {
        $ecuSubConfVehContainmentManager = $this->manager->getRepository
            (EcuSubConfigurationVehicleContainment::class);
        $ecuSwSubVehConfMappingManager = $this->manager->getRepository
            (EcuSwVersionSubVehicleConfigurationMapping::class);

        $subConfEcus = $ecuSubConfVehContainmentManager->findBy(['subVehicleConfiguration' =>
            $subVehicleConfiguration]);

        $subConfSws = $ecuSwSubVehConfMappingManager->findBy(['subVehicleConfiguration' =>
            $subVehicleConfiguration]);

        $subConfSws = array_filter($subConfSws, function($mapping) {
            return $mapping->getIsPrimarySw();
        });

        return count($subConfEcus) == count($subConfSws);
    }

    /**
     * @param SubVehicleConfigurations $subVehicleConfiguration
     *
     * @return bool
     */
    public function checkReleasedSoftwares(SubVehicleConfigurations $subVehicleConfiguration) : bool
    {
        $ecuSwSubVehConfMappingManager = $this->manager->getRepository
            (EcuSwVersionSubVehicleConfigurationMapping::class);

        $subConfSws = $ecuSwSubVehConfMappingManager->findBy(['subVehicleConfiguration' =>
            $subVehicleConfiguration]);

        $subConfSws = array_filter($subConfSws, function($mapping) {
            return $mapping->getEcuSwVersion()->getReleaseStatus()->getReleaseStatusId() !=
                ReleaseStatusEnum::RELEASE_STATUS_RELEASED;
        });

        return empty($subConfSws);
    }

    /**
     * @param SubVehicleConfigurations $subVehicleConfiguration
     *
     * @return bool
     */
    public function checkAssignedGlobalsValues(SubVehicleConfigurations $subVehicleConfiguration) : bool
    {
        $ecuSwSubVehConfMappingManager = $this->manager->getRepository
            (EcuSwVersionSubVehicleConfigurationMapping::class);
        $ecuSwParamEcuSwMappingManager = $this->manager->getRepository(EcuSwParameterEcuSwVersionMapping::class);
        $globalParamValSetsMappingManager = $this->manager->getRepository(GlobalParameterValuesSetsMapping::class);

        $subConfSws = $ecuSwSubVehConfMappingManager->findBy(['subVehicleConfiguration' =>
            $subVehicleConfiguration]);

        foreach ($subConfSws as $swMapping) {
            $allLinkedParameters = $ecuSwParamEcuSwMappingManager->findBy(['ecuSwVersion' =>
                $swMapping->getEcuSwVersion()]);

            $globalParameters = array_filter($allLinkedParameters, function ($parameter) {
               return !is_null($parameter->getEcuSwParameter()->getLinkedToGlobalParameter());
            });

            $globalSetsSubConf = $globalParamValSetsMappingManager->findBy(['subVehicleConfiguration' =>
                $swMapping->getSubVehicleConfiguration()]);

            $globalIds = array_map(function ($set) {
                return $set->getGlobalParameterValuesSet()->getGlobalParameter()->getGlobalParameterId();
            }, $globalSetsSubConf);

            $globalParameters = array_filter($globalParameters, function ($global) use ($globalIds) {
                return !in_array($global->getEcuSwParameter()->getLinkedToGlobalParameter()->getGlobalParameterId(),
                    $globalIds);
            });
        }

        return empty($globalParameters);
    }

    /**
     * @param ConfigurationI $model
     *
     * @return mixed
     */
    private function getDataAccess(ConfigurationI $model = null)
    {
        return
            new class($this->entityManager, $model)
            {
                //EntityManager
                /**
                 * @var EntityManagerInterface
                 */
                private $entityManager;

                //Managers
                private $configurationColorsManager;
                private $depotsManager;
                private $vehicleConfStateManager;
                private $releaseStatusManager;
                private $allowedSymbolsManager;
                private $vehConfPropertiesManager;
                private $specialVehPropertiesManager;
                private $confEcusManager;
                private $odxSourceTypesManager;
                private $vehicleConfigurationManager;
                private $subConfigurationManager;
                private $swSuVehConfMappingManager;
                private $parameterEcuSwMappingOverwriteManager;
                private $specialVehPropertiesValueManager;
                private $pentaVariantsManager;
                private $specialPropertiesMappingManager;
                private $ecuContainmentManager;
                private $vehConfPropertiesMappingManager;
                // Additional information
                private $pentaNumbersManager;
                private $vehicleVariantsManager;
                private $oldColorsManager;

                //Data
                private $allSymbols;
                private $allProperties;
                private $allSpecialProperties;
                private $allEcus;
                private $odxSource;
                private $dataKeyMethodsBefore;
                private $dataKeyMethodsAfter;
                private $propertiesMethodsBefore;
                private $propertiesMethodsAfter;

                /**
                 * Parameter constructor.
                 *
                 * @param EntityManagerInterface $entityManager
                 * @param ConfigurationI         $model
                 */
                public function __construct($entityManager, $model)
                {
                    $this->entityManager = $entityManager;
                    $this->configurationColorsManager = $this->entityManager
                        ->getRepository(ConfigurationColors::class);
                    $this->depotsManager = $this->entityManager->getRepository(Depots::class);
                    $this->vehicleConfStateManager = $this->entityManager->getRepository
                    (VehicleConfigurationState::class);
                    $this->releaseStatusManager = $this->entityManager
                        ->getRepository(ReleaseStatus::class);
                    $this->allowedSymbolsManager = $this->entityManager
                        ->getRepository(AllowedSymbols::class);
                    $this->vehConfPropertiesManager = $this->entityManager->getRepository
                    (VehicleConfigurationProperties::class);
                    $this->specialVehPropertiesManager = $this->entityManager
                        ->getRepository(SpecialVehicleProperties::class);
                    $this->specialVehPropertiesValueManager = $this->entityManager->getRepository
                    (SpecialVehiclePropertyValues::class);
                    $this->confEcusManager = $this->entityManager
                        ->getRepository(ConfigurationEcus::class);
                    $this->odxSourceTypesManager = $this->entityManager
                        ->getRepository(OdxSourceTypes::class);
                    $this->vehicleConfigurationManager = $this->entityManager
                        ->getRepository(VehicleConfigurations::class);
                    $this->subConfigurationManager = $this->entityManager
                        ->getRepository(SubVehicleConfigurations::class);
                    $this->swSuVehConfMappingManager = $this->entityManager->getRepository
                    (EcuSwVersionSubVehicleConfigurationMapping::class);
                    $this->parameterEcuSwMappingOverwriteManager = $this->entityManager->getRepository
                    (EcuSwParameterEcuSwVersionMappingOverwrite::class);
                    $this->specialPropertiesMappingManager = $this->entityManager->getRepository
                    (SpecialVehiclePropertiesMapping::class);
                    $this->ecuContainmentManager = $this->entityManager->getRepository
                    (EcuSubConfigurationVehicleContainment::class);
                    $this->pentaVariantsManager = $this->entityManager
                        ->getRepository(PentaVariants::class);
                    $this->vehConfPropertiesMappingManager = $this->entityManager->getRepository
                    (VehicleConfigurationPropertiesMapping::class);
                    // Additional information
                    $this->pentaNumbersManager = $this->entityManager->getRepository(PentaNumbers::class);
                    $this->vehicleVariantsManager = $this->entityManager->getRepository(VehicleVariants::class);
                    $this->oldColorsManager = $this->entityManager->getRepository(Colors::class);

                    $symbols = $this->allowedSymbolsManager->findAll();

                    $this->allSymbols = [];
                    foreach ($symbols as $symbol) {
                        $this->allSymbols[$symbol->getAllowedSymbolsId()] = $symbol;
                    }

                    $properties = $this->vehConfPropertiesManager->findAll();

                    $this->allProperties = [];
                    foreach ($properties as $property) {
                        $this->allProperties[$property->getVcPropertyId()] = $property;
                    }

                    $specialProperties = $this->specialVehPropertiesManager->findAll();

                    $this->allSpecialProperties = [];
                    foreach ($specialProperties as $specialProperty) {
                        $this->allSpecialProperties[$specialProperty->getSpecialVehiclePropertyId()]
                            = $specialProperty;
                    }

                    $allEcusFind = $this->confEcusManager->findAll();

                    $this->allEcus = [];
                    foreach ($allEcusFind as $ecu) {
                        $this->allEcus[$ecu->getCeEcuId()] = $ecu;
                    }

                    $this->odxSource = $this->odxSourceTypesManager->find(SubConfiguration::ODX_FILE_SOURCE_ID);

                    //Key and properties
                    $this->dataKeyMethodsBefore = [
                        ShortKeyModel::LAYOUT => "getLayout",
                        ShortKeyModel::FEATURE => "getFeature",
                        ShortKeyModel::BATTERY => "getBattery"
                    ];

                    $this->dataKeyMethodsAfter = [
                        LongKeyModel::DEV_STATUS => "getDevStatus",
                        LongKeyModel::BODY => "getBody",
                        LongKeyModel::NUMBER_DRIVE => "getNumberDrive",
                        LongKeyModel::ENGINE_TYPE => "getEngineType",
                        LongKeyModel::STAGE_OF_COMPLETION => "getStageOfCompletion",
                        LongKeyModel::BODY_LENGTH => "getBodyLength",
                        LongKeyModel::FRONT_AXLE => "getFrontAxle",
                        LongKeyModel::REAR_AXLE => "getRearAxle",
                        LongKeyModel::ZGG => "getZgg",
                        LongKeyModel::TYPE_OF_FUEL => "getTypeOfFuel",
                        LongKeyModel::TRACTION_BATTERY => "getTractionBattery",
                        LongKeyModel::CHARGING_SYSTEM => "getChargingSystem",
                        LongKeyModel::VMAX => "getVMAx",
                        LongKeyModel::SEATS => "getSeats",
                        LongKeyModel::TRAILER_HITCH => "getTrailerHitch",
                        LongKeyModel::SUPERSTRUCTURES => "getSuperstructures",
                        LongKeyModel::ENERGY_SUPPLY_SUPERSTRUCTURE => "getEnergySupplySuperStructure",
                        LongKeyModel::STEERING => "getSteering",
                        LongKeyModel::REAR_WINDOW => "getRearWindow",
                        LongKeyModel::AIR_CONDITIONING => "getAirConditioning",
                        LongKeyModel::PASSENGER_AIRBAG => "getPassengerAirbag",
                        LongKeyModel::KEYLESS => "getKeyless",
                        LongKeyModel::SPECIAL_APPLICATION_AREA => "getSpecialApplicationArea",
                        LongKeyModel::RADIO => "getRadio",
                        LongKeyModel::SOUND_GENERATOR => "getSoundGenerator",
                        LongKeyModel::COUNTRY_CODE => "getCountryCode",
                        LongKeyModel::COLOR => "getColor",
                        LongKeyModel::WHEELING => "getWheeling"
                    ];

                    $this->propertiesMethodsBefore = [
                        [
                            ShortKeyModel::ESP_PART => "isEspPart",
                            ShortKeyModel::ROTATING_BACON => "isRotatingBacon",
                            ShortKeyModel::PART_AT_CO_DRIVER_POSITION => "getPartAtCoDriverPosition",
                            ShortKeyModel::TYPE_OF_BATTERY => "getTypeOFBattery",
                            ShortKeyModel::RADIO => "isRadio"],
                        [
                            ShortKeyModel::IS_DEUTSCHE_POST_CONFIGURATION => "isDeutschePostConfiguration",
                            ShortKeyModel::TARGET_STATE => "getTargetState",
                            ShortKeyModel::ESP_FUNCTIONALITY => "isEspFunctionality",
                            ShortKeyModel::TIRE_PRES_FRONT => "getTirePressFront",
                            ShortKeyModel::TIRE_PRES_REAR => "getTirePressRear",
                            ShortKeyModel::COMMENT => "getComment",
                            ShortKeyModel::TEST_SOFTWARE_VERSION => "isTestSoftwareVersion"
                        ]
                    ];

                    $this->propertiesMethodsAfter = $keyFeatures = [
                        LongKeyModel::IS_DEUTSCHE_POST_CONFIGURATION => "isDeutschePostConfiguration",
                        LongKeyModel::ESP_FUNCTIONALITY => "isEspFunctionality",
                        LongKeyModel::TIRE_PRES_FRONT => "getTirePressFront",
                        LongKeyModel::TIRE_PRES_REAR => "getTirePressRear",
                        LongKeyModel::COMMENT => "getComment",
                        LongKeyModel::TEST_SOFTWARE_VERSION => "isTestSoftwareVersion"
                    ];
                }

                /**
                 * @return mixed
                 */
                public function getConfigurationColorsManager()
                {
                    return $this->configurationColorsManager;
                }

                /**
                 * @return mixed
                 */
                public function getDepotsManager()
                {
                    return $this->depotsManager;
                }

                /**
                 * @return mixed
                 */
                public function getVehicleConfStateManager()
                {
                    return $this->vehicleConfStateManager;
                }

                /**
                 * @return mixed
                 */
                public function getReleaseStatusManager()
                {
                    return $this->releaseStatusManager;
                }

                /**
                 * @return mixed
                 */
                public function getAllowedSymbolsManager()
                {
                    return $this->allowedSymbolsManager;
                }

                /**
                 * @return mixed
                 */
                public function getVehConfPropertiesManager()
                {
                    return $this->vehConfPropertiesManager;
                }

                /**
                 * @return mixed
                 */
                public function getSpecialVehPropertiesManager()
                {
                    return $this->specialVehPropertiesManager;
                }

                /**
                 * @return mixed
                 */
                public function getConfEcusManager()
                {
                    return $this->confEcusManager;
                }

                /**
                 * @return mixed
                 */
                public function getOdxSourceTypesManager()
                {
                    return $this->odxSourceTypesManager;
                }

                /**
                 * @return mixed
                 */
                public function getVehicleConfigurationManager()
                {
                    return $this->vehicleConfigurationManager;
                }

                /**
                 * @return mixed
                 */
                public function getSubConfigurationManager()
                {
                    return $this->subConfigurationManager;
                }

                /**
                 * @return mixed
                 */
                public function getSwSuVehConfMappingManager()
                {
                    return $this->swSuVehConfMappingManager;
                }

                /**
                 * @return mixed
                 */
                public function getParameterEcuSwMappingOverwriteManager()
                {
                    return $this->parameterEcuSwMappingOverwriteManager;
                }

                /**
                 * @return array
                 */
                public function getAllSymbols(): array
                {
                    return $this->allSymbols;
                }

                /**
                 * @return array
                 */
                public function getAllProperties(): array
                {
                    return $this->allProperties;
                }

                /**
                 * @return array
                 */
                public function getAllSpecialProperties(): array
                {
                    return $this->allSpecialProperties;
                }

                /**
                 * @return array
                 */
                public function getAllEcus(): array
                {
                    return $this->allEcus;
                }

                /**
                 * @return mixed
                 */
                public function getOdxSource()
                {
                    return $this->odxSource;
                }

                /**
                 * @return array
                 */
                public function getDataKeyMethodsBefore(): array
                {
                    return $this->dataKeyMethodsBefore;
                }

                /**
                 * @return array
                 */
                public function getDataKeyMethodsAfter(): array
                {
                    return $this->dataKeyMethodsAfter;
                }

                /**
                 * @return array
                 */
                public function getPropertiesMethodsBefore(): array
                {
                    return $this->propertiesMethodsBefore;
                }

                /**
                 * @return array
                 */
                public function getPropertiesMethodsAfter(): array
                {
                    return $this->propertiesMethodsAfter;
                }

                /**
                 * @return mixed
                 */
                public function getSpecialVehPropertiesValueManager()
                {
                    return $this->specialVehPropertiesValueManager;
                }

                /**
                 * @return mixed
                 */
                public function getPentaVariantsManager()
                {
                    return $this->pentaVariantsManager;
                }

                /**
                 * @return mixed
                 */
                public function getSpecialPropertiesMappingManager()
                {
                    return $this->specialPropertiesMappingManager;
                }

                /**
                 * @return mixed
                 */
                public function getEcuContainmentManager()
                {
                    return $this->ecuContainmentManager;
                }

                /**
                 * @return mixed
                 */
                public function getVehConfPropertiesMappingManager()
                {
                    return $this->vehConfPropertiesMappingManager;
                }

                /**
                 * @return \Doctrine\Common\Persistence\ObjectRepository
                 */
                public function getPentaNumbersManager(): \Doctrine\Common\Persistence\ObjectRepository
                {
                    return $this->pentaNumbersManager;
                }

                /**
                 * @param \Doctrine\Common\Persistence\ObjectRepository $pentaNumbersManager
                 */
                public function setPentaNumbersManager(\Doctrine\Common\Persistence\ObjectRepository $pentaNumbersManager): void
                {
                    $this->pentaNumbersManager = $pentaNumbersManager;
                }

                /**
                 * @return \Doctrine\Common\Persistence\ObjectRepository
                 */
                public function getVehicleVariantsManager(): \Doctrine\Common\Persistence\ObjectRepository
                {
                    return $this->vehicleVariantsManager;
                }

                /**
                 * @param \Doctrine\Common\Persistence\ObjectRepository $vehicleVariantsManager
                 */
                public function setVehicleVariantsManager(\Doctrine\Common\Persistence\ObjectRepository $vehicleVariantsManager): void
                {
                    $this->vehicleVariantsManager = $vehicleVariantsManager;
                }

                /**
                 * @return \Doctrine\Common\Persistence\ObjectRepository
                 */
                public function getOldColorsManager(): \Doctrine\Common\Persistence\ObjectRepository
                {
                    return $this->oldColorsManager;
                }

                /**
                 * @param \Doctrine\Common\Persistence\ObjectRepository $oldColorsManager
                 */
                public function setOldColorsManager(\Doctrine\Common\Persistence\ObjectRepository $oldColorsManager): void
                {
                    $this->oldColorsManager = $oldColorsManager;
                }
            };
    }

    /**
     * @param ConfigurationI        $model
     * @param string                $generatedKey
     * @param VehicleConfigurations $configuration
     *
     * @return mixed
     */
    private function setConfigurationAdditionalInformation(ConfigurationI $model, string $generatedKey,
                                                           VehicleConfigurations $configuration = null)
    {
        return
            new class($this->entityManager, $model, $generatedKey, $configuration)
            {
                /**
                 * @var EntityManagerInterface
                 */
                private $entityManager;
                private $model;
                private $pentaNumber;
                private $pentaNumberSub;
                private $vehicleVariant;
                private $oldColor;
                private $colorKey;
                private $generatedKey;
                private $configuration;
                private $batteryKeyDescription;
                private $isDeutschePostConfiguration;

                /**
                 * Parameter constructor.
                 *
                 * @param EntityManagerInterface $entityManager
                 * @param ConfigurationI         $model
                 * @param string                 $generatedKey
                 * @param VehicleConfigurations  $configuration
                 */
                public function __construct($entityManager, $model, $generatedKey, $configuration)
                {
                    $this->configuration = $configuration;

                    if (is_null($this->configuration)) {
                        $this->vehicleVariant = new VehicleVariants();
                        $this->pentaNumber = new PentaNumbers();
                    } else {
                        $this->pentaNumber = $entityManager->getRepository(PentaNumbers::class)
                            ->find($entityManager->getRepository(PentaNumbers::class)
                            ->findMainConfigurationPentaNumber($this->configuration->getOldVehicleVariant()
                                ->getVehicleVariantId()));
                        $this->vehicleVariant = $this->configuration->getOldVehicleVariant();
                    }

                    // Additional information
                    $propertiesSymbolsManager = $entityManager->getRepository
                    (VehicleConfigurationPropertiesSymbols::class);

                    $batteryPropertiesSymbols = $propertiesSymbolsManager->findBy(['vcProperty' =>
                        [ShortKeyModel::BATTERY, LongKeyModel::TRACTION_BATTERY]]);

                    $this->batteryKeyDescription = [];
                    foreach ($batteryPropertiesSymbols as  $row) {
                        $this->batteryKeyDescription[$row->getVcProperty()->getVcPropertyId()
                        ][$row->getAllowedSymbols()->getAllowedSymbolsId()] = $row->getDescription();
                    }

                    $this->generatedKey = $generatedKey;
                    $this->pentaNumberSub = null;
                    $this->model = $model;
                    $this->isDeutschePostConfiguration = $model->isDeutschePostConfiguration();

                    if ($model instanceof ShortKeyModel) {
                        $this->oldColor = $entityManager->getRepository(Colors::class)
                            ->find($model->getStandardColor());
                        $this->vehicleVariant->setBattery
                            ($this->batteryKeyDescription[ShortKeyModel::BATTERY][$model->getBattery()]);
                    } else {
                        $this->oldColor = $entityManager->getRepository(Colors::class)
                            ->find($entityManager->getRepository(ConfigurationColors::class)->findOneBy(
                                ["allowedSymbols" => $model->getColor()]));
                        $this->vehicleVariant->setBattery
                        ($this->batteryKeyDescription[LongKeyModel::TRACTION_BATTERY][$model->getTractionBattery()]);
                    }

                    $this->colorKey = $this->oldColor->getColorKey();

                    $this->vehicleVariant->setVinMethod($model->getVinMethod());
                    $this->vehicleVariant->setVinBatch(VinBatchConverter::convertSeriesToVinBatch(
                        $model->getSeries()));
                    $this->vehicleVariant->setChargerControllable($model->isChargerControllable());
                    $this->vehicleVariant->setDefaultColor($this->oldColor);
                    $this->vehicleVariant->setWindchillVariantName($this->generatedKey);
                    $this->vehicleVariant->setIsDp($this->isDeutschePostConfiguration);

                    $this->pentaNumber->setPentaConfig($this->pentaNumber);
                    $this->pentaNumber->setPentaNumber("{$this->generatedKey}_{$this->colorKey}_00");
                    $this->pentaNumber->setVehicleVariant($this->vehicleVariant);
                    $this->pentaNumber->setColor($this->oldColor);
                }

                public function addSubConfPentaNumber($keyIndex)
                {
                    $this->pentaNumberSub = new PentaNumbers();

                    $this->pentaNumberSub->setPentaConfig($this->pentaNumber);
                    $this->pentaNumberSub->setPentaNumber("{$this->generatedKey}_{$this->colorKey}_{$keyIndex}");
                    $this->pentaNumberSub->setVehicleVariant($this->vehicleVariant);
                    $this->pentaNumberSub->setColor($this->oldColor);
                }

                public function updateSubConfPentaNumber($keyIndex, PentaNumbers $pentaNumber)
                {
                    $this->pentaNumberSub = $pentaNumber;
                    $this->pentaNumberSub->setPentaConfig($this->pentaNumber);
                    $this->pentaNumberSub->setPentaNumber("{$this->generatedKey}_{$this->colorKey}_{$keyIndex}");
                    $this->pentaNumberSub->setVehicleVariant($this->vehicleVariant);
                    $this->pentaNumberSub->setColor($this->oldColor);
                }

                /**
                 * @return PentaNumbers
                 */
                public function getPentaNumber(): PentaNumbers
                {
                    return $this->pentaNumber;
                }

                /**
                 * @return null
                 */
                public function getPentaNumberSub()
                {
                    return $this->pentaNumberSub;
                }

                /**
                 * @return VehicleVariants
                 */
                public function getVehicleVariant(): VehicleVariants
                {
                    return $this->vehicleVariant;
                }
            };
    }


    /**
     * @param ShortKeyModel $shortKeyModel
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveShortKey(ShortKeyModel $shortKeyModel): ConfigurationSearch
    {
        /* Start transaction */
        $this->entityManager->beginTransaction();
        $dataAndManagersAccess = $this->getDataAccess($shortKeyModel);

        $generatedKey = $this->generateShortKey($shortKeyModel);

        $searchConfiguration = $dataAndManagersAccess->getVehicleConfigurationManager()->findOneBy(
            ['vehicleConfigurationKey' => $generatedKey]);
        $colorKey = $dataAndManagersAccess->getConfigurationColorsManager()->find($shortKeyModel->getStandardColor())
            ->getConfigurationColorKey();

        $allProperties = $dataAndManagersAccess->getAllProperties();
        $allSpecialProperties = $dataAndManagersAccess->getAllSpecialProperties();
        $allSymbols = $dataAndManagersAccess->getAllSymbols();
        $allEcus = $dataAndManagersAccess->getAllEcus();
        $odxSource = $dataAndManagersAccess->getOdxSource();

        try {
            $vehicleConfiguration = null;
            $keyIndex = 1;

            if (is_null($searchConfiguration)) {
                //Additional information - vehicle configuration
                $additionalInformation = $this->setConfigurationAdditionalInformation($shortKeyModel,
                    $generatedKey, null);
                $this->entityManager->persist($additionalInformation->getVehicleVariant());
                $this->entityManager->persist($additionalInformation->getPentaNumber());


                $vehicleConfiguration = new VehicleConfigurations();

                $vehicleConfiguration->setVehicleTypeName($shortKeyModel->getType())
                    ->setVehicleTypeYear($shortKeyModel->getYear())
                    ->setVehicleSeries($shortKeyModel->getSeries())
                    ->setVehicleConfigurationKey($generatedKey)
                    ->setDefaultConfigurationColor($dataAndManagersAccess->getConfigurationColorsManager()
                        ->find($shortKeyModel->getStandardColor()))
                    ->setDefaultProductionLocation($dataAndManagersAccess->getDepotsManager()
                        ->find($shortKeyModel->getStsPlaceOfProduction()))
                    ->setOldVehicleVariant($additionalInformation->getVehicleVariant());
                $this->entityManager->persist($vehicleConfiguration);
            } else {
                $vehicleConfiguration = $searchConfiguration;
                $subConfigurations = $dataAndManagersAccess->getSubConfigurationManager()->findBy(
                    ['vehicleConfiguration' => $vehicleConfiguration]);
                foreach ($subConfigurations as $subconfiguration) {
                    $currentKeyIndex = (int) explode("#", $subconfiguration->getSubVehicleConfigurationName())[1];
                    if ($currentKeyIndex > $keyIndex) {
                        $keyIndex = $currentKeyIndex;
                    }
                }

                ++$keyIndex;
                //Additional information - vehicle configuration
                $additionalInformation = $this->setConfigurationAdditionalInformation($shortKeyModel,
                    $generatedKey, $vehicleConfiguration);
                $this->entityManager->persist($additionalInformation->getVehicleVariant());
                $this->entityManager->persist($additionalInformation->getPentaNumber());
            }

            if ($keyIndex <= 9) {
                $keyIndex = "0$keyIndex";
            }

            $subVehicleConfiguration = new SubVehicleConfigurations();

            $subVehicleConfiguration->setVehicleConfiguration($vehicleConfiguration)
                ->setSubVehicleConfigurationName("{$generatedKey }#{$keyIndex}")
                ->setVehicleConfigurationState($dataAndManagersAccess->getVehicleConfStateManager()
                    ->find(self::VEHICLE_UNDER_DEVELOPMENT_STATE))
                ->setReleaseStatus($dataAndManagersAccess->getReleaseStatusManager()
                    ->find(self::UNDER_DEVELOPMENT_STATUS));

            if ($shortKeyModel->getSubConfigurationId() != '') {
                $subVehicleConfiguration->setSourceSubVehicleConfiguration
                ($dataAndManagersAccess->getSubConfigurationManager()->find($shortKeyModel->getSubConfigurationId
                ()));
            }


            if (!is_null($shortKeyModel->getSubConfigurationId())) {
                $subVehicleConfiguration->setSourceSubVehicleConfiguration(
                    $dataAndManagersAccess->getSubConfigurationManager()
                        ->find($shortKeyModel->getSubConfigurationId()));
            }


            $this->entityManager->persist($subVehicleConfiguration);

            //Additional information - sub-vehicle configuration
            $additionalInformation->addSubConfPentaNumber($keyIndex);
            $this->entityManager->persist($additionalInformation->getPentaNumberSub());

            if (is_null($searchConfiguration)) {
                $dataKey = $dataAndManagersAccess->getDataKeyMethodsBefore();

                foreach ($dataKey as $key => $value) {
                    $this->entityManager->persist((new VehicleConfigurationPropertiesMapping())
                        ->setVehicleConfiguration($vehicleConfiguration)
                        ->setVcProperty($allProperties[$key])
                        ->setAllowedSymbols($allSymbols[$shortKeyModel->$value()]));
                }
            }

            $dataSVPV = $dataAndManagersAccess->getPropertiesMethodsBefore();

            /* Additional key components */
            foreach ($dataSVPV[0] as $key => $value) {
                $inserted = new SpecialVehiclePropertyValues();
                switch (gettype($shortKeyModel->$value())) {
                    case VariableTypes::VARIABLE_TYPE_BOOLEAN:
                        $inserted->setValueBool($shortKeyModel->$value());
                        break;
                    case VariableTypes::VARIABLE_TYPE_STRING:
                        $inserted->setValueString($shortKeyModel->$value());
                        break;
                    case VariableTypes::VARIABLE_TYPE_INTEGER:
                        $inserted->setValueInteger($shortKeyModel->$value());
                        break;
                }
                $report = (substr($value, 0, 1) == 'g') ?
                    "is" . substr($value, 3, strlen($value)) . "Report" : $value . "Report";
                $this->entityManager->persist($inserted);

                $this->entityManager->persist((new SpecialVehiclePropertiesMapping())
                    ->setVehicleConfiguration($vehicleConfiguration)
                    ->setSubVehicleConfiguration($subVehicleConfiguration)
                    ->setSpecialVehicleProperty($allSpecialProperties[$key])
                    ->setGivenByVehicleConfigurationKey(false)
                    ->setSpecialVehiclePropertyValue($inserted)
                    ->setVisibleOnReport($shortKeyModel->$report())
                );
            }

            /* Additional key features */
            foreach ($dataSVPV[1] as $key => $value) {
                $inserted = new SpecialVehiclePropertyValues();
                switch (gettype($shortKeyModel->$value())) {
                    case VariableTypes::VARIABLE_TYPE_BOOLEAN:
                        $inserted->setValueBool($shortKeyModel->$value());
                        break;
                    case VariableTypes::VARIABLE_TYPE_STRING:
                        $inserted->setValueString($shortKeyModel->$value());
                        break;
                    case VariableTypes::VARIABLE_TYPE_INTEGER:
                        $inserted->setValueInteger($shortKeyModel->$value());
                        break;
                }

                $this->entityManager->persist($inserted);

                $this->entityManager->persist((new SpecialVehiclePropertiesMapping())
                    ->setVehicleConfiguration($vehicleConfiguration)
                    ->setSubVehicleConfiguration($subVehicleConfiguration)
                    ->setSpecialVehicleProperty($allSpecialProperties[$key])
                    ->setGivenByVehicleConfigurationKey(false)
                    ->setSpecialVehiclePropertyValue($inserted)
                    ->setVisibleOnReport(false)
                );
            }
            $this->entityManager->flush();

            $ecus = $shortKeyModel->getEcus();

            foreach ($ecus as $ecu) {
                $containment = new EcuSubConfigurationVehicleContainment();

                $containment->setCeEcu($allEcus[$ecu])
                    ->setSubVehicleConfiguration($subVehicleConfiguration)
                    ->setEbomPart(self::NEW_EBOM)
                    ->setOdxSourceType($odxSource)
                    ->setEcuParametersReleaseStatus($dataAndManagersAccess->getReleaseStatusManager()
                        ->find(self::UNDER_DEVELOPMENT_STATUS));
                $this->entityManager->persist($containment);

                if (!empty($shortKeyModel->getCans())) {
                    $canForwarding = current($shortKeyModel->getCans());
                    if ($canForwarding == $containment->getCeEcu()->getCeEcuId()) {
                        $containment->setCanForwarding(true);
                    } else {
                        $containment->setCanForwarding(false);
                    }
                    $this->entityManager->persist($containment);

                    $this->entityManager->flush();
                }

                if (!is_null($shortKeyModel->getSubConfigurationId())) {
                        $allSws = $dataAndManagersAccess->getSwSuVehConfMappingManager()->findBy(
                            ['subVehicleConfiguration' => $shortKeyModel->getSubConfigurationId()]);

                    if (!empty($allSws)) {
                        $ecuSws = array_filter($allSws, function ($mapping) use ($ecu)
                        {
                            return $mapping->getEcuSwVersion()->getCeEcu()->getCeEcuId() == $ecu;
                        });

                        foreach ($ecuSws as $mappedSw) {
                            $newMappingSw = new EcuSwVersionSubVehicleConfigurationMapping();
                            $newMappingSw->setSubVehicleConfiguration($subVehicleConfiguration);
                            $newMappingSw->setEcuSwVersion($mappedSw->getEcuSwVersion());
                            $newMappingSw->setIsPrimarySw($mappedSw->getIsPrimarySw());

                            $this->entityManager->persist($newMappingSw);

                            $overwritten = $dataAndManagersAccess->getParameterEcuSwMappingOverwriteManager()
                                ->findBy(
                                ['subVehicleConfiguration' => $shortKeyModel->getSubConfigurationId(),
                                    'ecuSwVersion' => $mappedSw->getEcuSwVersion()]
                                );

                            foreach ($overwritten as $mappedSet) {
                                $newMappingSet = new EcuSwParameterEcuSwVersionMappingOverwrite();
                                $newMappingSet->setSubVehicleConfiguration($subVehicleConfiguration);
                                $newMappingSet->setEcuSwVersion($mappedSw->getEcuSwVersion());

                                $newSet = clone $mappedSet->getEcuSwParameterValueSet();

                                $this->entityManager->persist($newSet);

                                $newMappingSet->setEcuSwParameterValueSet($newSet);

                                $this->entityManager->persist($newMappingSet);
                            }
                        }
                    }
                }
            }
            $this->entityManager->flush();

            $penta = new PentaVariants();
            $penta->setConfigurationColor($dataAndManagersAccess->getConfigurationColorsManager()
                ->find($shortKeyModel->getStandardColor()))
                ->setSubVehicleConfiguration($subVehicleConfiguration)
                ->setPentaVariantName("{$generatedKey}#{$keyIndex}_{$colorKey}")
                ->setOldPentaNumber($additionalInformation->getPentaNumberSub());
            $this->entityManager->persist($penta);


            $this->entityManager->flush();

            /* Commit */
            $this->entityManager->commit();
        } catch (\Exception $exception) {
            /* Rollback */
            $this->entityManager->rollBack();
            throw $exception;
        }

        return new ConfigurationSearch(
            $subVehicleConfiguration->getSubVehicleConfigurationId(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleTypeName(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleTypeYear(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleSeries(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleCustomerKey()
        );
    }

    /**
     * @param ShortKeyModel $shortKeyModel
     *
     * @return string
     * @return int
     */
    public function generateShortKey(ShortKeyModel $shortKeyModel): ?string
    {
        $allSymbols = $this->getAllOptionsToGenerateKey();
        $key = '';
        $year = ($shortKeyModel->getYear() <= 9) ? "0{$shortKeyModel->getYear()}"
            : "{$shortKeyModel->getYear()}";

        self::setErrorHandlerCatchUndefinedIndex();

        try {

            $key .= strtoupper($shortKeyModel->getType())
                . $year
                . $shortKeyModel->getSeries()
                . $allSymbols[ShortKeyModel::LAYOUT][$shortKeyModel->getLayout()]
                . $allSymbols[ShortKeyModel::FEATURE][$shortKeyModel->getFeature()]
                . $allSymbols[ShortKeyModel::BATTERY][$shortKeyModel->getBattery()];
        } catch (\ErrorException $e) {
            // Very important : restoring the previous error handler
            restore_error_handler();
            return null;
        }
        restore_error_handler();
        return $key;
    }

    /**
     * @return array
     */
    private function getAllOptionsToGenerateKey(): array
    {
        $vehicleCPSymbolsManager = $this->manager->getRepository(VehicleConfigurationPropertiesSymbols::class);
        $vehiclePropertiesManager = $this->manager->getRepository(VehicleConfigurationProperties::class);

        $options = new ArrayCollection($vehicleCPSymbolsManager->findAll());
        $properties = $vehiclePropertiesManager->findAll();

        $allOptions = [];
        foreach ($properties as $property) {
            $optionsByProperty = $options->filter(function ($option) use ($property)
            {
                return $option->getVcProperty()->getVcPropertyId() == $property->getVcPropertyId();
            });


            foreach ($optionsByProperty as $value) {
                $allOptions[$property->getVcPropertyId()][$value->getAllowedSymbols()->getAllowedSymbolsId()]
                    = $value->getAllowedSymbols()->getSymbol();
            }
        }

        return $allOptions;
    }

    /**
     * @param LongKeyModel $longKeyModel
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveLongKey(LongKeyModel $longKeyModel): ConfigurationSearch
    {
        /* Start transaction */
        $this->entityManager->beginTransaction();
        $dataAndManagersAccess = $this->getDataAccess($longKeyModel);

        $generatedKey = $this->generateLongKey($longKeyModel);

        $searchConfiguration = $dataAndManagersAccess->getVehicleConfigurationManager()
            ->findOneVConfigurationByKeyAndCustomerKey($generatedKey, $longKeyModel->getCustomerKey());

        $colorKey = $dataAndManagersAccess->getConfigurationColorsManager()->findOneBy(
            ["allowedSymbols" => $longKeyModel->getColor()])->getConfigurationColorKey();

        $generatedShortProductionDescription = $this->generateShortProductionDescriptionLongKey($longKeyModel);

        $allProperties = $dataAndManagersAccess->getAllProperties();
        $allSpecialProperties = $dataAndManagersAccess->getAllSpecialProperties();
        $allSymbols = $dataAndManagersAccess->getAllSymbols();
        $allEcus = $dataAndManagersAccess->getAllEcus();
        $odxSource = $dataAndManagersAccess->getOdxSource();

        try {
            $vehicleConfiguration = null;
            $keyIndex = 1;

            if (is_null($searchConfiguration)) {
                //Additional information - vehicle configuration
                $additionalInformation = $this->setConfigurationAdditionalInformation($longKeyModel,
                    $generatedKey, null);
                $this->entityManager->persist($additionalInformation->getVehicleVariant());
                $this->entityManager->persist($additionalInformation->getPentaNumber());
                $vehicleConfiguration = new VehicleConfigurations();

                $vehicleConfiguration->setVehicleTypeName($longKeyModel->getType())
                    ->setVehicleTypeYear($longKeyModel->getYear())
                    ->setVehicleSeries($longKeyModel->getSeries())
                    ->setVehicleConfigurationKey($generatedKey)
                    ->setDefaultConfigurationColor($dataAndManagersAccess->getConfigurationColorsManager()
                        ->findOneBy
                        (["allowedSymbols" =>
                            $longKeyModel->getColor()]))
                    ->setDefaultProductionLocation($dataAndManagersAccess->getDepotsManager()->find
                    ($longKeyModel->getStsPlaceOfProduction()))
                    ->setVehicleCustomerKey($longKeyModel->getCustomerKey())
                    ->setOldVehicleVariant($additionalInformation->getVehicleVariant());
                $this->entityManager->persist($vehicleConfiguration);
            } else {
                $vehicleConfiguration = $searchConfiguration;
                $subConfigurations = $dataAndManagersAccess->getSubConfigurationManager()->findBy(
                    ['vehicleConfiguration' => $vehicleConfiguration]);
                foreach ($subConfigurations as $subconfiguration) {
                    $currentKeyIndex = (int) explode("#", $subconfiguration->getSubVehicleConfigurationName())[1];
                    if ($currentKeyIndex > $keyIndex) {
                        $keyIndex = $currentKeyIndex;
                    }
                }

                ++$keyIndex;
                $additionalInformation = $this->setConfigurationAdditionalInformation($longKeyModel,
                    $generatedKey, $vehicleConfiguration);
                $this->entityManager->persist($additionalInformation->getVehicleVariant());
                $this->entityManager->persist($additionalInformation->getPentaNumber());
            }

            $keyIndexShort = $keyIndex;

            if ($keyIndex <= 9) {
                $keyIndex = "0$keyIndex";
            }

            $subVehicleConfiguration = new SubVehicleConfigurations();

            $subVehicleConfiguration->setVehicleConfiguration($vehicleConfiguration)
                ->setSubVehicleConfigurationName("{$generatedKey}#{$keyIndex}")
                ->setVehicleConfigurationState($dataAndManagersAccess->getVehicleConfStateManager()->find
                (self::VEHICLE_UNDER_DEVELOPMENT_STATE))
                ->setReleaseStatus($dataAndManagersAccess->getReleaseStatusManager()
                    ->find(self::UNDER_DEVELOPMENT_STATUS))
                ->setShortProductionDescription("{$generatedShortProductionDescription}{$keyIndexShort}");

            if (!is_null($longKeyModel->getSubConfigurationId())) {
                $subVehicleConfiguration->setSourceSubVehicleConfiguration(
                    $dataAndManagersAccess->getSubConfigurationManager()
                        ->find($longKeyModel->getSubConfigurationId()));
            }

            if ($longKeyModel->getSubConfigurationId() != '') {
                $subVehicleConfiguration
                    ->setSourceSubVehicleConfiguration($dataAndManagersAccess->getSubConfigurationManager()
                    ->find($longKeyModel->getSubConfigurationId()));
            }

            $this->entityManager->persist($subVehicleConfiguration);

            //Additional information - sub-vehicle configuration
            $additionalInformation->addSubConfPentaNumber($keyIndex);
            $this->entityManager->persist($additionalInformation->getPentaNumberSub());

            if (is_null($searchConfiguration)) {

                $dataKey = $dataAndManagersAccess->getDataKeyMethodsAfter();

                foreach ($dataKey as $key => $value) {
                    $this->entityManager->persist((new VehicleConfigurationPropertiesMapping())
                        ->setVehicleConfiguration($vehicleConfiguration)
                        ->setVcProperty($allProperties[$key])
                        ->setAllowedSymbols($allSymbols[$longKeyModel->$value()]));
                }
            }

            $keyFeatures = $dataAndManagersAccess->getPropertiesMethodsAfter();


            /* Additional key features */
            foreach ($keyFeatures as $key => $value) {
                $inserted = new SpecialVehiclePropertyValues();
                switch (gettype($longKeyModel->$value())) {
                    case VariableTypes::VARIABLE_TYPE_BOOLEAN:
                        $inserted->setValueBool($longKeyModel->$value());
                        break;
                    case VariableTypes::VARIABLE_TYPE_STRING:
                        $inserted->setValueString($longKeyModel->$value());
                        break;
                    case VariableTypes::VARIABLE_TYPE_INTEGER:
                        $inserted->setValueInteger($longKeyModel->$value());
                        break;
                }

                $this->entityManager->persist($inserted);

                $this->entityManager->persist((new SpecialVehiclePropertiesMapping())
                    ->setVehicleConfiguration($vehicleConfiguration)
                    ->setSubVehicleConfiguration($subVehicleConfiguration)
                    ->setSpecialVehicleProperty($allSpecialProperties[$key])
                    ->setGivenByVehicleConfigurationKey(false)
                    ->setSpecialVehiclePropertyValue($inserted)
                    ->setVisibleOnReport(false)
                );
            }

            $ecus = $longKeyModel->getEcus();

                foreach ($ecus as $ecu) {
                    $containment = new EcuSubConfigurationVehicleContainment();

                $containment->setCeEcu($allEcus[$ecu])
                    ->setSubVehicleConfiguration($subVehicleConfiguration)
                    ->setEbomPart(self::NEW_EBOM)
                    ->setOdxSourceType($odxSource)
                    ->setEcuParametersReleaseStatus($dataAndManagersAccess->getReleaseStatusManager()->find
                    (self::UNDER_DEVELOPMENT_STATUS));
                $this->entityManager->persist($containment);

                    if (!empty($longKeyModel->getCans())) {
                        $canForwarding = current($longKeyModel->getCans());
                        if ($canForwarding == $containment->getCeEcu()->getCeEcuId()) {
                            $containment->setCanForwarding(true);
                        } else {
                            $containment->setCanForwarding(false);
                        }
                        $this->entityManager->persist($containment);

                        $this->entityManager->flush();
                    }

                if (!is_null($longKeyModel->getSubConfigurationId())) {
                        $allSws = $dataAndManagersAccess->getSwSuVehConfMappingManager()->findBy(
                            ['subVehicleConfiguration' => $longKeyModel->getSubConfigurationId()]);

                    if (!empty($allSws)) {
                        $ecuSws = array_filter($allSws, function ($mapping) use ($ecu)
                        {
                            return $mapping->getEcuSwVersion()->getCeEcu()->getCeEcuId() == $ecu;
                        });

                        foreach ($ecuSws as $mappedSw) {
                            $newMappingSw = new EcuSwVersionSubVehicleConfigurationMapping();
                            $newMappingSw->setSubVehicleConfiguration($subVehicleConfiguration);
                            $newMappingSw->setEcuSwVersion($mappedSw->getEcuSwVersion());
                            $newMappingSw->setIsPrimarySw($mappedSw->getIsPrimarySw());

                            $this->entityManager->persist($newMappingSw);

                            $overwritten = $dataAndManagersAccess->getParameterEcuSwMappingOverwriteManager()
                                ->findBy(
                                ['subVehicleConfiguration' => $longKeyModel->getSubConfigurationId(),
                                    'ecuSwVersion' => $mappedSw->getEcuSwVersion()]
                                );

                            foreach ($overwritten as $mappedSet) {
                                $newMappingSet = new EcuSwParameterEcuSwVersionMappingOverwrite();
                                $newMappingSet->setSubVehicleConfiguration($subVehicleConfiguration);
                                $newMappingSet->setEcuSwVersion($mappedSw->getEcuSwVersion());

                                $newSet = clone $mappedSet->getEcuSwParameterValueSet();

                                $this->entityManager->persist($newSet);

                                $newMappingSet->setEcuSwParameterValueSet($newSet);

                                $this->entityManager->persist($newMappingSet);
                            }
                        }
                    }
                }
            }

            $penta = new PentaVariants();
            $penta->setConfigurationColor($dataAndManagersAccess->getConfigurationColorsManager()->findOneBy(
                ["allowedSymbols" => $longKeyModel->getColor()]))
                ->setSubVehicleConfiguration($subVehicleConfiguration)
                ->setPentaVariantName("{$generatedKey}#{$keyIndex}_{$colorKey}")
                ->setOldPentaNumber($additionalInformation->getPentaNumberSub());
            $this->entityManager->persist($penta);

            $this->entityManager->flush();

            /* Commit */
            $this->entityManager->commit();
        } catch (\Exception $exception) {
            /* Rollback */
            $this->entityManager->rollBack();
            throw $exception;
        }

        return new ConfigurationSearch(
            $subVehicleConfiguration->getSubVehicleConfigurationId(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleTypeName(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleTypeYear(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleSeries(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleCustomerKey()
        );
    }

    /**
     * @param LongKeyModel $longKeyModel
     *
     * @return string
     */
    public function generateLongKey(LongKeyModel $longKeyModel): ?string
    {
        $allSymbols = $this->getAllOptionsToGenerateKey();
        $key = '';
        $year = ($longKeyModel->getYear() <= 9) ?
            "0{$longKeyModel->getYear()}" : "{$longKeyModel->getYear()}";

        self::setErrorHandlerCatchUndefinedIndex();
        try {
            $key .= strtoupper($longKeyModel->getType())
                . $year
                . $longKeyModel->getSeries()
                . $allSymbols[LongKeyModel::DEV_STATUS][$longKeyModel->getDevStatus()]
                . $allSymbols[LongKeyModel::BODY][$longKeyModel->getBody()]
                . $allSymbols[LongKeyModel::NUMBER_DRIVE][$longKeyModel->getNumberDrive()]
                . $allSymbols[LongKeyModel::ENGINE_TYPE][$longKeyModel->getEngineType()]
                . $allSymbols[LongKeyModel::STAGE_OF_COMPLETION][$longKeyModel->getStageOFCompletion()]
                . $allSymbols[LongKeyModel::BODY_LENGTH][$longKeyModel->getBodyLength()]
                . $allSymbols[LongKeyModel::FRONT_AXLE][$longKeyModel->getFrontAxle()]
                . $allSymbols[LongKeyModel::REAR_AXLE][$longKeyModel->getRearAxle()]
                . $allSymbols[LongKeyModel::ZGG][$longKeyModel->getZgg()]
                . $allSymbols[LongKeyModel::TYPE_OF_FUEL][$longKeyModel->getTypeOfFuel()]
                . $allSymbols[LongKeyModel::TRACTION_BATTERY][$longKeyModel->getTractionBattery()]
                . $allSymbols[LongKeyModel::CHARGING_SYSTEM][$longKeyModel->getChargingSystem()]
                . $allSymbols[LongKeyModel::VMAX][$longKeyModel->getVMax()]
                . $allSymbols[LongKeyModel::SEATS][$longKeyModel->getSeats()]
                . $allSymbols[LongKeyModel::TRAILER_HITCH][$longKeyModel->getTrailerHitch()]
                . $allSymbols[LongKeyModel::SUPERSTRUCTURES][$longKeyModel->getSuperstructures()]
                . $allSymbols[LongKeyModel::ENERGY_SUPPLY_SUPERSTRUCTURE][
                    $longKeyModel->getEnergySupplySuperStructure()
                ]
                . $allSymbols[LongKeyModel::STEERING][$longKeyModel->getSteering()]
                . $allSymbols[LongKeyModel::REAR_WINDOW][$longKeyModel->getRearWindow()]
                . $allSymbols[LongKeyModel::AIR_CONDITIONING][$longKeyModel->getAirConditioning()]
                . $allSymbols[LongKeyModel::PASSENGER_AIRBAG][$longKeyModel->getpassengerAirbag()]
                . $allSymbols[LongKeyModel::KEYLESS][$longKeyModel->getKeyless()]
                . $allSymbols[LongKeyModel::SPECIAL_APPLICATION_AREA][$longKeyModel->getSpecialApplicationArea()]
                . $allSymbols[LongKeyModel::RADIO][$longKeyModel->getRadio()]
                . $allSymbols[LongKeyModel::SOUND_GENERATOR][$longKeyModel->getSoundGenerator()]
                . $allSymbols[LongKeyModel::COUNTRY_CODE][$longKeyModel->getCountryCode()]
                . $allSymbols[LongKeyModel::COLOR][$longKeyModel->getColor()]
                . $allSymbols[LongKeyModel::WHEELING][$longKeyModel->getWheeling()];
        } catch (\ErrorException $e) {
            // Very important : restoring the previous error handler
            restore_error_handler();
            return null;
        }
        restore_error_handler();
        return $key;
    }

    /**
     * @param LongKeyModel $longKeyModel
     *
     * @return string|null
     */
    public function generateShortProductionDescriptionLongKey(LongKeyModel $longKeyModel): ?string
    {
        $allSymbols = $this->getAllOptionsToGenerateKey();
        $key = '';

        $year = ($longKeyModel->getYear() <= 9) ? "0{$longKeyModel->getYear()}"
            : "{$longKeyModel->getYear()}";

        self::setErrorHandlerCatchUndefinedIndex();

        try {
            $key .= strtoupper($longKeyModel->getType())
                . $year
                . $longKeyModel->getSeries()
                . $allSymbols[LongKeyModel::BODY_LENGTH][$longKeyModel->getBodyLength()]
                . $allSymbols[LongKeyModel::TRACTION_BATTERY][$longKeyModel->getTractionBattery()]
                . $allSymbols[LongKeyModel::CHARGING_SYSTEM][$longKeyModel->getChargingSystem()]
                . $allSymbols[LongKeyModel::SEATS][$longKeyModel->getSeats()]
                . $allSymbols[LongKeyModel::SUPERSTRUCTURES][$longKeyModel->getSuperstructures()]
                . $allSymbols[LongKeyModel::STEERING][$longKeyModel->getSteering()]
                . $allSymbols[LongKeyModel::COLOR][$longKeyModel->getColor()];
        } catch (\ErrorException $e) {
            restore_error_handler();
            return null;
        }

        restore_error_handler();
        return $key;
    }

    /**
     * @param ShortKeyModel $shortKeyModel
     * @param Users         $user
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveEditionShortKey(ShortKeyModel $shortKeyModel, Users $user): ConfigurationSearch
    {
        /* Start transaction */
        $this->entityManager->beginTransaction();

        $dataAndManagersAccess = $this->getDataAccess($shortKeyModel);

        $subVehicleConfiguration = $dataAndManagersAccess->getSubConfigurationManager()
            ->find($shortKeyModel->getSubConfigurationId());

        $allSpecialProperties = $dataAndManagersAccess->getAllSpecialProperties();
        $allEcus = $dataAndManagersAccess->getAllEcus();
        $odxSource = $dataAndManagersAccess->getOdxSource();


        try {
            $dataSVPV = $dataAndManagersAccess->getPropertiesMethodsBefore();

            $specialPropertiesMapping = $dataAndManagersAccess->getSpecialPropertiesMappingManager()->findBy(
                ['subVehicleConfiguration' => $subVehicleConfiguration]);

            $currentKeyComponentArray = [];

            /* Additional key components */
            foreach ($dataSVPV[0] as $key => $value) {
                $currentKeyComponentArray = array_filter($specialPropertiesMapping, function ($element) use ($key)
                {
                    return $element->getSpecialVehicleProperty()->getSpecialVehiclePropertyId() == $key;
                });

                if (empty($currentKeyComponentArray)) {
                    $inserted = new SpecialVehiclePropertyValues();
                    switch (gettype($shortKeyModel->$value())) {
                        case VariableTypes::VARIABLE_TYPE_BOOLEAN
                        :
                            $inserted->setValueBool($shortKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_STRING:
                            $inserted->setValueString($shortKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_INTEGER:
                            $inserted->setValueInteger($shortKeyModel->$value());
                            break;
                    }
                    $report = (substr($value, 0, 1) == 'g') ?
                        "is" . substr($value, 3, strlen($value)) . "Report" : $value . "Report";
                    $this->entityManager->persist($inserted);

                    $this->entityManager->persist((new SpecialVehiclePropertiesMapping())
                        ->setVehicleConfiguration($subVehicleConfiguration->getVehicleConfiguration())
                        ->setSubVehicleConfiguration($subVehicleConfiguration)
                        ->setSpecialVehicleProperty($allSpecialProperties[$key])
                        ->setGivenByVehicleConfigurationKey(false)
                        ->setSpecialVehiclePropertyValue($inserted)
                        ->setVisibleOnReport($shortKeyModel->$report())
                    );
                } else {
                    $current = reset($currentKeyComponentArray);
                    $inserted = $dataAndManagersAccess->getSpecialVehPropertiesValueManager()
                        ->find($current->getSpecialVehiclePropertyValue());
                    $inserted->setValueInteger(null);
                    $inserted->setValueString(null);
                    $inserted->setValueBool(null);

                    switch (gettype($shortKeyModel->$value())) {
                        case VariableTypes::VARIABLE_TYPE_BOOLEAN:
                            $inserted->setValueBool($shortKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_STRING:
                            $inserted->setValueString($shortKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_INTEGER:
                            $inserted->setValueInteger($shortKeyModel->$value());
                            break;
                    }
                    $this->entityManager->persist($inserted);
                }
            }

            $currentKeyFeaturesArray = [];

            /* Additional key features */
            foreach ($dataSVPV[1] as $key => $value) {
                $currentKeyFeaturesArray = array_filter($specialPropertiesMapping, function ($element) use ($key)
                {
                    return $element->getSpecialVehicleProperty()->getSpecialVehiclePropertyId() == $key;
                });

                if (empty($currentKeyFeaturesArray)) {
                    $inserted = new SpecialVehiclePropertyValues();
                    switch (gettype($shortKeyModel->$value())) {
                        case VariableTypes::VARIABLE_TYPE_BOOLEAN:
                            $inserted->setValueBool($shortKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_STRING:
                            $inserted->setValueString($shortKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_INTEGER:
                            $inserted->setValueInteger($shortKeyModel->$value());
                            break;
                    }

                    $this->entityManager->persist($inserted);

                    $this->entityManager->persist((new SpecialVehiclePropertiesMapping())
                        ->setVehicleConfiguration($subVehicleConfiguration->getVehicleConfiguration())
                        ->setSubVehicleConfiguration($subVehicleConfiguration)
                        ->setSpecialVehicleProperty($allSpecialProperties[$key])
                        ->setGivenByVehicleConfigurationKey(false)
                        ->setSpecialVehiclePropertyValue($inserted)
                        ->setVisibleOnReport(false)
                    );
                } else {
                    $current = reset($currentKeyFeaturesArray);
                    $inserted = $dataAndManagersAccess->getSpecialVehPropertiesValueManager()
                        ->find($current->getSpecialVehiclePropertyValue());
                    $inserted->setValueInteger(null);
                    $inserted->setValueString(null);
                    $inserted->setValueBool(null);

                    switch (gettype($shortKeyModel->$value())) {
                        case VariableTypes::VARIABLE_TYPE_BOOLEAN:
                            $inserted->setValueBool($shortKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_STRING:
                            $inserted->setValueString($shortKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_INTEGER:
                            $inserted->setValueInteger($shortKeyModel->$value());
                            break;
                    }
                    $this->entityManager->persist($inserted);
                }
            }
            $this->entityManager->flush();

            $containmentOld = $dataAndManagersAccess->getEcuContainmentManager()->findBy(
                ['subVehicleConfiguration' => $shortKeyModel->getSubConfigurationId()]);
            $ecus = $shortKeyModel->getEcus();

            foreach ($ecus as $ecu) {
                $ecuFlag = false;
                if (!empty($containmentOld)) {
                    $containmentOldFiltered = array_filter($containmentOld, function ($element) use ($ecu, &$ecuFlag)
                    {
                        if ($ecu == $element->getCeEcu()->getCeEcuId()) {
                            $ecuFlag = true;
                        }

                        return $ecu == $element->getCeEcu()->getCeEcuId();
                    });

                    $containmentOld = array_filter($containmentOld, function ($element) use ($ecu, &$ecuFlag)
                    {
                        return $ecu != $element->getCeEcu()->getCeEcuId();
                    });
                }

                if (!$ecuFlag) {
                    $containmentNew = new EcuSubConfigurationVehicleContainment();

                    $containmentNew->setCeEcu($allEcus[$ecu])
                        ->setSubVehicleConfiguration($subVehicleConfiguration)
                        ->setEbomPart(self::NEW_EBOM)
                        ->setOdxSourceType($odxSource)
                        ->setEcuParametersReleaseStatus($dataAndManagersAccess->getReleaseStatusManager()->find
                        (self::UNDER_DEVELOPMENT_STATUS));
                    $this->entityManager->persist($containmentNew);

                    $containment = $containmentNew;
                } else {
                    $containment = reset($containmentOldFiltered);
                }

                if (!empty($shortKeyModel->getCans())) {
                    $canForwarding = current($shortKeyModel->getCans());
                    if ($canForwarding != $ecu && $containment->getCanForwarding() == true) {
                        $containment->setCanForwarding(false);
                    } elseif ($canForwarding == $containment->getCeEcu()->getCeEcuId()) {
                        $containment->setCanForwarding(true);
                    } else {
                        $containment->setCanForwarding(false);
                    }
                    $this->entityManager->persist($containment);

                    $this->entityManager->flush();
                }
            }

            $ecuVersionSubConfigMapping = $dataAndManagersAccess->getSwSuVehConfMappingManager()->findBy(
                ['subVehicleConfiguration' => $shortKeyModel->getSubConfigurationId()]);

            $ecuSwVerSubVehConfMapOverwrite = $dataAndManagersAccess->getParameterEcuSwMappingOverwriteManager()
                ->findBy(['subVehicleConfiguration' => $shortKeyModel->getSubConfigurationId()]);

            foreach ($containmentOld as $mapped) {
                $currentEcuMapping = array_filter($ecuVersionSubConfigMapping, function ($element) use ($mapped)
                {
                    return $mapped->getCeEcu()->getCeEcuId() == $element->getEcuSwVersion()->getCeEcu()->getCeEcuId();
                });

                $currentOverwriteMapping = array_filter($ecuSwVerSubVehConfMapOverwrite, function ($element) use ($mapped)
                {
                    return $mapped->getCeEcu()->getCeEcuId() == $element->getEcuSwVersion()->getCeEcu()->getCeEcuId();
                });

                foreach ($currentEcuMapping as $map) {
                    $this->entityManager->remove($map);
                }

                foreach ($currentOverwriteMapping as $map) {
                    $this->entityManager->remove($map->getEcuSwParameterValueSet());
                }

                $this->entityManager->remove($mapped);
            }

            $subVehicleConfiguration->setReleaseStatus($dataAndManagersAccess->getReleaseStatusManager()->find
            ($shortKeyModel->getReleaseState()));

            if ($subVehicleConfiguration->getDraft()) {
                $subVehicleConfiguration->setDraft(false);
            }
            $subVehicleConfiguration->setReleasedByUser($user);
            $subVehicleConfiguration->setReleaseDate(new \DateTime(date("Y-m-d H:i:s")));
            $this->entityManager->persist($subVehicleConfiguration);

            $this->entityManager->flush();

            /* Commit */
            $this->entityManager->commit();
        } catch (\Exception $exception) {
            /* Rollback */
            $this->entityManager->rollBack();
            throw $exception;
        }

        return new ConfigurationSearch(
            $subVehicleConfiguration->getSubVehicleConfigurationId(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleTypeName(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleTypeYear(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleSeries(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleCustomerKey()
        );
    }

    /**
     * @param LongKeyModel $longKeyModel
     * @param Users        $user
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveEditionLongKey(LongKeyModel $longKeyModel, Users $user): ConfigurationSearch
    {
        /* Start transaction */
        $this->entityManager->beginTransaction();

        $dataAndManagersAccess = $this->getDataAccess($longKeyModel);

        $subVehicleConfiguration = $dataAndManagersAccess->getSubConfigurationManager()
            ->find($longKeyModel->getSubConfigurationId());

        $allSpecialProperties = $dataAndManagersAccess->getAllSpecialProperties();
        $allEcus = $dataAndManagersAccess->getAllEcus();
        $odxSource = $dataAndManagersAccess->getOdxSource();

        try {
            $keyFeatures = $dataAndManagersAccess->getPropertiesMethodsAfter();

            $specialPropertiesMapping = $dataAndManagersAccess->getSpecialPropertiesMappingManager()
                ->findBy(['subVehicleConfiguration' => $subVehicleConfiguration]);

            /* Additional key features */
            foreach ($keyFeatures as $key => $value) {
                $currentKeyFeaturesArray = array_filter($specialPropertiesMapping, function ($element) use ($key)
                {
                    return $element->getSpecialVehicleProperty()->getSpecialVehiclePropertyId() == $key;
                });

                if (empty($currentKeyFeaturesArray)) {
                    $inserted = new SpecialVehiclePropertyValues();
                    switch (gettype($longKeyModel->$value())) {
                        case VariableTypes::VARIABLE_TYPE_BOOLEAN:
                            $inserted->setValueBool($longKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_STRING:
                            $inserted->setValueString($longKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_INTEGER:
                            $inserted->setValueInteger($longKeyModel->$value());
                            break;
                    }

                    $this->entityManager->persist($inserted);

                    $this->entityManager->persist((new SpecialVehiclePropertiesMapping())
                        ->setVehicleConfiguration($subVehicleConfiguration->getVehicleConfiguration())
                        ->setSubVehicleConfiguration($subVehicleConfiguration)
                        ->setSpecialVehicleProperty($allSpecialProperties[$key])
                        ->setGivenByVehicleConfigurationKey(false)
                        ->setSpecialVehiclePropertyValue($inserted)
                        ->setVisibleOnReport(false)
                    );
                } else {
                    $current = reset($currentKeyFeaturesArray);
                    $inserted = $dataAndManagersAccess->getSpecialVehPropertiesValueManager()
                        ->find($current->getSpecialVehiclePropertyValue());
                    $inserted->setValueInteger(null);
                    $inserted->setValueString(null);
                    $inserted->setValueBool(null);

                    switch (gettype($longKeyModel->$value())) {
                        case VariableTypes::VARIABLE_TYPE_BOOLEAN:
                            $inserted->setValueBool($longKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_STRING:
                            $inserted->setValueString($longKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_INTEGER:
                            $inserted->setValueInteger($longKeyModel->$value());
                            break;
                    }

                    $this->entityManager->persist($inserted);
                }
            }
            $this->entityManager->flush();

            $containmentOld = $dataAndManagersAccess->getEcuContainmentManager()->findBy(
                ['subVehicleConfiguration' => $longKeyModel->getSubConfigurationId()]);
            $ecus = $longKeyModel->getEcus();

            foreach ($ecus as $ecu) {
                $ecuFlag = false;
                if (!empty($containmentOld)) {
                    $containmentOldFiltered = array_filter($containmentOld, function ($element) use ($ecu, &$ecuFlag)
                    {
                        if ($ecu == $element->getCeEcu()->getCeEcuId()) {
                            $ecuFlag = true;
                        }

                        return $ecu == $element->getCeEcu()->getCeEcuId();
                    });

                    $containmentOld = array_filter($containmentOld, function ($element) use ($ecu, &$ecuFlag)
                    {
                        return $ecu != $element->getCeEcu()->getCeEcuId();
                    });
                }

                if (!$ecuFlag) {
                    $containmentNew = new EcuSubConfigurationVehicleContainment();

                    $containmentNew->setCeEcu($allEcus[$ecu])
                        ->setSubVehicleConfiguration($subVehicleConfiguration)
                        ->setEbomPart(self::NEW_EBOM)
                        ->setOdxSourceType($odxSource)
                        ->setEcuParametersReleaseStatus($dataAndManagersAccess->getReleaseStatusManager()->find
                        (self::UNDER_DEVELOPMENT_STATUS));
                    $this->entityManager->persist($containmentNew);

                    $containment = $containmentNew;
                } else {
                    $containment = reset($containmentOldFiltered);
                }

                if (!empty($longKeyModel->getCans())) {
                    $canForwarding = current($longKeyModel->getCans());
                    if ($canForwarding != $ecu && $containment->getCanForwarding() == true) {
                        $containment->setCanForwarding(false);
                    } elseif ($canForwarding == $containment->getCeEcu()->getCeEcuId()) {
                        $containment->setCanForwarding(true);
                    } else {
                        $containment->setCanForwarding(false);
                    }
                    $this->entityManager->persist($containment);

                    $this->entityManager->flush();
                }
            }

            $ecuVersionSubConfigMapping = $dataAndManagersAccess->getSwSuVehConfMappingManager()->findBy(
                ['subVehicleConfiguration' => $longKeyModel->getSubConfigurationId()]);

            $ecuSwVerSubVehConfMapOverwrite = $dataAndManagersAccess->getParameterEcuSwMappingOverwriteManager()
                ->findBy(['subVehicleConfiguration' => $longKeyModel->getSubConfigurationId()]);


                foreach ($containmentOld as $mapped) {
                    $currentEcuMapping = array_filter($ecuVersionSubConfigMapping, function ($element) use ($mapped)
                    {
                        return $mapped->getCeEcu()->getCeEcuId() == $element->getEcuSwVersion()->getCeEcu()->getCeEcuId();
                    });

                $currentOverwriteMapping = array_filter($ecuSwVerSubVehConfMapOverwrite, function ($element) use ($mapped)
                {
                    return $mapped->getCeEcu()->getCeEcuId() == $element->getEcuSwVersion()->getCeEcu()->getCeEcuId();
                });

                foreach ($currentEcuMapping as $map) {
                    $this->entityManager->remove($map);
                }

                foreach ($currentOverwriteMapping as $map) {
                    $this->entityManager->remove($map->getEcuSwParameterValueSet());
                }

                $this->entityManager->remove($mapped);
            }

            $subVehicleConfiguration->setReleaseStatus($dataAndManagersAccess->getReleaseStatusManager()->find
            ($longKeyModel->getReleaseState()));

            if ($subVehicleConfiguration->getDraft()) {
                $subVehicleConfiguration->setDraft(false);
            }
            $subVehicleConfiguration->setReleasedByUser($user);
            $subVehicleConfiguration->setReleaseDate(new \DateTime(date("Y-m-d H:i:s")));

            $this->entityManager->persist($subVehicleConfiguration);

            $this->entityManager->flush();

            /* Commit */
            $this->entityManager->commit();
        } catch (\Exception $exception) {
            /* Rollback */
            $this->entityManager->rollBack();
            throw $exception;
        }

        return new ConfigurationSearch(
            $subVehicleConfiguration->getSubVehicleConfigurationId(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleTypeName(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleTypeYear(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleSeries(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleCustomerKey()
        );
    }

    /**
     * @param ShortKeyModel $shortKeyModel
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveFixShortKey(ShortKeyModel $shortKeyModel): ConfigurationSearch
    {
        /* Start transaction */
        $this->entityManager->beginTransaction();

        $dataAndManagersAccess = $this->getDataAccess($shortKeyModel);

        $generatedKey = $this->generateShortKey($shortKeyModel);

        $allProperties = $dataAndManagersAccess->getAllProperties();
        $allSpecialProperties = $dataAndManagersAccess->getAllSpecialProperties();
        $allSymbols = $dataAndManagersAccess->getAllSymbols();
        $allEcus = $dataAndManagersAccess->getAllEcus();
        $odxSource = $dataAndManagersAccess->getOdxSource();


        $subVehicleConfiguration = $dataAndManagersAccess->getSubConfigurationManager()
            ->find($shortKeyModel->getSubConfigurationId());
        $linkedSubconfigurations = $dataAndManagersAccess->getSubConfigurationManager()
            ->findBy(['vehicleConfiguration' => $subVehicleConfiguration->getVehicleConfiguration()]);

        try {

            $vehicleConfiguration = $subVehicleConfiguration->getVehicleConfiguration();
            $keyIndex = explode('_',
                explode('#', $subVehicleConfiguration->getSubVehicleConfigurationName())[1])[0];

            $vehicleConfiguration->setVehicleTypeName($shortKeyModel->getType())
                ->setVehicleTypeYear($shortKeyModel->getYear())
                ->setVehicleSeries($shortKeyModel->getSeries())
                ->setVehicleConfigurationKey($generatedKey)
                ->setDefaultConfigurationColor($dataAndManagersAccess->getConfigurationColorsManager()
                    ->find($shortKeyModel->getStandardColor()))
                ->setDefaultProductionLocation($dataAndManagersAccess->getDepotsManager()->find
                ($shortKeyModel->getStsPlaceOfProduction()));
            $this->entityManager->persist($vehicleConfiguration);
            $colorKey = $vehicleConfiguration->getDefaultConfigurationColor()->getConfigurationColorKey();

            //Additional information - vehicle configuration
            $additionalInformation = $this->setConfigurationAdditionalInformation($shortKeyModel,
                $generatedKey, $vehicleConfiguration);
            $this->entityManager->persist($additionalInformation->getVehicleVariant());
            $this->entityManager->persist($additionalInformation->getPentaNumber());

            //IMPORTANT!
            $subVehicleConfiguration->setDraft(false);
            $vehicleConfiguration->setDraft(false);

            $subVehicleConfiguration->setVehicleConfiguration($vehicleConfiguration)
                ->setSubVehicleConfigurationName("{$generatedKey }#{$keyIndex}")
                ->setVehicleConfigurationState($dataAndManagersAccess->getVehicleConfStateManager()->find
                (self::VEHICLE_UNDER_DEVELOPMENT_STATE))
                ->setReleaseStatus($dataAndManagersAccess->getReleaseStatusManager()->find(self::UNDER_DEVELOPMENT_STATUS));

            $this->entityManager->persist($subVehicleConfiguration);

            foreach ($linkedSubconfigurations as $linked) {
                $currentKey = explode('_',
                    explode('#', $linked->getSubVehicleConfigurationName())[1])[0];
                $linked->setSubVehicleConfigurationName("{$generatedKey }#{$currentKey}");
                $this->entityManager->persist($linked);

                $currentPenta = $dataAndManagersAccess->getPentaVariantsManager()
                    ->findOneBy(['subVehicleConfiguration' => $linked]);
                if (is_null($currentPenta)) {
                    //Additional information - sub-vehicle configuration
                    $additionalInformation->addSubConfPentaNumber($keyIndex);
                    $this->entityManager->persist($additionalInformation->getPentaNumberSub());

                    $currentPenta = new PentaVariants();
                    $currentPenta->setSubVehicleConfiguration($linked)
                        ->setConfigurationColor($dataAndManagersAccess->getConfigurationColorsManager()
                            ->find($shortKeyModel->getStandardColor()))
                        ->setPentaVariantName("{$generatedKey}#{$currentKey}_{$colorKey}")
                        ->setOldPentaNumber($additionalInformation->getPentaNumberSub());
                } else {
                    //Additional information - sub-vehicle configuration
                    $additionalInformation->updateSubConfPentaNumber($keyIndex, $currentPenta->getOldPentaNumber());
                    $this->entityManager->persist($additionalInformation->getPentaNumberSub());

                    $currentColorKey = explode('_', $currentPenta->getPentaVariantName())[1];
                    $currentPenta->setPentaVariantName("{$generatedKey}#{$currentKey}_{$currentColorKey}")
                                    ->setOldPentaNumber($additionalInformation->getPentaNumberSub());
                }
                $this->entityManager->persist($currentPenta);
            }

            $dataKey = $dataAndManagersAccess->getDataKeyMethodsBefore();

            $properties = $dataAndManagersAccess->getVehConfPropertiesMappingManager()
                ->findBy(['vehicleConfiguration' => $vehicleConfiguration]);
            $currentPropertyArray = [];

            foreach ($dataKey as $key => $value) {
                $currentPropertyArray = array_filter($properties, function ($element) use ($key)
                {
                    return $element->getVcProperty()->getVcPropertyId() == $key;
                });

                if (empty($currentPropertyArray)) {
                    $this->entityManager->persist((new VehicleConfigurationPropertiesMapping())
                        ->setVehicleConfiguration($vehicleConfiguration)
                        ->setVcProperty($allProperties[$key])
                        ->setAllowedSymbols($allSymbols[$shortKeyModel->$value()]));
                } else {
                    $currentProperty = reset($currentPropertyArray);
                    $currentProperty->setVcProperty($allProperties[$key])
                        ->setAllowedSymbols($allSymbols[$shortKeyModel->$value()]);

                    $this->entityManager->persist($currentProperty);
                }
            }

            foreach ($properties as $property) {
                if (!isset($dataKey[$property->getVcProperty()->getVcPropertyId()])) {
                    $this->entityManager->remove($property);
                }
            }

            $dataSVPV = $dataAndManagersAccess->getPropertiesMethodsBefore();

            $specialPropertiesMapping = $dataAndManagersAccess->getSpecialPropertiesMappingManager()
                ->findBy(['subVehicleConfiguration' => $subVehicleConfiguration]);
            $currentKeyComponentArray = [];

            /* Additional key components */
            foreach ($dataSVPV[0] as $key => $value) {
                $currentKeyComponentArray = array_filter($specialPropertiesMapping, function ($element) use ($key)
                {
                    return $element->getSpecialVehicleProperty()->getSpecialVehiclePropertyId() == $key;
                });

                if (empty($currentKeyComponentArray)) {
                    $inserted = new SpecialVehiclePropertyValues();
                    switch (gettype($shortKeyModel->$value())) {
                        case VariableTypes::VARIABLE_TYPE_BOOLEAN:
                            $inserted->setValueBool($shortKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_STRING:
                            $inserted->setValueString($shortKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_INTEGER:
                            $inserted->setValueInteger($shortKeyModel->$value());
                            break;
                    }
                    $report = (substr($value, 0, 1) == 'g')
                        ? "is" . substr($value, 3, strlen($value))
                        . "Report" : $value . "Report";
                    $this->entityManager->persist($inserted);

                    $this->entityManager->persist((new SpecialVehiclePropertiesMapping())
                        ->setVehicleConfiguration($vehicleConfiguration)
                        ->setSubVehicleConfiguration($subVehicleConfiguration)
                        ->setSpecialVehicleProperty($allSpecialProperties[$key])
                        ->setGivenByVehicleConfigurationKey(false)
                        ->setSpecialVehiclePropertyValue($inserted)
                        ->setVisibleOnReport($shortKeyModel->$report())
                    );
                } else {
                    $current = reset($currentKeyComponentArray);
                    $inserted = $dataAndManagersAccess->getSpecialVehPropertiesValueManager()
                        ->find($current->getSpecialVehiclePropertyValue());
                    $inserted->setValueInteger(null);
                    $inserted->setValueString(null);
                    $inserted->setValueBool(null);

                    switch (gettype($shortKeyModel->$value())) {
                        case VariableTypes::VARIABLE_TYPE_BOOLEAN:
                            $inserted->setValueBool($shortKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_STRING:
                            $inserted->setValueString($shortKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_INTEGER:
                            $inserted->setValueInteger($shortKeyModel->$value());
                            break;
                    }
                    $this->entityManager->persist($inserted);
                }
            }

            $currentKeyFeaturesArray = [];

            /* Additional key features */
            foreach ($dataSVPV[1] as $key => $value) {
                $currentKeyFeaturesArray = array_filter($specialPropertiesMapping, function ($element) use ($key)
                {
                    return $element->getSpecialVehicleProperty()->getSpecialVehiclePropertyId() == $key;
                });

                if (empty($currentKeyFeaturesArray)) {
                    $inserted = new SpecialVehiclePropertyValues();
                    switch (gettype($shortKeyModel->$value())) {
                        case VariableTypes::VARIABLE_TYPE_BOOLEAN:
                            $inserted->setValueBool($shortKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_STRING:
                            $inserted->setValueString($shortKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_INTEGER:
                            $inserted->setValueInteger($shortKeyModel->$value());
                            break;
                    }

                    $this->entityManager->persist($inserted);

                    $this->entityManager->persist((new SpecialVehiclePropertiesMapping())
                        ->setVehicleConfiguration($vehicleConfiguration)
                        ->setSubVehicleConfiguration($subVehicleConfiguration)
                        ->setSpecialVehicleProperty($allSpecialProperties[$key])
                        ->setGivenByVehicleConfigurationKey(false)
                        ->setSpecialVehiclePropertyValue($inserted)
                        ->setVisibleOnReport(false)
                    );
                } else {
                    $current = reset($currentKeyFeaturesArray);
                    $inserted = $dataAndManagersAccess->getSpecialVehPropertiesValueManager()
                        ->find($current->getSpecialVehiclePropertyValue());
                    $inserted->setValueInteger(null);
                    $inserted->setValueString(null);
                    $inserted->setValueBool(null);

                    switch (gettype($shortKeyModel->$value())) {
                        case VariableTypes::VARIABLE_TYPE_BOOLEAN:
                            $inserted->setValueBool($shortKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_STRING:
                            $inserted->setValueString($shortKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_INTEGER:
                            $inserted->setValueInteger($shortKeyModel->$value());
                            break;
                    }
                    $this->entityManager->persist($inserted);
                }
            }
            $this->entityManager->flush();

            $containmentOld = $dataAndManagersAccess->getEcuContainmentManager()->findBy(
                ['subVehicleConfiguration' => $shortKeyModel->getSubConfigurationId()]);
            $ecus = $shortKeyModel->getEcus();

            foreach ($ecus as $ecu) {
                $ecuFlag = false;
                if (!empty($containmentOld)) {
                    $containmentOldFiltered = array_filter($containmentOld, function ($element) use ($ecu, &$ecuFlag)
                    {
                        if ($ecu == $element->getCeEcu()->getCeEcuId()) {
                            $ecuFlag = true;
                        }

                        return $ecu == $element->getCeEcu()->getCeEcuId();
                    });

                    $containmentOld = array_filter($containmentOld, function ($element) use ($ecu, &$ecuFlag)
                    {
                        return $ecu != $element->getCeEcu()->getCeEcuId();
                    });
                }

                if (!$ecuFlag) {
                    $containmentNew = new EcuSubConfigurationVehicleContainment();

                    $containmentNew->setCeEcu($allEcus[$ecu])
                        ->setSubVehicleConfiguration($subVehicleConfiguration)
                        ->setEbomPart(self::NEW_EBOM)
                        ->setOdxSourceType($odxSource)
                        ->setEcuParametersReleaseStatus($dataAndManagersAccess->getReleaseStatusManager()->find
                        (self::UNDER_DEVELOPMENT_STATUS));
                    $this->entityManager->persist($containmentNew);

                    $containment = $containmentNew;
                } else {
                    $containment = reset($containmentOldFiltered);
                }

                if (!empty($shortKeyModel->getCans())) {
                    $canForwarding = current($shortKeyModel->getCans());
                    if ($canForwarding != $ecu && $containment->getCanForwarding() == true) {
                        $containment->setCanForwarding(false);
                    } elseif ($canForwarding == $containment->getCeEcu()->getCeEcuId()) {
                        $containment->setCanForwarding(true);
                    } else {
                        $containment->setCanForwarding(false);
                    }
                    $this->entityManager->persist($containment);

                    $this->entityManager->flush();
                }
            }

            $ecuVersionSubConfigMapping = $dataAndManagersAccess->getSwSuVehConfMappingManager()->findBy(
                ['subVehicleConfiguration' => $shortKeyModel->getSubConfigurationId()]);

            $ecuSwVerSubVehConfMapOverwrite = $dataAndManagersAccess->getParameterEcuSwMappingOverwriteManager()
                ->findBy(['subVehicleConfiguration' => $shortKeyModel->getSubConfigurationId()]);

            foreach ($containmentOld as $mapped) {
                $currentEcuMapping = array_filter($ecuVersionSubConfigMapping, function ($element) use ($mapped)
                {
                    return $mapped->getCeEcu()->getCeEcuId() == $element->getEcuSwVersion()->getCeEcu()->getCeEcuId();
                });

                $currentOverwriteMapping = array_filter($ecuSwVerSubVehConfMapOverwrite, function ($element) use ($mapped)
                {
                    return $mapped->getCeEcu()->getCeEcuId() == $element->getEcuSwVersion()->getCeEcu()->getCeEcuId();
                });

                foreach ($currentEcuMapping as $map) {
                    $this->entityManager->remove($map);
                }

                foreach ($currentOverwriteMapping as $map) {
                    $this->entityManager->remove($map->getEcuSwParameterValueSet());
                }

                $this->entityManager->remove($mapped);
            }
            $this->entityManager->flush();

            $penta = $dataAndManagersAccess->getPentaVariantsManager()
                ->findOneBy(['subVehicleConfiguration' => $subVehicleConfiguration]);


            if (is_null($penta)) {
                //Additional information - sub-vehicle configuration
                $additionalInformation->addSubConfPentaNumber($keyIndex);
                $this->entityManager->persist($additionalInformation->getPentaNumberSub());

                $penta = new PentaVariants();
                $penta->setConfigurationColor($dataAndManagersAccess->getConfigurationColorsManager()
                    ->find($shortKeyModel->getStandardColor()))
                    ->setSubVehicleConfiguration($subVehicleConfiguration)
                    ->setPentaVariantName("{$generatedKey}#{$keyIndex}_{$colorKey}")
                    ->setOldPentaNumber($additionalInformation->getPentaNumberSub());
            } else {
                //Additional information - sub-vehicle configuration
                $additionalInformation->updateSubConfPentaNumber($keyIndex, $currentPenta->getOldPentaNumber());
                $this->entityManager->persist($additionalInformation->getPentaNumberSub());

                $pentaColorKey = explode('_', $penta->getPentaVariantName())[1];

                $penta
                    ->setSubVehicleConfiguration($subVehicleConfiguration)
                    ->setPentaVariantName("{$generatedKey}#{$keyIndex}_{$pentaColorKey}")
                    ->setOldPentaNumber($additionalInformation->getPentaNumberSub());
            }
            $this->entityManager->persist($penta);

            $this->entityManager->flush();

            /* Commit */
            $this->entityManager->commit();
        } catch (\Exception $exception) {
            /* Rollback */
            $this->entityManager->rollBack();
            throw $exception;
        }

        return new ConfigurationSearch(
            $subVehicleConfiguration->getSubVehicleConfigurationId(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleTypeName(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleTypeYear(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleSeries(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleCustomerKey()
        );
    }

    /**
     * @param LongKeyModel $longKeyModel
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    public function saveFixLongKey(LongKeyModel $longKeyModel): ConfigurationSearch
    {
        /* Start transaction */
        $this->entityManager->beginTransaction();

        $dataAndManagersAccess = $this->getDataAccess($longKeyModel);

        $generatedKey = $this->generateLongKey($longKeyModel);

        $allProperties = $dataAndManagersAccess->getAllProperties();
        $allSpecialProperties = $dataAndManagersAccess->getAllSpecialProperties();
        $allSymbols = $dataAndManagersAccess->getAllSymbols();
        $allEcus = $dataAndManagersAccess->getAllEcus();
        $odxSource = $dataAndManagersAccess->getOdxSource();

        $subVehicleConfiguration = $dataAndManagersAccess->getSubConfigurationManager()
            ->find($longKeyModel->getSubConfigurationId());
        $linkedSubconfigurations = $dataAndManagersAccess->getSubConfigurationManager()
            ->findBy(['vehicleConfiguration' => $subVehicleConfiguration->getVehicleConfiguration()]);

        $colorKey = $dataAndManagersAccess->getConfigurationColorsManager()
            ->findOneBy(["allowedSymbols" => $longKeyModel->getColor()])->getConfigurationColorKey();

        $generatedShortProductionDescription = $this->generateShortProductionDescriptionLongKey($longKeyModel);

        try {
            $vehicleConfiguration = $subVehicleConfiguration->getVehicleConfiguration();
            $keyIndex = explode('_',
                explode('#', $subVehicleConfiguration->getSubVehicleConfigurationName())[1])[0];

            $vehicleConfiguration->setVehicleTypeName($longKeyModel->getType())
                ->setVehicleTypeYear($longKeyModel->getYear())
                ->setVehicleSeries($longKeyModel->getSeries())
                ->setVehicleConfigurationKey($generatedKey)
                ->setDefaultConfigurationColor($dataAndManagersAccess->getConfigurationColorsManager()
                    ->findOneBy(["allowedSymbols" => $longKeyModel->getColor()]))
                ->setDefaultProductionLocation($dataAndManagersAccess->getDepotsManager()->find
                ($longKeyModel->getStsPlaceOfProduction()))
                ->setVehicleCustomerKey($longKeyModel->getCustomerKey());
            $this->entityManager->persist($vehicleConfiguration);

            //Additional information - vehicle configuration
            $additionalInformation = $this->setConfigurationAdditionalInformation($longKeyModel,
                $generatedKey, $vehicleConfiguration);
            $this->entityManager->persist($additionalInformation->getVehicleVariant());
            $this->entityManager->persist($additionalInformation->getPentaNumber());

            //IMPORTANT!
            $subVehicleConfiguration->setDraft(false);
            $vehicleConfiguration->setDraft(false);

            $subVehicleConfiguration->setVehicleConfiguration($vehicleConfiguration)
                ->setSubVehicleConfigurationName("{$generatedKey }#{$keyIndex}")
                ->setVehicleConfigurationState($dataAndManagersAccess->getVehicleConfStateManager()->find
                (self::VEHICLE_UNDER_DEVELOPMENT_STATE))
                ->setReleaseStatus($dataAndManagersAccess->getReleaseStatusManager()
                    ->find(self::UNDER_DEVELOPMENT_STATUS));

            $this->entityManager->persist($subVehicleConfiguration);

            foreach ($linkedSubconfigurations as $linked) {
                $currentKey = explode('_',
                    explode('#', $linked->getSubVehicleConfigurationName())[1])[0];

                $currentKeyShort = (int) $currentKey;

                $linked->setSubVehicleConfigurationName("{$generatedKey }#{$currentKey}")
                    ->setShortProductionDescription("{$generatedShortProductionDescription}{$currentKeyShort}");

                $this->entityManager->persist($linked);

                $currentPenta = $dataAndManagersAccess->getPentaVariantsManager()
                    ->findOneBy(['subVehicleConfiguration' => $linked]);

                if (is_null($currentPenta)) {
                    //Additional information - sub-vehicle configuration
                    $additionalInformation->addSubConfPentaNumber($keyIndex);
                    $this->entityManager->persist($additionalInformation->getPentaNumberSub());

                    $currentPenta = new PentaVariants();
                    $currentPenta->setSubVehicleConfiguration($linked)
                        ->setConfigurationColor($dataAndManagersAccess->getConfigurationColorsManager()->findOneBy(
                            ["allowedSymbols" => $longKeyModel->getColor()]))
                        ->setPentaVariantName("{$generatedKey}#{$currentKey}_{$colorKey}")
                        ->setOldPentaNumber($additionalInformation->getPentaNumberSub());
                } else {
                    //Additional information - sub-vehicle configuration
                    $additionalInformation->updateSubConfPentaNumber($keyIndex, $currentPenta->getOldPentaNumber());
                    $this->entityManager->persist($additionalInformation->getPentaNumberSub());

                    $currentColorKey = explode('_', $currentPenta->getPentaVariantName())[1];
                    $currentPenta->setPentaVariantName("{$generatedKey}#{$currentKey}_{$currentColorKey}")
                                    ->setOldPentaNumber($additionalInformation->getPentaNumberSub());
                }
                $this->entityManager->persist($currentPenta);
            }

            $dataKey = $dataAndManagersAccess->getDataKeyMethodsAfter();


            $properties = $dataAndManagersAccess->getVehConfPropertiesMappingManager()
                ->findBy(['vehicleConfiguration' => $vehicleConfiguration]);
            $currentPropertyArray = [];

            foreach ($dataKey as $key => $value) {
                $currentPropertyArray = array_filter($properties, function ($element) use ($key)
                {
                    return $element->getVcProperty()->getVcPropertyId() == $key;
                });

                if (empty($currentPropertyArray)) {
                    $this->entityManager->persist((new VehicleConfigurationPropertiesMapping())
                        ->setVehicleConfiguration($vehicleConfiguration)
                        ->setVcProperty($allProperties[$key])
                        ->setAllowedSymbols($allSymbols[$longKeyModel->$value()]));
                } else {
                    $currentProperty = reset($currentPropertyArray);
                    $currentProperty->setVcProperty($allProperties[$key])
                        ->setAllowedSymbols($allSymbols[$longKeyModel->$value()]);

                    $this->entityManager->persist($currentProperty);
                }
            }

            foreach ($properties as $property) {
                if (!isset($dataKey[$property->getVcProperty()->getVcPropertyId()])) {
                    $this->entityManager->remove($property);
                }
            }


            $keyFeatures = $dataAndManagersAccess->getPropertiesMethodsAfter();

            $specialPropertiesMapping = $dataAndManagersAccess->getSpecialPropertiesMappingManager()->findBy(
                ['subVehicleConfiguration' => $subVehicleConfiguration]);

            /* Additional key features */
            foreach ($keyFeatures as $key => $value) {
                $currentKeyFeaturesArray = array_filter($specialPropertiesMapping, function ($element) use ($key)
                {
                    return $element->getSpecialVehicleProperty()->getSpecialVehiclePropertyId() == $key;
                });

                if (empty($currentKeyFeaturesArray)) {
                    $inserted = new SpecialVehiclePropertyValues();
                    switch (gettype($longKeyModel->$value())) {
                        case VariableTypes::VARIABLE_TYPE_BOOLEAN:
                            $inserted->setValueBool($longKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_STRING:
                            $inserted->setValueString($longKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_INTEGER:
                            $inserted->setValueInteger($longKeyModel->$value());
                            break;
                    }

                    $this->entityManager->persist($inserted);

                    $this->entityManager->persist((new SpecialVehiclePropertiesMapping())
                        ->setVehicleConfiguration($vehicleConfiguration)
                        ->setSubVehicleConfiguration($subVehicleConfiguration)
                        ->setSpecialVehicleProperty($allSpecialProperties[$key])
                        ->setGivenByVehicleConfigurationKey(false)
                        ->setSpecialVehiclePropertyValue($inserted)
                        ->setVisibleOnReport(false)
                    );
                } else {
                    $current = reset($currentKeyFeaturesArray);
                    $inserted = $dataAndManagersAccess->getSpecialVehPropertiesValueManager()
                        ->find($current->getSpecialVehiclePropertyValue());
                    $inserted->setValueInteger(null);
                    $inserted->setValueString(null);
                    $inserted->setValueBool(null);

                    switch (gettype($longKeyModel->$value())) {
                        case VariableTypes::VARIABLE_TYPE_BOOLEAN:
                            $inserted->setValueBool($longKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_STRING:
                            $inserted->setValueString($longKeyModel->$value());
                            break;
                        case VariableTypes::VARIABLE_TYPE_INTEGER:
                            $inserted->setValueInteger($longKeyModel->$value());
                            break;
                    }
                    $this->entityManager->persist($inserted);
                }
            }
            $this->entityManager->flush();

            $containmentOld = $dataAndManagersAccess->getEcuContainmentManager()->findBy(
                ['subVehicleConfiguration' => $longKeyModel->getSubConfigurationId()]);
            $ecus = $longKeyModel->getEcus();

            foreach ($ecus as $ecu) {
                $ecuFlag = false;
                if (!empty($containmentOld)) {
                    $containmentOldFiltered = array_filter($containmentOld, function ($element) use ($ecu, &$ecuFlag)
                    {
                        if ($ecu == $element->getCeEcu()->getCeEcuId()) {
                            $ecuFlag = true;
                        }

                        return $ecu == $element->getCeEcu()->getCeEcuId();
                    });

                    $containmentOld = array_filter($containmentOld, function ($element) use ($ecu, &$ecuFlag)
                    {
                        return $ecu != $element->getCeEcu()->getCeEcuId();
                    });
                }

                if (!$ecuFlag) {
                    $containmentNew = new EcuSubConfigurationVehicleContainment();

                    $containmentNew->setCeEcu($allEcus[$ecu])
                        ->setSubVehicleConfiguration($subVehicleConfiguration)
                        ->setEbomPart(self::NEW_EBOM)
                        ->setOdxSourceType($odxSource)
                        ->setEcuParametersReleaseStatus($dataAndManagersAccess->getReleaseStatusManager()->find
                        (self::UNDER_DEVELOPMENT_STATUS));
                    $this->entityManager->persist($containmentNew);

                    $containment = $containmentNew;
                } else {
                    $containment = reset($containmentOldFiltered);
                }

                if (!empty($longKeyModel->getCans())) {
                    $canForwarding = current($longKeyModel->getCans());
                    if ($canForwarding != $ecu && $containment->getCanForwarding() == true) {
                        $containment->setCanForwarding(false);
                    } elseif ($canForwarding == $containment->getCeEcu()->getCeEcuId()) {
                        $containment->setCanForwarding(true);
                    } else {
                        $containment->setCanForwarding(false);
                    }
                    $this->entityManager->persist($containment);

                    $this->entityManager->flush();
                }
            }

            $ecuVersionSubConfigMapping = $dataAndManagersAccess->getSwSuVehConfMappingManager()->findBy(
                ['subVehicleConfiguration' => $longKeyModel->getSubConfigurationId()]);

            $ecuSwVerSubVehConfMapOverwrite = $dataAndManagersAccess->getParameterEcuSwMappingOverwriteManager()
                ->findBy(['subVehicleConfiguration' => $longKeyModel->getSubConfigurationId()]);

            foreach ($containmentOld as $mapped) {
                $currentEcuMapping = array_filter($ecuVersionSubConfigMapping, function ($element) use ($mapped)
                {
                    return $mapped->getCeEcu()->getCeEcuId() == $element->getEcuSwVersion()->getCeEcu()->getCeEcuId();
                });

                $currentOverwriteMapping = array_filter($ecuSwVerSubVehConfMapOverwrite, function ($element) use ($mapped)
                {
                    return $mapped->getCeEcu()->getCeEcuId() == $element->getEcuSwVersion()->getCeEcu()->getCeEcuId();
                });

                foreach ($currentEcuMapping as $map) {
                    $this->entityManager->remove($map);
                }

                foreach ($currentOverwriteMapping as $map) {
                    $this->entityManager->remove($map->getEcuSwParameterValueSet());
                }

                $this->entityManager->remove($mapped);
            }
            $this->entityManager->flush();

            $penta = $dataAndManagersAccess->getPentaVariantsManager()->findOneBy(['subVehicleConfiguration' =>
                $subVehicleConfiguration]);


            if (is_null($penta)) {
                //Additional information - sub-vehicle configuration
                $additionalInformation->addSubConfPentaNumber($keyIndex);
                $this->entityManager->persist($additionalInformation->getPentaNumberSub());

                $penta = new PentaVariants();
                $penta->setConfigurationColor($dataAndManagersAccess->getConfigurationColorsManager()->findOneBy(
                    ["allowedSymbols" => $longKeyModel->getColor()]))
                    ->setSubVehicleConfiguration($subVehicleConfiguration)
                    ->setPentaVariantName("{$generatedKey}#{$keyIndex}_{$colorKey}")
                    ->setOldPentaNumber($additionalInformation->getPentaNumberSub());
            } else {
                //Additional information - sub-vehicle configuration
                $additionalInformation->updateSubConfPentaNumber($keyIndex, $currentPenta->getOldPentaNumber());
                $this->entityManager->persist($additionalInformation->getPentaNumberSub());

                $pentaColorKey = explode('_', $penta->getPentaVariantName())[1];
                $penta
                    ->setSubVehicleConfiguration($subVehicleConfiguration)
                    ->setPentaVariantName("{$generatedKey}#{$keyIndex}_{$pentaColorKey}")
                    ->setOldPentaNumber($additionalInformation->getPentaNumberSub());
            }
            $this->entityManager->persist($penta);

            $this->entityManager->flush();

            /* Commit */
            $this->entityManager->commit();
        } catch (\Exception $exception) {
            /* Rollback */
            $this->entityManager->rollBack();
            throw $exception;
        }

        return new ConfigurationSearch(
            $subVehicleConfiguration->getSubVehicleConfigurationId(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleTypeName(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleTypeYear(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleSeries(),
            $subVehicleConfiguration->getVehicleConfiguration()->getVehicleCustomerKey()
        );
    }

    /**
     * @param SubVehicleConfigurations $subconfiguration
     *
     * @return bool
     * @throws \Exception
     */
    public function deleteSubConfiguration(SubVehicleConfigurations $subconfiguration) : bool
    {
        /* Start transaction */
        $this->entityManager->beginTransaction();

        $dataAndManagersAccess = $this->getDataAccess(null);
        $vehicleConfiguration = $subconfiguration->getVehicleConfiguration();
        try {
            $parameterEcuSwMappingOverwrite = $dataAndManagersAccess->getParameterEcuSwMappingOverwriteManager()
                ->findBy(['subVehicleConfiguration' => $subconfiguration]);

            foreach ($parameterEcuSwMappingOverwrite as $item) {
                $this->entityManager->remove($item->getEcuSwParameterValueSet());
                $this->entityManager->remove($item);
            }

            $cocParameterRelease = $this->manager->getRepository(CocParameterRelease::class)->find($subconfiguration);
            if (!is_null($cocParameterRelease)) {
                $this->manager->remove($cocParameterRelease);
            }

            $swSuVehConfMapping = $dataAndManagersAccess->getSwSuVehConfMappingManager()
                ->findBy(['subVehicleConfiguration' => $subconfiguration]);

            foreach ($swSuVehConfMapping as $item) {
                $this->entityManager->remove($item);
            }

            $ecuContainment = $dataAndManagersAccess->getEcuContainmentManager()
                ->findBy(['subVehicleConfiguration' => $subconfiguration]);

            foreach ($ecuContainment as $item) {
                $this->entityManager->remove($item);
            }

            $pentaVariants = $dataAndManagersAccess->getPentaVariantsManager()
                ->findBy(['subVehicleConfiguration' => $subconfiguration]);

            foreach ($pentaVariants as $item) {
                $this->entityManager->remove($item->getOldPentaNumber());
                $this->entityManager->remove($item);
            }

            $specialProperties = $dataAndManagersAccess->getSpecialPropertiesMappingManager()
                ->findBy(['subVehicleConfiguration' => $subconfiguration]);

            foreach ($specialProperties as $item) {
                $this->entityManager->remove($item->getSpecialVehiclePropertyValue());
                $this->entityManager->remove($item);
            }

            $this->entityManager->remove($subconfiguration);

            $this->entityManager->flush();

            $otherSubconfigurations = $this->manager->getRepository(SubVehicleConfigurations::class)
                ->findBy(['vehicleConfiguration' => $vehicleConfiguration]);

            if (empty($otherSubconfigurations)) {
                $confService = new Configurations($this->manager, $this->entityManager, $this);
                $confService->deleteConfiguration($vehicleConfiguration);
            }

            /* Commit */
            $this->entityManager->commit();
        } catch (\Exception $exception) {
            /* Rollback */
            $this->entityManager->rollBack();
            throw $exception;
        }

        return true;
    }
}

