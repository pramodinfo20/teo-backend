<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TcuPackages
 *
 * @ORM\Table(name="tcu_packages", indexes={@ORM\Index(name="IDX_D6B3536858B200B6", columns={"update_type"})})
 * @ORM\Entity
 */
class TcuPackages
{
    /**
     * @var int
     *
     * @ORM\Column(name="package_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="tcu_packages_package_id_seq", allocationSize=1, initialValue=1)
     */
    private $packageId;

    /**
     * @var float|null
     *
     * @ORM\Column(name="min_version_required", type="float", precision=10, scale=0, nullable=true)
     */
    private $minVersionRequired;

    /**
     * @var float|null
     *
     * @ORM\Column(name="version", type="float", precision=10, scale=0, nullable=true)
     */
    private $version;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="release_date", type="datetimetz", nullable=true)
     */
    private $releaseDate;

    /**
     * @var TcuUpdateTypes
     *
     * @ORM\ManyToOne(targetEntity="TcuUpdateTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="update_type", referencedColumnName="update_type")
     * })
     */
    private $updateType;

    public function getPackageId(): ?int
    {
        return $this->packageId;
    }

    public function getMinVersionRequired(): ?float
    {
        return $this->minVersionRequired;
    }

    public function setMinVersionRequired(?float $minVersionRequired): self
    {
        $this->minVersionRequired = $minVersionRequired;

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

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(?\DateTimeInterface $releaseDate): self
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getUpdateType(): ?TcuUpdateTypes
    {
        return $this->updateType;
    }

    public function setUpdateType(?TcuUpdateTypes $updateType): self
    {
        $this->updateType = $updateType;

        return $this;
    }


}
