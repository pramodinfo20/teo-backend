<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuSwParameterValueSpecialPartPresenceMapping
 *
 * @ORM\Table(name="ecu_sw_parameter_value_special_part_presence_mapping", uniqueConstraints={@ORM\UniqueConstraint(name="ecu_sw_parameter_value_specia_special_part_id_ecu_sw_parame_key", columns={"special_part_id", "ecu_sw_parameter_id"})}, indexes={@ORM\Index(name="IDX_D83577B7E51010D7", columns={"ecu_sw_parameter_id"}), @ORM\Index(name="IDX_D83577B794EF1779", columns={"special_part_id"})})
 * @ORM\Entity
 */
class EcuSwParameterValueSpecialPartPresenceMapping
{
    /**
     * @var int
     *
     * @ORM\Column(name="espvsppm_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ecu_sw_parameter_value_special_part_presence_mapping_espvsppm_id_seq", allocationSize=1,
     *                                                                                                             initialValue=1)
     */
    private $espvsppmId;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="value_bool", type="boolean", nullable=true)
     */
    private $valueBool;

    /**
     * @var float|null
     *
     * @ORM\Column(name="value_double", type="float", precision=10, scale=0, nullable=true)
     */
    private $valueDouble;

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

    /**
     * @var SpecialParts
     *
     * @ORM\ManyToOne(targetEntity="SpecialParts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="special_part_id", referencedColumnName="special_part_id")
     * })
     */
    private $specialPart;

    public function getEspvsppmId(): ?int
    {
        return $this->espvsppmId;
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

    public function getValueInteger(): ?int
    {
        return $this->valueInteger;
    }

    public function setValueInteger(?int $valueInteger): self
    {
        $this->valueInteger = $valueInteger;

        return $this;
    }

    public function getValueUnsigned(): ?int
    {
        return $this->valueUnsigned;
    }

    public function setValueUnsigned(?int $valueUnsigned): self
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

    public function getSpecialPart(): ?SpecialParts
    {
        return $this->specialPart;
    }

    public function setSpecialPart(?SpecialParts $specialPart): self
    {
        $this->specialPart = $specialPart;

        return $this;
    }


}
