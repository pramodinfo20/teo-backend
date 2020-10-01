<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VehiclesPost
 *
 * @ORM\Table(name="vehicles_post")
 * @ORM\Entity
 */
class VehiclesPost
{
    /**
     * @var int
     *
     * @ORM\Column(name="tempid", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="vehicles_post_tempid_seq", allocationSize=1, initialValue=1)
     */
    private $tempid;

    /**
     * @var string|null
     *
     * @ORM\Column(name="startikz", type="text", nullable=true)
     */
    private $startikz;

    /**
     * @var string|null
     *
     * @ORM\Column(name="startakz", type="text", nullable=true)
     */
    private $startakz;

    /**
     * @var int|null
     *
     * @ORM\Column(name="cntvehicles", type="integer", nullable=true)
     */
    private $cntvehicles;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="added_timestamp", type="datetimetz", nullable=true)
     */
    private $addedTimestamp;

    /**
     * @var int|null
     *
     * @ORM\Column(name="addedby_userid", type="integer", nullable=true)
     */
    private $addedbyUserid;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vorhaben", type="text", nullable=true)
     */
    private $vorhaben;

    /**
     * @var int|null
     *
     * @ORM\Column(name="vehicle_variant", type="integer", nullable=true)
     */
    private $vehicleVariant;

    /**
     * @var string|null
     *
     * @ORM\Column(name="tsnumber", type="text", nullable=true)
     */
    private $tsnumber;

    public function getTempid(): ?int
    {
        return $this->tempid;
    }

    public function getStartikz(): ?string
    {
        return $this->startikz;
    }

    public function setStartikz(?string $startikz): self
    {
        $this->startikz = $startikz;

        return $this;
    }

    public function getStartakz(): ?string
    {
        return $this->startakz;
    }

    public function setStartakz(?string $startakz): self
    {
        $this->startakz = $startakz;

        return $this;
    }

    public function getCntvehicles(): ?int
    {
        return $this->cntvehicles;
    }

    public function setCntvehicles(?int $cntvehicles): self
    {
        $this->cntvehicles = $cntvehicles;

        return $this;
    }

    public function getAddedTimestamp(): ?\DateTimeInterface
    {
        return $this->addedTimestamp;
    }

    public function setAddedTimestamp(?\DateTimeInterface $addedTimestamp): self
    {
        $this->addedTimestamp = $addedTimestamp;

        return $this;
    }

    public function getAddedbyUserid(): ?int
    {
        return $this->addedbyUserid;
    }

    public function setAddedbyUserid(?int $addedbyUserid): self
    {
        $this->addedbyUserid = $addedbyUserid;

        return $this;
    }

    public function getVorhaben(): ?string
    {
        return $this->vorhaben;
    }

    public function setVorhaben(?string $vorhaben): self
    {
        $this->vorhaben = $vorhaben;

        return $this;
    }

    public function getVehicleVariant(): ?int
    {
        return $this->vehicleVariant;
    }

    public function setVehicleVariant(?int $vehicleVariant): self
    {
        $this->vehicleVariant = $vehicleVariant;

        return $this;
    }

    public function getTsnumber(): ?string
    {
        return $this->tsnumber;
    }

    public function setTsnumber(?string $tsnumber): self
    {
        $this->tsnumber = $tsnumber;

        return $this;
    }


}
