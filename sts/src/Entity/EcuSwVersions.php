<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuSwVersions
 *
 * @ORM\Table(name="ecu_sw_versions", uniqueConstraints={@ORM\UniqueConstraint(name="ecu_sw_sts_suffix", columns={"sts_part_number", "sw_version", "ce_ecu_id", "suffix_if_is_sub_ecu_sw_version"})}, indexes={@ORM\Index(name="IDX_1DA2E39870710969", columns={"can_version_id"}), @ORM\Index(name="IDX_1DA2E39864998C34", columns={"ecu_communication_protocol_id"}), @ORM\Index(name="IDX_1DA2E3984E9C64F4", columns={"ecu_sw_version_lock_status_id"}), @ORM\Index(name="IDX_1DA2E398511951A8", columns={"release_status_id"}), @ORM\Index(name="IDX_1DA2E398C76E3BB0", columns={"parent_sw_version_id"}), @ORM\Index(name="IDX_1DA2E3988D3B41B6", columns={"ce_ecu_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\EcuSwVersionsRepository")
 */
class EcuSwVersions
{
    /**
     * @var int
     *
     * @ORM\Column(name="ecu_sw_version_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ecu_sw_versions_ecu_sw_version_id_seq", allocationSize=1, initialValue=1)
     */
    private $ecuSwVersionId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="sts_part_number", type="text", nullable=true)
     */
    private $stsPartNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="sw_version", type="text", nullable=false)
     */
    private $swVersion;

    /**
     * @var string|null
     *
     * @ORM\Column(name="suffix_if_is_sub_ecu_sw_version", type="text", nullable=true)
     */
    private $suffixIfIsSubEcuSwVersion;

    /**
     * @var int
     *
     * @ORM\Column(name="odx_version", type="integer", nullable=false, options={"default"="2"})
     */
    private $odxVersion = '2';

    /**
     * @var CanVersions
     *
     * @ORM\ManyToOne(targetEntity="CanVersions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="can_version_id", referencedColumnName="can_version_id")
     * })
     */
    private $canVersion;

    /**
     * @var EcuCommunicationProtocols
     *
     * @ORM\ManyToOne(targetEntity="EcuCommunicationProtocols")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_communication_protocol_id", referencedColumnName="ecu_communication_protocol_id")
     * })
     */
    private $ecuCommunicationProtocol;

    /**
     * @var EcuSwVersionLockStatus
     *
     * @ORM\ManyToOne(targetEntity="EcuSwVersionLockStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_sw_version_lock_status_id", referencedColumnName="ecu_sw_version_lock_status_id")
     * })
     */
    private $ecuSwVersionLockStatus;

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
     * @var EcuSwVersions
     *
     * @ORM\ManyToOne(targetEntity="EcuSwVersions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_sw_version_id", referencedColumnName="ecu_sw_version_id")
     * })
     */
    private $parentSwVersion;

    /**
     * @var ConfigurationEcus
     *
     * @ORM\ManyToOne(targetEntity="ConfigurationEcus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ce_ecu_id", referencedColumnName="ce_ecu_id")
     * })
     */
    private $ceEcu;

    public function getEcuSwVersionId(): ?int
    {
        return $this->ecuSwVersionId;
    }

    public function getStsPartNumber(): ?string
    {
        return $this->stsPartNumber;
    }

    public function setStsPartNumber(?string $stsPartNumber): self
    {
        $this->stsPartNumber = $stsPartNumber;

        return $this;
    }

    public function getSwVersion(): ?string
    {
        return $this->swVersion;
    }

    public function setSwVersion(string $swVersion): self
    {
        $this->swVersion = $swVersion;

        return $this;
    }

    public function getSuffixIfIsSubEcuSwVersion(): ?string
    {
        return $this->suffixIfIsSubEcuSwVersion;
    }

    public function setSuffixIfIsSubEcuSwVersion(?string $suffixIfIsSubEcuSwVersion): self
    {
        $this->suffixIfIsSubEcuSwVersion = $suffixIfIsSubEcuSwVersion;

        return $this;
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

    public function getCanVersion(): ?CanVersions
    {
        return $this->canVersion;
    }

    public function setCanVersion(?CanVersions $canVersion): self
    {
        $this->canVersion = $canVersion;

        return $this;
    }

    public function getEcuCommunicationProtocol(): ?EcuCommunicationProtocols
    {
        return $this->ecuCommunicationProtocol;
    }

    public function setEcuCommunicationProtocol(?EcuCommunicationProtocols $ecuCommunicationProtocol): self
    {
        $this->ecuCommunicationProtocol = $ecuCommunicationProtocol;

        return $this;
    }

    public function getEcuSwVersionLockStatus(): ?EcuSwVersionLockStatus
    {
        return $this->ecuSwVersionLockStatus;
    }

    public function setEcuSwVersionLockStatus(?EcuSwVersionLockStatus $ecuSwVersionLockStatus): self
    {
        $this->ecuSwVersionLockStatus = $ecuSwVersionLockStatus;

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

    public function getParentSwVersion(): ?self
    {
        return $this->parentSwVersion;
    }

    public function setParentSwVersion(?self $parentSwVersion): self
    {
        $this->parentSwVersion = $parentSwVersion;

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
