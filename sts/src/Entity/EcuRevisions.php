<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuRevisions
 *
 * @ORM\Table(name="ecu_revisions", uniqueConstraints={@ORM\UniqueConstraint(name="ecu_revisions_unique_sts_version", columns={"sts_version"})}, indexes={@ORM\Index(name="IDX_EBB68468F2887E5B", columns={"ecu_id"})})
 * @ORM\Entity
 */
class EcuRevisions
{
    /**
     * @var int
     *
     * @ORM\Column(name="ecu_revision_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ecu_revisions_ecu_revision_id_seq", allocationSize=1, initialValue=1)
     */
    private $ecuRevisionId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="hw", type="text", nullable=true)
     */
    private $hw;

    /**
     * @var string|null
     *
     * @ORM\Column(name="sw", type="text", nullable=true)
     */
    private $sw;

    /**
     * @var string|null
     *
     * @ORM\Column(name="sts_version", type="text", nullable=true)
     */
    private $stsVersion;

    /**
     * @var string|null
     *
     * @ORM\Column(name="request_id", type="string", length=8, nullable=true, options={"fixed"=true})
     */
    private $requestId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="response_id", type="string", length=8, nullable=true, options={"fixed"=true})
     */
    private $responseId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="href_windchill", type="text", nullable=true)
     */
    private $hrefWindchill;

    /**
     * @var bool
     *
     * @ORM\Column(name="use_uds", type="boolean", nullable=false, options={"default"="1"})
     */
    private $useUds = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="use_xcp", type="boolean", nullable=false)
     */
    private $useXcp = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="sw_profile_ok", type="boolean", nullable=false)
     */
    private $swProfileOk = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="released", type="boolean", nullable=false)
     */
    private $released = false;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="timestamp_last_change", type="datetime", nullable=true, options={"default"="now()"})
     */
    private $timestampLastChange = 'now()';

    /**
     * @var string|null
     *
     * @ORM\Column(name="info_text", type="text", nullable=true)
     */
    private $infoText;

    /**
     * @var string|null
     *
     * @ORM\Column(name="version_info", type="text", nullable=true)
     */
    private $versionInfo;

    /**
     * @var Ecus
     *
     * @ORM\ManyToOne(targetEntity="Ecus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_id", referencedColumnName="ecu_id")
     * })
     */
    private $ecu;

    public function getEcuRevisionId(): ?int
    {
        return $this->ecuRevisionId;
    }

    public function getHw(): ?string
    {
        return $this->hw;
    }

    public function setHw(?string $hw): self
    {
        $this->hw = $hw;

        return $this;
    }

    public function getSw(): ?string
    {
        return $this->sw;
    }

    public function setSw(?string $sw): self
    {
        $this->sw = $sw;

        return $this;
    }

    public function getStsVersion(): ?string
    {
        return $this->stsVersion;
    }

    public function setStsVersion(?string $stsVersion): self
    {
        $this->stsVersion = $stsVersion;

        return $this;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    public function setRequestId(?string $requestId): self
    {
        $this->requestId = $requestId;

        return $this;
    }

    public function getResponseId(): ?string
    {
        return $this->responseId;
    }

    public function setResponseId(?string $responseId): self
    {
        $this->responseId = $responseId;

        return $this;
    }

    public function getHrefWindchill(): ?string
    {
        return $this->hrefWindchill;
    }

    public function setHrefWindchill(?string $hrefWindchill): self
    {
        $this->hrefWindchill = $hrefWindchill;

        return $this;
    }

    public function getUseUds(): ?bool
    {
        return $this->useUds;
    }

    public function setUseUds(bool $useUds): self
    {
        $this->useUds = $useUds;

        return $this;
    }

    public function getUseXcp(): ?bool
    {
        return $this->useXcp;
    }

    public function setUseXcp(bool $useXcp): self
    {
        $this->useXcp = $useXcp;

        return $this;
    }

    public function getSwProfileOk(): ?bool
    {
        return $this->swProfileOk;
    }

    public function setSwProfileOk(bool $swProfileOk): self
    {
        $this->swProfileOk = $swProfileOk;

        return $this;
    }

    public function getReleased(): ?bool
    {
        return $this->released;
    }

    public function setReleased(bool $released): self
    {
        $this->released = $released;

        return $this;
    }

    public function getTimestampLastChange(): ?\DateTimeInterface
    {
        return $this->timestampLastChange;
    }

    public function setTimestampLastChange(?\DateTimeInterface $timestampLastChange): self
    {
        $this->timestampLastChange = $timestampLastChange;

        return $this;
    }

    public function getInfoText(): ?string
    {
        return $this->infoText;
    }

    public function setInfoText(?string $infoText): self
    {
        $this->infoText = $infoText;

        return $this;
    }

    public function getVersionInfo(): ?string
    {
        return $this->versionInfo;
    }

    public function setVersionInfo(?string $versionInfo): self
    {
        $this->versionInfo = $versionInfo;

        return $this;
    }

    public function getEcu(): ?Ecus
    {
        return $this->ecu;
    }

    public function setEcu(?Ecus $ecu): self
    {
        $this->ecu = $ecu;

        return $this;
    }


}
