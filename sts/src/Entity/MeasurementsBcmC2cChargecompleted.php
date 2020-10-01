<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MeasurementsBcmC2cChargecompleted
 *
 * @ORM\Table(name="measurements_bcm_c2c_chargecompleted", indexes={@ORM\Index(name="IDX_21A0EE1C545317D1", columns={"vehicle_id"})})
 * @ORM\Entity
 */
class MeasurementsBcmC2cChargecompleted
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
     * @var int|null
     *
     * @ORM\Column(name="bcm_c2c_chargecompleted", type="integer", nullable=true)
     */
    private $bcmC2cChargecompleted;

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

    public function getBcmC2cChargecompleted(): ?int
    {
        return $this->bcmC2cChargecompleted;
    }

    public function setBcmC2cChargecompleted(?int $bcmC2cChargecompleted): self
    {
        $this->bcmC2cChargecompleted = $bcmC2cChargecompleted;

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
