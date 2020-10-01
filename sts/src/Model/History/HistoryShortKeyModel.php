<?php

namespace App\Model\History;

use App\Model\EqualI;
use App\Model\History\Traits\HistoryEvent;

class HistoryShortKeyModel implements HistoryConfigurationI, HistoryI
{
    use HistoryEvent;

    /**
     * @var HistoryTuple
     */
    private $configurationId;

    /**
     * @var HistoryTuple
     */
    private $subConfigurationId;

    /* ----------------------------------- VEHICLE CONFIGURATION KEYS ------------------------------------------------*/
    /**
     * @var HistoryTuple
     */
    private $vehicleConfigurationKey;

    /**
     * @var HistoryTuple
     */
    private $pentaNumber;

    /* -------------------------------------------------------------------------------------------------------------- */
    /* --------------------------------------- KEY -------------------------------------------------------------------*/
    /**
     * @var HistoryTuple
     */
    private $type;

    /**
     * @var HistoryTuple
     */
    private $year;

    /**
     * @var HistoryTuple
     */
    private $series;
    /**
     * @var HistoryTuple
     */
    private $layout;

    /**
     * @var HistoryTuple
     */
    private $feature;

    /**
     * @var HistoryTuple
     */
    private $battery;
    /* ---------------------------------------------------------------------------------------------------------------*/

    /* ------------------------------- ADDITIONAL KEY COMPONENTS -----------------------------------------------------*/
    /**
     * @var HistoryTuple
     */
    private $espPart;

    /**
     * @var HistoryTuple
     */
    private $espPartReport;

    /**
     * @var HistoryTuple
     */
    private $rotatingBacon;

    /**
     * @var HistoryTuple
     */
    private $rotatingBaconReport;

    /**
     * @var HistoryTuple
     */
    private $partAtCoDriverPosition;

    /**
     * @var HistoryTuple
     */
    private $partAtCoDriverPositionReport;

    /**
     * @var HistoryTuple
     */
    private $typeOfBattery;

    /**
     * @var HistoryTuple
     */
    private $typeOfBatteryReport;

    /**
     * @var HistoryTuple
     */
    private $radio;

    /**
     * @var HistoryTuple
     */
    private $radioReport;

    /* ---------------------------------------------------------------------------------------------------------------*/

    /* ------------------------------- ADDITIONAL KEY FEATURES -------------------------------------------------------*/
    /**
     * @var HistoryTuple
     */
    private $standardColor;

    /**
     * @var HistoryTuple
     */
    private $isDeutschePostConfiguration;

    /**
     * @var HistoryTuple
     */
    private $targetState;


    /**
     * @var HistoryTuple
     */
    private $stsPlaceOfProduction;

    /**
     * @var HistoryTuple
     */
    private $espFunctionality;

    /**
     * @var HistoryTuple
     */
    private $tirePressFront;

    /**
     * @var HistoryTuple
     */
    private $tirePressRear;

    /**
     * @var HistoryTuple
     */
    private $comment;

    /**
     * @var HistoryTuple
     */
    private $testSoftwareVersion;

    /* ---------------------------------------------------------------------------------------------------------------*/
    /* ----------------------------------------- OLD TABLES - SUPPORT ----------------------------------------------- */
    /**
     * @var HistoryTuple
     */
    private $vinMethod;

    /**
     * @var HistoryTuple
     */
    private $chargerControllable;

    /* -------------------------------------------------------------------------------------------------------------- */

    /**
     * @var HistoryTuple
     */
    private $ecus;

    /**
     * @var HistoryTuple
     */
    private $cans;

    /**
     * @var HistoryTuple
     */
    private $releasedByUser;

    /**
     * @var HistoryTuple
     */
    private $releaseDate;

    /**
     * @var HistoryTuple
     */
    private $releaseState;

    /**
     * @return int
     */
    public function getHistoryEvent(): ?int
    {
        return $this->historyEvent;
    }

    /**
     * @param int $historyEvent
     *
     * @return HistoryShortKeyModel
     */
    public function setHistoryEvent(int $historyEvent = null): HistoryShortKeyModel
    {
        $this->historyEvent = $historyEvent;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getConfigurationId(): HistoryTuple
    {
        return $this->configurationId;
    }

    /**
     * @param HistoryTuple $configurationId
     *
     * @return HistoryShortKeyModel
     */
    public function setConfigurationId(HistoryTuple $configurationId): HistoryShortKeyModel
    {
        $this->configurationId = $configurationId;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getSubConfigurationId(): HistoryTuple
    {
        return $this->subConfigurationId;
    }

    /**
     * @param HistoryTuple $subConfigurationId
     *
     * @return HistoryShortKeyModel
     */
    public function setSubConfigurationId(HistoryTuple $subConfigurationId): HistoryShortKeyModel
    {
        $this->subConfigurationId = $subConfigurationId;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getVehicleConfigurationKey(): HistoryTuple
    {
        return $this->vehicleConfigurationKey;
    }

    /**
     * @param HistoryTuple $vehicleConfigurationKey
     *
     * @return HistoryShortKeyModel
     */
    public function setVehicleConfigurationKey(HistoryTuple $vehicleConfigurationKey): HistoryShortKeyModel
    {
        $this->vehicleConfigurationKey = $vehicleConfigurationKey;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getPentaNumber(): HistoryTuple
    {
        return $this->pentaNumber;
    }

    /**
     * @param HistoryTuple $pentaNumber
     *
     * @return HistoryShortKeyModel
     */
    public function setPentaNumber(HistoryTuple $pentaNumber): HistoryShortKeyModel
    {
        $this->pentaNumber = $pentaNumber;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getType(): HistoryTuple
    {
        return $this->type;
    }

    /**
     * @param HistoryTuple $type
     *
     * @return HistoryShortKeyModel
     */
    public function setType(HistoryTuple $type): HistoryShortKeyModel
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getYear(): HistoryTuple
    {
        return $this->year;
    }

    /**
     * @param HistoryTuple $year
     *
     * @return HistoryShortKeyModel
     */
    public function setYear(HistoryTuple $year): HistoryShortKeyModel
    {
        $this->year = $year;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getSeries(): HistoryTuple
    {
        return $this->series;
    }

    /**
     * @param HistoryTuple $series
     *
     * @return HistoryShortKeyModel
     */
    public function setSeries(HistoryTuple $series): HistoryShortKeyModel
    {
        $this->series = $series;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getLayout(): HistoryTuple
    {
        return $this->layout;
    }

    /**
     * @param HistoryTuple $layout
     *
     * @return HistoryShortKeyModel
     */
    public function setLayout(HistoryTuple $layout): HistoryShortKeyModel
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getFeature(): HistoryTuple
    {
        return $this->feature;
    }

    /**
     * @param HistoryTuple $feature
     *
     * @return HistoryShortKeyModel
     */
    public function setFeature(HistoryTuple $feature): HistoryShortKeyModel
    {
        $this->feature = $feature;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getBattery(): HistoryTuple
    {
        return $this->battery;
    }

    /**
     * @param HistoryTuple $battery
     *
     * @return HistoryShortKeyModel
     */
    public function setBattery(HistoryTuple $battery): HistoryShortKeyModel
    {
        $this->battery = $battery;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getEspPart(): HistoryTuple
    {
        return $this->espPart;
    }

    /**
     * @param HistoryTuple $espPart
     *
     * @return HistoryShortKeyModel
     */
    public function setEspPart(HistoryTuple $espPart): HistoryShortKeyModel
    {
        $this->espPart = $espPart;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getEspPartReport(): HistoryTuple
    {
        return $this->espPartReport;
    }

    /**
     * @param HistoryTuple $espPartReport
     *
     * @return HistoryShortKeyModel
     */
    public function setEspPartReport(HistoryTuple $espPartReport): HistoryShortKeyModel
    {
        $this->espPartReport = $espPartReport;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getRotatingBacon(): HistoryTuple
    {
        return $this->rotatingBacon;
    }

    /**
     * @param HistoryTuple $rotatingBacon
     *
     * @return HistoryShortKeyModel
     */
    public function setRotatingBacon(HistoryTuple $rotatingBacon): HistoryShortKeyModel
    {
        $this->rotatingBacon = $rotatingBacon;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getRotatingBaconReport(): HistoryTuple
    {
        return $this->rotatingBaconReport;
    }

    /**
     * @param HistoryTuple $rotatingBaconReport
     *
     * @return HistoryShortKeyModel
     */
    public function setRotatingBaconReport(HistoryTuple $rotatingBaconReport): HistoryShortKeyModel
    {
        $this->rotatingBaconReport = $rotatingBaconReport;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getPartAtCoDriverPosition(): HistoryTuple
    {
        return $this->partAtCoDriverPosition;
    }

    /**
     * @param HistoryTuple $partAtCoDriverPosition
     *
     * @return HistoryShortKeyModel
     */
    public function setPartAtCoDriverPosition(HistoryTuple $partAtCoDriverPosition): HistoryShortKeyModel
    {
        $this->partAtCoDriverPosition = $partAtCoDriverPosition;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getPartAtCoDriverPositionReport(): HistoryTuple
    {
        return $this->partAtCoDriverPositionReport;
    }

    /**
     * @param HistoryTuple $partAtCoDriverPositionReport
     *
     * @return HistoryShortKeyModel
     */
    public function setPartAtCoDriverPositionReport(HistoryTuple $partAtCoDriverPositionReport): HistoryShortKeyModel
    {
        $this->partAtCoDriverPositionReport = $partAtCoDriverPositionReport;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getTypeOfBattery(): HistoryTuple
    {
        return $this->typeOfBattery;
    }

    /**
     * @param HistoryTuple $typeOfBattery
     *
     * @return HistoryShortKeyModel
     */
    public function setTypeOfBattery(HistoryTuple $typeOfBattery): HistoryShortKeyModel
    {
        $this->typeOfBattery = $typeOfBattery;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getTypeOfBatteryReport(): HistoryTuple
    {
        return $this->typeOfBatteryReport;
    }

    /**
     * @param HistoryTuple $typeOfBatteryReport
     *
     * @return HistoryShortKeyModel
     */
    public function setTypeOfBatteryReport(HistoryTuple $typeOfBatteryReport): HistoryShortKeyModel
    {
        $this->typeOfBatteryReport = $typeOfBatteryReport;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getRadio(): HistoryTuple
    {
        return $this->radio;
    }

    /**
     * @param HistoryTuple $radio
     *
     * @return HistoryShortKeyModel
     */
    public function setRadio(HistoryTuple $radio): HistoryShortKeyModel
    {
        $this->radio = $radio;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getRadioReport(): HistoryTuple
    {
        return $this->radioReport;
    }

    /**
     * @param HistoryTuple $radioReport
     *
     * @return HistoryShortKeyModel
     */
    public function setRadioReport(HistoryTuple $radioReport): HistoryShortKeyModel
    {
        $this->radioReport = $radioReport;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getStandardColor(): HistoryTuple
    {
        return $this->standardColor;
    }

    /**
     * @param HistoryTuple $standardColor
     *
     * @return HistoryShortKeyModel
     */
    public function setStandardColor(HistoryTuple $standardColor): HistoryShortKeyModel
    {
        $this->standardColor = $standardColor;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getIsDeutschePostConfiguration(): HistoryTuple
    {
        return $this->isDeutschePostConfiguration;
    }

    /**
     * @param HistoryTuple $isDeutschePostConfiguration
     *
     * @return HistoryShortKeyModel
     */
    public function setIsDeutschePostConfiguration(HistoryTuple $isDeutschePostConfiguration): HistoryShortKeyModel
    {
        $this->isDeutschePostConfiguration = $isDeutschePostConfiguration;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getTargetState(): HistoryTuple
    {
        return $this->targetState;
    }

    /**
     * @param HistoryTuple $targetState
     *
     * @return HistoryShortKeyModel
     */
    public function setTargetState(HistoryTuple $targetState): HistoryShortKeyModel
    {
        $this->targetState = $targetState;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getStsPlaceOfProduction(): HistoryTuple
    {
        return $this->stsPlaceOfProduction;
    }

    /**
     * @param HistoryTuple $stsPlaceOfProduction
     *
     * @return HistoryShortKeyModel
     */
    public function setStsPlaceOfProduction(HistoryTuple $stsPlaceOfProduction): HistoryShortKeyModel
    {
        $this->stsPlaceOfProduction = $stsPlaceOfProduction;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getEspFunctionality(): HistoryTuple
    {
        return $this->espFunctionality;
    }

    /**
     * @param HistoryTuple $espFunctionality
     *
     * @return HistoryShortKeyModel
     */
    public function setEspFunctionality(HistoryTuple $espFunctionality): HistoryShortKeyModel
    {
        $this->espFunctionality = $espFunctionality;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getTirePressFront(): HistoryTuple
    {
        return $this->tirePressFront;
    }

    /**
     * @param HistoryTuple $tirePressFront
     *
     * @return HistoryShortKeyModel
     */
    public function setTirePressFront(HistoryTuple $tirePressFront): HistoryShortKeyModel
    {
        $this->tirePressFront = $tirePressFront;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getTirePressRear(): HistoryTuple
    {
        return $this->tirePressRear;
    }

    /**
     * @param HistoryTuple $tirePressRear
     *
     * @return HistoryShortKeyModel
     */
    public function setTirePressRear(HistoryTuple $tirePressRear): HistoryShortKeyModel
    {
        $this->tirePressRear = $tirePressRear;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getComment(): HistoryTuple
    {
        return $this->comment;
    }

    /**
     * @param HistoryTuple $comment
     *
     * @return HistoryShortKeyModel
     */
    public function setComment(HistoryTuple $comment): HistoryShortKeyModel
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getTestSoftwareVersion(): HistoryTuple
    {
        return $this->testSoftwareVersion;
    }

    /**
     * @param HistoryTuple $testSoftwareVersion
     *
     * @return HistoryShortKeyModel
     */
    public function setTestSoftwareVersion(HistoryTuple $testSoftwareVersion): HistoryShortKeyModel
    {
        $this->testSoftwareVersion = $testSoftwareVersion;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getVinMethod(): HistoryTuple
    {
        return $this->vinMethod;
    }

    /**
     * @param HistoryTuple $vinMethod
     *
     * @return HistoryShortKeyModel
     */
    public function setVinMethod(HistoryTuple $vinMethod): HistoryShortKeyModel
    {
        $this->vinMethod = $vinMethod;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getChargerControllable(): HistoryTuple
    {
        return $this->chargerControllable;
    }

    /**
     * @param HistoryTuple $chargerControllable
     *
     * @return HistoryShortKeyModel
     */
    public function setChargerControllable(HistoryTuple $chargerControllable): HistoryShortKeyModel
    {
        $this->chargerControllable = $chargerControllable;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getEcus(): HistoryTuple
    {
        return $this->ecus;
    }

    /**
     * @param HistoryTuple $ecus
     *
     * @return HistoryShortKeyModel
     */
    public function setEcus(HistoryTuple $ecus): HistoryShortKeyModel
    {
        $this->ecus = $ecus;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getCans(): HistoryTuple
    {
        return $this->cans;
    }

    /**
     * @param HistoryTuple $cans
     *
     * @return HistoryShortKeyModel
     */
    public function setCans(HistoryTuple $cans): HistoryShortKeyModel
    {
        $this->cans = $cans;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getReleasedByUser(): HistoryTuple
    {
        return $this->releasedByUser;
    }

    /**
     * @param HistoryTuple $releasedByUser
     *
     * @return HistoryShortKeyModel
     */
    public function setReleasedByUser(HistoryTuple $releasedByUser): HistoryShortKeyModel
    {
        $this->releasedByUser = $releasedByUser;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getReleaseDate(): HistoryTuple
    {
        return $this->releaseDate;
    }

    /**
     * @param HistoryTuple $releaseDate
     *
     * @return HistoryShortKeyModel
     */
    public function setReleaseDate(HistoryTuple $releaseDate): HistoryShortKeyModel
    {
        $this->releaseDate = $releaseDate;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getReleaseState(): HistoryTuple
    {
        return $this->releaseState;
    }

    /**
     * @param HistoryTuple $releaseState
     *
     * @return HistoryShortKeyModel
     */
    public function setReleaseState(HistoryTuple $releaseState): HistoryShortKeyModel
    {
        $this->releaseState = $releaseState;
        return $this;
    }
}