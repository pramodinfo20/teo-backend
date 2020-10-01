<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DynamicParameterValuesByDiagnosticSoftware
 *
 * @ORM\Table(name="dynamic_parameter_values_by_diagnostic_software", uniqueConstraints={@ORM\UniqueConstraint(name="dynamic_parameter_values_by_d_dynamic_parameter_values_by_d_key", columns={"dynamic_parameter_values_by_diagnostic_software_name"})})
 * @ORM\Entity
 */
class DynamicParameterValuesByDiagnosticSoftware
{
    /**
     * @var int
     *
     * @ORM\Column(name="dpvbds_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="dynamic_parameter_values_by_diagnostic_software_dpvbds_id_seq", allocationSize=1,
     *                                                                                                      initialValue=1)
     */
    private $dpvbdsId;

    /**
     * @var string
     *
     * @ORM\Column(name="dynamic_parameter_values_by_diagnostic_software_name", type="text", nullable=false)
     */
    private $dynamicParameterValuesByDiagnosticSoftwareName;

    public function getDpvbdsId(): ?int
    {
        return $this->dpvbdsId;
    }

    public function getDynamicParameterValuesByDiagnosticSoftwareName(): ?string
    {
        return $this->dynamicParameterValuesByDiagnosticSoftwareName;
    }

    public function setDynamicParameterValuesByDiagnosticSoftwareName(string $dynamicParameterValuesByDiagnosticSoftwareName): self
    {
        $this->dynamicParameterValuesByDiagnosticSoftwareName = $dynamicParameterValuesByDiagnosticSoftwareName;

        return $this;
    }

    public function __toString()
    {
        return $this->dynamicParameterValuesByDiagnosticSoftwareName;
    }
}
