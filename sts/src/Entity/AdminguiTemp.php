<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminguiTemp
 *
 * @ORM\Table(name="admingui_temp")
 * @ORM\Entity
 */
class AdminguiTemp
{
    /**
     * @var int
     *
     * @ORM\Column(name="admingui_temp_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="admingui_temp_admingui_temp_id_seq", allocationSize=1, initialValue=1)
     */
    private $adminguiTempId;

    /**
     * @var string
     *
     * @ORM\Column(name="c2cbox", type="text", nullable=false)
     */
    private $c2cbox;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vin", type="text", nullable=true)
     */
    private $vin;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="online", type="boolean", nullable=true)
     */
    private $online;

    public function getAdminguiTempId(): ?int
    {
        return $this->adminguiTempId;
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

    public function getVin(): ?string
    {
        return $this->vin;
    }

    public function setVin(?string $vin): self
    {
        $this->vin = $vin;

        return $this;
    }

    public function getOnline(): ?bool
    {
        return $this->online;
    }

    public function setOnline(?bool $online): self
    {
        $this->online = $online;

        return $this;
    }


}
