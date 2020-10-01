<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VehicleConfigurations
 *
 * @ORM\Table(name="vehicle_configurations", uniqueConstraints={@ORM\UniqueConstraint(name="vehicle_configurations_vehicle_configuration_key_key", columns={"vehicle_configuration_key"})}, indexes={@ORM\Index(name="IDX_78FA902A377DC374", columns={"default_production_location_id"}), @ORM\Index(name="IDX_78FA902A4DB4A13C", columns={"default_configuration_color_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\VehicleConfigurationsRepository")
 */
class VehicleConfigurations
{
    /**
     * @var int
     *
     * @ORM\Column(name="vehicle_configuration_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="vehicle_configurations_vehicle_configuration_id_seq", allocationSize=1, initialValue=1)
     */
    private $vehicleConfigurationId;

    /**
     * @var string
     *
     * @ORM\Column(name="vehicle_configuration_key", type="text", nullable=false)
     */
    private $vehicleConfigurationKey;

    /**
     * @var string
     *
     * @ORM\Column(name="vehicle_type_name", type="text", nullable=false)
     */
    private $vehicleTypeName;

    /**
     * @var int|null
     *
     * @ORM\Column(name="vehicle_type_year", type="integer", nullable=true)
     */
    private $vehicleTypeYear;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vehicle_series", type="text", nullable=true)
     */
    private $vehicleSeries;

    /**
     * @var bool
     *
     * @ORM\Column(name="draft", type="boolean", nullable=false, options={"comment"="Should be removed in the future, only used for migrations"})
     */
    private $draft = false;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vehicle_customer_key", type="text", nullable=true)
     */
    private $vehicleCustomerKey;

    /**
     * @var VehicleVariants
     *
     * @ORM\ManyToOne(targetEntity="VehicleVariants")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="old_vehicle_variant_id", referencedColumnName="vehicle_variant_id")
     * })
     */
    private $oldVehicleVariant;

    /**
     * @var Depots
     *
     * @ORM\ManyToOne(targetEntity="Depots")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="default_production_location_id", referencedColumnName="depot_id")
     * })
     */
    private $defaultProductionLocation;

    /**
     * @var ConfigurationColors
     *
     * @ORM\ManyToOne(targetEntity="ConfigurationColors")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="default_configuration_color_id", referencedColumnName="configuration_color_id")
     * })
     */
    private $defaultConfigurationColor;

    public function getVehicleConfigurationId(): ?int
    {
        return $this->vehicleConfigurationId;
    }

    public function getVehicleConfigurationKey(): ?string
    {
        return $this->vehicleConfigurationKey;
    }

    public function setVehicleConfigurationKey(string $vehicleConfigurationKey): self
    {
        $this->vehicleConfigurationKey = $vehicleConfigurationKey;

        return $this;
    }

    public function getVehicleTypeName(): ?string
    {
        return $this->vehicleTypeName;
    }

    public function setVehicleTypeName(string $vehicleTypeName): self
    {
        $this->vehicleTypeName = $vehicleTypeName;

        return $this;
    }

    public function getVehicleTypeYear(): ?int
    {
        return $this->vehicleTypeYear;
    }

    public function setVehicleTypeYear(?int $vehicleTypeYear): self
    {
        $this->vehicleTypeYear = $vehicleTypeYear;

        return $this;
    }

    public function getVehicleSeries(): ?string
    {
        return $this->vehicleSeries;
    }

    public function setVehicleSeries(?string $vehicleSeries): self
    {
        $this->vehicleSeries = $vehicleSeries;

        return $this;
    }

    public function getDraft(): ?bool
    {
        return $this->draft;
    }

    public function setDraft(bool $draft): self
    {
        $this->draft = $draft;

        return $this;
    }

    public function getVehicleCustomerKey(): ?string
    {
        return $this->vehicleCustomerKey;
    }

    public function setVehicleCustomerKey(?string $vehicleCustomerKey): self
    {
        $this->vehicleCustomerKey = $vehicleCustomerKey;

        return $this;
    }

    public function getOldVehicleVariant(): ?VehicleVariants
    {
        return $this->oldVehicleVariant;
    }

    public function setOldVehicleVariant(?VehicleVariants $oldVehicleVariant): self
    {
        $this->oldVehicleVariant = $oldVehicleVariant;

        return $this;
    }

    public function getDefaultProductionLocation(): ?Depots
    {
        return $this->defaultProductionLocation;
    }

    public function setDefaultProductionLocation(?Depots $defaultProductionLocation): self
    {
        $this->defaultProductionLocation = $defaultProductionLocation;

        return $this;
    }

    public function getDefaultConfigurationColor(): ?ConfigurationColors
    {
        return $this->defaultConfigurationColor;
    }

    public function setDefaultConfigurationColor(?ConfigurationColors $defaultConfigurationColor): self
    {
        $this->defaultConfigurationColor = $defaultConfigurationColor;

        return $this;
    }


}
