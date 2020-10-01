<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DeliveryPlanWeek
 *
 * @ORM\Table(name="delivery_plan_week")
 * @ORM\Entity
 */
class DeliveryPlanWeek
{
    /**
     * @var int
     *
     * @ORM\Column(name="delivery_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="delivery_plan_week_delivery_id_seq", allocationSize=1, initialValue=1)
     */
    private $deliveryId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="division_id", type="integer", nullable=true)
     */
    private $divisionId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="delivery_week", type="text", nullable=true)
     */
    private $deliveryWeek;

    /**
     * @var int|null
     *
     * @ORM\Column(name="delivery_year", type="integer", nullable=true)
     */
    private $deliveryYear;

    /**
     * @var int|null
     *
     * @ORM\Column(name="variant", type="integer", nullable=true)
     */
    private $variant;

    /**
     * @var int|null
     *
     * @ORM\Column(name="quantity", type="integer", nullable=true)
     */
    private $quantity;

    public function getDeliveryId(): ?int
    {
        return $this->deliveryId;
    }

    public function getDivisionId(): ?int
    {
        return $this->divisionId;
    }

    public function setDivisionId(?int $divisionId): self
    {
        $this->divisionId = $divisionId;

        return $this;
    }

    public function getDeliveryWeek(): ?string
    {
        return $this->deliveryWeek;
    }

    public function setDeliveryWeek(?string $deliveryWeek): self
    {
        $this->deliveryWeek = $deliveryWeek;

        return $this;
    }

    public function getDeliveryYear(): ?int
    {
        return $this->deliveryYear;
    }

    public function setDeliveryYear(?int $deliveryYear): self
    {
        $this->deliveryYear = $deliveryYear;

        return $this;
    }

    public function getVariant(): ?int
    {
        return $this->variant;
    }

    public function setVariant(?int $variant): self
    {
        $this->variant = $variant;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }


}
