<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Analysis.availabilityCategories
 *
 * @ORM\Table(name="availability_categories", schema="analysis",
 *                                            uniqueConstraints={@ORM\UniqueConstraint(name="availability_categories_availability_category_key", columns={"availability_category"})})
 * @ORM\Entity
 */
class AvailabilityCategories
{
    /**
     * @var int
     *
     * @ORM\Column(name="availability_category_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="Availability_categories_availability_category_id_seq", allocationSize=1,
     *                                                                                             initialValue=1)
     */
    private $availabilityCategoryId;

    /**
     * @var string
     *
     * @ORM\Column(name="availability_category", type="text", nullable=false)
     */
    private $availabilityCategory;

    /**
     * @var int
     *
     * @ORM\Column(name="ordering", type="integer", nullable=false, options={"comment"="Lower is better"})
     */
    private $ordering;

    /**
     * @var string|null
     *
     * @ORM\Column(name="color", type="text", nullable=true)
     */
    private $color;

    public function getAvailabilityCategoryId(): ?int
    {
        return $this->availabilityCategoryId;
    }

    public function getAvailabilityCategory(): ?string
    {
        return $this->availabilityCategory;
    }

    public function setAvailabilityCategory(string $availabilityCategory): self
    {
        $this->availabilityCategory = $availabilityCategory;

        return $this;
    }

    public function getOrdering(): ?int
    {
        return $this->ordering;
    }

    public function setOrdering(int $ordering): self
    {
        $this->ordering = $ordering;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }


}
