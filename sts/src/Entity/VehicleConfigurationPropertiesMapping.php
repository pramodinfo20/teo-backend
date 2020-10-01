<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VehicleConfigurationPropertiesMapping
 *
 * @ORM\Table(name="vehicle_configuration_properties_mapping", uniqueConstraints={@ORM\UniqueConstraint(name="vehicle_configuration_propert_vehicle_configuration_id_vc_p_key", columns={"vehicle_configuration_id", "vc_property_id", "allowed_symbols_id"})}, indexes={@ORM\Index(name="IDX_4DCD9614E95CFEF", columns={"allowed_symbols_id"}), @ORM\Index(name="IDX_4DCD96141D550D9", columns={"vc_property_id"}), @ORM\Index(name="IDX_4DCD9614110AFF42", columns={"vehicle_configuration_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\VehicleConfigurationPropertiesMappingRepository")
 */
class VehicleConfigurationPropertiesMapping
{
    /**
     * @var int
     *
     * @ORM\Column(name="vcpm_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="vehicle_configuration_properties_mapping_vcpm_id_seq", allocationSize=1,
     *                                                                                             initialValue=1)
     */
    private $vcpmId;

    /**
     * @var AllowedSymbols
     *
     * @ORM\ManyToOne(targetEntity="AllowedSymbols")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="allowed_symbols_id", referencedColumnName="allowed_symbols_id")
     * })
     */
    private $allowedSymbols;

    /**
     * @var VehicleConfigurationProperties
     *
     * @ORM\ManyToOne(targetEntity="VehicleConfigurationProperties")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vc_property_id", referencedColumnName="vc_property_id")
     * })
     */
    private $vcProperty;

    /**
     * @var VehicleConfigurations
     *
     * @ORM\ManyToOne(targetEntity="VehicleConfigurations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_configuration_id", referencedColumnName="vehicle_configuration_id")
     * })
     */
    private $vehicleConfiguration;

    public function getVcpmId(): ?int
    {
        return $this->vcpmId;
    }

    public function getAllowedSymbols(): ?AllowedSymbols
    {
        return $this->allowedSymbols;
    }

    public function setAllowedSymbols(?AllowedSymbols $allowedSymbols): self
    {
        $this->allowedSymbols = $allowedSymbols;

        return $this;
    }

    public function getVcProperty(): ?VehicleConfigurationProperties
    {
        return $this->vcProperty;
    }

    public function setVcProperty(?VehicleConfigurationProperties $vcProperty): self
    {
        $this->vcProperty = $vcProperty;

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
