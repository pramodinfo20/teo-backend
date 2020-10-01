<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PowerSchedule
 *
 * @ORM\Table(name="power_schedule", indexes={@ORM\Index(name="IDX_34B8278545317D1", columns={"vehicle_id"})})
 * @ORM\Entity
 */
class PowerSchedule
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
     * @ORM\Column(name="power", type="float", precision=10, scale=0, nullable=true)
     */
    private $power;

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

    public function getPower(): ?float
    {
        return $this->power;
    }

    public function setPower(?float $power): self
    {
        $this->power = $power;

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
