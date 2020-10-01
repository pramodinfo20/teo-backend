<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DeliveryToDivisions
 *
 * @ORM\Table(name="delivery_to_divisions")
 * @ORM\Entity
 */
class DeliveryToDivisions
{
    /**
     * @var int
     *
     * @ORM\Column(name="delivery_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="delivery_to_divisions_delivery_id_seq", allocationSize=1, initialValue=1)
     */
    private $deliveryId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="division_id", type="integer", nullable=true)
     */
    private $divisionId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="variant_value", type="integer", nullable=true)
     */
    private $variantValue;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="added_timestamp", type="datetimetz", nullable=true)
     */
    private $addedTimestamp;

    /**
     * @var int|null
     *
     * @ORM\Column(name="vehicles_delivered_quantity", type="integer", nullable=true)
     */
    private $vehiclesDeliveredQuantity = '0';

    /**
     * @var int|null
     *
     * @ORM\Column(name="delivery_quantity", type="integer", nullable=true)
     */
    private $deliveryQuantity;

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
     * @ORM\Column(name="priority", type="integer", nullable=true)
     */
    private $priority;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vehicles_delivered", type="text", nullable=true)
     */
    private $vehiclesDelivered;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="delivery_notification_email_sent", type="boolean", nullable=true)
     */
    private $deliveryNotificationEmailSent = false;

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

    public function getVariantValue(): ?int
    {
        return $this->variantValue;
    }

    public function setVariantValue(?int $variantValue): self
    {
        $this->variantValue = $variantValue;

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

    public function getVehiclesDeliveredQuantity(): ?int
    {
        return $this->vehiclesDeliveredQuantity;
    }

    public function setVehiclesDeliveredQuantity(?int $vehiclesDeliveredQuantity): self
    {
        $this->vehiclesDeliveredQuantity = $vehiclesDeliveredQuantity;

        return $this;
    }

    public function getDeliveryQuantity(): ?int
    {
        return $this->deliveryQuantity;
    }

    public function setDeliveryQuantity(?int $deliveryQuantity): self
    {
        $this->deliveryQuantity = $deliveryQuantity;

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

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getVehiclesDelivered(): ?string
    {
        return $this->vehiclesDelivered;
    }

    public function setVehiclesDelivered(?string $vehiclesDelivered): self
    {
        $this->vehiclesDelivered = $vehiclesDelivered;

        return $this;
    }

    public function getDeliveryNotificationEmailSent(): ?bool
    {
        return $this->deliveryNotificationEmailSent;
    }

    public function setDeliveryNotificationEmailSent(?bool $deliveryNotificationEmailSent): self
    {
        $this->deliveryNotificationEmailSent = $deliveryNotificationEmailSent;

        return $this;
    }


}
