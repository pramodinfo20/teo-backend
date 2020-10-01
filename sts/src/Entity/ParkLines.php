<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ParkLines
 *
 * @ORM\Table(name="park_lines", indexes={@ORM\Index(name="IDX_4DE4375253C55F64", columns={"map_id"})})
 * @ORM\Entity
 */
class ParkLines
{
    /**
     * @var int
     *
     * @ORM\Column(name="park_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="park_lines_park_id_seq", allocationSize=1, initialValue=1)
     */
    private $parkId;

    /**
     * @var string
     *
     * @ORM\Column(name="ident", type="string", length=8, nullable=false)
     */
    private $ident;

    /**
     * @var string|null
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var float|null
     *
     * @ORM\Column(name="from_lat", type="float", precision=10, scale=0, nullable=true)
     */
    private $fromLat;

    /**
     * @var float|null
     *
     * @ORM\Column(name="from_lon", type="float", precision=10, scale=0, nullable=true)
     */
    private $fromLon;

    /**
     * @var float|null
     *
     * @ORM\Column(name="to_lat", type="float", precision=10, scale=0, nullable=true)
     */
    private $toLat;

    /**
     * @var float|null
     *
     * @ORM\Column(name="to_lon", type="float", precision=10, scale=0, nullable=true)
     */
    private $toLon;

    /**
     * @var int
     *
     * @ORM\Column(name="num_positions", type="integer", nullable=false)
     */
    private $numPositions;

    /**
     * @var string
     *
     * @ORM\Column(name="first_pos_id", type="string", length=8, nullable=false)
     */
    private $firstPosId;

    /**
     * @var GpsMaps
     *
     * @ORM\ManyToOne(targetEntity="GpsMaps")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="map_id", referencedColumnName="map_id")
     * })
     */
    private $map;

    public function getParkId(): ?int
    {
        return $this->parkId;
    }

    public function getIdent(): ?string
    {
        return $this->ident;
    }

    public function setIdent(string $ident): self
    {
        $this->ident = $ident;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getFromLat(): ?float
    {
        return $this->fromLat;
    }

    public function setFromLat(?float $fromLat): self
    {
        $this->fromLat = $fromLat;

        return $this;
    }

    public function getFromLon(): ?float
    {
        return $this->fromLon;
    }

    public function setFromLon(?float $fromLon): self
    {
        $this->fromLon = $fromLon;

        return $this;
    }

    public function getToLat(): ?float
    {
        return $this->toLat;
    }

    public function setToLat(?float $toLat): self
    {
        $this->toLat = $toLat;

        return $this;
    }

    public function getToLon(): ?float
    {
        return $this->toLon;
    }

    public function setToLon(?float $toLon): self
    {
        $this->toLon = $toLon;

        return $this;
    }

    public function getNumPositions(): ?int
    {
        return $this->numPositions;
    }

    public function setNumPositions(int $numPositions): self
    {
        $this->numPositions = $numPositions;

        return $this;
    }

    public function getFirstPosId(): ?string
    {
        return $this->firstPosId;
    }

    public function setFirstPosId(string $firstPosId): self
    {
        $this->firstPosId = $firstPosId;

        return $this;
    }

    public function getMap(): ?GpsMaps
    {
        return $this->map;
    }

    public function setMap(?GpsMaps $map): self
    {
        $this->map = $map;

        return $this;
    }


}
