<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MeasurementsBcmC2cKl15a
 *
 * @ORM\Table(name="measurements_bcm_c2c_kl15a", indexes={@ORM\Index(name="IDX_4DF41B93545317D1",
 *                                               columns={"vehicle_id"})})
 * @ORM\Entity
 */
class MeasurementsBcmC2cKl15a
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
     * @ORM\Column(name="bcm_c2c_kl15a", type="boolean", nullable=true)
     */
    private $bcmC2cKl15a;

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

    public function getBcmC2cKl15a(): ?bool
    {
        return $this->bcmC2cKl15a;
    }

    public function setBcmC2cKl15a(?bool $bcmC2cKl15a): self
    {
        $this->bcmC2cKl15a = $bcmC2cKl15a;

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
