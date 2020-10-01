<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VariableTypes
 *
 * @ORM\Table(name="variable_types", uniqueConstraints={@ORM\UniqueConstraint(name="variable_types_variable_type_name_key", columns={"variable_type_name"})})
 * @ORM\Entity
 */
class VariableTypes
{
    /**
     * @var int
     *
     * @ORM\Column(name="variable_type_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="variable_types_variable_type_id_seq", allocationSize=1, initialValue=1)
     */
    private $variableTypeId;

    /**
     * @var string
     *
     * @ORM\Column(name="variable_type_name", type="text", nullable=false)
     */
    private $variableTypeName;

    public function getVariableTypeId(): ?int
    {
        return $this->variableTypeId;
    }

    public function getVariableTypeName(): ?string
    {
        return $this->variableTypeName;
    }

    public function setVariableTypeName(string $variableTypeName): self
    {
        $this->variableTypeName = $variableTypeName;

        return $this;
    }

    public function __toString()
    {
        return $this->variableTypeName;
    }
}
