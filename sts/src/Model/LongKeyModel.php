<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 5/27/19
 * Time: 1:50 PM
 */

namespace App\Model;


class LongKeyModel implements ConfigurationI, EqualI, ComparableI, ConvertibleToHistoryI
{
    /* --------------------------------------- KEY -------------------------------------------------------------------*/
    const DEV_STATUS = 31;
    const BODY = 1;
    const NUMBER_DRIVE = 2;
    const ENGINE_TYPE = 3;
    const STAGE_OF_COMPLETION = 4;
    const BODY_LENGTH = 5;
    const FRONT_AXLE = 6;
    const REAR_AXLE = 7;
    const ZGG = 8;
    const TYPE_OF_FUEL = 9;
    const TRACTION_BATTERY = 10;
    const CHARGING_SYSTEM = 11;
    const VMAX = 12;
    const SEATS = 13;
    const TRAILER_HITCH = 14;
    const SUPERSTRUCTURES = 15;
    const ENERGY_SUPPLY_SUPERSTRUCTURE = 16;
    const STEERING = 17;
    const REAR_WINDOW = 18;
    const AIR_CONDITIONING = 19;
    const PASSENGER_AIRBAG = 20;
    const KEYLESS = 21;
    const SPECIAL_APPLICATION_AREA = 22;
    const RADIO = 23;
    const SOUND_GENERATOR = 24;
    const COUNTRY_CODE = 25;
    const COLOR = 26;
    const WHEELING = 27;
    /* ---------------------------------------------------------------------------------------------------------------*/

    /* -------------------------------------- ADDITIONAL KEY FEATURES ------------------------------------------------*/
    const IS_DEUTSCHE_POST_CONFIGURATION = 6;
    const ESP_FUNCTIONALITY = 8;
    const TIRE_PRES_FRONT = 9;
    const TIRE_PRES_REAR = 10;
    const COMMENT = 11;
    const TEST_SOFTWARE_VERSION = 12;
    /* ---------------------------------------------------------------------------------------------------------------*/
    /**
     * @var int
     */
    private $configurationId;

    /**
     * @var int
     */
    private $subConfigurationId;

    /* ----------------------------------- VEHICLE CONFIGURATION KEYS ------------------------------------------------*/
    /**
     * @var string
     */
    private $typeDesignation;

    /**
     * @var string
     */
    private $vehicleConfigurationKey;

    /**
     * @var string
     */
    private $pentaNumber;

    /**
     * @var string
     */
    private $shortProductionDescription;

    /* -------------------------------------------------------------------------------------------------------------- */
    /* --------------------------------------- KEY -------------------------------------------------------------------*/

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $year;

    /**
     * @var string
     */
    private $series;

    /**
     * @var string
     */
    private $customerKey;

    /**
     * @var int
     */
    private $devStatus;

    /**
     * @var int
     */
    private $body;

    /**
     * @var int
     */
    private $numberDrive;

    /**
     * @var int
     */
    private $engineType;

    /**
     * @var int
     */
    private $stageOfCompletion;

    /**
     * @var int
     */
    private $bodyLength;

    /**
     * @var int
     */
    private $frontAxle;

    /**
     * @var int
     */
    private $rearAxle;

    /**
     * @var int
     */
    private $zgg;

    /**
     * @var int
     */
    private $typeOfFuel;

    /**
     * @var int
     */
    private $tractionBattery;

    /**
     * @var int
     */
    private $chargingSystem;

    /**
     * @var int
     */
    private $vMax;

    /**
     * @var int
     */
    private $seats;

    /**
     * @var int
     */
    private $trailerHitch;

    /**
     * @var int
     */
    private $superstructures;

    /**
     * @var int
     */
    private $energySupplySuperStructure;

    /**
     * @var int
     */
    private $steering;

    /**
     * @var int
     */
    private $rearWindow;

    /**
     * @var int
     */
    private $airConditioning;

    /**
     * @var int
     */
    private $passengerAirbag;

    /**
     * @var int
     */
    private $keyless;

    /**
     * @var int
     */
    private $specialApplicationArea;

    /**
     * @var int
     */
    private $radio;

    /**
     * @var int
     */
    private $soundGenerator;

    /**
     * @var int
     */
    private $countryCode;

    /**
     * @var int
     */
    private $color;

    /**
     * @var int
     */
    private $wheeling;
    /* ---------------------------------------------------------------------------------------------------------------*/

    /* -------------------------------------- ADDITIONAL KEY FEATURES ------------------------------------------------*/
    /**
     * @var boolean
     */
    private $isDeutschePostConfiguration;

    /**
     * @var int
     */
    private $stsPlaceOfProduction;

    /**
     * @var bool
     */
    private $espFunctionality;

    /**
     * @var int
     */
    private $tirePressFront;

    /**
     * @var int
     */
    private $tirePressRear;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var bool
     */
    private $testSoftwareVersion;
    /* ---------------------------------------------------------------------------------------------------------------*/
    /* ----------------------------------------- OLD TABLES - SUPPORT ----------------------------------------------- */
    /**
     * @var string
     */
    private $vinMethod;

    /**
     * @var bool
     */
    private $chargerControllable;

    /* -------------------------------------------------------------------------------------------------------------- */
    /**
     * @var array
     */
    private $ecus;

    /**
     * @var array
     */
    private $cans;

    /**
     * @var int
     */
    private $releasedByUser;

    /**
     * @var string
     */
    private $releaseDate;

    /**
     * @var int
     */
    private $releaseState;

    /**
     * @return int
     */
    public function getConfigurationId(): ?int
    {
        return $this->configurationId;
    }

    /**
     * @param int $configurationId
     */
    public function setConfigurationId(int $configurationId = null): void
    {
        $this->configurationId = $configurationId;
    }

    /**
     * @return int
     */
    public function getSubConfigurationId(): ?int
    {
        return $this->subConfigurationId;
    }

    /**
     * @param int $subConfigurationId
     */
    public function setSubConfigurationId(int $subConfigurationId = null): void
    {
        $this->subConfigurationId = $subConfigurationId;
    }
    /* ----------------------------------- VEHICLE CONFIGURATION KEYS ------------------------------------------------*/
    /**
     * @return string
     */
    public function getTypeDesignation(): ?string
    {
        return $this->typeDesignation;
    }

    /**
     * @param string $typeDesignation
     */
    public function setTypeDesignation(string $typeDesignation = null): void
    {
        $this->typeDesignation = $typeDesignation;
    }

    /**
     * @return string
     */
    public function getVehicleConfigurationKey(): ?string
    {
        return $this->vehicleConfigurationKey;
    }

    /**
     * @param string $vehicleConfigurationKey
     */
    public function setVehicleConfigurationKey(string $vehicleConfigurationKey = null): void
    {
        $this->vehicleConfigurationKey = $vehicleConfigurationKey;
    }

    /**
     * @return string
     */
    public function getPentaNumber(): ?string
    {
        return $this->pentaNumber;
    }

    /**
     * @param string $pentaNumber
     */
    public function setPentaNumber(string $pentaNumber = null): void
    {
        $this->pentaNumber = $pentaNumber;
    }

    /**
     * @return string
     */
    public function getShortProductionDescription(): ?string
    {
        return $this->shortProductionDescription;
    }

    /**
     * @param string $shortProductionDescription
     */
    public function setShortProductionDescription(string $shortProductionDescription = null): void
    {
        $this->shortProductionDescription = $shortProductionDescription;
    }
    /* -------------------------------------------------------------------------------------------------------------- */
    /* --------------------------------------- KEY -------------------------------------------------------------------*/

    /**
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type = null): void
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getYear(): ?int
    {
        return $this->year;
    }

    /**
     * @param int $year
     */
    public function setYear(int $year = null): void
    {
        $this->year = $year;
    }

    /**
     * @return string
     */
    public function getSeries(): ?string
    {
        return $this->series;
    }

    /**
     * @param string $series
     */
    public function setSeries(string $series = null): void
    {
        $this->series = $series;
    }

    /**
     * @return string
     */
    public function getCustomerKey(): ?string
    {
        return $this->customerKey;
    }

    /**
     * @param string $customerKey
     */
    public function setCustomerKey(string $customerKey = null): void
    {
        $this->customerKey = $customerKey;
    }

    /**
     * @return int
     */
    public function getDevStatus(): ?int
    {
        return $this->devStatus;
    }

    /**
     * @param int $devStatus
     */
    public function setDevStatus(int $devStatus = null): void
    {
        $this->devStatus = $devStatus;
    }

    /**
     * @return int
     */
    public function getBody(): ?int
    {
        return $this->body;
    }

    /**
     * @param int $body
     */
    public function setBody(int $body = null): void
    {
        $this->body = $body;
    }

    /**
     * @return int
     */
    public function getNumberDrive(): ?int
    {
        return $this->numberDrive;
    }

    /**
     * @param int $numberDrive
     */
    public function setNumberDrive(int $numberDrive = null): void
    {
        $this->numberDrive = $numberDrive;
    }

    /**
     * @return int
     */
    public function getEngineType(): ?int
    {
        return $this->engineType;
    }

    /**
     * @param int $engineType
     */
    public function setEngineType(int $engineType = null): void
    {
        $this->engineType = $engineType;
    }

    /**
     * @return int
     */
    public function getStageOFCompletion(): ?int
    {
        return $this->stageOfCompletion;
    }

    /**
     * @param int $stageOfCompletion
     */
    public function setStageOfCompletion(int $stageOfCompletion = null): void
    {
        $this->stageOfCompletion = $stageOfCompletion;
    }

    /**
     * @return int
     */
    public function getBodyLength(): ?int
    {
        return $this->bodyLength;
    }

    /**
     * @param int $bodyLength
     */
    public function setBodyLength(int $bodyLength = null): void
    {
        $this->bodyLength = $bodyLength;
    }

    /**
     * @return int
     */
    public function getFrontAxle(): ?int
    {
        return $this->frontAxle;
    }

    /**
     * @param int $frontAxle
     */
    public function setFrontAxle(int $frontAxle = null): void
    {
        $this->frontAxle = $frontAxle;
    }

    /**
     * @return int
     */
    public function getRearAxle(): ?int
    {
        return $this->rearAxle;
    }

    /**
     * @param int $rearAxle
     */
    public function setRearAxle(int $rearAxle = null): void
    {
        $this->rearAxle = $rearAxle;
    }

    /**
     * @return int
     */
    public function getZgg(): ?int
    {
        return $this->zgg;
    }

    /**
     * @param int $zgg
     */
    public function setZgg(int $zgg = null): void
    {
        $this->zgg = $zgg;
    }

    /**
     * @return int
     */
    public function getTypeOfFuel(): ?int
    {
        return $this->typeOfFuel;
    }

    /**
     * @param int $typeOfFuel
     */
    public function setTypeOfFuel(int $typeOfFuel = null): void
    {
        $this->typeOfFuel = $typeOfFuel;
    }

    /**
     * @return int
     */
    public function getTractionBattery(): ?int
    {
        return $this->tractionBattery;
    }

    /**
     * @param int $tractionBattery
     */
    public function setTractionBattery(int $tractionBattery = null): void
    {
        $this->tractionBattery = $tractionBattery;
    }

    /**
     * @return int
     */
    public function getChargingSystem(): ?int
    {
        return $this->chargingSystem;
    }

    /**
     * @param int $chargingSystem
     */
    public function setChargingSystem(int $chargingSystem = null): void
    {
        $this->chargingSystem = $chargingSystem;
    }

    /**
     * @return int
     */
    public function getVMax(): ?int
    {
        return $this->vMax;
    }

    /**
     * @param int $vMax
     */
    public function setVMax(int $vMax = null): void
    {
        $this->vMax = $vMax;
    }

    /**
     * @return int
     */
    public function getSeats(): ?int
    {
        return $this->seats;
    }

    /**
     * @param int $seats
     */
    public function setSeats(int $seats = null): void
    {
        $this->seats = $seats;
    }

    /**
     * @return int
     */
    public function getTrailerHitch(): ?int
    {
        return $this->trailerHitch;
    }

    /**
     * @param int $trailerHitch
     */
    public function setTrailerHitch(int $trailerHitch = null): void
    {
        $this->trailerHitch = $trailerHitch;
    }

    /**
     * @return int
     */
    public function getSuperstructures(): ?int
    {
        return $this->superstructures;
    }

    /**
     * @param int $superstructures
     */
    public function setSuperstructures(int $superstructures = null): void
    {
        $this->superstructures = $superstructures;
    }

    /**
     * @return int
     */
    public function getEnergySupplySuperStructure(): ?int
    {
        return $this->energySupplySuperStructure;
    }

    /**
     * @param int $energySupplySuperStructure
     */
    public function setEnergySupplySuperStructure(int $energySupplySuperStructure = null): void
    {
        $this->energySupplySuperStructure = $energySupplySuperStructure;
    }

    /**
     * @return int
     */
    public function getSteering(): ?int
    {
        return $this->steering;
    }

    /**
     * @param int $steering
     */
    public function setSteering(int $steering = null): void
    {
        $this->steering = $steering;
    }

    /**
     * @return int
     */
    public function getRearWindow(): ?int
    {
        return $this->rearWindow;
    }

    /**
     * @param int $rearWindow
     */
    public function setRearWindow(int $rearWindow = null): void
    {
        $this->rearWindow = $rearWindow;
    }

    /**
     * @return int
     */
    public function getAirConditioning(): ?int
    {
        return $this->airConditioning;
    }

    /**
     * @param int $airConditioning
     */
    public function setAirConditioning(int $airConditioning = null): void
    {
        $this->airConditioning = $airConditioning;
    }

    /**
     * @return int
     */
    public function getpassengerAirbag(): ?int
    {
        return $this->passengerAirbag;
    }

    /**
     * @param int $passengerAirbag
     */
    public function setpassengerAirbag(int $passengerAirbag = null): void
    {
        $this->passengerAirbag = $passengerAirbag;
    }

    /**
     * @return int
     */
    public function getKeyless(): ?int
    {
        return $this->keyless;
    }

    /**
     * @param int $keyless
     */
    public function setKeyless(int $keyless = null): void
    {
        $this->keyless = $keyless;
    }

    /**
     * @return int
     */
    public function getSpecialApplicationArea(): ?int
    {
        return $this->specialApplicationArea;
    }

    /**
     * @param int $specialApplicationArea
     */
    public function setSpecialApplicationArea(int $specialApplicationArea = null): void
    {
        $this->specialApplicationArea = $specialApplicationArea;
    }

    /**
     * @return int
     */
    public function getRadio(): ?int
    {
        return $this->radio;
    }

    /**
     * @param int $radio
     */
    public function setRadio(int $radio = null): void
    {
        $this->radio = $radio;
    }

    /**
     * @return int
     */
    public function getSoundGenerator(): ?int
    {
        return $this->soundGenerator;
    }

    /**
     * @param int $soundGenerator
     */
    public function setSoundGenerator(int $soundGenerator = null): void
    {
        $this->soundGenerator = $soundGenerator;
    }

    /**
     * @return int
     */
    public function getCountryCode(): ?int
    {
        return $this->countryCode;
    }

    /**
     * @param int $countryCode
     */
    public function setCountryCode(int $countryCode = null): void
    {
        $this->countryCode = $countryCode;
    }

    /**
     * @return int
     */
    public function getColor(): ?int
    {
        return $this->color;
    }

    /**
     * @param int $color
     */
    public function setColor(int $color = null): void
    {
        $this->color = $color;
    }

    /**
     * @return int
     */
    public function getWheeling(): ?int
    {
        return $this->wheeling;
    }

    /**
     * @param int $wheeling
     */
    public function setWheeling(int $wheeling = null): void
    {
        $this->wheeling = $wheeling;
    }

    /* ---------------------------------------------------------------------------------------------------------------*/

    /* -------------------------------------- ADDITIONAL KEY FEATURES ------------------------------------------------*/
    /**
     * @return bool
     */
    public function isDeutschePostConfiguration(): ?bool
    {
        return $this->isDeutschePostConfiguration;
    }

    /**
     * @param bool $isDeutschePostConfiguration
     */
    public function setIsDeutschePostConfiguration(bool $isDeutschePostConfiguration = null): void
    {
        $this->isDeutschePostConfiguration = $isDeutschePostConfiguration;
    }

    /**
     * @return int
     */
    public function getStsPlaceOfProduction(): ?int
    {
        return $this->stsPlaceOfProduction;
    }

    /**
     * @param int $stsPlaceOfProduction
     */
    public function setStsPlaceOfProduction(int $stsPlaceOfProduction = null): void
    {
        $this->stsPlaceOfProduction = $stsPlaceOfProduction;
    }

    /**
     * @return bool
     */
    public function isEspFunctionality(): ?bool
    {
        return $this->espFunctionality;
    }

    /**
     * @param bool $espFunctionality
     */
    public function setEspFunctionality(bool $espFunctionality = null): void
    {
        $this->espFunctionality = $espFunctionality;
    }

    /**
     * @return int
     */
    public function getTirePressFront(): ?int
    {
        return $this->tirePressFront;
    }

    /**
     * @param int $tirePressFront
     */
    public function setTirePressFront(int $tirePressFront = null): void
    {
        $this->tirePressFront = $tirePressFront;
    }

    /**
     * @return int
     */
    public function getTirePressRear(): ?int
    {
        return $this->tirePressRear;
    }

    /**
     * @param int $tirePressRear
     */
    public function setTirePressRear(int $tirePressRear = null): void
    {
        $this->tirePressRear = $tirePressRear;
    }

    /**
     * @return string
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment(string $comment = null): void
    {
        $this->comment = $comment;
    }

    /**
     * @return bool
     */
    public function isTestSoftwareVersion(): ?bool
    {
        return $this->testSoftwareVersion;
    }

    /**
     * @param bool $testSoftwareVersion
     */
    public function setTestSoftwareVersion(bool $testSoftwareVersion = null): void
    {
        $this->testSoftwareVersion = $testSoftwareVersion;
    }
    /* ---------------------------------------------------------------------------------------------------------------*/
    /**
     * @return array
     */
    public function getEcus(): ?array
    {
        return $this->ecus;
    }

    /**
     * @param array $ecus
     */
    public function setEcus(array $ecus = null): void
    {
        $this->ecus = $ecus;
    }

    /**
     * @return array
     */
    public function getCans(): ?array
    {
        return $this->cans;
    }

    /**
     * @param array $cans
     */
    public function setCans(array $cans = null): void
    {
        $this->cans = $cans;
    }

    /**
     * @return int
     */
    public function getReleasedByUser(): ?int
    {
        return $this->releasedByUser;
    }

    /**
     * @param int $releasedByUser
     */
    public function setReleasedByUser(int $releasedByUser = null): void
    {
        $this->releasedByUser = $releasedByUser;
    }

    /**
     * @return string
     */
    public function getReleaseDate(): ?string
    {
        return $this->releaseDate;
    }

    /**
     * @param string $releaseDate
     */
    public function setReleaseDate(string $releaseDate = null): void
    {
        $this->releaseDate = $releaseDate;
    }

    /**
     * @return int
     */
    public function getReleaseState(): ?int
    {
        return $this->releaseState;
    }

    /**
     * @param int $releaseState
     */
    public function setReleaseState(int $releaseState = null): void
    {
        $this->releaseState = $releaseState;
    }

    /**
     * @return string
     */
    public function getVinMethod(): ?string
    {
        return $this->vinMethod;
    }

    /**
     * @param string $vinMethod
     */
    public function setVinMethod(string $vinMethod = null): void
    {
        $this->vinMethod = $vinMethod;
    }

    /**
     * @return bool
     */
    public function isChargerControllable(): ?bool
    {
        return $this->chargerControllable;
    }

    /**
     * @param bool $chargerControllable
     */
    public function setChargerControllable(bool $chargerControllable = null) : void
    {
        $this->chargerControllable = $chargerControllable;
    }

    public function equals(EqualI $interface): bool
    {
        if (is_null($this->getSubConfigurationId()))  {
            return $this->getConfigurationId() == $interface->getConfigurationId();
        } else {
            return $this->getSubConfigurationId() == $interface->getSubConfigurationId();
        }
    }

    /** Like a Spaceship - <=> - 0 - equal, -1 - left < right, 1 left > right
     *
     * @param ComparableI $interface
     *
     * @return int
     */
    public function compare(ComparableI $interface): int
    {
        return 0;
    }
}