<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CocDescription
 *
 * @ORM\Table(name="coc_description", indexes={@ORM\Index(name="IDX_A56C9585F8BD700D", columns={"unit_id"})})
 * @ORM\Entity
 */
class CocDescription
{
    /**
     * @var string
     *
     * @ORM\Column(name="coc_column", type="string", length=50, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="coc_description_coc_column_seq", allocationSize=1, initialValue=1)
     */
    private $cocColumn;

    /**
     * @var string|null
     *
     * @ORM\Column(name="field_set", type="string", length=12, nullable=true)
     */
    private $fieldSet;

    /**
     * @var string|null
     *
     * @ORM\Column(name="field_prefix", type="string", length=2, nullable=true)
     */
    private $fieldPrefix;

    /**
     * @var int|null
     *
     * @ORM\Column(name="field_ident1", type="smallint", nullable=true)
     */
    private $fieldIdent1;

    /**
     * @var int|null
     *
     * @ORM\Column(name="field_ident2", type="smallint", nullable=true)
     */
    private $fieldIdent2;

    /**
     * @var int|null
     *
     * @ORM\Column(name="field_ident3", type="smallint", nullable=true)
     */
    private $fieldIdent3;

    /**
     * @var int|null
     *
     * @ORM\Column(name="field_ident4", type="smallint", nullable=true)
     */
    private $fieldIdent4;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var float|null
     *
     * @ORM\Column(name="min_value", type="float", precision=10, scale=0, nullable=true)
     */
    private $minValue;

    /**
     * @var float|null
     *
     * @ORM\Column(name="max_value", type="float", precision=10, scale=0, nullable=true)
     */
    private $maxValue;

    /**
     * @var string|null
     *
     * @ORM\Column(name="value_select", type="text", nullable=true)
     */
    private $valueSelect;

    /**
     * @var string|null
     *
     * @ORM\Column(name="control_type", type="text", nullable=true)
     */
    private $controlType;

    /**
     * @var Units
     *
     * @ORM\ManyToOne(targetEntity="Units")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="unit_id", referencedColumnName="unit_id")
     * })
     */
    private $unit;

    public function getCocColumn(): ?string
    {
        return $this->cocColumn;
    }

    public function getFieldSet(): ?string
    {
        return $this->fieldSet;
    }

    public function setFieldSet(?string $fieldSet): self
    {
        $this->fieldSet = $fieldSet;

        return $this;
    }

    public function getFieldPrefix(): ?string
    {
        return $this->fieldPrefix;
    }

    public function setFieldPrefix(?string $fieldPrefix): self
    {
        $this->fieldPrefix = $fieldPrefix;

        return $this;
    }

    public function getFieldIdent1(): ?int
    {
        return $this->fieldIdent1;
    }

    public function setFieldIdent1(?int $fieldIdent1): self
    {
        $this->fieldIdent1 = $fieldIdent1;

        return $this;
    }

    public function getFieldIdent2(): ?int
    {
        return $this->fieldIdent2;
    }

    public function setFieldIdent2(?int $fieldIdent2): self
    {
        $this->fieldIdent2 = $fieldIdent2;

        return $this;
    }

    public function getFieldIdent3(): ?int
    {
        return $this->fieldIdent3;
    }

    public function setFieldIdent3(?int $fieldIdent3): self
    {
        $this->fieldIdent3 = $fieldIdent3;

        return $this;
    }

    public function getFieldIdent4(): ?int
    {
        return $this->fieldIdent4;
    }

    public function setFieldIdent4(?int $fieldIdent4): self
    {
        $this->fieldIdent4 = $fieldIdent4;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getMinValue(): ?float
    {
        return $this->minValue;
    }

    public function setMinValue(?float $minValue): self
    {
        $this->minValue = $minValue;

        return $this;
    }

    public function getMaxValue(): ?float
    {
        return $this->maxValue;
    }

    public function setMaxValue(?float $maxValue): self
    {
        $this->maxValue = $maxValue;

        return $this;
    }

    public function getValueSelect(): ?string
    {
        return $this->valueSelect;
    }

    public function setValueSelect(?string $valueSelect): self
    {
        $this->valueSelect = $valueSelect;

        return $this;
    }

    public function getControlType(): ?string
    {
        return $this->controlType;
    }

    public function setControlType(?string $controlType): self
    {
        $this->controlType = $controlType;

        return $this;
    }

    public function getUnit(): ?Units
    {
        return $this->unit;
    }

    public function setUnit(?Units $unit): self
    {
        $this->unit = $unit;

        return $this;
    }


}
