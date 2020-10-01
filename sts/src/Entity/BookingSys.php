<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BookingSys
 *
 * @ORM\Table(name="booking_sys", indexes={@ORM\Index(name="IDX_B862D4F53301C60", columns={"booking_id"})})
 * @ORM\Entity
 */
class BookingSys
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="booking_sys_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="booking_date", type="datetime", nullable=true)
     */
    private $bookingDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="booking_start", type="datetime", nullable=true)
     */
    private $bookingStart;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="booking_end", type="datetime", nullable=true)
     */
    private $bookingEnd;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="booking_status", type="boolean", nullable=true)
     */
    private $bookingStatus;

    /**
     * @var int|null
     *
     * @ORM\Column(name="userid", type="integer", nullable=true)
     */
    private $userid;

    /**
     * @var int|null
     *
     * @ORM\Column(name="booking_vehicleid", type="integer", nullable=true)
     */
    private $bookingVehicleid;

    /**
     * @var BookingVehicles
     *
     * @ORM\ManyToOne(targetEntity="BookingVehicles")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="booking_id", referencedColumnName="booking_id")
     * })
     */
    private $booking;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBookingDate(): ?\DateTimeInterface
    {
        return $this->bookingDate;
    }

    public function setBookingDate(?\DateTimeInterface $bookingDate): self
    {
        $this->bookingDate = $bookingDate;

        return $this;
    }

    public function getBookingStart(): ?\DateTimeInterface
    {
        return $this->bookingStart;
    }

    public function setBookingStart(?\DateTimeInterface $bookingStart): self
    {
        $this->bookingStart = $bookingStart;

        return $this;
    }

    public function getBookingEnd(): ?\DateTimeInterface
    {
        return $this->bookingEnd;
    }

    public function setBookingEnd(?\DateTimeInterface $bookingEnd): self
    {
        $this->bookingEnd = $bookingEnd;

        return $this;
    }

    public function getBookingStatus(): ?bool
    {
        return $this->bookingStatus;
    }

    public function setBookingStatus(?bool $bookingStatus): self
    {
        $this->bookingStatus = $bookingStatus;

        return $this;
    }

    public function getUserid(): ?int
    {
        return $this->userid;
    }

    public function setUserid(?int $userid): self
    {
        $this->userid = $userid;

        return $this;
    }

    public function getBookingVehicleid(): ?int
    {
        return $this->bookingVehicleid;
    }

    public function setBookingVehicleid(?int $bookingVehicleid): self
    {
        $this->bookingVehicleid = $bookingVehicleid;

        return $this;
    }

    public function getBooking(): ?BookingVehicles
    {
        return $this->booking;
    }

    public function setBooking(?BookingVehicles $booking): self
    {
        $this->booking = $booking;

        return $this;
    }


}
