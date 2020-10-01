<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DailyStats
 *
 * @ORM\Table(name="daily_stats", indexes={@ORM\Index(name="daily_stats_date_idx", columns={"date"}),
 *                                @ORM\Index(name="daily_stats_vehicle_id_idx", columns={"vehicle_id"})})
 * @ORM\Entity
 */
class DailyStats
{
    /**
     * @var int
     *
     * @ORM\Column(name="vehicle_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $vehicleId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $date;

    /**
     * @var int
     *
     * @ORM\Column(name="timestamp_start", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $timestampStart;

    /**
     * @var int|null
     *
     * @ORM\Column(name="status_code", type="integer", nullable=true)
     */
    private $statusCode = '0';

    /**
     * @var int|null
     *
     * @ORM\Column(name="timestamp_end", type="integer", nullable=true)
     */
    private $timestampEnd;

    /**
     * @var float|null
     *
     * @ORM\Column(name="km_start", type="float", precision=10, scale=0, nullable=true)
     */
    private $kmStart;

    /**
     * @var float|null
     *
     * @ORM\Column(name="km_end", type="float", precision=10, scale=0, nullable=true)
     */
    private $kmEnd;

    /**
     * @var int|null
     *
     * @ORM\Column(name="ignition_count", type="integer", nullable=true)
     */
    private $ignitionCount;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ignition_time", type="string", nullable=true)
     */
    private $ignitionTime;

    /**
     * @var int|null
     *
     * @ORM\Column(name="stops", type="integer", nullable=true)
     */
    private $stops;

    /**
     * @var float|null
     *
     * @ORM\Column(name="speed_max", type="float", precision=10, scale=0, nullable=true)
     */
    private $speedMax;

    /**
     * @var float|null
     *
     * @ORM\Column(name="speed_avg", type="float", precision=10, scale=0, nullable=true)
     */
    private $speedAvg;

    /**
     * @var string|null
     *
     * @ORM\Column(name="driving_time", type="string", nullable=true)
     */
    private $drivingTime;

    /**
     * @var float|null
     *
     * @ORM\Column(name="gps_distance", type="float", precision=10, scale=0, nullable=true)
     */
    private $gpsDistance;

    /**
     * @var float|null
     *
     * @ORM\Column(name="ascent", type="float", precision=10, scale=0, nullable=true)
     */
    private $ascent;

    /**
     * @var float|null
     *
     * @ORM\Column(name="descent", type="float", precision=10, scale=0, nullable=true)
     */
    private $descent;

    /**
     * @var string|null
     *
     * @ORM\Column(name="drivemode_d_time", type="string", nullable=true)
     */
    private $drivemodeDTime;

    /**
     * @var string|null
     *
     * @ORM\Column(name="drivemode_n_time", type="string", nullable=true)
     */
    private $drivemodeNTime;

    /**
     * @var string|null
     *
     * @ORM\Column(name="drivemode_r_time", type="string", nullable=true)
     */
    private $drivemodeRTime;

    /**
     * @var string|null
     *
     * @ORM\Column(name="drivemode_e_time", type="string", nullable=true)
     */
    private $drivemodeETime;

    /**
     * @var float|null
     *
     * @ORM\Column(name="t_ambient_start", type="float", precision=10, scale=0, nullable=true)
     */
    private $tAmbientStart;

    /**
     * @var float|null
     *
     * @ORM\Column(name="t_ambient_end", type="float", precision=10, scale=0, nullable=true)
     */
    private $tAmbientEnd;

    /**
     * @var float|null
     *
     * @ORM\Column(name="t_ambient_min", type="float", precision=10, scale=0, nullable=true)
     */
    private $tAmbientMin;

    /**
     * @var float|null
     *
     * @ORM\Column(name="t_ambient_max", type="float", precision=10, scale=0, nullable=true)
     */
    private $tAmbientMax;

    /**
     * @var float|null
     *
     * @ORM\Column(name="t_ambient_avg", type="float", precision=10, scale=0, nullable=true)
     */
    private $tAmbientAvg;

    /**
     * @var float|null
     *
     * @ORM\Column(name="energy_soc", type="float", precision=10, scale=0, nullable=true)
     */
    private $energySoc;

    /**
     * @var float|null
     *
     * @ORM\Column(name="energy_per100km_soc", type="float", precision=10, scale=0, nullable=true)
     */
    private $energyPer100kmSoc;

    /**
     * @var float|null
     *
     * @ORM\Column(name="recuperated_energy", type="float", precision=10, scale=0, nullable=true)
     */
    private $recuperatedEnergy;

    /**
     * @var float|null
     *
     * @ORM\Column(name="temp_min", type="float", precision=10, scale=0, nullable=true)
     */
    private $tempMin;

    /**
     * @var float|null
     *
     * @ORM\Column(name="temp_max", type="float", precision=10, scale=0, nullable=true)
     */
    private $tempMax;

    /**
     * @var float|null
     *
     * @ORM\Column(name="u_min", type="float", precision=10, scale=0, nullable=true)
     */
    private $uMin;

    /**
     * @var float|null
     *
     * @ORM\Column(name="u_max", type="float", precision=10, scale=0, nullable=true)
     */
    private $uMax;

    /**
     * @var float|null
     *
     * @ORM\Column(name="i_min", type="float", precision=10, scale=0, nullable=true)
     */
    private $iMin;

    /**
     * @var float|null
     *
     * @ORM\Column(name="i_max", type="float", precision=10, scale=0, nullable=true)
     */
    private $iMax;

    /**
     * @var int|null
     *
     * @ORM\Column(name="recuperations", type="integer", nullable=true)
     */
    private $recuperations;

    /**
     * @var int|null
     *
     * @ORM\Column(name="accelerations", type="integer", nullable=true)
     */
    private $accelerations;

    /**
     * @var int|null
     *
     * @ORM\Column(name="hand_brake_count", type="integer", nullable=true)
     */
    private $handBrakeCount;

    /**
     * @var int|null
     *
     * @ORM\Column(name="door_open_count", type="integer", nullable=true)
     */
    private $doorOpenCount;

    /**
     * @var int|null
     *
     * @ORM\Column(name="belt_use_count", type="integer", nullable=true)
     */
    private $beltUseCount;

    /**
     * @var int|null
     *
     * @ORM\Column(name="door_lock_count", type="integer", nullable=true)
     */
    private $doorLockCount;

    /**
     * @var int|null
     *
     * @ORM\Column(name="front_window_heating_count", type="integer", nullable=true)
     */
    private $frontWindowHeatingCount;

    /**
     * @var string|null
     *
     * @ORM\Column(name="front_window_heating_time", type="string", nullable=true)
     */
    private $frontWindowHeatingTime;

    /**
     * @var string|null
     *
     * @ORM\Column(name="left_indicator_time", type="string", nullable=true)
     */
    private $leftIndicatorTime;

    /**
     * @var string|null
     *
     * @ORM\Column(name="right_indicator_time", type="string", nullable=true)
     */
    private $rightIndicatorTime;

    /**
     * @var string|null
     *
     * @ORM\Column(name="hazzard_time", type="string", nullable=true)
     */
    private $hazzardTime;

    /**
     * @var string|null
     *
     * @ORM\Column(name="low_beam_time", type="string", nullable=true)
     */
    private $lowBeamTime;

    /**
     * @var int|null
     *
     * @ORM\Column(name="low_beam_count", type="integer", nullable=true)
     */
    private $lowBeamCount;

    /**
     * @var string|null
     *
     * @ORM\Column(name="high_beam_time", type="string", nullable=true)
     */
    private $highBeamTime;

    /**
     * @var int|null
     *
     * @ORM\Column(name="high_beam_count", type="integer", nullable=true)
     */
    private $highBeamCount;

    /**
     * @var string|null
     *
     * @ORM\Column(name="parking_light_time", type="string", nullable=true)
     */
    private $parkingLightTime;

    /**
     * @var int|null
     *
     * @ORM\Column(name="parking_light_count", type="integer", nullable=true)
     */
    private $parkingLightCount;

    /**
     * @var string|null
     *
     * @ORM\Column(name="rear_fog_light_time", type="string", nullable=true)
     */
    private $rearFogLightTime;

    /**
     * @var int|null
     *
     * @ORM\Column(name="rear_fog_light_count", type="integer", nullable=true)
     */
    private $rearFogLightCount;

    /**
     * @var float|null
     *
     * @ORM\Column(name="lat_start", type="float", precision=10, scale=0, nullable=true)
     */
    private $latStart;

    /**
     * @var float|null
     *
     * @ORM\Column(name="lon_start", type="float", precision=10, scale=0, nullable=true)
     */
    private $lonStart;

    /**
     * @var float|null
     *
     * @ORM\Column(name="energy_integrated", type="float", precision=10, scale=0, nullable=true)
     */
    private $energyIntegrated;

    /**
     * @var float|null
     *
     * @ORM\Column(name="energy_per100km_integrated", type="float", precision=10, scale=0, nullable=true)
     */
    private $energyPer100kmIntegrated;

    /**
     * @var string|null
     *
     * @ORM\Column(name="hand_brake_time", type="string", nullable=true)
     */
    private $handBrakeTime;

    /**
     * @var float|null
     *
     * @ORM\Column(name="lat_end", type="float", precision=10, scale=0, nullable=true)
     */
    private $latEnd;

    /**
     * @var float|null
     *
     * @ORM\Column(name="lon_end", type="float", precision=10, scale=0, nullable=true)
     */
    private $lonEnd;

    /**
     * @var float|null
     *
     * @ORM\Column(name="soc_start_percent", type="float", precision=10, scale=0, nullable=true)
     */
    private $socStartPercent;

    /**
     * @var float|null
     *
     * @ORM\Column(name="soc_end_percent", type="float", precision=10, scale=0, nullable=true)
     */
    private $socEndPercent;

    /**
     * @var float|null
     *
     * @ORM\Column(name="soc_start_kwh", type="float", precision=10, scale=0, nullable=true)
     */
    private $socStartKwh;

    /**
     * @var float|null
     *
     * @ORM\Column(name="soc_end_kwh", type="float", precision=10, scale=0, nullable=true)
     */
    private $socEndKwh;

    /**
     * @var float|null
     *
     * @ORM\Column(name="heating_energy", type="float", precision=10, scale=0, nullable=true)
     */
    private $heatingEnergy;

    /**
     * @var float|null
     *
     * @ORM\Column(name="climate_energy", type="float", precision=10, scale=0, nullable=true)
     */
    private $climateEnergy;

    /**
     * @var int|null
     *
     * @ORM\Column(name="left_indicator_count", type="integer", nullable=true)
     */
    private $leftIndicatorCount;

    /**
     * @var int|null
     *
     * @ORM\Column(name="right_indicator_count", type="integer", nullable=true)
     */
    private $rightIndicatorCount;

    /**
     * @var int|null
     *
     * @ORM\Column(name="hazzard_count", type="integer", nullable=true)
     */
    private $hazzardCount;

    public function getVehicleId(): ?int
    {
        return $this->vehicleId;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function getTimestampStart(): ?int
    {
        return $this->timestampStart;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function setStatusCode(?int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getTimestampEnd(): ?int
    {
        return $this->timestampEnd;
    }

    public function setTimestampEnd(?int $timestampEnd): self
    {
        $this->timestampEnd = $timestampEnd;

        return $this;
    }

    public function getKmStart(): ?float
    {
        return $this->kmStart;
    }

    public function setKmStart(?float $kmStart): self
    {
        $this->kmStart = $kmStart;

        return $this;
    }

    public function getKmEnd(): ?float
    {
        return $this->kmEnd;
    }

    public function setKmEnd(?float $kmEnd): self
    {
        $this->kmEnd = $kmEnd;

        return $this;
    }

    public function getIgnitionCount(): ?int
    {
        return $this->ignitionCount;
    }

    public function setIgnitionCount(?int $ignitionCount): self
    {
        $this->ignitionCount = $ignitionCount;

        return $this;
    }

    public function getIgnitionTime(): ?string
    {
        return $this->ignitionTime;
    }

    public function setIgnitionTime(?string $ignitionTime): self
    {
        $this->ignitionTime = $ignitionTime;

        return $this;
    }

    public function getStops(): ?int
    {
        return $this->stops;
    }

    public function setStops(?int $stops): self
    {
        $this->stops = $stops;

        return $this;
    }

    public function getSpeedMax(): ?float
    {
        return $this->speedMax;
    }

    public function setSpeedMax(?float $speedMax): self
    {
        $this->speedMax = $speedMax;

        return $this;
    }

    public function getSpeedAvg(): ?float
    {
        return $this->speedAvg;
    }

    public function setSpeedAvg(?float $speedAvg): self
    {
        $this->speedAvg = $speedAvg;

        return $this;
    }

    public function getDrivingTime(): ?string
    {
        return $this->drivingTime;
    }

    public function setDrivingTime(?string $drivingTime): self
    {
        $this->drivingTime = $drivingTime;

        return $this;
    }

    public function getGpsDistance(): ?float
    {
        return $this->gpsDistance;
    }

    public function setGpsDistance(?float $gpsDistance): self
    {
        $this->gpsDistance = $gpsDistance;

        return $this;
    }

    public function getAscent(): ?float
    {
        return $this->ascent;
    }

    public function setAscent(?float $ascent): self
    {
        $this->ascent = $ascent;

        return $this;
    }

    public function getDescent(): ?float
    {
        return $this->descent;
    }

    public function setDescent(?float $descent): self
    {
        $this->descent = $descent;

        return $this;
    }

    public function getDrivemodeDTime(): ?string
    {
        return $this->drivemodeDTime;
    }

    public function setDrivemodeDTime(?string $drivemodeDTime): self
    {
        $this->drivemodeDTime = $drivemodeDTime;

        return $this;
    }

    public function getDrivemodeNTime(): ?string
    {
        return $this->drivemodeNTime;
    }

    public function setDrivemodeNTime(?string $drivemodeNTime): self
    {
        $this->drivemodeNTime = $drivemodeNTime;

        return $this;
    }

    public function getDrivemodeRTime(): ?string
    {
        return $this->drivemodeRTime;
    }

    public function setDrivemodeRTime(?string $drivemodeRTime): self
    {
        $this->drivemodeRTime = $drivemodeRTime;

        return $this;
    }

    public function getDrivemodeETime(): ?string
    {
        return $this->drivemodeETime;
    }

    public function setDrivemodeETime(?string $drivemodeETime): self
    {
        $this->drivemodeETime = $drivemodeETime;

        return $this;
    }

    public function getTAmbientStart(): ?float
    {
        return $this->tAmbientStart;
    }

    public function setTAmbientStart(?float $tAmbientStart): self
    {
        $this->tAmbientStart = $tAmbientStart;

        return $this;
    }

    public function getTAmbientEnd(): ?float
    {
        return $this->tAmbientEnd;
    }

    public function setTAmbientEnd(?float $tAmbientEnd): self
    {
        $this->tAmbientEnd = $tAmbientEnd;

        return $this;
    }

    public function getTAmbientMin(): ?float
    {
        return $this->tAmbientMin;
    }

    public function setTAmbientMin(?float $tAmbientMin): self
    {
        $this->tAmbientMin = $tAmbientMin;

        return $this;
    }

    public function getTAmbientMax(): ?float
    {
        return $this->tAmbientMax;
    }

    public function setTAmbientMax(?float $tAmbientMax): self
    {
        $this->tAmbientMax = $tAmbientMax;

        return $this;
    }

    public function getTAmbientAvg(): ?float
    {
        return $this->tAmbientAvg;
    }

    public function setTAmbientAvg(?float $tAmbientAvg): self
    {
        $this->tAmbientAvg = $tAmbientAvg;

        return $this;
    }

    public function getEnergySoc(): ?float
    {
        return $this->energySoc;
    }

    public function setEnergySoc(?float $energySoc): self
    {
        $this->energySoc = $energySoc;

        return $this;
    }

    public function getEnergyPer100kmSoc(): ?float
    {
        return $this->energyPer100kmSoc;
    }

    public function setEnergyPer100kmSoc(?float $energyPer100kmSoc): self
    {
        $this->energyPer100kmSoc = $energyPer100kmSoc;

        return $this;
    }

    public function getRecuperatedEnergy(): ?float
    {
        return $this->recuperatedEnergy;
    }

    public function setRecuperatedEnergy(?float $recuperatedEnergy): self
    {
        $this->recuperatedEnergy = $recuperatedEnergy;

        return $this;
    }

    public function getTempMin(): ?float
    {
        return $this->tempMin;
    }

    public function setTempMin(?float $tempMin): self
    {
        $this->tempMin = $tempMin;

        return $this;
    }

    public function getTempMax(): ?float
    {
        return $this->tempMax;
    }

    public function setTempMax(?float $tempMax): self
    {
        $this->tempMax = $tempMax;

        return $this;
    }

    public function getUMin(): ?float
    {
        return $this->uMin;
    }

    public function setUMin(?float $uMin): self
    {
        $this->uMin = $uMin;

        return $this;
    }

    public function getUMax(): ?float
    {
        return $this->uMax;
    }

    public function setUMax(?float $uMax): self
    {
        $this->uMax = $uMax;

        return $this;
    }

    public function getIMin(): ?float
    {
        return $this->iMin;
    }

    public function setIMin(?float $iMin): self
    {
        $this->iMin = $iMin;

        return $this;
    }

    public function getIMax(): ?float
    {
        return $this->iMax;
    }

    public function setIMax(?float $iMax): self
    {
        $this->iMax = $iMax;

        return $this;
    }

    public function getRecuperations(): ?int
    {
        return $this->recuperations;
    }

    public function setRecuperations(?int $recuperations): self
    {
        $this->recuperations = $recuperations;

        return $this;
    }

    public function getAccelerations(): ?int
    {
        return $this->accelerations;
    }

    public function setAccelerations(?int $accelerations): self
    {
        $this->accelerations = $accelerations;

        return $this;
    }

    public function getHandBrakeCount(): ?int
    {
        return $this->handBrakeCount;
    }

    public function setHandBrakeCount(?int $handBrakeCount): self
    {
        $this->handBrakeCount = $handBrakeCount;

        return $this;
    }

    public function getDoorOpenCount(): ?int
    {
        return $this->doorOpenCount;
    }

    public function setDoorOpenCount(?int $doorOpenCount): self
    {
        $this->doorOpenCount = $doorOpenCount;

        return $this;
    }

    public function getBeltUseCount(): ?int
    {
        return $this->beltUseCount;
    }

    public function setBeltUseCount(?int $beltUseCount): self
    {
        $this->beltUseCount = $beltUseCount;

        return $this;
    }

    public function getDoorLockCount(): ?int
    {
        return $this->doorLockCount;
    }

    public function setDoorLockCount(?int $doorLockCount): self
    {
        $this->doorLockCount = $doorLockCount;

        return $this;
    }

    public function getFrontWindowHeatingCount(): ?int
    {
        return $this->frontWindowHeatingCount;
    }

    public function setFrontWindowHeatingCount(?int $frontWindowHeatingCount): self
    {
        $this->frontWindowHeatingCount = $frontWindowHeatingCount;

        return $this;
    }

    public function getFrontWindowHeatingTime(): ?string
    {
        return $this->frontWindowHeatingTime;
    }

    public function setFrontWindowHeatingTime(?string $frontWindowHeatingTime): self
    {
        $this->frontWindowHeatingTime = $frontWindowHeatingTime;

        return $this;
    }

    public function getLeftIndicatorTime(): ?string
    {
        return $this->leftIndicatorTime;
    }

    public function setLeftIndicatorTime(?string $leftIndicatorTime): self
    {
        $this->leftIndicatorTime = $leftIndicatorTime;

        return $this;
    }

    public function getRightIndicatorTime(): ?string
    {
        return $this->rightIndicatorTime;
    }

    public function setRightIndicatorTime(?string $rightIndicatorTime): self
    {
        $this->rightIndicatorTime = $rightIndicatorTime;

        return $this;
    }

    public function getHazzardTime(): ?string
    {
        return $this->hazzardTime;
    }

    public function setHazzardTime(?string $hazzardTime): self
    {
        $this->hazzardTime = $hazzardTime;

        return $this;
    }

    public function getLowBeamTime(): ?string
    {
        return $this->lowBeamTime;
    }

    public function setLowBeamTime(?string $lowBeamTime): self
    {
        $this->lowBeamTime = $lowBeamTime;

        return $this;
    }

    public function getLowBeamCount(): ?int
    {
        return $this->lowBeamCount;
    }

    public function setLowBeamCount(?int $lowBeamCount): self
    {
        $this->lowBeamCount = $lowBeamCount;

        return $this;
    }

    public function getHighBeamTime(): ?string
    {
        return $this->highBeamTime;
    }

    public function setHighBeamTime(?string $highBeamTime): self
    {
        $this->highBeamTime = $highBeamTime;

        return $this;
    }

    public function getHighBeamCount(): ?int
    {
        return $this->highBeamCount;
    }

    public function setHighBeamCount(?int $highBeamCount): self
    {
        $this->highBeamCount = $highBeamCount;

        return $this;
    }

    public function getParkingLightTime(): ?string
    {
        return $this->parkingLightTime;
    }

    public function setParkingLightTime(?string $parkingLightTime): self
    {
        $this->parkingLightTime = $parkingLightTime;

        return $this;
    }

    public function getParkingLightCount(): ?int
    {
        return $this->parkingLightCount;
    }

    public function setParkingLightCount(?int $parkingLightCount): self
    {
        $this->parkingLightCount = $parkingLightCount;

        return $this;
    }

    public function getRearFogLightTime(): ?string
    {
        return $this->rearFogLightTime;
    }

    public function setRearFogLightTime(?string $rearFogLightTime): self
    {
        $this->rearFogLightTime = $rearFogLightTime;

        return $this;
    }

    public function getRearFogLightCount(): ?int
    {
        return $this->rearFogLightCount;
    }

    public function setRearFogLightCount(?int $rearFogLightCount): self
    {
        $this->rearFogLightCount = $rearFogLightCount;

        return $this;
    }

    public function getLatStart(): ?float
    {
        return $this->latStart;
    }

    public function setLatStart(?float $latStart): self
    {
        $this->latStart = $latStart;

        return $this;
    }

    public function getLonStart(): ?float
    {
        return $this->lonStart;
    }

    public function setLonStart(?float $lonStart): self
    {
        $this->lonStart = $lonStart;

        return $this;
    }

    public function getEnergyIntegrated(): ?float
    {
        return $this->energyIntegrated;
    }

    public function setEnergyIntegrated(?float $energyIntegrated): self
    {
        $this->energyIntegrated = $energyIntegrated;

        return $this;
    }

    public function getEnergyPer100kmIntegrated(): ?float
    {
        return $this->energyPer100kmIntegrated;
    }

    public function setEnergyPer100kmIntegrated(?float $energyPer100kmIntegrated): self
    {
        $this->energyPer100kmIntegrated = $energyPer100kmIntegrated;

        return $this;
    }

    public function getHandBrakeTime(): ?string
    {
        return $this->handBrakeTime;
    }

    public function setHandBrakeTime(?string $handBrakeTime): self
    {
        $this->handBrakeTime = $handBrakeTime;

        return $this;
    }

    public function getLatEnd(): ?float
    {
        return $this->latEnd;
    }

    public function setLatEnd(?float $latEnd): self
    {
        $this->latEnd = $latEnd;

        return $this;
    }

    public function getLonEnd(): ?float
    {
        return $this->lonEnd;
    }

    public function setLonEnd(?float $lonEnd): self
    {
        $this->lonEnd = $lonEnd;

        return $this;
    }

    public function getSocStartPercent(): ?float
    {
        return $this->socStartPercent;
    }

    public function setSocStartPercent(?float $socStartPercent): self
    {
        $this->socStartPercent = $socStartPercent;

        return $this;
    }

    public function getSocEndPercent(): ?float
    {
        return $this->socEndPercent;
    }

    public function setSocEndPercent(?float $socEndPercent): self
    {
        $this->socEndPercent = $socEndPercent;

        return $this;
    }

    public function getSocStartKwh(): ?float
    {
        return $this->socStartKwh;
    }

    public function setSocStartKwh(?float $socStartKwh): self
    {
        $this->socStartKwh = $socStartKwh;

        return $this;
    }

    public function getSocEndKwh(): ?float
    {
        return $this->socEndKwh;
    }

    public function setSocEndKwh(?float $socEndKwh): self
    {
        $this->socEndKwh = $socEndKwh;

        return $this;
    }

    public function getHeatingEnergy(): ?float
    {
        return $this->heatingEnergy;
    }

    public function setHeatingEnergy(?float $heatingEnergy): self
    {
        $this->heatingEnergy = $heatingEnergy;

        return $this;
    }

    public function getClimateEnergy(): ?float
    {
        return $this->climateEnergy;
    }

    public function setClimateEnergy(?float $climateEnergy): self
    {
        $this->climateEnergy = $climateEnergy;

        return $this;
    }

    public function getLeftIndicatorCount(): ?int
    {
        return $this->leftIndicatorCount;
    }

    public function setLeftIndicatorCount(?int $leftIndicatorCount): self
    {
        $this->leftIndicatorCount = $leftIndicatorCount;

        return $this;
    }

    public function getRightIndicatorCount(): ?int
    {
        return $this->rightIndicatorCount;
    }

    public function setRightIndicatorCount(?int $rightIndicatorCount): self
    {
        $this->rightIndicatorCount = $rightIndicatorCount;

        return $this;
    }

    public function getHazzardCount(): ?int
    {
        return $this->hazzardCount;
    }

    public function setHazzardCount(?int $hazzardCount): self
    {
        $this->hazzardCount = $hazzardCount;

        return $this;
    }


}
