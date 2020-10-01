<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * VehicleExternalNames
 *
 * @ORM\Table(name="vehicle_external_names", indexes={@ORM\Index(name="IDX_BB98C0304CE34BEC", columns={"part_id"})})
 * @ORM\Entity
 */
class VehicleExternalNames
{
    /**
     * @var int
     *
     * @ORM\Column(name="external_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="vehicle_external_names_external_id_seq", allocationSize=1, initialValue=1)
     */
    private $externalId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="external_name", type="text", nullable=true)
     */
    private $externalName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="variant_type", type="string", length=3, nullable=true, options={"fixed"=true})
     */
    private $variantType;

    /**
     * @var int|null
     *
     * @ORM\Column(name="num_seats", type="integer", nullable=true)
     */
    private $numSeats;

    /**
     * @var Parts
     *
     * @ORM\ManyToOne(targetEntity="Parts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="part_id", referencedColumnName="part_id")
     * })
     */
    private $part;

    /**
     * @var Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="VehicleVariants", mappedBy="external")
     */
    private $variant;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->variant = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getExternalId(): ?int
    {
        return $this->externalId;
    }

    public function getExternalName(): ?string
    {
        return $this->externalName;
    }

    public function setExternalName(?string $externalName): self
    {
        $this->externalName = $externalName;

        return $this;
    }

    public function getVariantType(): ?string
    {
        return $this->variantType;
    }

    public function setVariantType(?string $variantType): self
    {
        $this->variantType = $variantType;

        return $this;
    }

    public function getNumSeats(): ?int
    {
        return $this->numSeats;
    }

    public function setNumSeats(?int $numSeats): self
    {
        $this->numSeats = $numSeats;

        return $this;
    }

    public function getPart(): ?Parts
    {
        return $this->part;
    }

    public function setPart(?Parts $part): self
    {
        $this->part = $part;

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
            $variant->addExternal($this);
        }

        return $this;
    }

    public function removeVariant(VehicleVariants $variant): self
    {
        if ($this->variant->contains($variant)) {
            $this->variant->removeElement($variant);
            $variant->removeExternal($this);
        }

        return $this;
    }

}
