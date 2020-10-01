<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VehicleVariantData
 *
 * @ORM\Table(name="vehicle_variant_data", indexes={@ORM\Index(name="IDX_FBE38A82BEFB2632", columns={"vehicle_variant_id"}), @ORM\Index(name="IDX_FBE38A82BB55168B", columns={"ecu_parameter_id"})})
 * @ORM\Entity
 */
class VehicleVariantData
{
    /**
     * @var int
     *
     * @ORM\Column(name="overlayed_penta_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $overlayedPentaId = '0';

    /**
     * @var int|null
     *
     * @ORM\Column(name="value_int", type="integer", nullable=true)
     */
    private $valueInt;

    /**
     * @var float|null
     *
     * @ORM\Column(name="value_double", type="float", precision=10, scale=0, nullable=true)
     */
    private $valueDouble;

    /**
     * @var string|null
     *
     * @ORM\Column(name="value_string", type="text", nullable=true)
     */
    private $valueString;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="value_bool", type="boolean", nullable=true)
     */
    private $valueBool;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="tag_disabled", type="boolean", nullable=true)
     */
    private $tagDisabled = false;

    /**
     * @var string|null
     *
     * @ORM\Column(name="value", type="text", nullable=true)
     */
    private $value;

    /**
     * @var int|null
     *
     * @ORM\Column(name="set_by", type="integer", nullable=true)
     */
    private $setBy;

    /**
     * @var string|null
     *
     * @ORM\Column(name="sw_preset_type", type="string", length=1, nullable=true, options={"fixed"=true})
     */
    private $swPresetType;

    /**
     * @var VehicleVariants
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="VehicleVariants")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_variant_id", referencedColumnName="vehicle_variant_id")
     * })
     */
    private $vehicleVariant;

    /**
     * @var EcuParameters
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="EcuParameters")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_parameter_id", referencedColumnName="ecu_parameter_id")
     * })
     */
    private $ecuParameter;

    public function getOverlayedPentaId(): ?int
    {
        return $this->overlayedPentaId;
    }

    public function getValueInt(): ?int
    {
        return $this->valueInt;
    }

    public function setValueInt(?int $valueInt): self
    {
        $this->valueInt = $valueInt;

        return $this;
    }

    public function getValueDouble(): ?float
    {
        return $this->valueDouble;
    }

    public function setValueDouble(?float $valueDouble): self
    {
        $this->valueDouble = $valueDouble;

        return $this;
    }

    public function getValueString(): ?string
    {
        return $this->valueString;
    }

    public function setValueString(?string $valueString): self
    {
        $this->valueString = $valueString;

        return $this;
    }

    public function getValueBool(): ?bool
    {
        return $this->valueBool;
    }

    public function setValueBool(?bool $valueBool): self
    {
        $this->valueBool = $valueBool;

        return $this;
    }

    public function getTagDisabled(): ?bool
    {
        return $this->tagDisabled;
    }

    public function setTagDisabled(?bool $tagDisabled): self
    {
        $this->tagDisabled = $tagDisabled;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getSetBy(): ?int
    {
        return $this->setBy;
    }

    public function setSetBy(?int $setBy): self
    {
        $this->setBy = $setBy;

        return $this;
    }

    public function getSwPresetType(): ?string
    {
        return $this->swPresetType;
    }

    public function setSwPresetType(?string $swPresetType): self
    {
        $this->swPresetType = $swPresetType;

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

    public function getEcuParameter(): ?EcuParameters
    {
        return $this->ecuParameter;
    }

    public function setEcuParameter(?EcuParameters $ecuParameter): self
    {
        $this->ecuParameter = $ecuParameter;

        return $this;
    }


}
