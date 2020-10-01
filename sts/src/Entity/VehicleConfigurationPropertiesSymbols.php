<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VehicleConfigurationPropertiesSymbols
 *
 * @ORM\Table(name="vehicle_configuration_properties_symbols", uniqueConstraints={@ORM\UniqueConstraint(name="vehicle_configuration_propert_vc_property_id_allowed_symbol_key", columns={"vc_property_id", "allowed_symbols_id"})}, indexes={@ORM\Index(name="IDX_DBA8F71BE95CFEF", columns={"allowed_symbols_id"}), @ORM\Index(name="IDX_DBA8F71B1D550D9", columns={"vc_property_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\VehicleConfigurationPropertiesSymbolsRepository")
 */
class VehicleConfigurationPropertiesSymbols
{
    /**
     * @var int
     *
     * @ORM\Column(name="vcps_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="vehicle_configuration_properties_symbols_vcps_id_seq", allocationSize=1, initialValue=1)
     */
    private $vcpsId;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="german_description", type="text", nullable=false, options={"default"="undefined"})
     */
    private $germanDescription = 'undefined';

    /**
     * @var \AllowedSymbols
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

    public function getVcpsId(): ?int
    {
        return $this->vcpsId;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getGermanDescription(): ?string
    {
        return $this->germanDescription;
    }

    public function setGermanDescription(string $germanDescription): self
    {
        $this->germanDescription = $germanDescription;

        return $this;
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


}
