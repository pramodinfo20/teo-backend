<?php

namespace App\Service\Vehicles\Configuration;

use App\Converter\Vin\VinBatchConverter;
use App\Entity\AllowedSymbols;
use App\Entity\Colors;
use App\Entity\ConfigurationColors;
use App\Entity\Depots;
use App\Entity\PentaNumbers;
use App\Entity\PentaVariants;
use App\Entity\SpecialVehiclePropertiesMapping;
use App\Entity\SpecialVehiclePropertyValues;
use App\Entity\SubVehicleConfigurations;
use App\Entity\VehicleConfigurationProperties;
use App\Entity\VehicleConfigurationPropertiesMapping;
use App\Entity\VehicleConfigurationPropertiesSymbols;
use App\Entity\VehicleConfigurations;
use App\Entity\VehicleVariants;
use App\Model\LongKeyModel;
use App\Model\ShortKeyModel;
use App\Model\ConfigurationI;
use App\Model\Vehicles\Configuration\ConfigurationSearch;
use App\Service\History\Vehicles\Configuration\HistoryConfigurationsI;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class VehicleConfigurations
 * @package App\Service\Vehicles\Configuration
 */
class Configurations implements HistoryConfigurationsI
{
    const FIX_MODE = 1;
    const EDIT_MODE = 2;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
    * @var SubConfiguration
    */
    private $subConfService;


    /**
     * Parameter constructor.
     *
     * @param ObjectManager $manager
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ObjectManager $manager, EntityManagerInterface $entityManager, SubConfiguration $subConfService)
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
        $this->subConfService = $subConfService;
    }

    public function getKeyParametersToView(VehicleConfigurations $configuration) : array
    {
        $confKey = $configuration->getVehicleConfigurationKey();

        $typeYearSeries = $this->manager->getRepository(VehicleConfigurations::class)->getTypeSeriesYear($configuration->getVehicleConfigurationId());

        $parameters = $this->manager->getRepository(VehicleConfigurations::class)->getAssignedParameters($configuration->getVehicleConfigurationId());

        usort($parameters, function ($item1, $item2) {
            return $item1['positionInVehicleConfigurationKey'] <=> $item2['positionInVehicleConfigurationKey'];
        });

        // Parts and features corresponding to the configuration key
        $featuresCorrespondingToKey = [];
        foreach ($parameters as $parameter) {
            $temp['Key'] = $parameter['vehicleConfigurationPropertyName'];
            $temp['Description'] = "[ {$parameter['symbol']} ] - ". $parameter['description'];
            $temp['PropertyId'] = $parameter['vcPropertyId'];

            array_push($featuresCorrespondingToKey, $temp);
        }

        $vinMethod =  $configuration->getOldVehicleVariant()->getVinMethod();
        $chargerControllable = $configuration->getOldVehicleVariant()->getChargerControllable();

        return [
            [
                'VehicleConfigurationKey' => $confKey,
                'type' => $typeYearSeries['type'],
                'year' => $typeYearSeries['year'],
                'series' => $typeYearSeries['series'],
                'customerKey' => $typeYearSeries['customer_key'],
                'subVehConfigId' => null,
                'configurationId' => $configuration->getVehicleConfigurationId(),
                'vinMethod' => $vinMethod,
                'chargerControllable' => $chargerControllable
            ],
            $featuresCorrespondingToKey
        ];
    }

    /**
     * @param VehicleConfigurations $configuration
     * @return int
     */
    public function getConfigurationType(VehicleConfigurations $configuration): int
    {
        if ($this->subConfService->detectSubConfigurationVersion($configuration->getVehicleConfigurationKey()) == SubConfiguration::SHORT_KEY) {
            return SubConfiguration::SHORT_KEY;
        } else {
            return SubConfiguration::LONG_KEY;
        }
    }

    /**
     * @param VehicleConfigurations $configuration
     *
     * @return LongKeyModel|ShortKeyModel
     */
    public function getModelToEdit(VehicleConfigurations $configuration)
    {
        if ($this->getConfigurationType($configuration) == SubConfiguration::SHORT_KEY) {
            return $this->getShortKeyModelToEdit($configuration);
        } else {
            return $this->getLongKeyModelToEdit($configuration);
        }

    }

    /**
     * @param VehicleConfigurations $configuration
     *
     * @return LongKeyModel
     */
    private function getLongKeyModelToEdit(VehicleConfigurations $configuration): LongKeyModel
    {
        $propertiesMappingManager = $this->manager->getRepository(VehicleConfigurationPropertiesMapping::class);

        $properties = $propertiesMappingManager->findBy(['vehicleConfiguration' =>
            $configuration->getVehicleConfigurationId()]);

        $cProperties = [];
        foreach ($properties as $property) {
            $cProperties[$property->getVcProperty()->getVcPropertyId()] = $property->getAllowedSymbols()->getAllowedSymbolsId();
        }

        $longKeyModel = new LongKeyModel();

        /* Configuration id */
        $longKeyModel->setConfigurationId($configuration->getVehicleConfigurationId());

        /* Vehicle Configuration Keys */
        $longKeyModel->setVehicleConfigurationKey($configuration->getVehicleConfigurationKey());

        /* Key */
        $longKeyModel->setType($configuration->getVehicleTypeName());
        $longKeyModel->setYear($configuration->getVehicleTypeYear());
        $longKeyModel->setSeries($configuration->getVehicleSeries());
        $longKeyModel->setSeries($configuration->getVehicleSeries());
        $longKeyModel->setCustomerKey($configuration->getVehicleCustomerKey());
        $longKeyModel->setDevStatus($this->getProperty(LongKeyModel::DEV_STATUS, $cProperties));
        $longKeyModel->setBody($this->getProperty(LongKeyModel::BODY, $cProperties));
        $longKeyModel->setNumberDrive($this->getProperty(LongKeyModel::NUMBER_DRIVE, $cProperties));
        $longKeyModel->setEngineType($this->getProperty(LongKeyModel::ENGINE_TYPE, $cProperties));
        $longKeyModel->setStageOfCompletion($this->getProperty(LongKeyModel::STAGE_OF_COMPLETION, $cProperties));
        $longKeyModel->setBodyLength($this->getProperty(LongKeyModel::BODY_LENGTH, $cProperties));
        $longKeyModel->setFrontAxle($this->getProperty(LongKeyModel::FRONT_AXLE, $cProperties));
        $longKeyModel->setRearAxle($this->getProperty(LongKeyModel::REAR_AXLE, $cProperties));
        $longKeyModel->setZgg($this->getProperty(LongKeyModel::ZGG, $cProperties));
        $longKeyModel->setTypeOfFuel($this->getProperty(LongKeyModel::TYPE_OF_FUEL, $cProperties));
        $longKeyModel->setTractionBattery($this->getProperty(LongKeyModel::TRACTION_BATTERY, $cProperties));
        $longKeyModel->setChargingSystem($this->getProperty(LongKeyModel::CHARGING_SYSTEM, $cProperties));
        $longKeyModel->setVMax($this->getProperty(LongKeyModel::VMAX, $cProperties));
        $longKeyModel->setSeats($this->getProperty(LongKeyModel::SEATS, $cProperties));
        $longKeyModel->setTrailerHitch($this->getProperty(LongKeyModel::TRAILER_HITCH, $cProperties));
        $longKeyModel->setSuperstructures($this->getProperty(LongKeyModel::SUPERSTRUCTURES, $cProperties));
        $longKeyModel->setEnergySupplySuperStructure($this->getProperty(LongKeyModel::ENERGY_SUPPLY_SUPERSTRUCTURE, $cProperties));
        $longKeyModel->setSteering($this->getProperty(LongKeyModel::STEERING, $cProperties));
        $longKeyModel->setRearWindow($this->getProperty(LongKeyModel::REAR_WINDOW, $cProperties));
        $longKeyModel->setAirConditioning($this->getProperty(LongKeyModel::AIR_CONDITIONING, $cProperties));
        $longKeyModel->setpassengerAirbag($this->getProperty(LongKeyModel::PASSENGER_AIRBAG, $cProperties));
        $longKeyModel->setKeyless($this->getProperty(LongKeyModel::KEYLESS, $cProperties));
        $longKeyModel->setSpecialApplicationArea($this->getProperty(LongKeyModel::SPECIAL_APPLICATION_AREA, $cProperties));
        $longKeyModel->setRadio($this->getProperty(LongKeyModel::RADIO, $cProperties));
        $longKeyModel->setSoundGenerator($this->getProperty(LongKeyModel::SOUND_GENERATOR, $cProperties));
        $longKeyModel->setCountryCode($this->getProperty(LongKeyModel::COUNTRY_CODE, $cProperties));
        $longKeyModel->setColor($this->getProperty(LongKeyModel::COLOR, $cProperties));
        $longKeyModel->setWheeling($this->getProperty(LongKeyModel::WHEELING, $cProperties));

        /* Additional information */
        $longKeyModel->setVinMethod($configuration->getOldVehicleVariant()->getVinMethod());
        $longKeyModel->setChargerControllable($configuration->getOldVehicleVariant()->getChargerControllable());

        /* Additional key features */
        $longKeyModel->setStsPlaceOfProduction((!is_null($configuration->getDefaultProductionLocation())) ?
            $configuration->getDefaultProductionLocation()->getDepotId() : null);


        return $longKeyModel;
    }
    /**
     * @param int $key
     * @param array $properties
     * @return int|null
     */
    private function getProperty(int $key, array $properties) : ?int
    {
        return array_key_exists($key, $properties) ? $properties[$key] : null;
    }
    

    /**
     * @param VehicleConfigurations $configuration
     *
     * @return ShortKeyModel
     */
    private function getShortKeyModelToEdit(VehicleConfigurations $configuration): ShortKeyModel
    {
        $propertiesMappingManager = $this->manager->getRepository(VehicleConfigurationPropertiesMapping::class);

        $properties = $propertiesMappingManager->findBy(['vehicleConfiguration' =>
            $configuration->getVehicleConfigurationId()]);

        $cProperties = [];
        foreach ($properties as $property) {
            $cProperties[$property->getVcProperty()->getVcPropertyId()] = $property->getAllowedSymbols()->getAllowedSymbolsId();
        }


        $shortKeyModel = new ShortKeyModel();

        /* Configuration id */
        $shortKeyModel->setConfigurationId($configuration->getVehicleConfigurationId());

        /* Vehicle Configuration Keys */
        $shortKeyModel->setVehicleConfigurationKey($configuration->getVehicleConfigurationKey());

        /* Key */
        $shortKeyModel->setType($configuration->getVehicleTypeName());
        $shortKeyModel->setYear($configuration->getVehicleTypeYear());
        $shortKeyModel->setSeries($configuration->getVehicleSeries());
        $shortKeyModel->setLayout($this->getProperty(ShortKeyModel::LAYOUT, $cProperties));
        $shortKeyModel->setFeature($this->getProperty(ShortKeyModel::FEATURE, $cProperties));
        $shortKeyModel->setBattery($this->getProperty(ShortKeyModel::BATTERY, $cProperties));

        /* Additional information */
        $shortKeyModel->setVinMethod($configuration->getOldVehicleVariant()->getVinMethod());
        $shortKeyModel->setChargerControllable($configuration->getOldVehicleVariant()->getChargerControllable());

        /* Additional key features */
        $shortKeyModel->setStandardColor((!is_null($configuration->getDefaultConfigurationColor())) ?
            $configuration->getDefaultConfigurationColor()->getConfigurationColorId() : null);
        $shortKeyModel->setStsPlaceOfProduction((!is_null($configuration->getDefaultProductionLocation())) ?
            $configuration->getDefaultProductionLocation()->getDepotId() : null);


        return $shortKeyModel;
    }


    private function getSaveManagers(VehicleConfigurations $configuration = null) {
        return
            new class($this->entityManager, $configuration)
            {
                private $configurationColorsManager;
                private $depotsManager;
                private $allowedSymbolsManager;
                private $propertiesManager;
                private $pentaManager;
                private $subConfigurationManager;
                private $subConfigurations;
                private $pentaVariants;
                private $allSymbols;
                private $allProperties;
                private $entityManager;
                private $propertiesMappingManager;
                private $mappedProperties;
                private $propertiesSymbolsManager;
                // Additional information
                private $pentaNumbersManager;
                private $vehicleVariantsManager;
                private $oldColorsManager;

                public function __construct($entityManager, $configuration)
                {
                    $this->entityManager = $entityManager;
                    $this->configurationColorsManager = $this->entityManager->getRepository(ConfigurationColors::class);
                    $this->depotsManager = $this->entityManager->getRepository(Depots::class);
                    $this->allowedSymbolsManager = $this->entityManager->getRepository(AllowedSymbols::class);
                    $this->propertiesManager = $this->entityManager->getRepository(VehicleConfigurationProperties::class);
                    $this->propertiesMappingManager = $this->entityManager->getRepository(VehicleConfigurationPropertiesMapping::class);
                    $this->pentaManager = $this->entityManager->getRepository(PentaVariants::class);
                    $this->subConfigurationManager = $this->entityManager->getRepository(SubVehicleConfigurations::class);
                    $this->sVehPropertiesMappingManger = $this->entityManager->getRepository
                        (SpecialVehiclePropertiesMapping::class);
                    $this->sVehPropertyValuesManager = $this->entityManager->getRepository
                        (SpecialVehiclePropertyValues::class);
                    // Additional information
                    $this->pentaNumbersManager = $this->entityManager->getRepository(PentaNumbers::class);
                    $this->vehicleVariantsManager = $this->entityManager->getRepository(VehicleVariants::class);
                    $this->oldColorsManager = $this->entityManager->getRepository(Colors::class);
                    $this->propertiesSymbolsManager = $this->entityManager->getRepository
                        (VehicleConfigurationPropertiesSymbols::class);

                    $this->mappedProperties = $this->propertiesMappingManager->findBy(['vehicleConfiguration' => $configuration]);
                    $this->subConfigurations = $this->subConfigurationManager->findBy(['vehicleConfiguration' => $configuration]);
                    $this->pentaVariants = $this->pentaManager->findBy(['subVehicleConfiguration' => $this->subConfigurations]);

                    $symbols = $this->allowedSymbolsManager->findAll();

                    $this->allSymbols = [];
                    foreach ($symbols as $symbol) {
                        $this->allSymbols[$symbol->getAllowedSymbolsId()] = $symbol;
                    }

                    $properties = $this->propertiesManager->findAll();

                    $this->allProperties = [];
                    foreach ($properties as $property) {
                        $this->allProperties[$property->getVcPropertyId()] = $property;
                    }
                }

                /**
                 * @return mixed
                 */
                public function getConfigurationColorsManager() {
                    return $this->configurationColorsManager;
                }

                /**
                 * @return mixed
                 */
                public function getDepotsManager() {
                    return $this->depotsManager;
                }

                /**
                 * @return mixed
                 */
                public function getAllowedSymbolsManager() {
                    return $this->allowedSymbolsManager;
                }

                /**
                 * @return mixed
                 */
                public function getPropertiesManager() {
                    return $this->propertiesManager;
                }

                /**
                 * @return mixed
                 */
                public function getPentaManager() {
                    return $this->pentaManager;
                }

                /**
                 * @return mixed
                 */
                public function getSubConfigurationManager() {
                    return $this->subConfigurationManager;
                }

                /**
                 * @return mixed
                 */
                public function getSubConfigurations() {
                    return $this->subConfigurations;
                }

                /**
                 * @return mixed
                 */
                public function getPentaVariants() {
                    return $this->pentaVariants;
                }

                /**
                 * @return array
                 */
                public function getAllSymbols(): array {
                    return $this->allSymbols;
                }

                /**
                 * @return array
                 */
                public function getAllProperties(): array {
                    return $this->allProperties;
                }

                /**
                 * @return mixed
                 */
                public function getPropertiesMappingManager() {
                    return $this->propertiesMappingManager;
                }

                /**
                 * @return mixed
                 */
                public function getMappedProperties() {
                    return $this->mappedProperties;
                }

                /**
                 * @return mixed
                 */
                public function getSVehPropertiesMappingManger()
                {
                    return $this->sVehPropertiesMappingManger;
                }

                /**
                 * @return mixed
                 */
                public function getSVehPropertyValuesManager()
                {
                    return $this->sVehPropertyValuesManager;
                }

                /**
                 * @return mixed
                 */
                public function getPentaNumbersManager()
                {
                    return $this->pentaNumbersManager;
                }

                /**
                 * @param mixed $pentaNumbersManager
                 */
                public function setPentaNumbersManager($pentaNumbersManager): void
                {
                    $this->pentaNumbersManager = $pentaNumbersManager;
                }

                /**
                 * @return mixed
                 */
                public function getVehicleVariantsManager()
                {
                    return $this->vehicleVariantsManager;
                }

                /**
                 * @param mixed $vehicleVariantsManager
                 */
                public function setVehicleVariantsManager($vehicleVariantsManager): void
                {
                    $this->vehicleVariantsManager = $vehicleVariantsManager;
                }

                /**
                 * @return mixed
                 */
                public function getOldColorsManager()
                {
                    return $this->oldColorsManager;
                }

                /**
                 * @param mixed $oldColorsManager
                 */
                public function setOldColorsManager($oldColorsManager): void
                {
                    $this->oldColorsManager = $oldColorsManager;
                }

                /**
                 * @return mixed
                 */
                public function getPropertiesSymbolsManager()
                {
                    return $this->propertiesSymbolsManager;
                }

                /**
                 * @param mixed $propertiesSymbolsManager
                 */
                public function setPropertiesSymbolsManager($propertiesSymbolsManager): void
                {
                    $this->propertiesSymbolsManager = $propertiesSymbolsManager;
                }
        };
    }

    /**
     * @param ShortKeyModel         $shortKeyModel
     * @param VehicleConfigurations $configuration
     * @param int                   $mode
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    private function saveShortKey(
        ShortKeyModel $shortKeyModel,
        VehicleConfigurations $configuration,
        int $mode
    ) : ConfigurationSearch
    {
        /* Start transaction */
        $this->entityManager->beginTransaction();

        $managers = $this->getSaveManagers($configuration);

        $generatedKey = $this->subConfService->generateShortKey($shortKeyModel);

        $colorKey = $managers->getConfigurationColorsManager()->find($shortKeyModel->getStandardColor())->getConfigurationColorKey();
        $color = $managers->getConfigurationColorsManager()->find($shortKeyModel->getStandardColor());
        try {
            $configuration->setVehicleTypeName($shortKeyModel->getType())
                ->setVehicleTypeYear($shortKeyModel->getYear())
                ->setVehicleSeries($shortKeyModel->getSeries())
                ->setVehicleConfigurationKey($generatedKey)
                ->setDefaultConfigurationColor($color)
                ->setDefaultProductionLocation($managers->getDepotsManager()->find($shortKeyModel->getStsPlaceOfProduction()))
                ->setDraft(false);
            $this->entityManager->persist($configuration);

            //Additional information
            $oldColor = $managers->getOldColorsManager()
                ->find($shortKeyModel->getStandardColor());

            $pentaNumber = $managers->getPentaNumbersManager()->find($managers->getPentaNumbersManager()
                    ->findMainConfigurationPentaNumber(
                $configuration->getOldVehicleVariant()->getVehicleVariantId()));
            $pentaNumber->setPentaNumber("{$generatedKey}_{$oldColor->getColorKey()}_00");
            $pentaNumber->setColor($oldColor);
            $this->entityManager->persist($pentaNumber);

            $vehicleVariant = $configuration->getOldVehicleVariant();
            $vehicleVariant->setVinMethod($shortKeyModel->getVinMethod());
            $vehicleVariant->setVinBatch(VinBatchConverter::convertSeriesToVinBatch($shortKeyModel->getSeries()));
            $vehicleVariant->setChargerControllable($shortKeyModel->isChargerControllable());
            $vehicleVariant->setDefaultColor($oldColor);
            $vehicleVariant->setBattery($managers->getPropertiesSymbolsManager()
                ->findOneBy(['vcProperty' => ShortKeyModel::BATTERY,
                    'allowedSymbols' => $shortKeyModel->getBattery()])->getDescription());
            $vehicleVariant->setWindchillVariantName($generatedKey);

            $this->entityManager->persist($vehicleVariant);


            foreach ($managers->getSubConfigurations() as $linked) {
                $currentKey = explode('_', explode('#', $linked->getSubVehicleConfigurationName())[1])[0];
                $linked->setSubVehicleConfigurationName("{$generatedKey }#{$currentKey}");
                $this->entityManager->persist($linked);

                $currentPenta = $managers->getPentaManager()->findOneBy(['subVehicleConfiguration' => $linked]);
                if (is_null($currentPenta)) {
                    $currentPenta = new PentaVariants();
                    $currentPenta->setSubVehicleConfiguration($linked)
                        ->setConfigurationColor($color)
                        ->setPentaVariantName("{$generatedKey}#{$currentKey}_{$colorKey}");
                } else {
                    if ($mode == SELF::FIX_MODE) {
                        $currentColorKey = explode('_', $currentPenta->getPentaVariantName())[1];
                        $currentPenta->setPentaVariantName("{$generatedKey}#{$currentKey}_{$currentColorKey}");
                    } else {
                        $currentPenta->setPentaVariantName("{$generatedKey}#{$currentKey}_{$colorKey}");
                    }
                }
                $this->entityManager->persist($currentPenta);
            }

            $dataKey = [
                ShortKeyModel::LAYOUT => "getLayout",
                ShortKeyModel::FEATURE => "getFeature",
                ShortKeyModel::BATTERY => "getBattery"
            ];


            $properties = $managers->getMappedProperties();
            $allProperties = $managers->getAllProperties();
            $allSymbols = $managers->getAllSymbols();
            $currentPropertyArray = [];

            foreach ($dataKey as $key => $value) {
                $currentPropertyArray = array_filter($properties, function ($element) use ($key) {
                    return $element->getVcProperty()->getVcPropertyId() == $key;
                });

                if (empty($currentPropertyArray)) {
                    $this->entityManager->persist((new VehicleConfigurationPropertiesMapping())
                        ->setVehicleConfiguration($configuration)
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

            $this->entityManager->flush();

            /* Commit */
            $this->entityManager->commit();
        } catch (\Exception $exception) {
            /* Rollback */
            $this->entityManager->rollBack();
            throw $exception;
        }

        return new ConfigurationSearch(
            $configuration->getVehicleConfigurationId(),
            $configuration->getVehicleTypeName(),
            $configuration->getVehicleTypeYear(),
            $configuration->getVehicleSeries(),
            $configuration->getVehicleCustomerKey()
        );
    }


    /**
     * @param LongKeyModel          $longKeyModel
     * @param VehicleConfigurations $configuration
     * @param int                   $mode
     *
     * @return ConfigurationSearch
     * @throws \Exception
     */
    private function saveLongKey(
        LongKeyModel $longKeyModel,
        VehicleConfigurations $configuration,
        int $mode
    ) : ConfigurationSearch
    {
        /* Start transaction */
        $this->entityManager->beginTransaction();

        $managers = $this->getSaveManagers($configuration);

        $generatedKey = $this->subConfService->generateLongKey($longKeyModel);

        $colorKey = $managers->getConfigurationColorsManager()->findOneBy(['allowedSymbols' => $longKeyModel->getColor()])->getConfigurationColorKey();
        $color = $managers->getConfigurationColorsManager()->findOneBy(['allowedSymbols' => $longKeyModel->getColor()]);
        try {
            $configuration->setVehicleTypeName($longKeyModel->getType())
                ->setVehicleTypeYear($longKeyModel->getYear())
                ->setVehicleSeries($longKeyModel->getSeries())
                ->setVehicleConfigurationKey($generatedKey)
                ->setDefaultConfigurationColor($color)
                ->setDefaultProductionLocation($managers->getDepotsManager()->find($longKeyModel->getStsPlaceOfProduction()))
                ->setVehicleCustomerKey($longKeyModel->getCustomerKey())
                ->setDraft(false);
            $this->entityManager->persist($configuration);

            $generatedShortProductionDescription =  $this->subConfService
                ->generateShortProductionDescriptionLongKey($longKeyModel);

            //Additional information
            $oldColor =  $this->entityManager->getRepository(Colors::class)
                ->find($managers->getConfigurationColorsManager()->findOneBy(
                    ["allowedSymbols" => $longKeyModel->getColor()]));

            $managers->getOldColorsManager()
                ->find($longKeyModel->getColor());

            $pentaNumber = $managers->getPentaNumbersManager()->find($managers->getPentaNumbersManager()
                ->findMainConfigurationPentaNumber(
                    $configuration->getOldVehicleVariant()->getVehicleVariantId()));
            $pentaNumber->setPentaNumber("{$generatedKey}_{$oldColor->getColorKey()}_00");
            $pentaNumber->setColor($oldColor);
            $this->entityManager->persist($pentaNumber);

            $vehicleVariant = $configuration->getOldVehicleVariant();
            $vehicleVariant->setVinMethod($longKeyModel->getVinMethod());
            $vehicleVariant->setVinBatch(VinBatchConverter::convertSeriesToVinBatch($longKeyModel->getSeries()));
            $vehicleVariant->setChargerControllable($longKeyModel->isChargerControllable());
            $vehicleVariant->setDefaultColor($oldColor);
            $vehicleVariant->setBattery($managers->getPropertiesSymbolsManager()
                ->findOneBy(['vcProperty' => LongKeyModel::TRACTION_BATTERY,
                    'allowedSymbols' => $longKeyModel->getTractionBattery()])->getDescription());
            $vehicleVariant->setWindchillVariantName($generatedKey);

            $this->entityManager->persist($vehicleVariant);

            foreach ($managers->getSubConfigurations() as $linked) {
                $currentKey = explode('_', explode('#', $linked->getSubVehicleConfigurationName())[1])[0];
                $linked->setSubVehicleConfigurationName("{$generatedKey }#{$currentKey}");
                $linked->setShortProductionDescription("{$generatedShortProductionDescription}{$currentKey}");
                $this->entityManager->persist($linked);

                $currentPenta = $managers->getPentaManager()->findOneBy(['subVehicleConfiguration' => $linked]);
                if (is_null($currentPenta)) {
                    $currentPenta = new PentaVariants();
                    $currentPenta->setSubVehicleConfiguration($linked)
                        ->setConfigurationColor($color)
                        ->setPentaVariantName("{$generatedKey}#{$currentKey}_{$colorKey}");
                } else {
                    if ($mode == SELF::FIX_MODE) {
                        $currentColorKey = explode('_', $currentPenta->getPentaVariantName())[1];
                        $currentPenta->setPentaVariantName("{$generatedKey}#{$currentKey}_{$currentColorKey}");
                    } else {
                        $currentPenta->setPentaVariantName("{$generatedKey}#{$currentKey}_{$colorKey}");
                    }
                }
                $this->entityManager->persist($currentPenta);
            }

            $dataKey = [
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
                LongKeyModel::WHEELING => "getWheeling",
            ];

            $properties = $managers->getMappedProperties();
            $allProperties = $managers->getAllProperties();
            $allSymbols = $managers->getAllSymbols();
            $currentPropertyArray = [];

            foreach ($dataKey as $key => $value) {
                $currentPropertyArray = array_filter($properties, function ($element) use ($key) {
                    return $element->getVcProperty()->getVcPropertyId() == $key;
                });

                if (empty($currentPropertyArray)) {
                    $this->entityManager->persist((new VehicleConfigurationPropertiesMapping())
                        ->setVehicleConfiguration($configuration)
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

            $this->entityManager->flush();

            /* Commit */
            $this->entityManager->commit();
        } catch (\Exception $exception) {
            /* Rollback */
            $this->entityManager->rollBack();
            throw $exception;
        }

        return new ConfigurationSearch(
            $configuration->getVehicleConfigurationId(),
            $configuration->getVehicleTypeName(),
            $configuration->getVehicleTypeYear(),
            $configuration->getVehicleSeries(),
            $configuration->getVehicleCustomerKey()
        );
    }

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
    ) : ConfigurationSearch
    {
        return $this->saveShortKey($shortKeyModel, $configuration, SELF::FIX_MODE);
    }

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
    ) : ConfigurationSearch
    {
        return $this->saveLongKey($longKeyModel, $configuration, SELF::FIX_MODE);

    }

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
    ) : ConfigurationSearch
    {
        return $this->saveShortKey($shortKeyModel, $configuration, SELF::EDIT_MODE);
    }

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
    ) : ConfigurationSearch
    {
        return $this->saveLongKey($longKeyModel, $configuration, SELF::EDIT_MODE);
    }


    /**
     * @param VehicleConfigurations $configuration
     *
     * @return bool
     * @throws \Exception
     */
    public function deleteConfiguration(VehicleConfigurations $configuration) : bool
    {
        /* Start transaction */
        $this->entityManager->beginTransaction();

        $dataAndManagersAccess = $this->getSaveManagers(null);

        try {
            $subconfigurations = $dataAndManagersAccess->getSubConfigurationManager()
                ->findBy(['vehicleConfiguration' => $configuration]);

            foreach ($subconfigurations as $item) {
                $this->subConfService->deleteSubConfiguration($item);
            }

            $propertiesMapping = $dataAndManagersAccess->getPropertiesMappingManager()
                ->findBy(['vehicleConfiguration' => $configuration]);

            foreach ($propertiesMapping as $item) {
                $this->entityManager->remove($item);
            }

            $specialProperties = $dataAndManagersAccess->getSVehPropertiesMappingManger()
                ->findBy(['vehicleConfiguration' => $configuration]);

            foreach ($specialProperties as $item) {
                $this->entityManager->remove($item->getSpecialVehiclePropertyValue());
                $this->entityManager->remove($item);
            }

            $pentaNumber = $dataAndManagersAccess->getPentaNumbersManager()->findOneBy(['vehicleVariant' =>
                $configuration->getOldVehicleVariant()]);

            if (!is_null($pentaNumber)) {
                $this->entityManager->remove($pentaNumber);
            }

            if (!is_null($configuration)) {
                $this->entityManager->remove($configuration->getOldVehicleVariant());

                $this->entityManager->remove($configuration);
            }

            $this->entityManager->flush();

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


