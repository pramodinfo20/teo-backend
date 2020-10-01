<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MeasurementsBcmC2cBchargeplug
 *
 * @ORM\Table(name="measurements_bcm_c2c_bchargeplug", indexes={@ORM\Index(name="IDX_70E439BB545317D1", columns={"vehicle_id"})})
 * @ORM\Entity
 */
class MeasurementsBcmC2cBchargeplug
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
     * @ORM\Column(name="bcm_c2c_bchargeplug", type="boolean", nullable=true)
     */
    private $bcmC2cBchargeplug;

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

    public function getBcmC2cBchargeplug(): ?bool
    {
        return $this->bcmC2cBchargeplug;
    }

    public function setBcmC2cBchargeplug(?bool $bcmC2cBchargeplug): self
    {
        $this->bcmC2cBchargeplug = $bcmC2cBchargeplug;

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
