<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VehiclesFailedVins
 *
 * @ORM\Table(name="vehicles_failed_vins")
 * @ORM\Entity
 */
class VehiclesFailedVins
{
    /**
     * @var string
     *
     * @ORM\Column(name="c2cbox", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="vehicles_failed_vins_c2cbox_seq", allocationSize=1, initialValue=1)
     */
    private $c2cbox;

    /**
     * @var string
     *
     * @ORM\Column(name="vin", type="text", nullable=false)
     */
    private $vin;

    /**
     * @var int|null
     *
     * @ORM\Column(name="timestamp", type="integer", nullable=true)
     */
    private $timestamp;

    public function getC2cbox(): ?string
    {
        return $this->c2cbox;
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

    public function getTimestamp(): ?int
    {
        return $this->timestamp;
    }

    public function setTimestamp(?int $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }


}
