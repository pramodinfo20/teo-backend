<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MeasurementsBcmC2cIacmains
 *
 * @ORM\Table(name="measurements_bcm_c2c_iacmains", indexes={@ORM\Index(name="IDX_C51FF22545317D1", columns={"vehicle_id"})})
 * @ORM\Entity
 */
class MeasurementsBcmC2cIacmains
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
     * @ORM\Column(name="bcm_c2c_iacmains", type="boolean", nullable=true)
     */
    private $bcmC2cIacmains;

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

    public function getBcmC2cIacmains(): ?bool
    {
        return $this->bcmC2cIacmains;
    }

    public function setBcmC2cIacmains(?bool $bcmC2cIacmains): self
    {
        $this->bcmC2cIacmains = $bcmC2cIacmains;

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
