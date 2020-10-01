<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VehiclesIllegalDispatched
 *
 * @ORM\Table(name="vehicles_illegal_dispatched")
 * @ORM\Entity
 */
class VehiclesIllegalDispatched
{
    /**
     * @var string
     *
     * @ORM\Column(name="vin", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="vehicles_illegal_dispatched_vin_seq", allocationSize=1, initialValue=1)
     */
    private $vin;

    /**
     * @var float|null
     *
     * @ORM\Column(name="lat", type="float", precision=10, scale=0, nullable=true)
     */
    private $lat;

    /**
     * @var float|null
     *
     * @ORM\Column(name="lon", type="float", precision=10, scale=0, nullable=true)
     */
    private $lon;

    public function getVin(): ?string
    {
        return $this->vin;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(?float $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLon(): ?float
    {
        return $this->lon;
    }

    public function setLon(?float $lon): self
    {
        $this->lon = $lon;

        return $this;
    }


}
