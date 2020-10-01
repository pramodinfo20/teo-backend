<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProcessedTeoStatus
 *
 * @ORM\Table(name="processed_teo_status")
 * @ORM\Entity
 */
class ProcessedTeoStatus
{
    /**
     * @var int
     *
     * @ORM\Column(name="diagnostic_session_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="processed_teo_status_diagnostic_session_id_seq", allocationSize=1,
     *                                                                                       initialValue=1)
     */
    private $diagnosticSessionId;

    /**
     * @var string
     *
     * @ORM\Column(name="teo_vin", type="text", nullable=false)
     */
    private $teoVin;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="diagnose_status_time", type="datetimetz", nullable=false)
     */
    private $diagnoseStatusTime;

    /**
     * @var string
     *
     * @ORM\Column(name="diagnose_status", type="text", nullable=false)
     */
    private $diagnoseStatus;

    /**
     * @var int
     *
     * @ORM\Column(name="vehicle_variant_id", type="integer", nullable=false)
     */
    private $vehicleVariantId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="penta_number_id", type="integer", nullable=true)
     */
    private $pentaNumberId;

    /**
     * @var string
     *
     * @ORM\Column(name="processed_diagnose_status", type="text", nullable=false)
     */
    private $processedDiagnoseStatus;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="targetsw_changed", type="boolean", nullable=true)
     */
    private $targetswChanged = false;

    public function getDiagnosticSessionId(): ?int
    {
        return $this->diagnosticSessionId;
    }

    public function getTeoVin(): ?string
    {
        return $this->teoVin;
    }

    public function setTeoVin(string $teoVin): self
    {
        $this->teoVin = $teoVin;

        return $this;
    }

    public function getDiagnoseStatusTime(): ?\DateTimeInterface
    {
        return $this->diagnoseStatusTime;
    }

    public function setDiagnoseStatusTime(\DateTimeInterface $diagnoseStatusTime): self
    {
        $this->diagnoseStatusTime = $diagnoseStatusTime;

        return $this;
    }

    public function getDiagnoseStatus(): ?string
    {
        return $this->diagnoseStatus;
    }

    public function setDiagnoseStatus(string $diagnoseStatus): self
    {
        $this->diagnoseStatus = $diagnoseStatus;

        return $this;
    }

    public function getVehicleVariantId(): ?int
    {
        return $this->vehicleVariantId;
    }

    public function setVehicleVariantId(int $vehicleVariantId): self
    {
        $this->vehicleVariantId = $vehicleVariantId;

        return $this;
    }

    public function getPentaNumberId(): ?int
    {
        return $this->pentaNumberId;
    }

    public function setPentaNumberId(?int $pentaNumberId): self
    {
        $this->pentaNumberId = $pentaNumberId;

        return $this;
    }

    public function getProcessedDiagnoseStatus(): ?string
    {
        return $this->processedDiagnoseStatus;
    }

    public function setProcessedDiagnoseStatus(string $processedDiagnoseStatus): self
    {
        $this->processedDiagnoseStatus = $processedDiagnoseStatus;

        return $this;
    }

    public function getTargetswChanged(): ?bool
    {
        return $this->targetswChanged;
    }

    public function setTargetswChanged(?bool $targetswChanged): self
    {
        $this->targetswChanged = $targetswChanged;

        return $this;
    }


}
