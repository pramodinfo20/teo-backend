<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MeasurementsBcmC2cTambient
 *
 * @ORM\Table(name="measurements_bcm_c2c_tambient", indexes={@ORM\Index(name="IDX_17F6951545317D1", columns={"vehicle_id"})})
 * @ORM\Entity
 */
class MeasurementsBcmC2cTambient
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
     * @var float|null
     *
     * @ORM\Column(name="bcm_c2c_tambient", type="float", precision=10, scale=0, nullable=true)
     */
    private $bcmC2cTambient;

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

    public function getBcmC2cTambient(): ?float
    {
        return $this->bcmC2cTambient;
    }

    public function setBcmC2cTambient(?float $bcmC2cTambient): self
    {
        $this->bcmC2cTambient = $bcmC2cTambient;

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
