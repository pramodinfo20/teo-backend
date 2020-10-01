<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ParameterValueTypes
 *
 * @ORM\Table(name="parameter_value_types", uniqueConstraints={@ORM\UniqueConstraint(name="parameter_value_types_parameter_value_types_id_value_types_key", columns={"parameter_value_types_id", "value_types"})})
 * @ORM\Entity
 */
class ParameterValueTypes
{
    /**
     * @var int
     *
     * @ORM\Column(name="parameter_value_types_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="parameter_value_types_parameter_value_types_id_seq", allocationSize=1,
     *                                                                                           initialValue=1)
     */
    private $parameterValueTypesId;

    /**
     * @var string
     *
     * @ORM\Column(name="value_types", type="text", nullable=false)
     */
    private $valueTypes;

    public function getParameterValueTypesId(): ?int
    {
        return $this->parameterValueTypesId;
    }

    public function getValueTypes(): ?string
    {
        return $this->valueTypes;
    }

    public function setValueTypes(string $valueTypes): self
    {
        $this->valueTypes = $valueTypes;

        return $this;
    }


}
