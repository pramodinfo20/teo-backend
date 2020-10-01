<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuSwParameterEcuSwVersionMapping
 *
 * @ORM\Table(name="ecu_sw_parameter_ecu_sw_version_mapping", uniqueConstraints={@ORM\UniqueConstraint(name="ecu_sw_parameter_ecu_sw_versi_ecu_sw_version_id_ecu_sw_para_key", columns={"ecu_sw_version_id", "ecu_sw_parameter_id"})}, indexes={@ORM\Index(name="IDX_6667069FE51010D7", columns={"ecu_sw_parameter_id"}), @ORM\Index(name="IDX_6667069FEF576A6", columns={"ecu_sw_version_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\EcuSwParameterEcuSwVersionMappingRepository")
 */
class EcuSwParameterEcuSwVersionMapping
{
    /**
     * @var int
     *
     * @ORM\Column(name="espesvm_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ecu_sw_parameter_ecu_sw_version_mapping_espesvm_id_seq", allocationSize=1,
     *                                                                                               initialValue=1)
     */
    private $espesvmId;

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
     * @var EcuSwVersions
     *
     * @ORM\ManyToOne(targetEntity="EcuSwVersions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_sw_version_id", referencedColumnName="ecu_sw_version_id")
     * })
     */
    private $ecuSwVersion;

    public function getEspesvmId(): ?int
    {
        return $this->espesvmId;
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

    public function getEcuSwVersion(): ?EcuSwVersions
    {
        return $this->ecuSwVersion;
    }

    public function setEcuSwVersion(?EcuSwVersions $ecuSwVersion): self
    {
        $this->ecuSwVersion = $ecuSwVersion;

        return $this;
    }


}
