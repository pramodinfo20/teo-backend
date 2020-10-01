<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AixacctTemp
 *
 * @ORM\Table(name="aixacct_temp")
 * @ORM\Entity
 */
class AixacctTemp
{
    /**
     * @var int
     *
     * @ORM\Column(name="aixacct_temp_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="aixacct_temp_aixacct_temp_id_seq", allocationSize=1, initialValue=1)
     */
    private $aixacctTempId;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="text", nullable=false)
     */
    private $code;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vin", type="text", nullable=true)
     */
    private $vin;

    /**
     * @var string
     *
     * @ORM\Column(name="c2cbox", type="text", nullable=false)
     */
    private $c2cbox;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="ok", type="boolean", nullable=true)
     */
    private $ok;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vin_bcm", type="text", nullable=true)
     */
    private $vinBcm;

    /**
     * @var string|null
     *
     * @ORM\Column(name="real_c2cbox", type="text", nullable=true)
     */
    private $realC2cbox;

    public function getAixacctTempId(): ?int
    {
        return $this->aixacctTempId;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getVin(): ?string
    {
        return $this->vin;
    }

    public function setVin(?string $vin): self
    {
        $this->vin = $vin;

        return $this;
    }

    public function getC2cbox(): ?string
    {
        return $this->c2cbox;
    }

    public function setC2cbox(string $c2cbox): self
    {
        $this->c2cbox = $c2cbox;

        return $this;
    }

    public function getOk(): ?bool
    {
        return $this->ok;
    }

    public function setOk(?bool $ok): self
    {
        $this->ok = $ok;

        return $this;
    }

    public function getVinBcm(): ?string
    {
        return $this->vinBcm;
    }

    public function setVinBcm(?string $vinBcm): self
    {
        $this->vinBcm = $vinBcm;

        return $this;
    }

    public function getRealC2cbox(): ?string
    {
        return $this->realC2cbox;
    }

    public function setRealC2cbox(?string $realC2cbox): self
    {
        $this->realC2cbox = $realC2cbox;

        return $this;
    }


}
