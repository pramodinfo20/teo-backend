<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuSwVersionSubVehicleConfigurationMapping
 *
 * @ORM\Table(name="ecu_sw_version_sub_vehicle_configuration_mapping", uniqueConstraints={@ORM\UniqueConstraint(name="ecu_sw_version_sub_vehicle_co_sub_vehicle_configuration_id__key", columns={"sub_vehicle_configuration_id", "ecu_sw_version_id"})}, indexes={@ORM\Index(name="IDX_5376AABEEF576A6", columns={"ecu_sw_version_id"}), @ORM\Index(name="IDX_5376AABE602D1907", columns={"sub_vehicle_configuration_id"})})
 * @ORM\Entity
 */
class EcuSwVersionSubVehicleConfigurationMapping
{
    /**
     * @var int
     *
     * @ORM\Column(name="esvsvcm_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ecu_sw_version_sub_vehicle_configuration_mapping_esvsvcm_id_seq", allocationSize=1, initialValue=1)
     */
    private $esvsvcmId;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_primary_sw", type="boolean", nullable=false, options={"default"="1"})
     */
    private $isPrimarySw = true;

    /**
     * @var EcuSwVersions
     *
     * @ORM\ManyToOne(targetEntity="EcuSwVersions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_sw_version_id", referencedColumnName="ecu_sw_version_id")
     * })
     */
    private $ecuSwVersion;

    /**
     * @var SubVehicleConfigurations
     *
     * @ORM\ManyToOne(targetEntity="SubVehicleConfigurations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sub_vehicle_configuration_id", referencedColumnName="sub_vehicle_configuration_id")
     * })
     */
    private $subVehicleConfiguration;

    public function getEsvsvcmId(): ?int
    {
        return $this->esvsvcmId;
    }

    public function getIsPrimarySw(): ?bool
    {
        return $this->isPrimarySw;
    }

    public function setIsPrimarySw(bool $isPrimarySw): self
    {
        $this->isPrimarySw = $isPrimarySw;

        return $this;
    }

    public function getEcuSwVersion(): ?EcuSwVersions
    {
        return $this->ecuSwVersion;
    }

    public function setEcuSwVersion(?EcuSwVersions $ecuSwVersion): self
    {
        $this->ecuSwVersion = $ecuSwVersion;

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
}
