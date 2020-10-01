<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WorkshopDelivery
 *
 * @ORM\Table(name="workshop_delivery")
 * @ORM\Entity
 */
class WorkshopDelivery
{
    /**
     * @var int
     *
     * @ORM\Column(name="workshop_delivery_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="workshop_delivery_workshop_delivery_id_seq", allocationSize=1,
     *                                                                                   initialValue=1)
     */
    private $workshopDeliveryId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="vehicle_id", type="integer", nullable=true)
     */
    private $vehicleId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="workshop_id", type="integer", nullable=true)
     */
    private $workshopId;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="delivery_date", type="datetimetz", nullable=true)
     */
    private $deliveryDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="update_timestamp", type="datetimetz", nullable=true)
     */
    private $updateTimestamp;

    public function getWorkshopDeliveryId(): ?int
    {
        return $this->workshopDeliveryId;
    }

    public function getVehicleId(): ?int
    {
        return $this->vehicleId;
    }

    public function setVehicleId(?int $vehicleId): self
    {
        $this->vehicleId = $vehicleId;

        return $this;
    }

    public function getWorkshopId(): ?int
    {
        return $this->workshopId;
    }

    public function setWorkshopId(?int $workshopId): self
    {
        $this->workshopId = $workshopId;

        return $this;
    }

    public function getDeliveryDate(): ?\DateTimeInterface
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(?\DateTimeInterface $deliveryDate): self
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    public function getUpdateTimestamp(): ?\DateTimeInterface
    {
        return $this->updateTimestamp;
    }

    public function setUpdateTimestamp(?\DateTimeInterface $updateTimestamp): self
    {
        $this->updateTimestamp = $updateTimestamp;

        return $this;
    }


}
