<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PostVehicleOrderPositions
 *
 * @ORM\Table(name="post_vehicle_order_positions", indexes={@ORM\Index(name="IDX_F38487158D9F6D38",
 *                                                 columns={"order_id"}), @ORM\Index(name="IDX_F38487154003CBC4",
 *                                                 columns={"delivered_vehicle_id"})})
 * @ORM\Entity
 */
class PostVehicleOrderPositions
{
    /**
     * @var int
     *
     * @ORM\Column(name="position_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="post_vehicle_order_positions_position_id_seq", allocationSize=1,
     *                                                                                     initialValue=1)
     */
    private $positionId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="depot_id", type="integer", nullable=true)
     */
    private $depotId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="station_id", type="integer", nullable=true)
     */
    private $stationId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="variant_type", type="string", length=3, nullable=true, options={"fixed"=true})
     */
    private $variantType;

    /**
     * @var int|null
     *
     * @ORM\Column(name="variant_num_seats", type="integer", nullable=true)
     */
    private $variantNumSeats;

    /**
     * @var string|null
     *
     * @ORM\Column(name="variant_battery", type="string", length=8, nullable=true)
     */
    private $variantBattery;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="delivery_datetime", type="datetime", nullable=true)
     */
    private $deliveryDatetime;

    /**
     * @var PostVehicleOrder
     *
     * @ORM\ManyToOne(targetEntity="PostVehicleOrder")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="order_id", referencedColumnName="order_id")
     * })
     */
    private $order;

    /**
     * @var Vehicles
     *
     * @ORM\ManyToOne(targetEntity="Vehicles")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="delivered_vehicle_id", referencedColumnName="vehicle_id")
     * })
     */
    private $deliveredVehicle;

    public function getPositionId(): ?int
    {
        return $this->positionId;
    }

    public function getDepotId(): ?int
    {
        return $this->depotId;
    }

    public function setDepotId(?int $depotId): self
    {
        $this->depotId = $depotId;

        return $this;
    }

    public function getStationId(): ?int
    {
        return $this->stationId;
    }

    public function setStationId(?int $stationId): self
    {
        $this->stationId = $stationId;

        return $this;
    }

    public function getVariantType(): ?string
    {
        return $this->variantType;
    }

    public function setVariantType(?string $variantType): self
    {
        $this->variantType = $variantType;

        return $this;
    }

    public function getVariantNumSeats(): ?int
    {
        return $this->variantNumSeats;
    }

    public function setVariantNumSeats(?int $variantNumSeats): self
    {
        $this->variantNumSeats = $variantNumSeats;

        return $this;
    }

    public function getVariantBattery(): ?string
    {
        return $this->variantBattery;
    }

    public function setVariantBattery(?string $variantBattery): self
    {
        $this->variantBattery = $variantBattery;

        return $this;
    }

    public function getDeliveryDatetime(): ?\DateTimeInterface
    {
        return $this->deliveryDatetime;
    }

    public function setDeliveryDatetime(?\DateTimeInterface $deliveryDatetime): self
    {
        $this->deliveryDatetime = $deliveryDatetime;

        return $this;
    }

    public function getOrder(): ?PostVehicleOrder
    {
        return $this->order;
    }

    public function setOrder(?PostVehicleOrder $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getDeliveredVehicle(): ?Vehicles
    {
        return $this->deliveredVehicle;
    }

    public function setDeliveredVehicle(?Vehicles $deliveredVehicle): self
    {
        $this->deliveredVehicle = $deliveredVehicle;

        return $this;
    }


}
