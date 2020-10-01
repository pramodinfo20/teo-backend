<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SpecialPartsMapping
 *
 * @ORM\Table(name="special_parts_mapping", uniqueConstraints={@ORM\UniqueConstraint(name="special_parts_mapping_vehicle_configuration_id_sub_vehicle__key", columns={"vehicle_configuration_id", "sub_vehicle_configuration_id", "special_part_id", "ebom_part_id"})}, indexes={@ORM\Index(name="IDX_B73D473513D5BE78", columns={"ebom_part_id"}), @ORM\Index(name="IDX_B73D473594EF1779", columns={"special_part_id"}), @ORM\Index(name="IDX_B73D4735602D1907", columns={"sub_vehicle_configuration_id"}), @ORM\Index(name="IDX_B73D4735110AFF42", columns={"vehicle_configuration_id"})})
 * @ORM\Entity
 */
class SpecialPartsMapping
{
    /**
     * @var int
     *
     * @ORM\Column(name="special_parts_mapping_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="special_parts_mapping_special_parts_mapping_id_seq", allocationSize=1,
     *                                                                                           initialValue=1)
     */
    private $specialPartsMappingId;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="given_by_vehicle_configuration_key", type="boolean", nullable=true)
     */
    private $givenByVehicleConfigurationKey;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="visible_in_vehicle_report_paper", type="boolean", nullable=true)
     */
    private $visibleInVehicleReportPaper;

    /**
     * @var EbomParts
     *
     * @ORM\ManyToOne(targetEntity="EbomParts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ebom_part_id", referencedColumnName="ebom_part_id")
     * })
     */
    private $ebomPart;

    /**
     * @var SpecialParts
     *
     * @ORM\ManyToOne(targetEntity="SpecialParts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="special_part_id", referencedColumnName="special_part_id")
     * })
     */
    private $specialPart;

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
     * @var VehicleConfigurations
     *
     * @ORM\ManyToOne(targetEntity="VehicleConfigurations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_configuration_id", referencedColumnName="vehicle_configuration_id")
     * })
     */
    private $vehicleConfiguration;

    public function getSpecialPartsMappingId(): ?int
    {
        return $this->specialPartsMappingId;
    }

    public function getGivenByVehicleConfigurationKey(): ?bool
    {
        return $this->givenByVehicleConfigurationKey;
    }

    public function setGivenByVehicleConfigurationKey(?bool $givenByVehicleConfigurationKey): self
    {
        $this->givenByVehicleConfigurationKey = $givenByVehicleConfigurationKey;

        return $this;
    }

    public function getVisibleInVehicleReportPaper(): ?bool
    {
        return $this->visibleInVehicleReportPaper;
    }

    public function setVisibleInVehicleReportPaper(?bool $visibleInVehicleReportPaper): self
    {
        $this->visibleInVehicleReportPaper = $visibleInVehicleReportPaper;

        return $this;
    }

    public function getEbomPart(): ?EbomParts
    {
        return $this->ebomPart;
    }

    public function setEbomPart(?EbomParts $ebomPart): self
    {
        $this->ebomPart = $ebomPart;

        return $this;
    }

    public function getSpecialPart(): ?SpecialParts
    {
        return $this->specialPart;
    }

    public function setSpecialPart(?SpecialParts $specialPart): self
    {
        $this->specialPart = $specialPart;

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
