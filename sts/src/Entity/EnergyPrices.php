<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EnergyPrices
 *
 * @ORM\Table(name="energy_prices", indexes={@ORM\Index(name="IDX_57DCC748510D4DE", columns={"depot_id"})})
 * @ORM\Entity
 */
class EnergyPrices
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time_from", type="time", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $timeFrom;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="time_to", type="time", nullable=true)
     */
    private $timeTo;

    /**
     * @var float|null
     *
     * @ORM\Column(name="price", type="float", precision=10, scale=0, nullable=true)
     */
    private $price;

    /**
     * @var Depots
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Depots")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="depot_id", referencedColumnName="depot_id")
     * })
     */
    private $depot;

    public function getTimeFrom(): ?\DateTimeInterface
    {
        return $this->timeFrom;
    }

    public function getTimeTo(): ?\DateTimeInterface
    {
        return $this->timeTo;
    }

    public function setTimeTo(?\DateTimeInterface $timeTo): self
    {
        $this->timeTo = $timeTo;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getDepot(): ?Depots
    {
        return $this->depot;
    }

    public function setDepot(?Depots $depot): self
    {
        $this->depot = $depot;

        return $this;
    }


}
