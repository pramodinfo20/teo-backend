<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SpecialVehiclePropertyValues
 *
 * @ORM\Table(name="special_vehicle_property_values")
 * @ORM\Entity
 */
class SpecialVehiclePropertyValues
{
    /**
     * @var int
     *
     * @ORM\Column(name="svpv_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="special_vehicle_property_values_svpv_id_seq", allocationSize=1,
     *                                                                                    initialValue=1)
     */
    private $svpvId;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="value_bool", type="boolean", nullable=true)
     */
    private $valueBool;

    /**
     * @var string|null
     *
     * @ORM\Column(name="value_string", type="text", nullable=true)
     */
    private $valueString;

    /**
     * @var int|null
     *
     * @ORM\Column(name="value_integer", type="integer", nullable=true)
     */
    private $valueInteger;

    public function getSvpvId(): ?int
    {
        return $this->svpvId;
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

    public function getValueString(): ?string
    {
        return $this->valueString;
    }

    public function setValueString(?string $valueString): self
    {
        $this->valueString = $valueString;

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


}
