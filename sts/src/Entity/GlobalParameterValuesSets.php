<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GlobalParameterValuesSets
 *
 * @ORM\Table(name="global_parameter_values_sets", indexes={@ORM\Index(name="IDX_A786EF757F9ADA8F", columns={"global_parameter_id"})})
 * @ORM\Entity
 */
class GlobalParameterValuesSets
{
    /**
     * @var int
     *
     * @ORM\Column(name="global_parameter_values_set_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="global_parameter_values_sets_global_parameter_values_set_id_seq", allocationSize=1, initialValue=1)
     */
    private $globalParameterValuesSetId;

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
     * @var int|null
     *
     * @ORM\Column(name="value_integer", type="integer", nullable=true)
     */
    private $valueInteger;

    /**
     * @var float|null
     *
     * @ORM\Column(name="value_double", type="float", precision=17, scale=0, nullable=true)
     */
    private $valueDouble;

    /**
     * @var int|null
     *
     * @ORM\Column(name="value_unsigned", type="bigint", nullable=true)
     */
    private $valueUnsigned;

    /**
     * @var string|null
     *
     * @ORM\Column(name="value_hex", type="text", nullable=true)
     */
    private $valueHex;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="value_date", type="date", nullable=true)
     */
    private $valueDate;

    /**
     * @var GlobalParameters
     *
     * @ORM\ManyToOne(targetEntity="GlobalParameters")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="global_parameter_id", referencedColumnName="global_parameter_id")
     * })
     */
    private $globalParameter;

    public function getGlobalParameterValuesSetId(): ?int
    {
        return $this->globalParameterValuesSetId;
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

    public function getValueInteger(): ?int
    {
        return $this->valueInteger;
    }

    public function setValueInteger(?int $valueInteger): self
    {
        $this->valueInteger = $valueInteger;

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

    public function getValueUnsigned(): ?string
    {
        return $this->valueUnsigned;
    }

    public function setValueUnsigned(?string $valueUnsigned): self
    {
        $this->valueUnsigned = $valueUnsigned;

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

    public function getValueDate(): ?\DateTimeInterface
    {
        return $this->valueDate;
    }

    public function setValueDate(?\DateTimeInterface $valueDate): self
    {
        $this->valueDate = $valueDate;

        return $this;
    }

    public function getGlobalParameter(): ?GlobalParameters
    {
        return $this->globalParameter;
    }

    public function setGlobalParameter(?GlobalParameters $globalParameter): self
    {
        $this->globalParameter = $globalParameter;

        return $this;
    }


}
