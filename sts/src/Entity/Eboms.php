<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Eboms
 *
 * @ORM\Table(name="eboms", uniqueConstraints={@ORM\UniqueConstraint(name="eboms_vehicle_configuration_id_ebom_assembly_id_key", columns={"vehicle_configuration_id", "ebom_assembly_id"})}, indexes={@ORM\Index(name="IDX_5C0B89D939CB6794", columns={"ebom_assembly_id"}), @ORM\Index(name="IDX_5C0B89D9110AFF42", columns={"vehicle_configuration_id"})})
 * @ORM\Entity
 */
class Eboms
{
    /**
     * @var int
     *
     * @ORM\Column(name="ebom_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="eboms_ebom_id_seq", allocationSize=1, initialValue=1)
     */
    private $ebomId;

    /**
     * @var EbomAssemblies
     *
     * @ORM\ManyToOne(targetEntity="EbomAssemblies")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ebom_assembly_id", referencedColumnName="ebom_assembly_id")
     * })
     */
    private $ebomAssembly;

    /**
     * @var VehicleConfigurations
     *
     * @ORM\ManyToOne(targetEntity="VehicleConfigurations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_configuration_id", referencedColumnName="vehicle_configuration_id")
     * })
     */
    private $vehicleConfiguration;

    public function getEbomId(): ?int
    {
        return $this->ebomId;
    }

    public function getEbomAssembly(): ?EbomAssemblies
    {
        return $this->ebomAssembly;
    }

    public function setEbomAssembly(?EbomAssemblies $ebomAssembly): self
    {
        $this->ebomAssembly = $ebomAssembly;

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
