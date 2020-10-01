<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MeasurementsBcmC2cPercsoc
 *
 * @ORM\Table(name="measurements_bcm_c2c_percsoc", indexes={@ORM\Index(name="IDX_B4F94B1545317D1",
 *                                                 columns={"vehicle_id"})})
 * @ORM\Entity
 */
class MeasurementsBcmC2cPercsoc
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
     * @ORM\Column(name="bcm_c2c_percsoc", type="float", precision=10, scale=0, nullable=true)
     */
    private $bcmC2cPercsoc;

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

    public function getBcmC2cPercsoc(): ?float
    {
        return $this->bcmC2cPercsoc;
    }

    public function setBcmC2cPercsoc(?float $bcmC2cPercsoc): self
    {
        $this->bcmC2cPercsoc = $bcmC2cPercsoc;

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
