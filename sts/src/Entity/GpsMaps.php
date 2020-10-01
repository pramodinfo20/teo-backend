<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GpsMaps
 *
 * @ORM\Table(name="gps_maps", indexes={@ORM\Index(name="IDX_2FB562C8510D4DE", columns={"depot_id"}),
 *                             @ORM\Index(name="IDX_2FB562CA80EC9DE", columns={"parent_map"})})
 * @ORM\Entity
 */
class GpsMaps
{
    /**
     * @var int
     *
     * @ORM\Column(name="map_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="gps_maps_map_id_seq", allocationSize=1, initialValue=1)
     */
    private $mapId;

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
     * @var string|null
     *
     * @ORM\Column(name="display_group", type="text", nullable=true)
     */
    private $displayGroup;

    /**
     * @var int|null
     *
     * @ORM\Column(name="display_index", type="integer", nullable=true)
     */
    private $displayIndex;

    /**
     * @var string|null
     *
     * @ORM\Column(name="image_file", type="text", nullable=true)
     */
    private $imageFile;

    /**
     * @var float|null
     *
     * @ORM\Column(name="from_lat", type="float", precision=10, scale=0, nullable=true)
     */
    private $fromLat;

    /**
     * @var float|null
     *
     * @ORM\Column(name="from_lon", type="float", precision=10, scale=0, nullable=true)
     */
    private $fromLon;

    /**
     * @var float|null
     *
     * @ORM\Column(name="to_lat", type="float", precision=10, scale=0, nullable=true)
     */
    private $toLat;

    /**
     * @var float|null
     *
     * @ORM\Column(name="to_lon", type="float", precision=10, scale=0, nullable=true)
     */
    private $toLon;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_parking_location", type="boolean", nullable=false, options={"default"="1"})
     */
    private $isParkingLocation = true;

    /**
     * @var Depots
     *
     * @ORM\ManyToOne(targetEntity="Depots")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="depot_id", referencedColumnName="depot_id")
     * })
     */
    private $depot;

    /**
     * @var GpsMaps
     *
     * @ORM\ManyToOne(targetEntity="GpsMaps")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_map", referencedColumnName="map_id")
     * })
     */
    private $parentMap;

    public function getMapId(): ?int
    {
        return $this->mapId;
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

    public function getDisplayGroup(): ?string
    {
        return $this->displayGroup;
    }

    public function setDisplayGroup(?string $displayGroup): self
    {
        $this->displayGroup = $displayGroup;

        return $this;
    }

    public function getDisplayIndex(): ?int
    {
        return $this->displayIndex;
    }

    public function setDisplayIndex(?int $displayIndex): self
    {
        $this->displayIndex = $displayIndex;

        return $this;
    }

    public function getImageFile(): ?string
    {
        return $this->imageFile;
    }

    public function setImageFile(?string $imageFile): self
    {
        $this->imageFile = $imageFile;

        return $this;
    }

    public function getFromLat(): ?float
    {
        return $this->fromLat;
    }

    public function setFromLat(?float $fromLat): self
    {
        $this->fromLat = $fromLat;

        return $this;
    }

    public function getFromLon(): ?float
    {
        return $this->fromLon;
    }

    public function setFromLon(?float $fromLon): self
    {
        $this->fromLon = $fromLon;

        return $this;
    }

    public function getToLat(): ?float
    {
        return $this->toLat;
    }

    public function setToLat(?float $toLat): self
    {
        $this->toLat = $toLat;

        return $this;
    }

    public function getToLon(): ?float
    {
        return $this->toLon;
    }

    public function setToLon(?float $toLon): self
    {
        $this->toLon = $toLon;

        return $this;
    }

    public function getIsParkingLocation(): ?bool
    {
        return $this->isParkingLocation;
    }

    public function setIsParkingLocation(bool $isParkingLocation): self
    {
        $this->isParkingLocation = $isParkingLocation;

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

    public function getParentMap(): ?self
    {
        return $this->parentMap;
    }

    public function setParentMap(?self $parentMap): self
    {
        $this->parentMap = $parentMap;

        return $this;
    }


}
