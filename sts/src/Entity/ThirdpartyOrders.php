<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ThirdpartyOrders
 *
 * @ORM\Table(name="thirdparty_orders")
 * @ORM\Entity
 */
class ThirdpartyOrders
{
    /**
     * @var int
     *
     * @ORM\Column(name="order_num", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="thirdparty_orders_order_num_seq", allocationSize=1, initialValue=1)
     */
    private $orderNum;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="delivery_date", type="date", nullable=true)
     */
    private $deliveryDate;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vehicle_variant_label", type="text", nullable=true)
     */
    private $vehicleVariantLabel;

    /**
     * @var int|null
     *
     * @ORM\Column(name="depot_id", type="integer", nullable=true)
     */
    private $depotId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="pr_contact", type="text", nullable=true)
     */
    private $prContact;

    /**
     * @var string|null
     *
     * @ORM\Column(name="pr_tel", type="text", nullable=true)
     */
    private $prTel;

    /**
     * @var int|null
     *
     * @ORM\Column(name="penta_folge_id", type="integer", nullable=true)
     */
    private $pentaFolgeId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="vehicle_delivered", type="integer", nullable=true)
     */
    private $vehicleDelivered;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vehicle_color", type="text", nullable=true)
     */
    private $vehicleColor;

    public function getOrderNum(): ?int
    {
        return $this->orderNum;
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

    public function getVehicleVariantLabel(): ?string
    {
        return $this->vehicleVariantLabel;
    }

    public function setVehicleVariantLabel(?string $vehicleVariantLabel): self
    {
        $this->vehicleVariantLabel = $vehicleVariantLabel;

        return $this;
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

    public function getPrContact(): ?string
    {
        return $this->prContact;
    }

    public function setPrContact(?string $prContact): self
    {
        $this->prContact = $prContact;

        return $this;
    }

    public function getPrTel(): ?string
    {
        return $this->prTel;
    }

    public function setPrTel(?string $prTel): self
    {
        $this->prTel = $prTel;

        return $this;
    }

    public function getPentaFolgeId(): ?int
    {
        return $this->pentaFolgeId;
    }

    public function setPentaFolgeId(?int $pentaFolgeId): self
    {
        $this->pentaFolgeId = $pentaFolgeId;

        return $this;
    }

    public function getVehicleDelivered(): ?int
    {
        return $this->vehicleDelivered;
    }

    public function setVehicleDelivered(?int $vehicleDelivered): self
    {
        $this->vehicleDelivered = $vehicleDelivered;

        return $this;
    }

    public function getVehicleColor(): ?string
    {
        return $this->vehicleColor;
    }

    public function setVehicleColor(?string $vehicleColor): self
    {
        $this->vehicleColor = $vehicleColor;

        return $this;
    }


}
