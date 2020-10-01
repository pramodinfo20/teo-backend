<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuSwParameterTypes
 *
 * @ORM\Table(name="ecu_sw_parameter_types", uniqueConstraints={@ORM\UniqueConstraint(name="ecu_sw_parameter_types_parameter_type_key", columns={"parameter_type"})})
 * @ORM\Entity
 */
class EcuSwParameterTypes
{
    /**
     * @var int
     *
     * @ORM\Column(name="ecu_sw_parameter_type_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ecu_sw_parameter_types_ecu_sw_parameter_type_id_seq", allocationSize=1,
     *                                                                                            initialValue=1)
     */
    private $ecuSwParameterTypeId;

    /**
     * @var string
     *
     * @ORM\Column(name="parameter_type", type="text", nullable=false)
     */
    private $parameterType;

    public function getEcuSwParameterTypeId(): ?int
    {
        return $this->ecuSwParameterTypeId;
    }

    public function getParameterType(): ?string
    {
        return $this->parameterType;
    }

    public function setParameterType(string $parameterType): self
    {
        $this->parameterType = $parameterType;

        return $this;
    }

    public function __toString()
    {
        return $this->parameterType;
    }
}
