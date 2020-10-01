<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CocParameterValuesSetsMapping
 *
 * @ORM\Table(name="coc_parameter_values_sets_mapping", uniqueConstraints={@ORM\UniqueConstraint(name="coc_parameter_values_sets_map_coc_parameter_value_set_id_su_key", columns={"coc_parameter_value_set_id", "sub_vehicle_configuration_id"})}, indexes={@ORM\Index(name="IDX_67BCB346602D1907", columns={"sub_vehicle_configuration_id"}), @ORM\Index(name="IDX_67BCB346A8A44982", columns={"coc_parameter_value_set_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\CocParameterValuesSetsMappingRepository")
 */
class CocParameterValuesSetsMapping
{
    /**
     * @var int
     *
     * @ORM\Column(name="cpvsm_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="coc_parameter_values_sets_mapping_cpvsm_id_seq", allocationSize=1,
     *                                                                                       initialValue=1)
     */
    private $cpvsmId;

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
     * @var CocParameterValuesSets
     *
     * @ORM\ManyToOne(targetEntity="CocParameterValuesSets")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="coc_parameter_value_set_id", referencedColumnName="coc_parameter_values_set_id")
     * })
     */
    private $cocParameterValueSet;

    public function getCpvsmId(): ?int
    {
        return $this->cpvsmId;
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

    public function getCocParameterValueSet(): ?CocParameterValuesSets
    {
        return $this->cocParameterValueSet;
    }

    public function setCocParameterValueSet(?CocParameterValuesSets $cocParameterValueSet): self
    {
        $this->cocParameterValueSet = $cocParameterValueSet;

        return $this;
    }


}
