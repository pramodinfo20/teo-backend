<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuParametersOverwrite
 *
 * @ORM\Table(name="ecu_parameters_overwrite", indexes={@ORM\Index(name="IDX_7105E43FC54C8C93", columns={"type_id"}),
 *                                             @ORM\Index(name="IDX_7105E43FBB55168B", columns={"ecu_parameter_id"}),
 *                                                                                     @ORM\Index(name="IDX_7105E43FF2887E5B", columns={"ecu_id"})})
 * @ORM\Entity
 */
class EcuParametersOverwrite
{
    /**
     * @var int
     *
     * @ORM\Column(name="parameter_overwrite_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $parameterOverwriteId;

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
    private $tagDisabled;

    /**
     * @var string|null
     *
     * @ORM\Column(name="value", type="text", nullable=true)
     */
    private $value;

    /**
     * @var ParameterValueTypes
     *
     * @ORM\ManyToOne(targetEntity="ParameterValueTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_id", referencedColumnName="parameter_value_types_id")
     * })
     */
    private $type;

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

    /**
     * @var Ecus
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Ecus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_id", referencedColumnName="ecu_id")
     * })
     */
    private $ecu;

    public function getParameterOverwriteId(): ?int
    {
        return $this->parameterOverwriteId;
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

    public function getType(): ?ParameterValueTypes
    {
        return $this->type;
    }

    public function setType(?ParameterValueTypes $type): self
    {
        $this->type = $type;

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

    public function getEcu(): ?Ecus
    {
        return $this->ecu;
    }

    public function setEcu(?Ecus $ecu): self
    {
        $this->ecu = $ecu;

        return $this;
    }


}
