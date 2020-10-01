<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuSwParameterValuesSets
 *
 * @ORM\Table(name="ecu_sw_parameter_values_sets", indexes={@ORM\Index(name="IDX_15F1D77DE51010D7", columns={"ecu_sw_parameter_id"})})
 * @ORM\Entity
 */
class EcuSwParameterValuesSets
{
    /**
     * @var int
     *
     * @ORM\Column(name="ecu_sw_parameter_value_set_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ecu_sw_parameter_values_sets_ecu_sw_parameter_value_set_id_seq", allocationSize=1, initialValue=1)
     */
    private $ecuSwParameterValueSetId;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_dummy", type="boolean", nullable=false)
     */
    private $isDummy = false;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="value_bool", type="boolean", nullable=true)
     */
    private $valueBool;

    /**
     * @var int|null
     *
     * @ORM\Column(name="value_integer", type="integer", nullable=true)
     */
    private $valueInteger;

    /**
     * @var int|null
     *
     * @ORM\Column(name="value_unsigned", type="bigint", nullable=true)
     */
    private $valueUnsigned;

    /**
     * @var string|null
     *
     * @ORM\Column(name="value_string", type="text", nullable=true)
     */
    private $valueString;

    /**
     * @var string|null
     *
     * @ORM\Column(name="value_hex", type="text", nullable=true)
     */
    private $valueHex;

    /**
     * @var EcuSwParameters
     *
     * @ORM\ManyToOne(targetEntity="EcuSwParameters")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_sw_parameter_id", referencedColumnName="ecu_sw_parameter_id")
     * })
     */
    private $ecuSwParameter;

    public function getEcuSwParameterValueSetId(): ?int
    {
        return $this->ecuSwParameterValueSetId;
    }

    public function getIsDummy(): ?bool
    {
        return $this->isDummy;
    }

    public function setIsDummy(bool $isDummy): self
    {
        $this->isDummy = $isDummy;

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

    public function getValueInteger(): ?int
    {
        return $this->valueInteger;
    }

    public function setValueInteger(?int $valueInteger): self
    {
        $this->valueInteger = $valueInteger;

        return $this;
    }

    public function getValueUnsigned(): ?string
    {
        return $this->valueUnsigned;
    }

    public function setValueUnsigned(?string $valueUnsigned): self
    {
        $this->valueUnsigned = $valueUnsigned;

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

    public function getValueHex(): ?string
    {
        return $this->valueHex;
    }

    public function setValueHex(?string $valueHex): self
    {
        $this->valueHex = $valueHex;

        return $this;
    }

    public function getEcuSwParameter(): ?EcuSwParameters
    {
        return $this->ecuSwParameter;
    }

    public function setEcuSwParameter(?EcuSwParameters $ecuSwParameter): self
    {
        $this->ecuSwParameter = $ecuSwParameter;

        return $this;
    }


}
