<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConfigurationOdxVersion
 *
 * @ORM\Table(name="configuration_odx_version", uniqueConstraints={@ORM\UniqueConstraint(name="configuration_odx_version_ce_ecu_id_sub_vehicle_configurati_key", columns={"ce_ecu_id", "sub_vehicle_configuration_id"})}, indexes={@ORM\Index(name="IDX_FE3D997A602D1907", columns={"sub_vehicle_configuration_id"}), @ORM\Index(name="IDX_FE3D997A8D3B41B6", columns={"ce_ecu_id"})})
 * @ORM\Entity
 */
class ConfigurationOdxVersion
{
    /**
     * @var int
     *
     * @ORM\Column(name="cov_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="configuration_odx_version_cov_id_seq", allocationSize=1, initialValue=1)
     */
    private $covId;

    /**
     * @var int
     *
     * @ORM\Column(name="odx_version", type="integer", nullable=false, options={"default"="2"})
     */
    private $odxVersion = '2';

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
     * @var ConfigurationEcus
     *
     * @ORM\ManyToOne(targetEntity="ConfigurationEcus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ce_ecu_id", referencedColumnName="ce_ecu_id")
     * })
     */
    private $ceEcu;

    public function getCovId(): ?int
    {
        return $this->covId;
    }

    public function getOdxVersion(): ?int
    {
        return $this->odxVersion;
    }

    public function setOdxVersion(int $odxVersion): self
    {
        $this->odxVersion = $odxVersion;

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

    public function getCeEcu(): ?ConfigurationEcus
    {
        return $this->ceEcu;
    }

    public function setCeEcu(?ConfigurationEcus $ceEcu): self
    {
        $this->ceEcu = $ceEcu;

        return $this;
    }


}
