<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Depots
 *
 * @ORM\Table(name="depots", uniqueConstraints={@ORM\UniqueConstraint(name="unique_dp_depot_id",
 *                           columns={"dp_depot_id"})}, indexes={@ORM\Index(name="depots_name_idx", columns={"name"}),
 *                           @ORM\Index(name="depots_division_id_idx", columns={"division_id"}),
 *                                                                     @ORM\Index(name="IDX_D99EA427A1DEC01B", columns={"depot_restriction_id"}),
 *                                                                                                             @ORM\Index(name="IDX_D99EA4271E1847AD", columns={"sibling_depot_id"}),
 *                                                                                                                                                     @ORM\Index(name="IDX_D99EA427D668C002", columns={"depot_type_id"})})
 * @ORM\Entity
 */
class Depots
{
    /**
     * @var int
     *
     * @ORM\Column(name="depot_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="depots_depot_id_seq", allocationSize=1, initialValue=1)
     */
    private $depotId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="address", type="text", nullable=true)
     */
    private $address;

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

    /**
     * @var string|null
     *
     * @ORM\Column(name="power_supplier", type="text", nullable=true)
     */
    private $powerSupplier;

    /**
     * @var int|null
     *
     * @ORM\Column(name="zspl_id", type="integer", nullable=true)
     */
    private $zsplId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="dp_depot_id", type="bigint", nullable=true)
     */
    private $dpDepotId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="emails", type="text", nullable=true)
     */
    private $emails;

    /**
     * @var string|null
     *
     * @ORM\Column(name="wenumber", type="text", nullable=true)
     */
    private $wenumber;

    /**
     * @var int|null
     *
     * @ORM\Column(name="stationprovider", type="integer", nullable=true)
     */
    private $stationprovider;

    /**
     * @var int|null
     *
     * @ORM\Column(name="real_depot_restriction_id", type="integer", nullable=true)
     */
    private $realDepotRestrictionId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vehicle_variant_values_allowed", type="text", nullable=true)
     */
    private $vehicleVariantValuesAllowed;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="active", type="boolean", nullable=true)
     */
    private $active;

    /**
     * @var int|null
     *
     * @ORM\Column(name="workshop_id", type="integer", nullable=true)
     */
    private $workshopId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="street", type="text", nullable=true)
     */
    private $street;

    /**
     * @var string|null
     *
     * @ORM\Column(name="housenr", type="text", nullable=true)
     */
    private $housenr;

    /**
     * @var string|null
     *
     * @ORM\Column(name="postcode", type="text", nullable=true)
     */
    private $postcode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="place", type="text", nullable=true)
     */
    private $place;

    /**
     * @var int|null
     *
     * @ORM\Column(name="penta_folge_id", type="integer", nullable=true)
     */
    private $pentaFolgeId;

    /**
     * @var bool
     *
     * @ORM\Column(name="unregulated_charging", type="boolean", nullable=false)
     */
    private $unregulatedCharging = false;

    /**
     * @var int|null
     *
     * @ORM\Column(name="subcompany_id", type="integer", nullable=true)
     */
    private $subcompanyId;

    /**
     * @var Divisions
     *
     * @ORM\ManyToOne(targetEntity="Divisions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="division_id", referencedColumnName="division_id")
     * })
     */
    private $division;

    /**
     * @var Restrictions
     *
     * @ORM\ManyToOne(targetEntity="Restrictions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="depot_restriction_id", referencedColumnName="restriction_id")
     * })
     */
    private $depotRestriction;

    /**
     * @var Depots
     *
     * @ORM\ManyToOne(targetEntity="Depots")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sibling_depot_id", referencedColumnName="depot_id")
     * })
     */
    private $siblingDepot;

    /**
     * @var DepotTypes
     *
     * @ORM\ManyToOne(targetEntity="DepotTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="depot_type_id", referencedColumnName="depot_type_id")
     * })
     */
    private $depotType;

    public function getDepotId(): ?int
    {
        return $this->depotId;
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
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

    public function getPowerSupplier(): ?string
    {
        return $this->powerSupplier;
    }

    public function setPowerSupplier(?string $powerSupplier): self
    {
        $this->powerSupplier = $powerSupplier;

        return $this;
    }

    public function getZsplId(): ?int
    {
        return $this->zsplId;
    }

    public function setZsplId(?int $zsplId): self
    {
        $this->zsplId = $zsplId;

        return $this;
    }

    public function getDpDepotId(): ?int
    {
        return $this->dpDepotId;
    }

    public function setDpDepotId(?int $dpDepotId): self
    {
        $this->dpDepotId = $dpDepotId;

        return $this;
    }

    public function getEmails(): ?string
    {
        return $this->emails;
    }

    public function setEmails(?string $emails): self
    {
        $this->emails = $emails;

        return $this;
    }

    public function getWenumber(): ?string
    {
        return $this->wenumber;
    }

    public function setWenumber(?string $wenumber): self
    {
        $this->wenumber = $wenumber;

        return $this;
    }

    public function getStationprovider(): ?int
    {
        return $this->stationprovider;
    }

    public function setStationprovider(?int $stationprovider): self
    {
        $this->stationprovider = $stationprovider;

        return $this;
    }

    public function getRealDepotRestrictionId(): ?int
    {
        return $this->realDepotRestrictionId;
    }

    public function setRealDepotRestrictionId(?int $realDepotRestrictionId): self
    {
        $this->realDepotRestrictionId = $realDepotRestrictionId;

        return $this;
    }

    public function getVehicleVariantValuesAllowed(): ?string
    {
        return $this->vehicleVariantValuesAllowed;
    }

    public function setVehicleVariantValuesAllowed(?string $vehicleVariantValuesAllowed): self
    {
        $this->vehicleVariantValuesAllowed = $vehicleVariantValuesAllowed;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getWorkshopId(): ?int
    {
        return $this->workshopId;
    }

    public function setWorkshopId(?int $workshopId): self
    {
        $this->workshopId = $workshopId;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getHousenr(): ?string
    {
        return $this->housenr;
    }

    public function setHousenr(?string $housenr): self
    {
        $this->housenr = $housenr;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(?string $postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(?string $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getPentaFolgeId(): ?int
    {
        return $this->pentaFolgeId;
    }

    public function setPentaFolgeId(?int $pentaFolgeId): self
    {
        $this->pentaFolgeId = $pentaFolgeId;

        return $this;
    }

    public function getUnregulatedCharging(): ?bool
    {
        return $this->unregulatedCharging;
    }

    public function setUnregulatedCharging(bool $unregulatedCharging): self
    {
        $this->unregulatedCharging = $unregulatedCharging;

        return $this;
    }

    public function getSubcompanyId(): ?int
    {
        return $this->subcompanyId;
    }

    public function setSubcompanyId(?int $subcompanyId): self
    {
        $this->subcompanyId = $subcompanyId;

        return $this;
    }

    public function getDivision(): ?Divisions
    {
        return $this->division;
    }

    public function setDivision(?Divisions $division): self
    {
        $this->division = $division;

        return $this;
    }

    public function getDepotRestriction(): ?Restrictions
    {
        return $this->depotRestriction;
    }

    public function setDepotRestriction(?Restrictions $depotRestriction): self
    {
        $this->depotRestriction = $depotRestriction;

        return $this;
    }

    public function getSiblingDepot(): ?self
    {
        return $this->siblingDepot;
    }

    public function setSiblingDepot(?self $siblingDepot): self
    {
        $this->siblingDepot = $siblingDepot;

        return $this;
    }

    public function getDepotType(): ?DepotTypes
    {
        return $this->depotType;
    }

    public function setDepotType(?DepotTypes $depotType): self
    {
        $this->depotType = $depotType;

        return $this;
    }


}
