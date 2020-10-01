<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MeasurementsBcmC2cIpack
 *
 * @ORM\Table(name="measurements_bcm_c2c_ipack", indexes={@ORM\Index(name="IDX_4D739DA8545317D1",
 *                                               columns={"vehicle_id"})})
 * @ORM\Entity
 */
class MeasurementsBcmC2cIpack
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
     * @ORM\Column(name="bcm_c2c_ipack", type="float", precision=10, scale=0, nullable=true)
     */
    private $bcmC2cIpack;

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

    public function getBcmC2cIpack(): ?float
    {
        return $this->bcmC2cIpack;
    }

    public function setBcmC2cIpack(?float $bcmC2cIpack): self
    {
        $this->bcmC2cIpack = $bcmC2cIpack;

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
