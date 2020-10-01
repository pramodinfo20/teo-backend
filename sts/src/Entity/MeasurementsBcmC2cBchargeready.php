<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MeasurementsBcmC2cBchargeready
 *
 * @ORM\Table(name="measurements_bcm_c2c_bchargeready", indexes={@ORM\Index(name="IDX_90D2ECAD545317D1", columns={"vehicle_id"})})
 * @ORM\Entity
 */
class MeasurementsBcmC2cBchargeready
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timestamp", type="datetimetz", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $timestamp;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="bcm_c2c_bchargeready", type="boolean", nullable=true)
     */
    private $bcmC2cBchargeready;

    /**
     * @var Vehicles
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Vehicles")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_id", referencedColumnName="vehicle_id")
     * })
     */
    private $vehicle;

    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    public function getBcmC2cBchargeready(): ?bool
    {
        return $this->bcmC2cBchargeready;
    }

    public function setBcmC2cBchargeready(?bool $bcmC2cBchargeready): self
    {
        $this->bcmC2cBchargeready = $bcmC2cBchargeready;

        return $this;
    }

    public function getVehicle(): ?Vehicles
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicles $vehicle): self
    {
        $this->vehicle = $vehicle;

        return $this;
    }


}
