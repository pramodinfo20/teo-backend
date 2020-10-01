<?php

namespace App\Model\History;

use App\Model\History\Traits\HistoryEvent;

class HistoryLongKeyModel implements HistoryConfigurationI, HistoryI
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
    private $typeDesignation;

    /**
     * @var HistoryTuple
     */
    private $vehicleConfigurationKey;

    /**
     * @var HistoryTuple
     */
    private $pentaNumber;

    /**
     * @var HistoryTuple
     */
    private $shortProductionDescription;

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
    private $customerKey;
  
    /**
     * @var HistoryTuple
     */
    private $devStatus;
  
    /**
     * @var HistoryTuple
     */
    private $body;
  
    /**
     * @var HistoryTuple
     */
    private $numberDrive;
  
    /**
     * @var HistoryTuple
     */
    private $engineType;
  
    /**
     * @var HistoryTuple
     */
    private $stageOfCompletion;
  
    /**
     * @var HistoryTuple
     */
    private $bodyLength;
  
    /**
     * @var HistoryTuple
     */
    private $frontAxle;
  
    /**
     * @var HistoryTuple
     */
    private $rearAxle;
  
    /**
     * @var HistoryTuple
     */
    private $zgg;
  
    /**
     * @var HistoryTuple
     */
    private $typeOfFuel;
  
    /**
     * @var HistoryTuple
     */
    private $tractionBattery;
  
    /**
     * @var HistoryTuple
     */
    private $chargingSystem;
  
    /**
     * @var HistoryTuple
     */
    private $vMax;
  
    /**
     * @var HistoryTuple
     */
    private $seats;
  
    /**
     * @var HistoryTuple
     */
    private $trailerHitch;
  
    /**
     * @var HistoryTuple
     */
    private $superstructures;
  
    /**
     * @var HistoryTuple
     */
    private $energySupplySuperStructure;
  
    /**
     * @var HistoryTuple
     */
    private $steering;
  
    /**
     * @var HistoryTuple
     */
    private $rearWindow;
  
    /**
     * @var HistoryTuple
     */
    private $airConditioning;
  
    /**
     * @var HistoryTuple
     */
    private $passengerAirbag;
  
    /**
     * @var HistoryTuple
     */
    private $keyless;
  
    /**
     * @var HistoryTuple
     */
    private $specialApplicationArea;
  
    /**
     * @var HistoryTuple
     */
    private $radio;
  
    /**
     * @var HistoryTuple
     */
    private $soundGenerator;
  
    /**
     * @var HistoryTuple
     */
    private $countryCode;
  
    /**
     * @var HistoryTuple
     */
    private $color;
  
    /**
     * @var HistoryTuple
     */
    private $wheeling;
    /* ---------------------------------------------------------------------------------------------------------------*/
  
    /* -------------------------------------- ADDITIONAL KEY FEATURES ------------------------------------------------*/
    /**
     * @var HistoryTuple
     */
    private $isDeutschePostConfiguration;
  
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
     * @return HistoryLongKeyModel
     */
    public function setHistoryEvent(int $historyEvent = null): HistoryLongKeyModel
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
     * @return HistoryLongKeyModel
     */
    public function setConfigurationId(HistoryTuple $configurationId): HistoryLongKeyModel
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
     * @return HistoryLongKeyModel
     */
    public function setSubConfigurationId(HistoryTuple $subConfigurationId): HistoryLongKeyModel
    {
        $this->subConfigurationId = $subConfigurationId;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getTypeDesignation(): HistoryTuple
    {
        return $this->typeDesignation;
    }

    /**
     * @param HistoryTuple $typeDesignation
     *
     * @return HistoryLongKeyModel
     */
    public function setTypeDesignation(HistoryTuple $typeDesignation): HistoryLongKeyModel
    {
        $this->typeDesignation = $typeDesignation;
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
     * @return HistoryLongKeyModel
     */
    public function setVehicleConfigurationKey(HistoryTuple $vehicleConfigurationKey): HistoryLongKeyModel
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
     * @return HistoryLongKeyModel
     */
    public function setPentaNumber(HistoryTuple $pentaNumber): HistoryLongKeyModel
    {
        $this->pentaNumber = $pentaNumber;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getShortProductionDescription(): HistoryTuple
    {
        return $this->shortProductionDescription;
    }

    /**
     * @param HistoryTuple $shortProductionDescription
     *
     * @return HistoryLongKeyModel
     */
    public function setShortProductionDescription(HistoryTuple $shortProductionDescription): HistoryLongKeyModel
    {
        $this->shortProductionDescription = $shortProductionDescription;
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
     * @return HistoryLongKeyModel
     */
    public function setType(HistoryTuple $type): HistoryLongKeyModel
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
     * @return HistoryLongKeyModel
     */
    public function setYear(HistoryTuple $year): HistoryLongKeyModel
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
     * @return HistoryLongKeyModel
     */
    public function setSeries(HistoryTuple $series): HistoryLongKeyModel
    {
        $this->series = $series;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getCustomerKey(): HistoryTuple
    {
        return $this->customerKey;
    }

    /**
     * @param HistoryTuple $customerKey
     *
     * @return HistoryLongKeyModel
     */
    public function setCustomerKey(HistoryTuple $customerKey): HistoryLongKeyModel
    {
        $this->customerKey = $customerKey;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getDevStatus(): HistoryTuple
    {
        return $this->devStatus;
    }

    /**
     * @param HistoryTuple $devStatus
     *
     * @return HistoryLongKeyModel
     */
    public function setDevStatus(HistoryTuple $devStatus): HistoryLongKeyModel
    {
        $this->devStatus = $devStatus;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getBody(): HistoryTuple
    {
        return $this->body;
    }

    /**
     * @param HistoryTuple $body
     *
     * @return HistoryLongKeyModel
     */
    public function setBody(HistoryTuple $body): HistoryLongKeyModel
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getNumberDrive(): HistoryTuple
    {
        return $this->numberDrive;
    }

    /**
     * @param HistoryTuple $numberDrive
     *
     * @return HistoryLongKeyModel
     */
    public function setNumberDrive(HistoryTuple $numberDrive): HistoryLongKeyModel
    {
        $this->numberDrive = $numberDrive;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getEngineType(): HistoryTuple
    {
        return $this->engineType;
    }

    /**
     * @param HistoryTuple $engineType
     *
     * @return HistoryLongKeyModel
     */
    public function setEngineType(HistoryTuple $engineType): HistoryLongKeyModel
    {
        $this->engineType = $engineType;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getStageOfCompletion(): HistoryTuple
    {
        return $this->stageOfCompletion;
    }

    /**
     * @param HistoryTuple $stageOfCompletion
     *
     * @return HistoryLongKeyModel
     */
    public function setStageOfCompletion(HistoryTuple $stageOfCompletion): HistoryLongKeyModel
    {
        $this->stageOfCompletion = $stageOfCompletion;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getBodyLength(): HistoryTuple
    {
        return $this->bodyLength;
    }

    /**
     * @param HistoryTuple $bodyLength
     *
     * @return HistoryLongKeyModel
     */
    public function setBodyLength(HistoryTuple $bodyLength): HistoryLongKeyModel
    {
        $this->bodyLength = $bodyLength;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getFrontAxle(): HistoryTuple
    {
        return $this->frontAxle;
    }

    /**
     * @param HistoryTuple $frontAxle
     *
     * @return HistoryLongKeyModel
     */
    public function setFrontAxle(HistoryTuple $frontAxle): HistoryLongKeyModel
    {
        $this->frontAxle = $frontAxle;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getRearAxle(): HistoryTuple
    {
        return $this->rearAxle;
    }

    /**
     * @param HistoryTuple $rearAxle
     *
     * @return HistoryLongKeyModel
     */
    public function setRearAxle(HistoryTuple $rearAxle): HistoryLongKeyModel
    {
        $this->rearAxle = $rearAxle;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getZgg(): HistoryTuple
    {
        return $this->zgg;
    }

    /**
     * @param HistoryTuple $zgg
     *
     * @return HistoryLongKeyModel
     */
    public function setZgg(HistoryTuple $zgg): HistoryLongKeyModel
    {
        $this->zgg = $zgg;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getTypeOfFuel(): HistoryTuple
    {
        return $this->typeOfFuel;
    }

    /**
     * @param HistoryTuple $typeOfFuel
     *
     * @return HistoryLongKeyModel
     */
    public function setTypeOfFuel(HistoryTuple $typeOfFuel): HistoryLongKeyModel
    {
        $this->typeOfFuel = $typeOfFuel;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getTractionBattery(): HistoryTuple
    {
        return $this->tractionBattery;
    }

    /**
     * @param HistoryTuple $tractionBattery
     *
     * @return HistoryLongKeyModel
     */
    public function setTractionBattery(HistoryTuple $tractionBattery): HistoryLongKeyModel
    {
        $this->tractionBattery = $tractionBattery;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getChargingSystem(): HistoryTuple
    {
        return $this->chargingSystem;
    }

    /**
     * @param HistoryTuple $chargingSystem
     *
     * @return HistoryLongKeyModel
     */
    public function setChargingSystem(HistoryTuple $chargingSystem): HistoryLongKeyModel
    {
        $this->chargingSystem = $chargingSystem;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getVMax(): HistoryTuple
    {
        return $this->vMax;
    }

    /**
     * @param HistoryTuple $vMax
     *
     * @return HistoryLongKeyModel
     */
    public function setVMax(HistoryTuple $vMax): HistoryLongKeyModel
    {
        $this->vMax = $vMax;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getSeats(): HistoryTuple
    {
        return $this->seats;
    }

    /**
     * @param HistoryTuple $seats
     *
     * @return HistoryLongKeyModel
     */
    public function setSeats(HistoryTuple $seats): HistoryLongKeyModel
    {
        $this->seats = $seats;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getTrailerHitch(): HistoryTuple
    {
        return $this->trailerHitch;
    }

    /**
     * @param HistoryTuple $trailerHitch
     *
     * @return HistoryLongKeyModel
     */
    public function setTrailerHitch(HistoryTuple $trailerHitch): HistoryLongKeyModel
    {
        $this->trailerHitch = $trailerHitch;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getSuperstructures(): HistoryTuple
    {
        return $this->superstructures;
    }

    /**
     * @param HistoryTuple $superstructures
     *
     * @return HistoryLongKeyModel
     */
    public function setSuperstructures(HistoryTuple $superstructures): HistoryLongKeyModel
    {
        $this->superstructures = $superstructures;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getEnergySupplySuperStructure(): HistoryTuple
    {
        return $this->energySupplySuperStructure;
    }

    /**
     * @param HistoryTuple $energySupplySuperStructure
     *
     * @return HistoryLongKeyModel
     */
    public function setEnergySupplySuperStructure(HistoryTuple $energySupplySuperStructure): HistoryLongKeyModel
    {
        $this->energySupplySuperStructure = $energySupplySuperStructure;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getSteering(): HistoryTuple
    {
        return $this->steering;
    }

    /**
     * @param HistoryTuple $steering
     *
     * @return HistoryLongKeyModel
     */
    public function setSteering(HistoryTuple $steering): HistoryLongKeyModel
    {
        $this->steering = $steering;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getRearWindow(): HistoryTuple
    {
        return $this->rearWindow;
    }

    /**
     * @param HistoryTuple $rearWindow
     *
     * @return HistoryLongKeyModel
     */
    public function setRearWindow(HistoryTuple $rearWindow): HistoryLongKeyModel
    {
        $this->rearWindow = $rearWindow;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getAirConditioning(): HistoryTuple
    {
        return $this->airConditioning;
    }

    /**
     * @param HistoryTuple $airConditioning
     *
     * @return HistoryLongKeyModel
     */
    public function setAirConditioning(HistoryTuple $airConditioning): HistoryLongKeyModel
    {
        $this->airConditioning = $airConditioning;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getPassengerAirbag(): HistoryTuple
    {
        return $this->passengerAirbag;
    }

    /**
     * @param HistoryTuple $passengerAirbag
     *
     * @return HistoryLongKeyModel
     */
    public function setPassengerAirbag(HistoryTuple $passengerAirbag): HistoryLongKeyModel
    {
        $this->passengerAirbag = $passengerAirbag;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getKeyless(): HistoryTuple
    {
        return $this->keyless;
    }

    /**
     * @param HistoryTuple $keyless
     *
     * @return HistoryLongKeyModel
     */
    public function setKeyless(HistoryTuple $keyless): HistoryLongKeyModel
    {
        $this->keyless = $keyless;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getSpecialApplicationArea(): HistoryTuple
    {
        return $this->specialApplicationArea;
    }

    /**
     * @param HistoryTuple $specialApplicationArea
     *
     * @return HistoryLongKeyModel
     */
    public function setSpecialApplicationArea(HistoryTuple $specialApplicationArea): HistoryLongKeyModel
    {
        $this->specialApplicationArea = $specialApplicationArea;
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
     * @return HistoryLongKeyModel
     */
    public function setRadio(HistoryTuple $radio): HistoryLongKeyModel
    {
        $this->radio = $radio;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getSoundGenerator(): HistoryTuple
    {
        return $this->soundGenerator;
    }

    /**
     * @param HistoryTuple $soundGenerator
     *
     * @return HistoryLongKeyModel
     */
    public function setSoundGenerator(HistoryTuple $soundGenerator): HistoryLongKeyModel
    {
        $this->soundGenerator = $soundGenerator;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getCountryCode(): HistoryTuple
    {
        return $this->countryCode;
    }

    /**
     * @param HistoryTuple $countryCode
     *
     * @return HistoryLongKeyModel
     */
    public function setCountryCode(HistoryTuple $countryCode): HistoryLongKeyModel
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getColor(): HistoryTuple
    {
        return $this->color;
    }

    /**
     * @param HistoryTuple $color
     *
     * @return HistoryLongKeyModel
     */
    public function setColor(HistoryTuple $color): HistoryLongKeyModel
    {
        $this->color = $color;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getWheeling(): HistoryTuple
    {
        return $this->wheeling;
    }

    /**
     * @param HistoryTuple $wheeling
     *
     * @return HistoryLongKeyModel
     */
    public function setWheeling(HistoryTuple $wheeling): HistoryLongKeyModel
    {
        $this->wheeling = $wheeling;
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
     * @return HistoryLongKeyModel
     */
    public function setIsDeutschePostConfiguration(HistoryTuple $isDeutschePostConfiguration): HistoryLongKeyModel
    {
        $this->isDeutschePostConfiguration = $isDeutschePostConfiguration;
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
     * @return HistoryLongKeyModel
     */
    public function setStsPlaceOfProduction(HistoryTuple $stsPlaceOfProduction): HistoryLongKeyModel
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
     * @return HistoryLongKeyModel
     */
    public function setEspFunctionality(HistoryTuple $espFunctionality): HistoryLongKeyModel
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
     * @return HistoryLongKeyModel
     */
    public function setTirePressFront(HistoryTuple $tirePressFront): HistoryLongKeyModel
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
     * @return HistoryLongKeyModel
     */
    public function setTirePressRear(HistoryTuple $tirePressRear): HistoryLongKeyModel
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
     * @return HistoryLongKeyModel
     */
    public function setComment(HistoryTuple $comment): HistoryLongKeyModel
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
     * @return HistoryLongKeyModel
     */
    public function setTestSoftwareVersion(HistoryTuple $testSoftwareVersion): HistoryLongKeyModel
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
     * @return HistoryLongKeyModel
     */
    public function setVinMethod(HistoryTuple $vinMethod): HistoryLongKeyModel
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
     * @return HistoryLongKeyModel
     */
    public function setChargerControllable(HistoryTuple $chargerControllable): HistoryLongKeyModel
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
     * @return HistoryLongKeyModel
     */
    public function setEcus(HistoryTuple $ecus): HistoryLongKeyModel
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
     * @return HistoryLongKeyModel
     */
    public function setCans(HistoryTuple $cans): HistoryLongKeyModel
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
     * @return HistoryLongKeyModel
     */
    public function setReleasedByUser(HistoryTuple $releasedByUser): HistoryLongKeyModel
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
     * @return HistoryLongKeyModel
     */
    public function setReleaseDate(HistoryTuple $releaseDate): HistoryLongKeyModel
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
     * @return HistoryLongKeyModel
     */
    public function setReleaseState(HistoryTuple $releaseState): HistoryLongKeyModel
    {
        $this->releaseState = $releaseState;
        return $this;
    }
}