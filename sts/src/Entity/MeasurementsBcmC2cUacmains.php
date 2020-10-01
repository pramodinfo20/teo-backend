<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MeasurementsBcmC2cUacmains
 *
 * @ORM\Table(name="measurements_bcm_c2c_uacmains", indexes={@ORM\Index(name="IDX_2745DB46545317D1", columns={"vehicle_id"})})
 * @ORM\Entity
 */
class MeasurementsBcmC2cUacmains
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
     * @ORM\Column(name="bcm_c2c_uacmains", type="boolean", nullable=true)
     */
    private $bcmC2cUacmains;

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

    public function getBcmC2cUacmains(): ?bool
    {
        return $this->bcmC2cUacmains;
    }

    public function setBcmC2cUacmains(?bool $bcmC2cUacmains): self
    {
        $this->bcmC2cUacmains = $bcmC2cUacmains;

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
