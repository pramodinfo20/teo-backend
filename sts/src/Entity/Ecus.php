<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ecus
 *
 * @ORM\Table(name="ecus")
 * @ORM\Entity
 */
class Ecus
{
    /**
     * @var int
     *
     * @ORM\Column(name="ecu_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ecus_ecu_id_seq", allocationSize=1, initialValue=1)
     */
    private $ecuId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="supports_odx02", type="boolean", nullable=false)
     */
    private $supportsOdx02 = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="big_endian", type="boolean", nullable=false)
     */
    private $bigEndian = false;

    public function getEcuId(): ?int
    {
        return $this->ecuId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSupportsOdx02(): ?bool
    {
        return $this->supportsOdx02;
    }

    public function setSupportsOdx02(bool $supportsOdx02): self
    {
        $this->supportsOdx02 = $supportsOdx02;

        return $this;
    }

    public function getBigEndian(): ?bool
    {
        return $this->bigEndian;
    }

    public function setBigEndian(bool $bigEndian): self
    {
        $this->bigEndian = $bigEndian;

        return $this;
    }


}
