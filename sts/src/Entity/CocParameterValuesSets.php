<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CocParameterValuesSets
 *
 * @ORM\Table(name="coc_parameter_values_sets", indexes={@ORM\Index(name="IDX_5B2765B03341A151", columns={"coc_parameter_id"})})
 * @ORM\Entity
 */
class CocParameterValuesSets
{
    /**
     * @var int
     *
     * @ORM\Column(name="coc_parameter_values_set_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="coc_parameter_values_sets_coc_parameter_values_set_id_seq", allocationSize=1,
     *                                                                                                  initialValue=1)
     */
    private $cocParameterValuesSetId;

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
     * @var float|null
     *
     * @ORM\Column(name="value_double", type="float", precision=17, scale=0, nullable=true)
     */
    private $valueDouble;

    /**
     * @var string|null
     *
     * @ORM\Column(name="value_hex", type="text", nullable=true)
     */
    private $valueHex;

    /**
     * @var int|null
     *
     * @ORM\Column(name="value_integer", type="integer", nullable=true)
     */
    private $valueInteger;

    /**
     * @var int|null
     *
     * @ORM\Column(name="value_biginteger", type="bigint", nullable=true)
     */
    private $valueBiginteger;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="value_date", type="date", nullable=true)
     */
    private $valueDate;

    /**
     * @var CocParameters
     *
     * @ORM\ManyToOne(targetEntity="CocParameters")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="coc_parameter_id", referencedColumnName="coc_parameter_id")
     * })
     */
    private $cocParameter;

    public function getCocParameterValuesSetId(): ?int
    {
        return $this->cocParameterValuesSetId;
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

    public function getValueDouble(): ?float
    {
        return $this->valueDouble;
    }

    public function setValueDouble(?float $valueDouble): self
    {
        $this->valueDouble = $valueDouble;

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

    public function getValueInteger(): ?int
    {
        return $this->valueInteger;
    }

    public function setValueInteger(?int $valueInteger): self
    {
        $this->valueInteger = $valueInteger;

        return $this;
    }

    public function getValueBiginteger(): ?int
    {
        return $this->valueBiginteger;
    }

    public function setValueBiginteger(?int $valueBiginteger): self
    {
        $this->valueBiginteger = $valueBiginteger;

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

    public function getCocParameter(): ?CocParameters
    {
        return $this->cocParameter;
    }

    public function setCocParameter(?CocParameters $cocParameter): self
    {
        $this->cocParameter = $cocParameter;

        return $this;
    }


}
