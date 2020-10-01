<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TempTeoStatus
 *
 * @ORM\Table(name="temp_teo_status")
 * @ORM\Entity
 */
class TempTeoStatus
{
    /**
     * @var int
     *
     * @ORM\Column(name="diagnostic_session_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="temp_teo_status_diagnostic_session_id_seq", allocationSize=1,
     *                                                                                  initialValue=1)
     */
    private $diagnosticSessionId;

    /**
     * @var string
     *
     * @ORM\Column(name="vin", type="text", nullable=false)
     */
    private $vin;

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

    public function getDiagnosticSessionId(): ?int
    {
        return $this->diagnosticSessionId;
    }

    public function getVin(): ?string
    {
        return $this->vin;
    }

    public function setVin(string $vin): self
    {
        $this->vin = $vin;

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


}
