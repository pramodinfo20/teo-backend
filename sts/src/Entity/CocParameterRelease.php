<?php

namespace App\Entity;

use App\Model\ConvertibleToHistoryI;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * CocParameterRelease
 *
 * @ORM\Table(name="coc_parameter_release", indexes={@ORM\Index(name="IDX_3DD575D8844840E2", columns={"released_by"}),
 *                                          @ORM\Index(name="IDX_3DD575D8511951A8", columns={"release_status_id"})})
 * @ORM\Entity
 */
class CocParameterRelease implements ConvertibleToHistoryI
{
    /**
     * @var DateTime
     *
     * @ORM\Column(name="approval_date", type="datetimetz", nullable=false)
     */
    private $approvalDate;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="released_date", type="datetimetz", nullable=true)
     */
    private $releasedDate;

    /**
     * @var string|null
     *
     * @ORM\Column(name="approval_code", type="text", nullable=true)
     */
    private $approvalCode;

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
     *   @ORM\JoinColumn(name="cpr_sub_vehicle_configuration_id", referencedColumnName="sub_vehicle_configuration_id")
     * })
     */
    private $cprSubVehicleConfiguration;

    public function getApprovalDate(): ?DateTimeInterface
    {
        return $this->approvalDate;
    }

    public function setApprovalDate(?DateTimeInterface $approvalDate): self
    {
        $this->approvalDate = $approvalDate;

        return $this;
    }

    public function getReleasedDate(): ?DateTimeInterface
    {
        return $this->releasedDate;
    }

    public function setReleasedDate(?DateTimeInterface $releasedDate): self
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

    public function getCprSubVehicleConfiguration(): ?SubVehicleConfigurations
    {
        return $this->cprSubVehicleConfiguration;
    }

    public function setCprSubVehicleConfiguration(?SubVehicleConfigurations $subVehicleConfiguration): self
    {
        $this->cprSubVehicleConfiguration = $subVehicleConfiguration;

        return $this;
    }

    public function getApprovalCode(): ?string
    {
      return $this->approvalCode;
    }

    public function setApprovalCode(?string $approvalCode): self
    {
      $this->approvalCode = $approvalCode;

      return $this;
    }


}
