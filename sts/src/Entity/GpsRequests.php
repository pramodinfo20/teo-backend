<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GpsRequests
 *
 * @ORM\Table(name="gps_requests")
 * @ORM\Entity
 */
class GpsRequests
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_requested", type="datetimetz", nullable=false, options={"default"="now()"})
     */
    private $lastRequested = 'now()';

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

    public function getLastRequested(): ?\DateTimeInterface
    {
        return $this->lastRequested;
    }

    public function setLastRequested(\DateTimeInterface $lastRequested): self
    {
        $this->lastRequested = $lastRequested;

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
