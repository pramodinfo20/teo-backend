<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Parts
 *
 * @ORM\Table(name="parts", indexes={@ORM\Index(name="IDX_6940A7FEFE54D947", columns={"group_id"})})
 * @ORM\Entity
 */
class Parts
{
    /**
     * @var int
     *
     * @ORM\Column(name="part_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="parts_part_id_seq", allocationSize=1, initialValue=1)
     */
    private $partId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=false)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="begleitscheinname", type="text", nullable=true)
     */
    private $begleitscheinname;

    /**
     * @var int|null
     *
     * @ORM\Column(name="parameter_overwrite_id", type="integer", nullable=true)
     */
    private $parameterOverwriteId;

    /**
     * @var bool
     *
     * @ORM\Column(name="show_in_production_protocol", type="boolean", nullable=false, options={"default"="1"})
     */
    private $showInProductionProtocol = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_default", type="boolean", nullable=false)
     */
    private $isDefault = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="visible_engg", type="boolean", nullable=false, options={"default"="1"})
     */
    private $visibleEngg = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="visible_sales", type="boolean", nullable=false, options={"default"="1"})
     */
    private $visibleSales = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_default_dp", type="boolean", nullable=false)
     */
    private $isDefaultDp = false;

    /**
     * @var string|null
     *
     * @ORM\Column(name="part_properties", type="text", nullable=true)
     */
    private $partProperties;

    /**
     * @var string|null
     *
     * @ORM\Column(name="link_windchill", type="text", nullable=true)
     */
    private $linkWindchill;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_feature", type="boolean", nullable=false)
     */
    private $isFeature = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_special_feature", type="boolean", nullable=false)
     */
    private $isSpecialFeature = false;

    /**
     * @var PartGroups
     *
     * @ORM\ManyToOne(targetEntity="PartGroups")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="group_id", referencedColumnName="group_id")
     * })
     */
    private $group;

    /**
     * @var Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Vehicles", mappedBy="part")
     */
    private $vehicle;

    /**
     * @var Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="PentaNumbers", mappedBy="part")
     */
    private $pentaNumber;

    /**
     * @var Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="VehicleVariants", mappedBy="part")
     */
    private $variant;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->vehicle = new \Doctrine\Common\Collections\ArrayCollection();
        $this->pentaNumber = new \Doctrine\Common\Collections\ArrayCollection();
        $this->variant = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getPartId(): ?int
    {
        return $this->partId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBegleitscheinname(): ?string
    {
        return $this->begleitscheinname;
    }

    public function setBegleitscheinname(?string $begleitscheinname): self
    {
        $this->begleitscheinname = $begleitscheinname;

        return $this;
    }

    public function getParameterOverwriteId(): ?int
    {
        return $this->parameterOverwriteId;
    }

    public function setParameterOverwriteId(?int $parameterOverwriteId): self
    {
        $this->parameterOverwriteId = $parameterOverwriteId;

        return $this;
    }

    public function getShowInProductionProtocol(): ?bool
    {
        return $this->showInProductionProtocol;
    }

    public function setShowInProductionProtocol(bool $showInProductionProtocol): self
    {
        $this->showInProductionProtocol = $showInProductionProtocol;

        return $this;
    }

    public function getIsDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    public function getVisibleEngg(): ?bool
    {
        return $this->visibleEngg;
    }

    public function setVisibleEngg(bool $visibleEngg): self
    {
        $this->visibleEngg = $visibleEngg;

        return $this;
    }

    public function getVisibleSales(): ?bool
    {
        return $this->visibleSales;
    }

    public function setVisibleSales(bool $visibleSales): self
    {
        $this->visibleSales = $visibleSales;

        return $this;
    }

    public function getIsDefaultDp(): ?bool
    {
        return $this->isDefaultDp;
    }

    public function setIsDefaultDp(bool $isDefaultDp): self
    {
        $this->isDefaultDp = $isDefaultDp;

        return $this;
    }

    public function getPartProperties(): ?string
    {
        return $this->partProperties;
    }

    public function setPartProperties(?string $partProperties): self
    {
        $this->partProperties = $partProperties;

        return $this;
    }

    public function getLinkWindchill(): ?string
    {
        return $this->linkWindchill;
    }

    public function setLinkWindchill(?string $linkWindchill): self
    {
        $this->linkWindchill = $linkWindchill;

        return $this;
    }

    public function getIsFeature(): ?bool
    {
        return $this->isFeature;
    }

    public function setIsFeature(bool $isFeature): self
    {
        $this->isFeature = $isFeature;

        return $this;
    }

    public function getIsSpecialFeature(): ?bool
    {
        return $this->isSpecialFeature;
    }

    public function setIsSpecialFeature(bool $isSpecialFeature): self
    {
        $this->isSpecialFeature = $isSpecialFeature;

        return $this;
    }

    public function getGroup(): ?PartGroups
    {
        return $this->group;
    }

    public function setGroup(?PartGroups $group): self
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return Collection|Vehicles[]
     */
    public function getVehicle(): Collection
    {
        return $this->vehicle;
    }

    public function addVehicle(Vehicles $vehicle): self
    {
        if (!$this->vehicle->contains($vehicle)) {
            $this->vehicle[] = $vehicle;
            $vehicle->addPart($this);
        }

        return $this;
    }

    public function removeVehicle(Vehicles $vehicle): self
    {
        if ($this->vehicle->contains($vehicle)) {
            $this->vehicle->removeElement($vehicle);
            $vehicle->removePart($this);
        }

        return $this;
    }

    /**
     * @return Collection|PentaNumbers[]
     */
    public function getPentaNumber(): Collection
    {
        return $this->pentaNumber;
    }

    public function addPentaNumber(PentaNumbers $pentaNumber): self
    {
        if (!$this->pentaNumber->contains($pentaNumber)) {
            $this->pentaNumber[] = $pentaNumber;
            $pentaNumber->addPart($this);
        }

        return $this;
    }

    public function removePentaNumber(PentaNumbers $pentaNumber): self
    {
        if ($this->pentaNumber->contains($pentaNumber)) {
            $this->pentaNumber->removeElement($pentaNumber);
            $pentaNumber->removePart($this);
        }

        return $this;
    }

    /**
     * @return Collection|VehicleVariants[]
     */
    public function getVariant(): Collection
    {
        return $this->variant;
    }

    public function addVariant(VehicleVariants $variant): self
    {
        if (!$this->variant->contains($variant)) {
            $this->variant[] = $variant;
            $variant->addPart($this);
        }

        return $this;
    }

    public function removeVariant(VehicleVariants $variant): self
    {
        if ($this->variant->contains($variant)) {
            $this->variant->removeElement($variant);
            $variant->removePart($this);
        }

        return $this;
    }

}
