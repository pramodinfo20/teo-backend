<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 5/27/19
 * Time: 1:50 PM
 */

namespace App\Model;


class ShortKeyModel implements ConfigurationI, EqualI, ComparableI, ConvertibleToHistoryI
{
    /* --------------------------------------- KEY -------------------------------------------------------------------*/
    const LAYOUT = 28;
    const FEATURE = 29;
    const BATTERY = 30;
    /* ---------------------------------------------------------------------------------------------------------------*/

    /* ------------------------------- ADDITIONAL KEY COMPONENTS -----------------------------------------------------*/
    const ESP_PART = 1;
    const ROTATING_BACON = 2;
    const PART_AT_CO_DRIVER_POSITION = 3;
    const TYPE_OF_BATTERY = 4;
    const RADIO = 5;
    /* ---------------------------------------------------------------------------------------------------------------*/

    /* ------------------------------- ADDITIONAL KEY FEATURES -------------------------------------------------------*/
    const IS_DEUTSCHE_POST_CONFIGURATION = 6;
    const TARGET_STATE = 7;
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
    private $vehicleConfigurationKey;

    /**
     * @var string
     */
    private $pentaNumber;

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
     * @var int
     */
    private $layout;

    /**
     * @var int
     */
    private $feature;

    /**
     * @var int
     */
    private $battery;
    /* ---------------------------------------------------------------------------------------------------------------*/

    /* ------------------------------- ADDITIONAL KEY COMPONENTS -----------------------------------------------------*/
    /**
     * @var bool
     */
    private $espPart;

    /**
     * @var bool
     */
    private $espPartReport;

    /**
     * @var bool
     */
    private $rotatingBacon;

    /**
     * @var bool
     */
    private $rotatingBaconReport;

    /**
     * @var string
     */
    private $partAtCoDriverPosition;

    /**
     * @var bool
     */
    private $partAtCoDriverPositionReport;

    /**
     * @var string
     */
    private $typeOfBattery;

    /**
     * @var bool
     */
    private $typeOfBatteryReport;

    /**
     * @var bool
     */
    private $radio;

    /**
     * @var bool
     */
    private $radioReport;

    /* ---------------------------------------------------------------------------------------------------------------*/

    /* ------------------------------- ADDITIONAL KEY FEATURES -------------------------------------------------------*/
    /**
     * @var int
     */
    private $standardColor;

    /**
     * @var bool
     */
    private $isDeutschePostConfiguration;

    /**
     * @var string
     */
    private $targetState;


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
     * @return int
     */
    public function getLayout(): ?int
    {
        return $this->layout;
    }

    /**
     * @param int $layout
     */
    public function setLayout(int $layout = null): void
    {
        $this->layout = $layout;
    }

    /**
     * @return int
     */
    public function getFeature(): ?int
    {
        return $this->feature;
    }

    /**
     * @param int $feature
     */
    public function setFeature(int $feature = null): void
    {
        $this->feature = $feature;
    }

    /**
     * @return int
     */
    public function getBattery(): ?int
    {
        return $this->battery;
    }

    /**
     * @param int $battery
     */
    public function setBattery(int $battery = null): void
    {
        $this->battery = $battery;
    }

    /* ---------------------------------------------------------------------------------------------------------------*/

    /* ------------------------------- ADDITIONAL KEY COMPONENTS -----------------------------------------------------*/
    /**
     * @return bool
     */
    public function isEspPart(): ?bool
    {
        return $this->espPart;
    }

    /**
     * @param bool $espPart
     */
    public function setEspPart(bool $espPart = null): void
    {
        $this->espPart = $espPart;
    }

    /**
     * @return bool
     */
    public function isEspPartReport(): ?bool
    {
        return $this->espPartReport;
    }

    /**
     * @param bool $espPartReport
     */
    public function setEspPartReport(bool $espPartReport = null): void
    {
        $this->espPartReport = $espPartReport;
    }

    /**
     * @return bool
     */
    public function isRotatingBacon(): ?bool
    {
        return $this->rotatingBacon;
    }

    /**
     * @param bool $rotatingBacon
     */
    public function setRotatingBacon(bool $rotatingBacon = null): void
    {
        $this->rotatingBacon = $rotatingBacon;
    }

    /**
     * @return bool
     */
    public function isRotatingBaconReport(): ?bool
    {
        return $this->rotatingBaconReport;
    }

    /**
     * @param bool $rotatingBaconReport
     */
    public function setRotatingBaconReport(bool $rotatingBaconReport = null): void
    {
        $this->rotatingBaconReport = $rotatingBaconReport;
    }

    /**
     * @return string
     */
    public function getPartAtCoDriverPosition(): ?string
    {
        return $this->partAtCoDriverPosition;
    }

    /**
     * @param string $partAtCoDriverPosition
     */
    public function setPartAtCoDriverPosition(string $partAtCoDriverPosition = null): void
    {
        $this->partAtCoDriverPosition = $partAtCoDriverPosition;
    }

    /**
     * @return bool
     */
    public function isPartAtCoDriverPositionReport(): ?bool
    {
        return $this->partAtCoDriverPositionReport;
    }

    /**
     * @param bool $partAtCoDriverPositionReport
     */
    public function setPartAtCoDriverPositionReport(bool $partAtCoDriverPositionReport = null): void
    {
        $this->partAtCoDriverPositionReport = $partAtCoDriverPositionReport;
    }

    /**
     * @return string
     */
    public function getTypeOfBattery(): ?string
    {
        return $this->typeOfBattery;
    }

    /**
     * @param string $typeOfBattery
     */
    public function setTypeOfBattery(string $typeOfBattery = null): void
    {
        $this->typeOfBattery = $typeOfBattery;
    }

    /**
     * @return bool
     */
    public function isTypeOfBatteryReport(): ?bool
    {
        return $this->typeOfBatteryReport;
    }

    /**
     * @param bool $typeOfBatteryReport
     */
    public function setTypeOfBatteryReport(bool $typeOfBatteryReport = null): void
    {
        $this->typeOfBatteryReport = $typeOfBatteryReport;
    }

    /**
     * @return bool
     */
    public function isRadio(): ?bool
    {
        return $this->radio;
    }

    /**
     * @param bool $radio
     */
    public function setRadio(bool $radio = null): void
    {
        $this->radio = $radio;
    }

    /**
     * @return bool
     */
    public function isRadioReport(): ?bool
    {
        return $this->radioReport;
    }

    /**
     * @param bool $radioReport
     */
    public function setRadioReport(bool $radioReport = null): void
    {
        $this->radioReport = $radioReport;
    }


    /* ---------------------------------------------------------------------------------------------------------------*/


    /* ------------------------------- ADDITIONAL KEY FEATURES -------------------------------------------------------*/
    /**
     * @return int
     */
    public function getStandardColor(): ?int
    {
        return $this->standardColor;
    }

    /**
     * @param int $standardColor
     */
    public function setStandardColor(int $standardColor = null): void
    {
        $this->standardColor = $standardColor;
    }

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
     * @return string
     */
    public function getTargetState(): ?string
    {
        return $this->targetState;
    }

    /**
     * @param string $targetState
     */
    public function setTargetState(string $targetState = null): void
    {
        $this->targetState = $targetState;
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
    public function setChargerControllable(bool $chargerControllable = null): void
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