<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * C2cSoftware
 *
 * @ORM\Table(name="c2c_software", indexes={@ORM\Index(name="IDX_D1D60C383A38FE74", columns={"c2cbox"})})
 * @ORM\Entity
 */
class C2cSoftware
{
    /**
     * @var string
     *
     * @ORM\Column(name="package", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $package;

    /**
     * @var string
     *
     * @ORM\Column(name="version", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $version;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="installation_date", type="datetimetz", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $installationDate;

    /**
     * @var C2cConfiguration
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="C2cConfiguration")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="c2cbox", referencedColumnName="c2cbox")
     * })
     */
    private $c2cbox;

    public function getPackage(): ?string
    {
        return $this->package;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function getInstallationDate(): ?\DateTimeInterface
    {
        return $this->installationDate;
    }

    public function getC2cbox(): ?C2cConfiguration
    {
        return $this->c2cbox;
    }

    public function setC2cbox(?C2cConfiguration $c2cbox): self
    {
        $this->c2cbox = $c2cbox;

        return $this;
    }


}
