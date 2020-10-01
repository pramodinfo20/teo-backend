<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VehicleTypes
 *
 * @ORM\Table(name="vehicle_types", uniqueConstraints={@ORM\UniqueConstraint(name="vehicle_types_vehicle_type_name_key", columns={"vehicle_type_name"})})
 * @ORM\Entity
 */
class VehicleTypes
{
    /**
     * @var int
     *
     * @ORM\Column(name="vehicle_type_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="vehicle_types_vehicle_type_id_seq", allocationSize=1, initialValue=1)
     */
    private $vehicleTypeId;

    /**
     * @var string
     *
     * @ORM\Column(name="vehicle_type_name", type="text", nullable=false)
     */
    private $vehicleTypeName;

    public function getVehicleTypeId(): ?int
    {
        return $this->vehicleTypeId;
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


}
