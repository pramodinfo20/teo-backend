<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GlobalParameterRelease
 *
 * @ORM\Table(name="global_parameter_release", indexes={@ORM\Index(name="IDX_6A3E49A5844840E2",
 *                                             columns={"released_by"}), @ORM\Index(name="IDX_6A3E49A5511951A8",
 *                                             columns={"release_status_id"})})
 * @ORM\Entity
 */
class GlobalParameterRelease
{
    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="approval_date", type="datetimetz", nullable=true)
     */
    private $approvalDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="released_date", type="datetimetz", nullable=true)
     */
    private $releasedDate;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="released_by", referencedColumnName="id")
     * })
     */
    private $releasedBy;

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
     * @var SubVehicleConfigurations
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="SubVehicleConfigurations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="gpr_sub_vehicle_configuration_id", referencedColumnName="sub_vehicle_configuration_id")
     * })
     */
    private $gprSubVehicleConfiguration;

    public function getApprovalDate(): ?\DateTimeInterface
    {
        return $this->approvalDate;
    }

    public function setApprovalDate(?\DateTimeInterface $approvalDate): self
    {
        $this->approvalDate = $approvalDate;

        return $this;
    }

    public function getReleasedDate(): ?\DateTimeInterface
    {
        return $this->releasedDate;
    }

    public function setReleasedDate(?\DateTimeInterface $releasedDate): self
    {
        $this->releasedDate = $releasedDate;

        return $this;
    }

    public function getReleasedBy(): ?Users
    {
        return $this->releasedBy;
    }

    public function setReleasedBy(?Users $releasedBy): self
    {
        $this->releasedBy = $releasedBy;

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

    public function getGprSubVehicleConfiguration(): ?SubVehicleConfigurations
    {
        return $this->gprSubVehicleConfiguration;
    }

    public function setGprSubVehicleConfiguration(?SubVehicleConfigurations $gprSubVehicleConfiguration): self
    {
        $this->gprSubVehicleConfiguration = $gprSubVehicleConfiguration;

        return $this;
    }


}
