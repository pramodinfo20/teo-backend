<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BookingVehicles
 *
 * @ORM\Table(name="booking_vehicles", uniqueConstraints={@ORM\UniqueConstraint(name="booking_vehicles_cost_centre_vehicleid_key", columns={"cost_centre", "vehicleid"})}, indexes={@ORM\Index(name="IDX_24FAF7777587657C", columns={"vehicleid"})})
 * @ORM\Entity
 */
class BookingVehicles
{
    /**
     * @var int
     *
     * @ORM\Column(name="booking_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="booking_vehicles_booking_id_seq", allocationSize=1, initialValue=1)
     */
    private $bookingId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="cost_centre", type="integer", nullable=true)
     */
    private $costCentre;

    /**
     * @var Vehicles
     *
     * @ORM\ManyToOne(targetEntity="Vehicles")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicleid", referencedColumnName="vehicle_id")
     * })
     */
    private $vehicleid;

    public function getBookingId(): ?int
    {
        return $this->bookingId;
    }

    public function getCostCentre(): ?int
    {
        return $this->costCentre;
    }

    public function setCostCentre(?int $costCentre): self
    {
        $this->costCentre = $costCentre;

        return $this;
    }

    public function getVehicleid(): ?Vehicles
    {
        return $this->vehicleid;
    }

    public function setVehicleid(?Vehicles $vehicleid): self
    {
        $this->vehicleid = $vehicleid;

        return $this;
    }


}
