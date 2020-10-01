<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Lukas2
 *
 * @ORM\Table(name="lukas2")
 * @ORM\Entity
 */
class Lukas2
{
    /**
     * @var int
     *
     * @ORM\Column(name="lukas2_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="lukas2_lukas2_id_seq", allocationSize=1, initialValue=1)
     */
    private $lukas2Id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="zsp", type="text", nullable=true)
     */
    private $zsp;

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

    public function getLukas2Id(): ?int
    {
        return $this->lukas2Id;
    }

    public function getZsp(): ?string
    {
        return $this->zsp;
    }

    public function setZsp(?string $zsp): self
    {
        $this->zsp = $zsp;

        return $this;
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


}
