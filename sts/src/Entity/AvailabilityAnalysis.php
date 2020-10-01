<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Analysis.availabilityAnalysis
 *
 * @ORM\Table(name="availability_analysis", schema="analysis",
 *                                          indexes={@ORM\Index(name="IDX_30F60BFF7E327543", columns={"availability_category_id"})})
 * @ORM\Entity
 */
class AvailabilityAnalysis
{
    /**
     * @var int
     *
     * @ORM\Column(name="vehicle_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $vehicleId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="day_of_analysis", type="date", nullable=false, options={"default"="now()"})
     */
    private $dayOfAnalysis = 'now()';

    /**
     * @var string|null
     *
     * @ORM\Column(name="info", type="text", nullable=true)
     */
    private $info;

    /**
     * @var Analysis.availabilityCategories
     *
     * @ORM\ManyToOne(targetEntity="AvailabilityCategories")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="availability_category_id", referencedColumnName="availability_category_id")
     * })
     */
    private $availabilityCategory;

    public function getVehicleId(): ?int
    {
        return $this->vehicleId;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function getDayOfAnalysis(): ?\DateTimeInterface
    {
        return $this->dayOfAnalysis;
    }

    public function setDayOfAnalysis(\DateTimeInterface $dayOfAnalysis): self
    {
        $this->dayOfAnalysis = $dayOfAnalysis;

        return $this;
    }

    public function getInfo(): ?string
    {
        return $this->info;
    }

    public function setInfo(?string $info): self
    {
        $this->info = $info;

        return $this;
    }

    public function getAvailabilityCategory(): ?AvailabilityCategories
    {
        return $this->availabilityCategory;
    }

    public function setAvailabilityCategory(?AvailabilityCategories $availabilityCategory): self
    {
        $this->availabilityCategory = $availabilityCategory;

        return $this;
    }


}
