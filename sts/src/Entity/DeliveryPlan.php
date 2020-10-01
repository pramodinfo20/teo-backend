<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DeliveryPlan
 *
 * @ORM\Table(name="delivery_plan")
 * @ORM\Entity
 */
class DeliveryPlan
{
    /**
     * @var int
     *
     * @ORM\Column(name="delivery_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="delivery_plan_delivery_id_seq", allocationSize=1, initialValue=1)
     */
    private $deliveryId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="division_id", type="integer", nullable=true)
     */
    private $divisionId;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="yearmonth", type="date", nullable=true)
     */
    private $yearmonth;

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

    /**
     * @var bool|null
     *
     * @ORM\Column(name="processed_status", type="boolean", nullable=true)
     */
    private $processedStatus = false;

    /**
     * @var int|null
     *
     * @ORM\Column(name="requirement_met", type="integer", nullable=true)
     */
    private $requirementMet = '0';

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

    public function getYearmonth(): ?\DateTimeInterface
    {
        return $this->yearmonth;
    }

    public function setYearmonth(?\DateTimeInterface $yearmonth): self
    {
        $this->yearmonth = $yearmonth;

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

    public function getProcessedStatus(): ?bool
    {
        return $this->processedStatus;
    }

    public function setProcessedStatus(?bool $processedStatus): self
    {
        $this->processedStatus = $processedStatus;

        return $this;
    }

    public function getRequirementMet(): ?int
    {
        return $this->requirementMet;
    }

    public function setRequirementMet(?int $requirementMet): self
    {
        $this->requirementMet = $requirementMet;

        return $this;
    }


}
