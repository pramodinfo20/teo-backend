<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EbomVariantComponents
 *
 * @ORM\Table(name="ebom_variant_components", indexes={@ORM\Index(name="IDX_524F9451BEFB2632", columns={"vehicle_variant_id"}), @ORM\Index(name="IDX_524F9451AF00DFC1", columns={"part_number"}), @ORM\Index(name="IDX_524F9451727ACA70", columns={"parent_id"})})
 * @ORM\Entity
 */
class EbomVariantComponents
{
    /**
     * @var int
     *
     * @ORM\Column(name="ebom_variant_component_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ebom_variant_components_ebom_variant_component_id_seq", allocationSize=1,
     *                                                                                              initialValue=1)
     */
    private $ebomVariantComponentId;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="verbaut_von", type="datetimetz", nullable=true)
     */
    private $verbautVon;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="verbaut_bis", type="datetimetz", nullable=true)
     */
    private $verbautBis;

    /**
     * @var float|null
     *
     * @ORM\Column(name="amount", type="float", precision=10, scale=0, nullable=true)
     */
    private $amount;

    /**
     * @var int|null
     *
     * @ORM\Column(name="structure_level", type="integer", nullable=true)
     */
    private $structureLevel;

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
     * @var Components
     *
     * @ORM\ManyToOne(targetEntity="Components")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="part_number", referencedColumnName="part_number")
     * })
     */
    private $partNumber;

    /**
     * @var Components
     *
     * @ORM\ManyToOne(targetEntity="Components")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="part_number")
     * })
     */
    private $parent;

    public function getEbomVariantComponentId(): ?int
    {
        return $this->ebomVariantComponentId;
    }

    public function getVerbautVon(): ?\DateTimeInterface
    {
        return $this->verbautVon;
    }

    public function setVerbautVon(?\DateTimeInterface $verbautVon): self
    {
        $this->verbautVon = $verbautVon;

        return $this;
    }

    public function getVerbautBis(): ?\DateTimeInterface
    {
        return $this->verbautBis;
    }

    public function setVerbautBis(?\DateTimeInterface $verbautBis): self
    {
        $this->verbautBis = $verbautBis;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getStructureLevel(): ?int
    {
        return $this->structureLevel;
    }

    public function setStructureLevel(?int $structureLevel): self
    {
        $this->structureLevel = $structureLevel;

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

    public function getPartNumber(): ?Components
    {
        return $this->partNumber;
    }

    public function setPartNumber(?Components $partNumber): self
    {
        $this->partNumber = $partNumber;

        return $this;
    }

    public function getParent(): ?Components
    {
        return $this->parent;
    }

    public function setParent(?Components $parent): self
    {
        $this->parent = $parent;

        return $this;
    }


}
