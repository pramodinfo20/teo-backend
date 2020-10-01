<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VehiclesSales
 *
 * @ORM\Table(name="vehicles_sales")
 * @ORM\Entity
 */
class VehiclesSales
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="tsnumber", type="text", nullable=true)
     */
    private $tsnumber;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="delivery_date", type="datetimetz", nullable=true)
     */
    private $deliveryDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="production_date", type="datetimetz", nullable=true)
     */
    private $productionDate;

    /**
     * @var string|null
     *
     * @ORM\Column(name="delivery_week", type="text", nullable=true)
     */
    private $deliveryWeek;

    /**
     * @var string|null
     *
     * @ORM\Column(name="coc", type="text", nullable=true)
     */
    private $coc;

    /**
     * @var string|null
     *
     * @ORM\Column(name="vorhaben", type="text", nullable=true)
     */
    private $vorhaben;

    /**
     * @var string|null
     *
     * @ORM\Column(name="aib", type="text", nullable=true)
     */
    private $aib;

    /**
     * @var string|null
     *
     * @ORM\Column(name="kostenstelle", type="text", nullable=true)
     */
    private $kostenstelle;

    /**
     * @var string|null
     *
     * @ORM\Column(name="bill_number", type="text", nullable=true)
     */
    private $billNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ikz", type="text", nullable=true)
     */
    private $ikz;

    /**
     * @var string|null
     *
     * @ORM\Column(name="akz", type="text", nullable=true)
     */
    private $akz;

    /**
     * @var string|null
     *
     * @ORM\Column(name="production_week", type="text", nullable=true)
     */
    private $productionWeek;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="added_timestamp", type="datetimetz", nullable=true)
     */
    private $addedTimestamp;

    /**
     * @var int|null
     *
     * @ORM\Column(name="vehicle_variant", type="integer", nullable=true)
     */
    private $vehicleVariant;

    /**
     * @var string|null
     *
     * @ORM\Column(name="qs_user", type="text", nullable=true)
     */
    private $qsUser;

    /**
     * @var string|null
     *
     * @ORM\Column(name="comments", type="text", nullable=true)
     */
    private $comments;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="delivery_status", type="boolean", nullable=true)
     */
    private $deliveryStatus;

    /**
     * @var int|null
     *
     * @ORM\Column(name="production_location", type="integer", nullable=true)
     */
    private $productionLocation;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_dp_order", type="boolean", nullable=false)
     */
    private $isDpOrder = false;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="shipping_date", type="datetimetz", nullable=true)
     */
    private $shippingDate;

    /**
     * @var int|null
     *
     * @ORM\Column(name="coc_year", type="integer", nullable=true)
     */
    private $cocYear;

    /**
     * @var Vehicles
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Vehicles")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_id", referencedColumnName="vehicle_id")
     * })
     */
    private $vehicle;

    public function getTsnumber(): ?string
    {
        return $this->tsnumber;
    }

    public function setTsnumber(?string $tsnumber): self
    {
        $this->tsnumber = $tsnumber;

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

    public function getProductionDate(): ?\DateTimeInterface
    {
        return $this->productionDate;
    }

    public function setProductionDate(?\DateTimeInterface $productionDate): self
    {
        $this->productionDate = $productionDate;

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

    public function getCoc(): ?string
    {
        return $this->coc;
    }

    public function setCoc(?string $coc): self
    {
        $this->coc = $coc;

        return $this;
    }

    public function getVorhaben(): ?string
    {
        return $this->vorhaben;
    }

    public function setVorhaben(?string $vorhaben): self
    {
        $this->vorhaben = $vorhaben;

        return $this;
    }

    public function getAib(): ?string
    {
        return $this->aib;
    }

    public function setAib(?string $aib): self
    {
        $this->aib = $aib;

        return $this;
    }

    public function getKostenstelle(): ?string
    {
        return $this->kostenstelle;
    }

    public function setKostenstelle(?string $kostenstelle): self
    {
        $this->kostenstelle = $kostenstelle;

        return $this;
    }

    public function getBillNumber(): ?string
    {
        return $this->billNumber;
    }

    public function setBillNumber(?string $billNumber): self
    {
        $this->billNumber = $billNumber;

        return $this;
    }

    public function getIkz(): ?string
    {
        return $this->ikz;
    }

    public function setIkz(?string $ikz): self
    {
        $this->ikz = $ikz;

        return $this;
    }

    public function getAkz(): ?string
    {
        return $this->akz;
    }

    public function setAkz(?string $akz): self
    {
        $this->akz = $akz;

        return $this;
    }

    public function getProductionWeek(): ?string
    {
        return $this->productionWeek;
    }

    public function setProductionWeek(?string $productionWeek): self
    {
        $this->productionWeek = $productionWeek;

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

    public function getVehicleVariant(): ?int
    {
        return $this->vehicleVariant;
    }

    public function setVehicleVariant(?int $vehicleVariant): self
    {
        $this->vehicleVariant = $vehicleVariant;

        return $this;
    }

    public function getQsUser(): ?string
    {
        return $this->qsUser;
    }

    public function setQsUser(?string $qsUser): self
    {
        $this->qsUser = $qsUser;

        return $this;
    }

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function setComments(?string $comments): self
    {
        $this->comments = $comments;

        return $this;
    }

    public function getDeliveryStatus(): ?bool
    {
        return $this->deliveryStatus;
    }

    public function setDeliveryStatus(?bool $deliveryStatus): self
    {
        $this->deliveryStatus = $deliveryStatus;

        return $this;
    }

    public function getProductionLocation(): ?int
    {
        return $this->productionLocation;
    }

    public function setProductionLocation(?int $productionLocation): self
    {
        $this->productionLocation = $productionLocation;

        return $this;
    }

    public function getIsDpOrder(): ?bool
    {
        return $this->isDpOrder;
    }

    public function setIsDpOrder(bool $isDpOrder): self
    {
        $this->isDpOrder = $isDpOrder;

        return $this;
    }

    public function getShippingDate(): ?\DateTimeInterface
    {
        return $this->shippingDate;
    }

    public function setShippingDate(?\DateTimeInterface $shippingDate): self
    {
        $this->shippingDate = $shippingDate;

        return $this;
    }

    public function getCocYear(): ?int
    {
        return $this->cocYear;
    }

    public function setCocYear(?int $cocYear): self
    {
        $this->cocYear = $cocYear;

        return $this;
    }

    public function getVehicle(): ?Vehicles
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicles $vehicle): self
    {
        $this->vehicle = $vehicle;

        return $this;
    }


}
