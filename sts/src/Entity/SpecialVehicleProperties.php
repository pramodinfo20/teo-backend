<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SpecialVehicleProperties
 *
 * @ORM\Table(name="special_vehicle_properties", uniqueConstraints={@ORM\UniqueConstraint(name="special_vehicle_properties_special_vehicle_property_name_key", columns={"special_vehicle_property_name"})}, indexes={@ORM\Index(name="IDX_3AF51B37ABA835F1", columns={"variable_type_id"})})
 * @ORM\Entity
 */
class SpecialVehicleProperties
{
    /**
     * @var int
     *
     * @ORM\Column(name="special_vehicle_property_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="special_vehicle_properties_special_vehicle_property_id_seq", allocationSize=1,
     *                                                                                                   initialValue=1)
     */
    private $specialVehiclePropertyId;

    /**
     * @var string
     *
     * @ORM\Column(name="special_vehicle_property_name", type="text", nullable=false)
     */
    private $specialVehiclePropertyName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="german_name", type="text", nullable=true)
     */
    private $germanName;

    /**
     * @var VariableTypes
     *
     * @ORM\ManyToOne(targetEntity="VariableTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="variable_type_id", referencedColumnName="variable_type_id")
     * })
     */
    private $variableType;

    public function getSpecialVehiclePropertyId(): ?int
    {
        return $this->specialVehiclePropertyId;
    }

    public function getSpecialVehiclePropertyName(): ?string
    {
        return $this->specialVehiclePropertyName;
    }

    public function setSpecialVehiclePropertyName(string $specialVehiclePropertyName): self
    {
        $this->specialVehiclePropertyName = $specialVehiclePropertyName;

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

    public function getVariableType(): ?VariableTypes
    {
        return $this->variableType;
    }

    public function setVariableType(?VariableTypes $variableType): self
    {
        $this->variableType = $variableType;

        return $this;
    }


}
