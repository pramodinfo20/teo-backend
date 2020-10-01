<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * C2cboxHistory
 *
 * @ORM\Table(name="c2cbox_history", indexes={@ORM\Index(name="IDX_4C116A3F545317D1", columns={"vehicle_id"})})
 * @ORM\Entity
 */
class C2cboxHistory
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timestamp", type="datetimetz", nullable=false, options={"default"="now()"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $timestamp = 'now()';

    /**
     * @var string
     *
     * @ORM\Column(name="c2cbox", type="text", nullable=false)
     */
    private $c2cbox;

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

    public function getC2cbox(): ?string
    {
        return $this->c2cbox;
    }

    public function setC2cbox(string $c2cbox): self
    {
        $this->c2cbox = $c2cbox;

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
