<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MeasurementsBcmC2cUpack
 *
 * @ORM\Table(name="measurements_bcm_c2c_upack", indexes={@ORM\Index(name="IDX_E863E72B545317D1",
 *                                               columns={"vehicle_id"})})
 * @ORM\Entity
 */
class MeasurementsBcmC2cUpack
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
     * @ORM\Column(name="bcm_c2c_upack", type="float", precision=10, scale=0, nullable=true)
     */
    private $bcmC2cUpack;

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

    public function getBcmC2cUpack(): ?float
    {
        return $this->bcmC2cUpack;
    }

    public function setBcmC2cUpack(?float $bcmC2cUpack): self
    {
        $this->bcmC2cUpack = $bcmC2cUpack;

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
