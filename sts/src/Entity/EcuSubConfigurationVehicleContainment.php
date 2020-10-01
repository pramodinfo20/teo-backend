<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuSubConfigurationVehicleContainment
 *
 * @ORM\Table(name="ecu_sub_configuration_vehicle_containment", uniqueConstraints={@ORM\UniqueConstraint(name="ecu_sub_configuration_vehicle_ce_ecu_id_sub_vehicle_configu_key", columns={"ce_ecu_id", "sub_vehicle_configuration_id"})}, indexes={@ORM\Index(name="IDX_7FB3FFDB4CB8FAC0", columns={"ecu_parameters_release_status_id"}), @ORM\Index(name="IDX_7FB3FFDB1D957DB0", columns={"ecu_parameters_released_by_user_id"}), @ORM\Index(name="IDX_7FB3FFDB5D0E9571", columns={"odx_source_type_id"}), @ORM\Index(name="IDX_7FB3FFDB13D5BE78", columns={"ebom_part_id"}), @ORM\Index(name="IDX_7FB3FFDB602D1907", columns={"sub_vehicle_configuration_id"}), @ORM\Index(name="IDX_7FB3FFDB8D3B41B6", columns={"ce_ecu_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\EcuSubConfigurationVehicleContainmentRepository")
 */
class EcuSubConfigurationVehicleContainment
{
    /**
     * @var int
     *
     * @ORM\Column(name="escvc_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ecu_sub_configuration_vehicle_containment_escvc_id_seq", allocationSize=1, initialValue=1)
     */
    private $escvcId;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="ecu_parameters_release_date", type="datetimetz", nullable=true)
     */
    private $ecuParametersReleaseDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="can_forwarding", type="boolean", nullable=false)
     */
    private $canForwarding = false;

    /**
     * @var ReleaseStatus
     *
     * @ORM\ManyToOne(targetEntity="ReleaseStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_parameters_release_status_id", referencedColumnName="release_status_id")
     * })
     */
    private $ecuParametersReleaseStatus;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_parameters_released_by_user_id", referencedColumnName="id")
     * })
     */
    private $ecuParametersReleasedByUser;

    /**
     * @var OdxSourceTypes
     *
     * @ORM\ManyToOne(targetEntity="OdxSourceTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="odx_source_type_id", referencedColumnName="odx_source_type_id")
     * })
     */
    private $odxSourceType;

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

    public function getEscvcId(): ?int
    {
        return $this->escvcId;
    }

    public function getEcuParametersReleaseDate(): ?\DateTimeInterface
    {
        return $this->ecuParametersReleaseDate;
    }

    public function setEcuParametersReleaseDate(?\DateTimeInterface $ecuParametersReleaseDate): self
    {
        $this->ecuParametersReleaseDate = $ecuParametersReleaseDate;

        return $this;
    }

    public function getCanForwarding(): ?bool
    {
        return $this->canForwarding;
    }

    public function setCanForwarding(bool $canForwarding): self
    {
        $this->canForwarding = $canForwarding;

        return $this;
    }

    public function getEcuParametersReleaseStatus(): ?ReleaseStatus
    {
        return $this->ecuParametersReleaseStatus;
    }

    public function setEcuParametersReleaseStatus(?ReleaseStatus $ecuParametersReleaseStatus): self
    {
        $this->ecuParametersReleaseStatus = $ecuParametersReleaseStatus;

        return $this;
    }

    public function getEcuParametersReleasedByUser(): ?Users
    {
        return $this->ecuParametersReleasedByUser;
    }

    public function setEcuParametersReleasedByUser(?Users $ecuParametersReleasedByUser): self
    {
        $this->ecuParametersReleasedByUser = $ecuParametersReleasedByUser;

        return $this;
    }

    public function getOdxSourceType(): ?OdxSourceTypes
    {
        return $this->odxSourceType;
    }

    public function setOdxSourceType(?OdxSourceTypes $odxSourceType): self
    {
        $this->odxSourceType = $odxSourceType;

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
