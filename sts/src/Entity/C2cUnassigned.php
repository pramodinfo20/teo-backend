<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * C2cUnassigned
 *
 * @ORM\Table(name="c2c_unassigned")
 * @ORM\Entity
 */
class C2cUnassigned
{
    /**
     * @var string
     *
     * @ORM\Column(name="c2cbox", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="c2c_unassigned_c2cbox_seq", allocationSize=1, initialValue=1)
     */
    private $c2cbox;

    /**
     * @var float|null
     *
     * @ORM\Column(name="lat", type="float", precision=10, scale=0, nullable=true)
     */
    private $lat;

    /**
     * @var float|null
     *
     * @ORM\Column(name="lon", type="float", precision=10, scale=0, nullable=true)
     */
    private $lon;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="gps_timestamp", type="datetimetz", nullable=true)
     */
    private $gpsTimestamp;

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

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="online_timestamp", type="datetimetz", nullable=true)
     */
    private $onlineTimestamp;

    public function getC2cbox(): ?string
    {
        return $this->c2cbox;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(?float $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLon(): ?float
    {
        return $this->lon;
    }

    public function setLon(?float $lon): self
    {
        $this->lon = $lon;

        return $this;
    }

    public function getGpsTimestamp(): ?\DateTimeInterface
    {
        return $this->gpsTimestamp;
    }

    public function setGpsTimestamp(?\DateTimeInterface $gpsTimestamp): self
    {
        $this->gpsTimestamp = $gpsTimestamp;

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

    public function getOnlineTimestamp(): ?\DateTimeInterface
    {
        return $this->onlineTimestamp;
    }

    public function setOnlineTimestamp(?\DateTimeInterface $onlineTimestamp): self
    {
        $this->onlineTimestamp = $onlineTimestamp;

        return $this;
    }


}
