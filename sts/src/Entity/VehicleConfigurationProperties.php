<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VehicleConfigurationProperties
 *
 * @ORM\Table(name="vehicle_configuration_properties", uniqueConstraints={@ORM\UniqueConstraint(name="vehicle_configuration_propert_vehicle_configuration_propert_key", columns={"vehicle_configuration_property_name"})}, indexes={@ORM\Index(name="IDX_28836B3ECE3C9BAA", columns={"special_parts_id"})})
 * @ORM\Entity
 */
class VehicleConfigurationProperties
{
    /**
     * @var int
     *
     * @ORM\Column(name="vc_property_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="vehicle_configuration_properties_vc_property_id_seq", allocationSize=1,
     *                                                                                            initialValue=1)
     */
    private $vcPropertyId;

    /**
     * @var string
     *
     * @ORM\Column(name="vehicle_configuration_property_name", type="text", nullable=false)
     */
    private $vehicleConfigurationPropertyName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="german_name", type="text", nullable=true)
     */
    private $germanName;

    /**
     * @var SpecialParts
     *
     * @ORM\ManyToOne(targetEntity="SpecialParts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="special_parts_id", referencedColumnName="special_part_id")
     * })
     */
    private $specialParts;

    public function getVcPropertyId(): ?int
    {
        return $this->vcPropertyId;
    }

    public function getVehicleConfigurationPropertyName(): ?string
    {
        return $this->vehicleConfigurationPropertyName;
    }

    public function setVehicleConfigurationPropertyName(string $vehicleConfigurationPropertyName): self
    {
        $this->vehicleConfigurationPropertyName = $vehicleConfigurationPropertyName;

        return $this;
    }

    public function getGermanName(): ?string
    {
        return $this->germanName;
    }

    public function setGermanName(?string $germanName): self
    {
        $this->germanName = $germanName;

        return $this;
    }

    public function getSpecialParts(): ?SpecialParts
    {
        return $this->specialParts;
    }

    public function setSpecialParts(?SpecialParts $specialParts): self
    {
        $this->specialParts = $specialParts;

        return $this;
    }


}
