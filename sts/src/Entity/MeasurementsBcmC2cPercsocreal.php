<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MeasurementsBcmC2cPercsocreal
 *
 * @ORM\Table(name="measurements_bcm_c2c_percsocreal", indexes={@ORM\Index(name="IDX_296B7AC4545317D1", columns={"vehicle_id"})})
 * @ORM\Entity
 */
class MeasurementsBcmC2cPercsocreal
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
     * @ORM\Column(name="bcm_c2c_percsocreal", type="float", precision=10, scale=0, nullable=true)
     */
    private $bcmC2cPercsocreal;

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

    public function getBcmC2cPercsocreal(): ?float
    {
        return $this->bcmC2cPercsocreal;
    }

    public function setBcmC2cPercsocreal(?float $bcmC2cPercsocreal): self
    {
        $this->bcmC2cPercsocreal = $bcmC2cPercsocreal;

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
