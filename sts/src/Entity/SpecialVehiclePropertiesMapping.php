<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SpecialVehiclePropertiesMapping
 *
 * @ORM\Table(name="special_vehicle_properties_mapping", uniqueConstraints={@ORM\UniqueConstraint(name="special_vehicle_properties_ma_vehicle_configuration_id_sub_key1", columns={"vehicle_configuration_id", "sub_vehicle_configuration_id", "special_vehicle_property_id", "special_vehicle_property_value_id"}), @ORM\UniqueConstraint(name="special_vehicle_properties_ma_vehicle_configuration_id_sub__key", columns={"vehicle_configuration_id", "sub_vehicle_configuration_id", "special_vehicle_property_id"})}, indexes={@ORM\Index(name="IDX_79B1772AA497480E", columns={"special_vehicle_property_value_id"}), @ORM\Index(name="IDX_79B1772AE992D7DA", columns={"special_vehicle_property_id"}), @ORM\Index(name="IDX_79B1772A602D1907", columns={"sub_vehicle_configuration_id"}), @ORM\Index(name="IDX_79B1772A110AFF42", columns={"vehicle_configuration_id"})})
 * @ORM\Entity
 */
class SpecialVehiclePropertiesMapping
{
    /**
     * @var int
     *
     * @ORM\Column(name="svpm_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="special_vehicle_properties_mapping_svpm_id_seq", allocationSize=1,
     *                                                                                       initialValue=1)
     */
    private $svpmId;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="given_by_vehicle_configuration_key", type="boolean", nullable=true)
     */
    private $givenByVehicleConfigurationKey;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="visible_on_report", type="boolean", nullable=true)
     */
    private $visibleOnReport;

    /**
     * @var SpecialVehiclePropertyValues
     *
     * @ORM\ManyToOne(targetEntity="SpecialVehiclePropertyValues")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="special_vehicle_property_value_id", referencedColumnName="svpv_id")
     * })
     */
    private $specialVehiclePropertyValue;

    /**
     * @var SpecialVehicleProperties
     *
     * @ORM\ManyToOne(targetEntity="SpecialVehicleProperties")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="special_vehicle_property_id", referencedColumnName="special_vehicle_property_id")
     * })
     */
    private $specialVehicleProperty;

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
     * @var VehicleConfigurations
     *
     * @ORM\ManyToOne(targetEntity="VehicleConfigurations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_configuration_id", referencedColumnName="vehicle_configuration_id")
     * })
     */
    private $vehicleConfiguration;

    public function getSvpmId(): ?int
    {
        return $this->svpmId;
    }

    public function getGivenByVehicleConfigurationKey(): ?bool
    {
        return $this->givenByVehicleConfigurationKey;
    }

    public function setGivenByVehicleConfigurationKey(?bool $givenByVehicleConfigurationKey): self
    {
        $this->givenByVehicleConfigurationKey = $givenByVehicleConfigurationKey;

        return $this;
    }

    public function getVisibleOnReport(): ?bool
    {
        return $this->visibleOnReport;
    }

    public function setVisibleOnReport(?bool $visibleOnReport): self
    {
        $this->visibleOnReport = $visibleOnReport;

        return $this;
    }

    public function getSpecialVehiclePropertyValue(): ?SpecialVehiclePropertyValues
    {
        return $this->specialVehiclePropertyValue;
    }

    public function setSpecialVehiclePropertyValue(?SpecialVehiclePropertyValues $specialVehiclePropertyValue): self
    {
        $this->specialVehiclePropertyValue = $specialVehiclePropertyValue;

        return $this;
    }

    public function getSpecialVehicleProperty(): ?SpecialVehicleProperties
    {
        return $this->specialVehicleProperty;
    }

    public function setSpecialVehicleProperty(?SpecialVehicleProperties $specialVehicleProperty): self
    {
        $this->specialVehicleProperty = $specialVehicleProperty;

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

    public function getVehicleConfiguration(): ?VehicleConfigurations
    {
        return $this->vehicleConfiguration;
    }

    public function setVehicleConfiguration(?VehicleConfigurations $vehicleConfiguration): self
    {
        $this->vehicleConfiguration = $vehicleConfiguration;

        return $this;
    }


}
