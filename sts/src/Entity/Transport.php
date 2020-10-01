<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Transport
 *
 * @ORM\Table(name="transport", indexes={@ORM\Index(name="IDX_66AB212EA036E2D4", columns={"transporter"}),
 *                              @ORM\Index(name="IDX_66AB212E602D5E40", columns={"super_type"})})
 * @ORM\Entity
 */
class Transport
{
    /**
     * @var int
     *
     * @ORM\Column(name="bundle_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $bundleId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="count", type="integer", nullable=true)
     */
    private $count;

    /**
     * @var Transporter
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Transporter")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transporter", referencedColumnName="transporter_id")
     * })
     */
    private $transporter;

    /**
     * @var SuperTypes
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="SuperTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="super_type", referencedColumnName="super_type_id")
     * })
     */
    private $superType;

    public function getBundleId(): ?int
    {
        return $this->bundleId;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(?int $count): self
    {
        $this->count = $count;

        return $this;
    }

    public function getTransporter(): ?Transporter
    {
        return $this->transporter;
    }

    public function setTransporter(?Transporter $transporter): self
    {
        $this->transporter = $transporter;

        return $this;
    }

    public function getSuperType(): ?SuperTypes
    {
        return $this->superType;
    }

    public function setSuperType(?SuperTypes $superType): self
    {
        $this->superType = $superType;

        return $this;
    }


}
