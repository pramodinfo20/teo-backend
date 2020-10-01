<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SubVehicleConfigurations
 *
 * @ORM\Table(name="sub_vehicle_configurations", uniqueConstraints={@ORM\UniqueConstraint(name="sub_vehicle_configurations_sub_vehicle_configuration_name_key", columns={"sub_vehicle_configuration_name"})}, indexes={@ORM\Index(name="IDX_129E5A60511951A8", columns={"release_status_id"}), @ORM\Index(name="IDX_129E5A60F1B5FBDE", columns={"released_by_user_id"}), @ORM\Index(name="IDX_129E5A609BC8D4A5", columns={"vehicle_configuration_state_id"}), @ORM\Index(name="IDX_129E5A60EFACE4D9", columns={"source_sub_vehicle_configuration_id"}), @ORM\Index(name="IDX_129E5A60110AFF42", columns={"vehicle_configuration_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\SubVehicleConfigurationsRepository")
 */
class SubVehicleConfigurations
{
    /**
     * @var int
     *
     * @ORM\Column(name="sub_vehicle_configuration_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="sub_vehicle_configurations_sub_vehicle_configuration_id_seq", allocationSize=1, initialValue=1)
     */
    private $subVehicleConfigurationId;

    /**
     * @var string
     *
     * @ORM\Column(name="sub_vehicle_configuration_name", type="text", nullable=false)
     */
    private $subVehicleConfigurationName;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="release_date", type="datetimetz", nullable=true)
     */
    private $releaseDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="draft", type="boolean", nullable=false)
     */
    private $draft = false;

    /**
     * @var string|null
     *
     * @ORM\Column(name="short_production_description", type="text", nullable=true)
     */
    private $shortProductionDescription;

    /**
     * @var ReleaseStatus
     *
     * @ORM\ManyToOne(targetEntity="ReleaseStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="release_status_id", referencedColumnName="release_status_id")
     * })
     */
    private $releaseStatus;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="released_by_user_id", referencedColumnName="id")
     * })
     */
    private $releasedByUser;

    /**
     * @var VehicleConfigurationState
     *
     * @ORM\ManyToOne(targetEntity="VehicleConfigurationState")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_configuration_state_id", referencedColumnName="vehicle_configuration_state_id")
     * })
     */
    private $vehicleConfigurationState;

    /**
     * @var SubVehicleConfigurations
     *
     * @ORM\ManyToOne(targetEntity="SubVehicleConfigurations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="source_sub_vehicle_configuration_id", referencedColumnName="sub_vehicle_configuration_id")
     * })
     */
    private $sourceSubVehicleConfiguration;

    /**
     * @var VehicleConfigurations
     *
     * @ORM\ManyToOne(targetEntity="VehicleConfigurations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_configuration_id", referencedColumnName="vehicle_configuration_id")
     * })
     */
    private $vehicleConfiguration;

    public function getSubVehicleConfigurationId(): ?int
    {
        return $this->subVehicleConfigurationId;
    }

    public function getSubVehicleConfigurationName(): ?string
    {
        return $this->subVehicleConfigurationName;
    }

    public function setSubVehicleConfigurationName(string $subVehicleConfigurationName): self
    {
        $this->subVehicleConfigurationName = $subVehicleConfigurationName;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(?\DateTimeInterface $releaseDate): self
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getDraft(): ?bool
    {
        return $this->draft;
    }

    public function setDraft(bool $draft): self
    {
        $this->draft = $draft;

        return $this;
    }

    public function getShortProductionDescription(): ?string
    {
        return $this->shortProductionDescription;
    }

    public function setShortProductionDescription(?string $shortProductionDescription): self
    {
        $this->shortProductionDescription = $shortProductionDescription;

        return $this;
    }

    public function getReleaseStatus(): ?ReleaseStatus
    {
        return $this->releaseStatus;
    }

    public function setReleaseStatus(?ReleaseStatus $releaseStatus): self
    {
        $this->releaseStatus = $releaseStatus;

        return $this;
    }

    public function getReleasedByUser(): ?Users
    {
        return $this->releasedByUser;
    }

    public function setReleasedByUser(?Users $releasedByUser): self
    {
        $this->releasedByUser = $releasedByUser;

        return $this;
    }

    public function getVehicleConfigurationState(): ?VehicleConfigurationState
    {
        return $this->vehicleConfigurationState;
    }

    public function setVehicleConfigurationState(?VehicleConfigurationState $vehicleConfigurationState): self
    {
        $this->vehicleConfigurationState = $vehicleConfigurationState;

        return $this;
    }

    public function getSourceSubVehicleConfiguration(): ?self
    {
        return $this->sourceSubVehicleConfiguration;
    }

    public function setSourceSubVehicleConfiguration(?self $sourceSubVehicleConfiguration): self
    {
        $this->sourceSubVehicleConfiguration = $sourceSubVehicleConfiguration;

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
