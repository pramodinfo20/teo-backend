<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stations
 *
 * @ORM\Table(name="stations", indexes={@ORM\Index(name="idx_stations_depot_id", columns={"depot_id"}),
 *                             @ORM\Index(name="IDX_A7F775E9E6160631", columns={"restriction_id"})})
 * @ORM\Entity
 */
class Stations
{
    /**
     * @var int
     *
     * @ORM\Column(name="station_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="stations_station_id_seq", allocationSize=1, initialValue=1)
     */
    private $stationId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(name="station_power", type="float", precision=10, scale=0, nullable=false)
     */
    private $stationPower;

    /**
     * @var int|null
     *
     * @ORM\Column(name="restriction_id2", type="integer", nullable=true)
     */
    private $restrictionId2;

    /**
     * @var int|null
     *
     * @ORM\Column(name="restriction_id3", type="integer", nullable=true)
     */
    private $restrictionId3;

    /**
     * @var int|null
     *
     * @ORM\Column(name="vehicle_variant_value_allowed", type="integer", nullable=true)
     */
    private $vehicleVariantValueAllowed;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="vehicle_variant_update_ts", type="datetimetz", nullable=true)
     */
    private $vehicleVariantUpdateTs;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="deactivate", type="boolean", nullable=true)
     */
    private $deactivate = false;

    /**
     * @var Restrictions
     *
     * @ORM\ManyToOne(targetEntity="Restrictions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="restriction_id", referencedColumnName="restriction_id")
     * })
     */
    private $restriction;

    /**
     * @var Depots
     *
     * @ORM\ManyToOne(targetEntity="Depots")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="depot_id", referencedColumnName="depot_id")
     * })
     */
    private $depot;

    public function getStationId(): ?int
    {
        return $this->stationId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getStationPower(): ?float
    {
        return $this->stationPower;
    }

    public function setStationPower(float $stationPower): self
    {
        $this->stationPower = $stationPower;

        return $this;
    }

    public function getRestrictionId2(): ?int
    {
        return $this->restrictionId2;
    }

    public function setRestrictionId2(?int $restrictionId2): self
    {
        $this->restrictionId2 = $restrictionId2;

        return $this;
    }

    public function getRestrictionId3(): ?int
    {
        return $this->restrictionId3;
    }

    public function setRestrictionId3(?int $restrictionId3): self
    {
        $this->restrictionId3 = $restrictionId3;

        return $this;
    }

    public function getVehicleVariantValueAllowed(): ?int
    {
        return $this->vehicleVariantValueAllowed;
    }

    public function setVehicleVariantValueAllowed(?int $vehicleVariantValueAllowed): self
    {
        $this->vehicleVariantValueAllowed = $vehicleVariantValueAllowed;

        return $this;
    }

    public function getVehicleVariantUpdateTs(): ?\DateTimeInterface
    {
        return $this->vehicleVariantUpdateTs;
    }

    public function setVehicleVariantUpdateTs(?\DateTimeInterface $vehicleVariantUpdateTs): self
    {
        $this->vehicleVariantUpdateTs = $vehicleVariantUpdateTs;

        return $this;
    }

    public function getDeactivate(): ?bool
    {
        return $this->deactivate;
    }

    public function setDeactivate(?bool $deactivate): self
    {
        $this->deactivate = $deactivate;

        return $this;
    }

    public function getRestriction(): ?Restrictions
    {
        return $this->restriction;
    }

    public function setRestriction(?Restrictions $restriction): self
    {
        $this->restriction = $restriction;

        return $this;
    }

    public function getDepot(): ?Depots
    {
        return $this->depot;
    }

    public function setDepot(?Depots $depot): self
    {
        $this->depot = $depot;

        return $this;
    }


}
