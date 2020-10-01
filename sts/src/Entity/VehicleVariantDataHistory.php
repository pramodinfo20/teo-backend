<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VehicleVariantDataHistory
 *
 * @ORM\Table(name="vehicle_variant_data_history")
 * @ORM\Entity
 */
class VehicleVariantDataHistory
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="backup_time", type="datetime", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $backupTime;

    /**
     * @var int
     *
     * @ORM\Column(name="vehicle_variant_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $vehicleVariantId;

    /**
     * @var int
     *
     * @ORM\Column(name="ecu_parameter_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $ecuParameterId;

    /**
     * @var int
     *
     * @ORM\Column(name="overlayed_penta_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $overlayedPentaId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="action", type="string", length=6, nullable=true)
     */
    private $action;

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
     * @var int|null
     *
     * @ORM\Column(name="changed_by", type="integer", nullable=true)
     */
    private $changedBy;

    /**
     * @var string|null
     *
     * @ORM\Column(name="sw_preset_type", type="string", length=1, nullable=true, options={"fixed"=true})
     */
    private $swPresetType;

    public function getBackupTime(): ?\DateTimeInterface
    {
        return $this->backupTime;
    }

    public function getVehicleVariantId(): ?int
    {
        return $this->vehicleVariantId;
    }

    public function getEcuParameterId(): ?int
    {
        return $this->ecuParameterId;
    }

    public function getOverlayedPentaId(): ?int
    {
        return $this->overlayedPentaId;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): self
    {
        $this->action = $action;

        return $this;
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

    public function getChangedBy(): ?int
    {
        return $this->changedBy;
    }

    public function setChangedBy(?int $changedBy): self
    {
        $this->changedBy = $changedBy;

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


}
