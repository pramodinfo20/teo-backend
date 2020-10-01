<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VehicleConfigurationKeyLogic
 *
 * @ORM\Table(name="vehicle_configuration_key_logic", uniqueConstraints={@ORM\UniqueConstraint(name="vehicle_configuration_key_log_vc_property_id_beginning_vehi_key", columns={"vc_property_id", "beginning_vehicle_configuration"})}, indexes={@ORM\Index(name="IDX_7CC67FCB1D550D9", columns={"vc_property_id"})})
 * @ORM\Entity
 */
class VehicleConfigurationKeyLogic
{
    /**
     * @var int
     *
     * @ORM\Column(name="vckl_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="vehicle_configuration_key_logic_vckl_id_seq", allocationSize=1,
     *                                                                                    initialValue=1)
     */
    private $vcklId;

    /**
     * @var string
     *
     * @ORM\Column(name="beginning_vehicle_configuration", type="text", nullable=false)
     */
    private $beginningVehicleConfiguration;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ending_vehicle_configuration", type="text", nullable=true)
     */
    private $endingVehicleConfiguration;

    /**
     * @var int|null
     *
     * @ORM\Column(name="position_in_vehicle_configuration_key", type="integer", nullable=true)
     */
    private $positionInVehicleConfigurationKey;

    /**
     * @var int|null
     *
     * @ORM\Column(name="length_in_vehicle_configuration_key", type="integer", nullable=true)
     */
    private $lengthInVehicleConfigurationKey;

    /**
     * @var VehicleConfigurationProperties
     *
     * @ORM\ManyToOne(targetEntity="VehicleConfigurationProperties")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vc_property_id", referencedColumnName="vc_property_id")
     * })
     */
    private $vcProperty;

    public function getVcklId(): ?int
    {
        return $this->vcklId;
    }

    public function getBeginningVehicleConfiguration(): ?string
    {
        return $this->beginningVehicleConfiguration;
    }

    public function setBeginningVehicleConfiguration(string $beginningVehicleConfiguration): self
    {
        $this->beginningVehicleConfiguration = $beginningVehicleConfiguration;

        return $this;
    }

    public function getEndingVehicleConfiguration(): ?string
    {
        return $this->endingVehicleConfiguration;
    }

    public function setEndingVehicleConfiguration(?string $endingVehicleConfiguration): self
    {
        $this->endingVehicleConfiguration = $endingVehicleConfiguration;

        return $this;
    }

    public function getPositionInVehicleConfigurationKey(): ?int
    {
        return $this->positionInVehicleConfigurationKey;
    }

    public function setPositionInVehicleConfigurationKey(?int $positionInVehicleConfigurationKey): self
    {
        $this->positionInVehicleConfigurationKey = $positionInVehicleConfigurationKey;

        return $this;
    }

    public function getLengthInVehicleConfigurationKey(): ?int
    {
        return $this->lengthInVehicleConfigurationKey;
    }

    public function setLengthInVehicleConfigurationKey(?int $lengthInVehicleConfigurationKey): self
    {
        $this->lengthInVehicleConfigurationKey = $lengthInVehicleConfigurationKey;

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
