<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuSwVersionGeneralProperties
 *
 * @ORM\Table(name="ecu_sw_version_general_properties", indexes={@ORM\Index(name="IDX_4BD5F87BAE33DD24", columns={"secure_access_properties_id"})})
 * @ORM\Entity
 */
class EcuSwVersionGeneralProperties
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="uds_request_id", type="text", nullable=true)
     */
    private $udsRequestId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="uds_response_id", type="text", nullable=true)
     */
    private $udsResponseId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="windchill_link", type="text", nullable=true)
     */
    private $windchillLink;

    /**
     * @var string|null
     *
     * @ORM\Column(name="information", type="text", nullable=true)
     */
    private $information;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_big_endian", type="boolean", nullable=false)
     */
    private $isBigEndian = false;

    /**
     * @var string|null
     *
     * @ORM\Column(name="diagnostic_identifier", type="text", nullable=true)
     */
    private $diagnosticIdentifier;

    /**
     * @var SecureAccessProperties
     *
     * @ORM\ManyToOne(targetEntity="SecureAccessProperties")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="secure_access_properties_id", referencedColumnName="secure_access_properties_id")
     * })
     */
    private $secureAccessProperties;

    /**
     * @var EcuSwVersions
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="EcuSwVersions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="esvgp_ecu_sw_version_id", referencedColumnName="ecu_sw_version_id")
     * })
     */
    private $esvgpEcuSwVersion;

    public function getUdsRequestId(): ?string
    {
        return $this->udsRequestId;
    }

    public function setUdsRequestId(?string $udsRequestId): self
    {
        $this->udsRequestId = $udsRequestId;

        return $this;
    }

    public function getUdsResponseId(): ?string
    {
        return $this->udsResponseId;
    }

    public function setUdsResponseId(?string $udsResponseId): self
    {
        $this->udsResponseId = $udsResponseId;

        return $this;
    }

    public function getWindchillLink(): ?string
    {
        return $this->windchillLink;
    }

    public function setWindchillLink(?string $windchillLink): self
    {
        $this->windchillLink = $windchillLink;

        return $this;
    }

    public function getInformation(): ?string
    {
        return $this->information;
    }

    public function setInformation(?string $information): self
    {
        $this->information = $information;

        return $this;
    }

    public function getIsBigEndian(): ?bool
    {
        return $this->isBigEndian;
    }

    public function setIsBigEndian(bool $isBigEndian): self
    {
        $this->isBigEndian = $isBigEndian;

        return $this;
    }

    public function getDiagnosticIdentifier(): ?string
    {
        return $this->diagnosticIdentifier;
    }

    public function setDiagnosticIdentifier(?string $diagnosticIdentifier): self
    {
        $this->diagnosticIdentifier = $diagnosticIdentifier;

        return $this;
    }

    public function getSecureAccessProperties(): ?SecureAccessProperties
    {
        return $this->secureAccessProperties;
    }

    public function setSecureAccessProperties(?SecureAccessProperties $secureAccessProperties): self
    {
        $this->secureAccessProperties = $secureAccessProperties;

        return $this;
    }

    public function getEsvgpEcuSwVersion(): ?EcuSwVersions
    {
        return $this->esvgpEcuSwVersion;
    }

    public function setEsvgpEcuSwVersion(?EcuSwVersions $esvgpEcuSwVersion): self
    {
        $this->esvgpEcuSwVersion = $esvgpEcuSwVersion;

        return $this;
    }
}
