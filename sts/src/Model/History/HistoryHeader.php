<?php

namespace App\Model\History;

use App\Model\History\Traits\HistoryEvent;

class HistoryHeader implements HistoryI
{
    use HistoryEvent;

    /**
     * @var HistoryTuple
     */
    private $ecuSwVersion;

    /**
     * @var HistoryTuple
     */
    private $stsVersion;
    /**
     * @var HistoryTuple
     */
    private $swVersion;

    /**
     * @var HistoryTuple
     */
    private $ecuId;

    /**
     * @var HistoryTuple
     */
    private $odxSts02;

    /**
     * @var HistoryTuple
     */
    private $protocol;

    /**
     * @var HistoryTuple
     */
    private $request;

    /**
     * @var HistoryTuple
     */
    private $response;

    /**
     * @var HistoryTuple
     */
    private $info;

    /**
     * @var HistoryTuple
     */
    private $status;

    /**
     * @var HistoryTuple
     */
    private $windchillUrl;

    /**
     * @var HistoryTuple
     */
    private $subversionSuffix;

    /**
     * @var HistoryTuple
     */
    private $odxVersion;

    /**
     * @var HistoryTuple
     */
    private $bigEndian;

    /**
     * @var HistoryTuple
     */
    private $diagnosticIdentifier;

    /**
     * @return HistoryTuple
     */
    public function getEcuSwVersion(): HistoryTuple
    {
        return $this->ecuSwVersion;
    }

    /**
     * @param HistoryTuple $ecuSwVersion
     *
     * @return HistoryHeader
     */
    public function setEcuSwVersion(HistoryTuple $ecuSwVersion): HistoryHeader
    {
        $this->ecuSwVersion = $ecuSwVersion;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getStsVersion(): HistoryTuple
    {
        return $this->stsVersion;
    }

    /**
     * @param HistoryTuple $stsVersion
     *
     * @return HistoryHeader
     */
    public function setStsVersion(HistoryTuple $stsVersion): HistoryHeader
    {
        $this->stsVersion = $stsVersion;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getSwVersion(): HistoryTuple
    {
        return $this->swVersion;
    }

    /**
     * @param HistoryTuple $swVersion
     *
     * @return HistoryHeader
     */
    public function setSwVersion(HistoryTuple $swVersion): HistoryHeader
    {
        $this->swVersion = $swVersion;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getEcuId(): HistoryTuple
    {
        return $this->ecuId;
    }

    /**
     * @param HistoryTuple $ecuId
     *
     * @return HistoryHeader
     */
    public function setEcuId(HistoryTuple $ecuId): HistoryHeader
    {
        $this->ecuId = $ecuId;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getOdxSts02(): HistoryTuple
    {
        return $this->odxSts02;
    }

    /**
     * @param HistoryTuple $odxSts02
     *
     * @return HistoryHeader
     */
    public function setOdxSts02(HistoryTuple $odxSts02): HistoryHeader
    {
        $this->odxSts02 = $odxSts02;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getProtocol(): HistoryTuple
    {
        return $this->protocol;
    }

    /**
     * @param HistoryTuple $protocol
     *
     * @return HistoryHeader
     */
    public function setProtocol(HistoryTuple $protocol): HistoryHeader
    {
        $this->protocol = $protocol;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getRequest(): HistoryTuple
    {
        return $this->request;
    }

    /**
     * @param HistoryTuple $request
     *
     * @return HistoryHeader
     */
    public function setRequest(HistoryTuple $request): HistoryHeader
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getResponse(): HistoryTuple
    {
        return $this->response;
    }

    /**
     * @param HistoryTuple $response
     *
     * @return HistoryHeader
     */
    public function setResponse(HistoryTuple $response): HistoryHeader
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getInfo(): HistoryTuple
    {
        return $this->info;
    }

    /**
     * @param HistoryTuple $info
     *
     * @return HistoryHeader
     */
    public function setInfo(HistoryTuple $info): HistoryHeader
    {
        $this->info = $info;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getStatus(): HistoryTuple
    {
        return $this->status;
    }

    /**
     * @param HistoryTuple $status
     *
     * @return HistoryHeader
     */
    public function setStatus(HistoryTuple $status): HistoryHeader
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getWindchillUrl(): HistoryTuple
    {
        return $this->windchillUrl;
    }

    /**
     * @param HistoryTuple $windchillUrl
     *
     * @return HistoryHeader
     */
    public function setWindchillUrl(HistoryTuple $windchillUrl): HistoryHeader
    {
        $this->windchillUrl = $windchillUrl;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getSubversionSuffix(): HistoryTuple
    {
        return $this->subversionSuffix;
    }

    /**
     * @param HistoryTuple $subversionSuffix
     *
     * @return HistoryHeader
     */
    public function setSubversionSuffix(HistoryTuple $subversionSuffix): HistoryHeader
    {
        $this->subversionSuffix = $subversionSuffix;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getOdxVersion(): HistoryTuple
    {
        return $this->odxVersion;
    }

    /**
     * @param HistoryTuple $odxVersion
     *
     * @return HistoryHeader
     */
    public function setOdxVersion(HistoryTuple $odxVersion): HistoryHeader
    {
        $this->odxVersion = $odxVersion;
        return $this;
    }

    /**
     * @return \App\Model\History\HistoryTuple
     */
    public function getBigEndian(): \App\Model\History\HistoryTuple
    {
        return $this->bigEndian;
    }

    /**
     * @param \App\Model\History\HistoryTuple $bigEndian
     *
     * @return HistoryHeader
     */
    public function setBigEndian(\App\Model\History\HistoryTuple $bigEndian): HistoryHeader
    {
        $this->bigEndian = $bigEndian;
        return $this;
    }

    /**
     * @return HistoryTuple
     */
    public function getDiagnosticIdentifier(): HistoryTuple
    {
        return $this->diagnosticIdentifier;
    }

    /**
     * @param HistoryTuple $diagnosticIdentifier
     *
     * @return HistoryHeader
     */
    public function setDiagnosticIdentifier(HistoryTuple $diagnosticIdentifier): HistoryHeader
    {
        $this->diagnosticIdentifier = $diagnosticIdentifier;
        return $this;
    }
}