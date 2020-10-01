<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Units
 *
 * @ORM\Table(name="units")
 * @ORM\Entity
 */
class Units
{
    /**
     * @var int
     *
     * @ORM\Column(name="unit_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="units_unit_id_seq", allocationSize=1, initialValue=1)
     */
    private $unitId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="unit_name", type="text", nullable=false)
     */
    private $unitName;

    public function getUnitId(): ?int
    {
        return $this->unitId;
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

    public function getUnitName(): ?string
    {
        return $this->unitName;
    }

    public function setUnitName(string $unitName): self
    {
        $this->unitName = $unitName;

        return $this;
    }

    public function __toString()
    {
        return $this->unitName;
    }
}
