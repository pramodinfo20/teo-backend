<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * PentaNumbers
 *
 * @ORM\Table(name="penta_numbers", indexes={@ORM\Index(name="IDX_D59DD8C8BEFB2632", columns={"vehicle_variant_id"}),
 *                                  @ORM\Index(name="IDX_D59DD8C89F75D7B0", columns={"external_id"}),
 *                                                                          @ORM\Index(name="IDX_D59DD8C87ADA1FB5", columns={"color_id"}),
 *                                                                                                                  @ORM\Index(name="IDX_D59DD8C8113FD27A", columns={"penta_config_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\PentaNumbersRepository")
 */
class PentaNumbers
{
    /**
     * @var int
     *
     * @ORM\Column(name="penta_number_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="penta_numbers_penta_number_id_seq", allocationSize=1, initialValue=1)
     */
    private $pentaNumberId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="penta_number", type="text", nullable=true)
     */
    private $pentaNumber;

    /**
     * @var bool
     *
     * @ORM\Column(name="internal_use", type="boolean", nullable=false)
     */
    private $internalUse = false;

    /**
     * @var VehicleVariants
     *
     * @ORM\ManyToOne(targetEntity="VehicleVariants")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_variant_id", referencedColumnName="vehicle_variant_id")
     * })
     */
    private $vehicleVariant;

    /**
     * @var VehicleExternalNames
     *
     * @ORM\ManyToOne(targetEntity="VehicleExternalNames")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="external_id", referencedColumnName="external_id")
     * })
     */
    private $external;

    /**
     * @var Colors
     *
     * @ORM\ManyToOne(targetEntity="Colors")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="color_id", referencedColumnName="color_id")
     * })
     */
    private $color;

    /**
     * @var PentaNumbers
     *
     * @ORM\ManyToOne(targetEntity="PentaNumbers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="penta_config_id", referencedColumnName="penta_number_id")
     * })
     */
    private $pentaConfig;

    /**
     * @var Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Parts", inversedBy="pentaNumber")
     * @ORM\JoinTable(name="penta_number_parts_mapping",
     *   joinColumns={
     *     @ORM\JoinColumn(name="penta_number_id", referencedColumnName="penta_number_id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="part_id", referencedColumnName="part_id")
     *   }
     * )
     */
    private $part;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->part = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getPentaNumberId(): ?int
    {
        return $this->pentaNumberId;
    }

    public function getPentaNumber(): ?string
    {
        return $this->pentaNumber;
    }

    public function setPentaNumber(?string $pentaNumber): self
    {
        $this->pentaNumber = $pentaNumber;

        return $this;
    }

    public function getInternalUse(): ?bool
    {
        return $this->internalUse;
    }

    public function setInternalUse(bool $internalUse): self
    {
        $this->internalUse = $internalUse;

        return $this;
    }

    public function getVehicleVariant(): ?VehicleVariants
    {
        return $this->vehicleVariant;
    }

    public function setVehicleVariant(?VehicleVariants $vehicleVariant): self
    {
        $this->vehicleVariant = $vehicleVariant;

        return $this;
    }

    public function getExternal(): ?VehicleExternalNames
    {
        return $this->external;
    }

    public function setExternal(?VehicleExternalNames $external): self
    {
        $this->external = $external;

        return $this;
    }

    public function getColor(): ?Colors
    {
        return $this->color;
    }

    public function setColor(?Colors $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getPentaConfig(): ?self
    {
        return $this->pentaConfig;
    }

    public function setPentaConfig(?self $pentaConfig): self
    {
        $this->pentaConfig = $pentaConfig;

        return $this;
    }

    /**
     * @return Collection|Parts[]
     */
    public function getPart(): Collection
    {
        return $this->part;
    }

    public function addPart(Parts $part): self
    {
        if (!$this->part->contains($part)) {
            $this->part[] = $part;
        }

        return $this;
    }

    public function removePart(Parts $part): self
    {
        if ($this->part->contains($part)) {
            $this->part->removeElement($part);
        }

        return $this;
    }

}
