<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ChargingStats
 *
 * @ORM\Table(name="charging_stats")
 * @ORM\Entity
 */
class ChargingStats
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
     * @ORM\Column(name="timestamp_end", type="integer", nullable=true)
     */
    private $timestampEnd;

    /**
     * @var float|null
     *
     * @ORM\Column(name="km_total", type="float", precision=10, scale=0, nullable=true)
     */
    private $kmTotal;

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
     * @ORM\Column(name="lat", type="float", precision=10, scale=0, nullable=true)
     */
    private $lat;

    /**
     * @var float|null
     *
     * @ORM\Column(name="lon", type="float", precision=10, scale=0, nullable=true)
     */
    private $lon;

    /**
     * @var float|null
     *
     * @ORM\Column(name="ac_energy", type="float", precision=10, scale=0, nullable=true)
     */
    private $acEnergy;

    /**
     * @var float|null
     *
     * @ORM\Column(name="dc_energy", type="float", precision=10, scale=0, nullable=true)
     */
    private $dcEnergy;

    /**
     * @var float|null
     *
     * @ORM\Column(name="uac_avg", type="float", precision=10, scale=0, nullable=true)
     */
    private $uacAvg;

    /**
     * @var float|null
     *
     * @ORM\Column(name="uac_max", type="float", precision=10, scale=0, nullable=true)
     */
    private $uacMax;

    /**
     * @var float|null
     *
     * @ORM\Column(name="iac_avg", type="float", precision=10, scale=0, nullable=true)
     */
    private $iacAvg;

    /**
     * @var float|null
     *
     * @ORM\Column(name="iac_max", type="float", precision=10, scale=0, nullable=true)
     */
    private $iacMax;

    /**
     * @var float|null
     *
     * @ORM\Column(name="pac_avg", type="float", precision=10, scale=0, nullable=true)
     */
    private $pacAvg;

    /**
     * @var float|null
     *
     * @ORM\Column(name="pac_max", type="float", precision=10, scale=0, nullable=true)
     */
    private $pacMax;

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

    public function getTimestampEnd(): ?int
    {
        return $this->timestampEnd;
    }

    public function setTimestampEnd(?int $timestampEnd): self
    {
        $this->timestampEnd = $timestampEnd;

        return $this;
    }

    public function getKmTotal(): ?float
    {
        return $this->kmTotal;
    }

    public function setKmTotal(?float $kmTotal): self
    {
        $this->kmTotal = $kmTotal;

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

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(?float $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLon(): ?float
    {
        return $this->lon;
    }

    public function setLon(?float $lon): self
    {
        $this->lon = $lon;

        return $this;
    }

    public function getAcEnergy(): ?float
    {
        return $this->acEnergy;
    }

    public function setAcEnergy(?float $acEnergy): self
    {
        $this->acEnergy = $acEnergy;

        return $this;
    }

    public function getDcEnergy(): ?float
    {
        return $this->dcEnergy;
    }

    public function setDcEnergy(?float $dcEnergy): self
    {
        $this->dcEnergy = $dcEnergy;

        return $this;
    }

    public function getUacAvg(): ?float
    {
        return $this->uacAvg;
    }

    public function setUacAvg(?float $uacAvg): self
    {
        $this->uacAvg = $uacAvg;

        return $this;
    }

    public function getUacMax(): ?float
    {
        return $this->uacMax;
    }

    public function setUacMax(?float $uacMax): self
    {
        $this->uacMax = $uacMax;

        return $this;
    }

    public function getIacAvg(): ?float
    {
        return $this->iacAvg;
    }

    public function setIacAvg(?float $iacAvg): self
    {
        $this->iacAvg = $iacAvg;

        return $this;
    }

    public function getIacMax(): ?float
    {
        return $this->iacMax;
    }

    public function setIacMax(?float $iacMax): self
    {
        $this->iacMax = $iacMax;

        return $this;
    }

    public function getPacAvg(): ?float
    {
        return $this->pacAvg;
    }

    public function setPacAvg(?float $pacAvg): self
    {
        $this->pacAvg = $pacAvg;

        return $this;
    }

    public function getPacMax(): ?float
    {
        return $this->pacMax;
    }

    public function setPacMax(?float $pacMax): self
    {
        $this->pacMax = $pacMax;

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


}
