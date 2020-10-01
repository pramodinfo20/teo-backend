<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuSwParameterValuesSetsMapping
 *
 * @ORM\Table(name="ecu_sw_parameter_values_sets_mapping", uniqueConstraints={@ORM\UniqueConstraint(name="ecu_sw_parameter_values_sets__sub_vehicle_configuration_id__key", columns={"sub_vehicle_configuration_id", "ecu_sw_parameter_value_set_id"})}, indexes={@ORM\Index(name="IDX_1251A6334D3F213D", columns={"ecu_sw_parameter_value_set_id"}), @ORM\Index(name="IDX_1251A633602D1907", columns={"sub_vehicle_configuration_id"})})
 * @ORM\Entity
 */
class EcuSwParameterValuesSetsMapping
{
    /**
     * @var int
     *
     * @ORM\Column(name="espvsm_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ecu_sw_parameter_values_sets_mapping_espvsm_id_seq", allocationSize=1, initialValue=1)
     */
    private $espvsmId;

    /**
     * @var EcuSwParameterValuesSets
     *
     * @ORM\ManyToOne(targetEntity="EcuSwParameterValuesSets")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_sw_parameter_value_set_id", referencedColumnName="ecu_sw_parameter_value_set_id")
     * })
     */
    private $ecuSwParameterValueSet;

    /**
     * @var SubVehicleConfigurations
     *
     * @ORM\ManyToOne(targetEntity="SubVehicleConfigurations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sub_vehicle_configuration_id", referencedColumnName="sub_vehicle_configuration_id")
     * })
     */
    private $subVehicleConfiguration;

    public function getEspvsmId(): ?int
    {
        return $this->espvsmId;
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

    public function getSubVehicleConfiguration(): ?SubVehicleConfigurations
    {
        return $this->subVehicleConfiguration;
    }

    public function setSubVehicleConfiguration(?SubVehicleConfigurations $subVehicleConfiguration): self
    {
        $this->subVehicleConfiguration = $subVehicleConfiguration;

        return $this;
    }


}
