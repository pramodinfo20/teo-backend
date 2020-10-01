<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Departures
 *
 * @ORM\Table(name="departures", indexes={@ORM\Index(name="IDX_3C496767B08FA272", columns={"district_id"})})
 * @ORM\Entity
 */
class Departures
{
    /**
     * @var int
     *
     * @ORM\Column(name="vehicle_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $vehicleId;

    /**
     * @var string
     *
     * @ORM\Column(name="day", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $day;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time", type="time", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $time;

    /**
     * @var int|null
     *
     * @ORM\Column(name="soc", type="integer", nullable=true)
     */
    private $soc;

    /**
     * @var Districts
     *
     * @ORM\ManyToOne(targetEntity="Districts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="district_id", referencedColumnName="district_id")
     * })
     */
    private $district;

    public function getVehicleId(): ?int
    {
        return $this->vehicleId;
    }

    public function getDay(): ?string
    {
        return $this->day;
    }

    public function getTime(): ?\DateTimeInterface
    {
        return $this->time;
    }

    public function getSoc(): ?int
    {
        return $this->soc;
    }

    public function setSoc(?int $soc): self
    {
        $this->soc = $soc;

        return $this;
    }

    public function getDistrict(): ?Districts
    {
        return $this->district;
    }

    public function setDistrict(?Districts $district): self
    {
        $this->district = $district;

        return $this;
    }


}
