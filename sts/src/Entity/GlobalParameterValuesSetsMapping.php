<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GlobalParameterValuesSetsMapping
 *
 * @ORM\Table(name="global_parameter_values_sets_mapping", uniqueConstraints={@ORM\UniqueConstraint(name="global_parameter_values_sets__global_parameter_values_set_i_key", columns={"global_parameter_values_set_id", "sub_vehicle_configuration_id"})}, indexes={@ORM\Index(name="IDX_261830BE602D1907", columns={"sub_vehicle_configuration_id"}), @ORM\Index(name="IDX_261830BEEC930D08", columns={"global_parameter_values_set_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\GlobalParameterValuesSetsMappingRepository")
 */
class GlobalParameterValuesSetsMapping
{
    /**
     * @var int
     *
     * @ORM\Column(name="gpvsm_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="global_parameter_values_sets_mapping_gpvsm_id_seq", allocationSize=1, initialValue=1)
     */
    private $gpvsmId;

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
     * @var GlobalParameterValuesSets
     *
     * @ORM\ManyToOne(targetEntity="GlobalParameterValuesSets")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="global_parameter_values_set_id", referencedColumnName="global_parameter_values_set_id")
     * })
     */
    private $globalParameterValuesSet;

    public function getGpvsmId(): ?int
    {
        return $this->gpvsmId;
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

    public function getGlobalParameterValuesSet(): ?GlobalParameterValuesSets
    {
        return $this->globalParameterValuesSet;
    }

    public function setGlobalParameterValuesSet(?GlobalParameterValuesSets $globalParameterValuesSet): self
    {
        $this->globalParameterValuesSet = $globalParameterValuesSet;

        return $this;
    }


}
