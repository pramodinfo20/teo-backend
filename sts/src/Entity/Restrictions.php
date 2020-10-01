<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Restrictions
 *
 * @ORM\Table(name="restrictions", indexes={@ORM\Index(name="IDX_67A8B927F8CB723A", columns={"parent_restriction_id"})})
 * @ORM\Entity
 */
class Restrictions
{
    /**
     * @var int
     *
     * @ORM\Column(name="restriction_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="restrictions_restriction_id_seq", allocationSize=1, initialValue=1)
     */
    private $restrictionId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="power", type="integer", nullable=false)
     */
    private $power;

    /**
     * @var string|null
     *
     * @ORM\Column(name="wiring_type", type="string", nullable=true)
     */
    private $wiringType;

    /**
     * @var bool
     *
     * @ORM\Column(name="trenner", type="boolean", nullable=false)
     */
    private $trenner = false;

    /**
     * @var float|null
     *
     * @ORM\Column(name="original_power", type="float", precision=10, scale=0, nullable=true)
     */
    private $originalPower;

    /**
     * @var Restrictions
     *
     * @ORM\ManyToOne(targetEntity="Restrictions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_restriction_id", referencedColumnName="restriction_id")
     * })
     */
    private $parentRestriction;

    public function getRestrictionId(): ?int
    {
        return $this->restrictionId;
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

    public function getPower(): ?int
    {
        return $this->power;
    }

    public function setPower(int $power): self
    {
        $this->power = $power;

        return $this;
    }

    public function getWiringType(): ?string
    {
        return $this->wiringType;
    }

    public function setWiringType(?string $wiringType): self
    {
        $this->wiringType = $wiringType;

        return $this;
    }

    public function getTrenner(): ?bool
    {
        return $this->trenner;
    }

    public function setTrenner(bool $trenner): self
    {
        $this->trenner = $trenner;

        return $this;
    }

    public function getOriginalPower(): ?float
    {
        return $this->originalPower;
    }

    public function setOriginalPower(?float $originalPower): self
    {
        $this->originalPower = $originalPower;

        return $this;
    }

    public function getParentRestriction(): ?self
    {
        return $this->parentRestriction;
    }

    public function setParentRestriction(?self $parentRestriction): self
    {
        $this->parentRestriction = $parentRestriction;

        return $this;
    }


}
