<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TcuUpdate
 *
 * @ORM\Table(name="tcu_update", indexes={@ORM\Index(name="IDX_610C6284F44CABFF", columns={"package_id"})})
 * @ORM\Entity
 */
class TcuUpdate
{
    /**
     * @var int
     *
     * @ORM\Column(name="update_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="tcu_update_update_id_seq", allocationSize=1, initialValue=1)
     */
    private $updateId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="tcu_id", type="text", nullable=true)
     */
    private $tcuId;

    /**
     * @var float|null
     *
     * @ORM\Column(name="version", type="float", precision=10, scale=0, nullable=true)
     */
    private $version;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="success", type="boolean", nullable=true)
     */
    private $success = false;

    /**
     * @var string|null
     *
     * @ORM\Column(name="result_text", type="text", nullable=true)
     */
    private $resultText;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="timestamp_start", type="datetimetz", nullable=true, options={"default"="now()"})
     */
    private $timestampStart = 'now()';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="timestamp_end", type="datetimetz", nullable=true)
     */
    private $timestampEnd;

    /**
     * @var TcuPackages
     *
     * @ORM\ManyToOne(targetEntity="TcuPackages")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="package_id", referencedColumnName="package_id")
     * })
     */
    private $package;

    public function getUpdateId(): ?int
    {
        return $this->updateId;
    }

    public function getTcuId(): ?string
    {
        return $this->tcuId;
    }

    public function setTcuId(?string $tcuId): self
    {
        $this->tcuId = $tcuId;

        return $this;
    }

    public function getVersion(): ?float
    {
        return $this->version;
    }

    public function setVersion(?float $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getSuccess(): ?bool
    {
        return $this->success;
    }

    public function setSuccess(?bool $success): self
    {
        $this->success = $success;

        return $this;
    }

    public function getResultText(): ?string
    {
        return $this->resultText;
    }

    public function setResultText(?string $resultText): self
    {
        $this->resultText = $resultText;

        return $this;
    }

    public function getTimestampStart(): ?\DateTimeInterface
    {
        return $this->timestampStart;
    }

    public function setTimestampStart(?\DateTimeInterface $timestampStart): self
    {
        $this->timestampStart = $timestampStart;

        return $this;
    }

    public function getTimestampEnd(): ?\DateTimeInterface
    {
        return $this->timestampEnd;
    }

    public function setTimestampEnd(?\DateTimeInterface $timestampEnd): self
    {
        $this->timestampEnd = $timestampEnd;

        return $this;
    }

    public function getPackage(): ?TcuPackages
    {
        return $this->package;
    }

    public function setPackage(?TcuPackages $package): self
    {
        $this->package = $package;

        return $this;
    }


}
