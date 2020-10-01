<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuSwParameterEcuSwVersionMappingOverwrite
 *
 * @ORM\Table(name="ecu_sw_parameter_ecu_sw_version_mapping_overwrite", uniqueConstraints={@ORM\UniqueConstraint(name="ecu_sw_parameter_ecu_sw_versi_ecu_sw_version_id_ecu_sw_par_key1", columns={"sub_vehicle_configuration_id", "ecu_sw_version_id", "ecu_sw_parameter_value_set_id"})}, indexes={@ORM\Index(name="IDX_17AF5B88602D1907", columns={"sub_vehicle_configuration_id"}), @ORM\Index(name="IDX_17AF5B88EF576A6", columns={"ecu_sw_version_id"}), @ORM\Index(name="IDX_17AF5B884D3F213D", columns={"ecu_sw_parameter_value_set_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\EcuSwParameterEcuSwVersionMappingOverwriteRepository")
 */
class EcuSwParameterEcuSwVersionMappingOverwrite
{
    /**
     * @var int
     *
     * @ORM\Column(name="mo_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ecu_sw_parameter_ecu_sw_version_mapping_overwrite_mo_id_seq", allocationSize=1,
     *                                                                                                    initialValue=1)
     */
    private $moId;

    /**
     * @var SubVehicleConfigurations
     *
     * @ORM\ManyToOne(targetEntity="SubVehicleConfigurations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sub_vehicle_configuration_id", referencedColumnName="sub_vehicle_configuration_id")
     * })
     */
    private $subVehicleConfiguration;

    /**
     * @var EcuSwVersions
     *
     * @ORM\ManyToOne(targetEntity="EcuSwVersions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_sw_version_id", referencedColumnName="ecu_sw_version_id")
     * })
     */
    private $ecuSwVersion;

    /**
     * @var EcuSwParameterValuesSets
     *
     * @ORM\ManyToOne(targetEntity="EcuSwParameterValuesSets")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_sw_parameter_value_set_id", referencedColumnName="ecu_sw_parameter_value_set_id")
     * })
     */
    private $ecuSwParameterValueSet;

    public function getMoId(): ?int
    {
        return $this->moId;
    }

    public function getSubVehicleConfiguration(): ?SubVehicleConfigurations
    {
        return $this->subVehicleConfiguration;
    }

    public function setSubVehicleConfiguration(?SubVehicleConfigurations $subVehicleConfiguration): self
    {
        $this->subVehicleConfiguration = $subVehicleConfiguration;

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

    public function getEcuSwParameterValueSet(): ?EcuSwParameterValuesSets
    {
        return $this->ecuSwParameterValueSet;
    }

    public function setEcuSwParameterValueSet(?EcuSwParameterValuesSets $ecuSwParameterValueSet): self
    {
        $this->ecuSwParameterValueSet = $ecuSwParameterValueSet;

        return $this;
    }


}
