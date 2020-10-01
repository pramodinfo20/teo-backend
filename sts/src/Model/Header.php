<?php

namespace App\Model;

use App\Entity\EcuCommunicationProtocols;
use App\Entity\ReleaseStatus;

class Header implements ConvertibleToHistoryI
{
    /**
     * @var int
     */
    private $ecuSwVersion;

    /**
     * @var string
     */
    private $stsVersion;
    /**
     * @var string
     */
    private $swVersion;

    /**
     * @var int
     */
    private $ecuId;

    /**
     * @var bool
     */
    private $odxSts02;

    /**
     * @var EcuCommunicationProtocols
     */
    private $protocol;

    /**
     * @var int
     */
    private $request;

    /**
     * @var int
     */
    private $response;

    /**
     * @var string
     */
    private $info;

    /**
     * @var ReleaseStatus
     */
    private $status;

    /**
     * @var string
     */
    private $windchillUrl;

    /**
     * @var string
     */
    private $subversionSuffix;

    /**
     * @var int
     */
    private $odxVersion;

    /**
     * @var bool
     */
    private $bigEndian;

    /**
     * @var string
     */
    private $diagnosticIdentifier;

    /**
     * @return int
     */
    public function getEcuSwVersion(): int
    {
        return $this->ecuSwVersion;
    }

    /**
     * @param int $ecuSwVersion
     *
     * @return Header
     */
    public function setEcuSwVersion(int $ecuSwVersion): self
    {
        $this->ecuSwVersion = $ecuSwVersion;

        return $this;
    }

    /**
     * @return string
     */
    public function getStsVersion(): ?string
    {
        return $this->stsVersion;
    }

    /**
     * @param string $stsVersion
     *
     * @return Header
     */
    public function setStsVersion(string $stsVersion = null): self
    {
        $this->stsVersion = $stsVersion;

        return $this;
    }

    /**
     * @return int
     */
    public function getEcuId(): int
    {
        return $this->ecuId;
    }

    /**
     * @param int $ecuId
     */
    public function setEcuId(int $ecuId): void
    {
        $this->ecuId = $ecuId;
    }

    /**
     * @return bool
     */
    public function isOdxSts02(): bool
    {
        return $this->odxSts02;
    }

    /**
     * @param bool $odxSts02
     *
     * @return Header
     */
    public function setOdxSts02(bool $odxSts02): self
    {
        $this->odxSts02 = $odxSts02;

        return $this;
    }

    /**
     * @return EcuCommunicationProtocols
     */
    public function getProtocol(): EcuCommunicationProtocols
    {
        return $this->protocol;
    }

    /**
     * @param EcuCommunicationProtocols $protocol
     *
     * @return Header
     */
    public function setProtocol(EcuCommunicationProtocols $protocol): self
    {
        $this->protocol = $protocol;

        return $this;
    }

    /**
     * @return string
     */
    public function getRequest(): ?string
    {
        return $this->request;
    }

    /**
     * @param string $request
     *
     * @return Header
     */
    public function setRequest(string $request = null): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return string
     */
    public function getResponse(): ?string
    {
        return $this->response;
    }

    /**
     * @param string $response
     *
     * @return Header
     */
    public function setResponse(string $response = null): self
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return string
     */
    public function getInfo(): ?string
    {
        return $this->info;
    }

    /**
     * @param string $info
     *
     * @return Header
     */
    public function setInfo($info = null): self
    {
        $this->info = $info;

        return $this;
    }

    /**
     * @return ReleaseStatus
     */
    public function getStatus(): ReleaseStatus
    {
        return $this->status;
    }

    /**
     * @param ReleaseStatus $status
     *
     * @return Header
     */
    public function setStatus(ReleaseStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getWindchillUrl(): ?string
    {
        return $this->windchillUrl;
    }

    /**
     * @param string $windchillUrl
     *
     * @return Header
     */
    public function setWindchillUrl(string $windchillUrl = null): self
    {
        $this->windchillUrl = $windchillUrl;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubversionSuffix(): ?string
    {
        return $this->subversionSuffix;
    }

    /**
     * @param string $subversionSuffix
     *
     * @return Header
     */
    public function setSubversionSuffix(string $subversionSuffix = null): self
    {
        $this->subversionSuffix = $subversionSuffix;

        return $this;
    }

    /**
     * @return string
     */
    public function getSwVersion(): string
    {
        return $this->swVersion;
    }

    /**
     * @param string $swVersion
     *
     * @return Header
     */
    public function setSwVersion(string $swVersion): self
    {
        $this->swVersion = $swVersion;

        return $this;
    }

    /**
     * @return int
     */
    public function getOdxVersion(): ?int
    {
        return $this->odxVersion;
    }

    /**
     * @param int $odxVersion
     *
     * @return Header
     */
    public function setOdxVersion(int $odxVersion = null): Header
    {
        $this->odxVersion = $odxVersion;
        return $this;
    }

    /**
     * @return bool
     */
    public function isBigEndian(): ?bool
    {
        return $this->bigEndian;
    }

    /**
     * @param bool $bigEndian
     *
     * @return Header
     */
    public function setBigEndian(bool $bigEndian = null): Header
    {
        $this->bigEndian = $bigEndian;
        return $this;
    }

    /**
     * @return string
     */
    public function getDiagnosticIdentifier(): ?string
    {
        return $this->diagnosticIdentifier;
    }

    /**
     * @param string $diagnosticIdentifier
     *
     * @return Header
     */
    public function setDiagnosticIdentifier(string $diagnosticIdentifier = null): Header
    {
        $this->diagnosticIdentifier = $diagnosticIdentifier;
        return $this;
    }
}