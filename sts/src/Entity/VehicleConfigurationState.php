<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VehicleConfigurationState
 *
 * @ORM\Table(name="vehicle_configuration_state", uniqueConstraints={@ORM\UniqueConstraint(name="vehicle_configuration_state_vehicle_configuration_state_nam_key", columns={"vehicle_configuration_state_name"})})
 * @ORM\Entity
 */
class VehicleConfigurationState
{
    /**
     * @var int
     *
     * @ORM\Column(name="vehicle_configuration_state_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="vehicle_configuration_state_vehicle_configuration_state_id_seq", allocationSize=1,
     *                                                                                                       initialValue=1)
     */
    private $vehicleConfigurationStateId;

    /**
     * @var string
     *
     * @ORM\Column(name="vehicle_configuration_state_name", type="text", nullable=false)
     */
    private $vehicleConfigurationStateName;

    public function getVehicleConfigurationStateId(): ?int
    {
        return $this->vehicleConfigurationStateId;
    }

    public function getVehicleConfigurationStateName(): ?string
    {
        return $this->vehicleConfigurationStateName;
    }

    public function setVehicleConfigurationStateName(string $vehicleConfigurationStateName): self
    {
        $this->vehicleConfigurationStateName = $vehicleConfigurationStateName;

        return $this;
    }


}
