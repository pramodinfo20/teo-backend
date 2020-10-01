<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuSoftwareParameterNames
 *
 * @ORM\Table(name="ecu_software_parameter_names")
 * @ORM\Entity
 */
class EcuSoftwareParameterNames
{
    /**
     * @var int
     *
     * @ORM\Column(name="ecu_software_parameter_name_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ecu_software_parameter_names_ecu_software_parameter_name_id_seq", allocationSize=1,
     *                                                                                                        initialValue=1)
     */
    private $ecuSoftwareParameterNameId;

    /**
     * @var string
     *
     * @ORM\Column(name="ecu_software_parameter_name", type="text", nullable=false)
     */
    private $ecuSoftwareParameterName;

    public function getEcuSoftwareParameterNameId(): ?int
    {
        return $this->ecuSoftwareParameterNameId;
    }

    public function getEcuSoftwareParameterName(): ?string
    {
        return $this->ecuSoftwareParameterName;
    }

    public function setEcuSoftwareParameterName(string $ecuSoftwareParameterName): self
    {
        $this->ecuSoftwareParameterName = $ecuSoftwareParameterName;

        return $this;
    }

    public function __toString()
    {
        return $this->ecuSoftwareParameterName;
    }
}
