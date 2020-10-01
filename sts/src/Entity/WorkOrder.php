<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WorkOrder
 *
 * @ORM\Table(name="work_order", indexes={@ORM\Index(name="IDX_DDD2E8B7F132696E", columns={"userid"}),
 *                               @ORM\Index(name="IDX_DDD2E8B76BF700BD", columns={"status_id"}),
 *                                                                       @ORM\Index(name="IDX_DDD2E8B744990C25", columns={"park_id"}),
 *                                                                                                               @ORM\Index(name="IDX_DDD2E8B7E70B032", columns={"typeid"}),
 *                                                                                                                                                      @ORM\Index(name="IDX_DDD2E8B7318D3E69", columns={"park_destination"}),
 *                                                                                                                                                                                              @ORM\Index(name="IDX_DDD2E8B7DE12AB56", columns={"created_by"})})
 * @ORM\Entity
 */
class WorkOrder
{
    /**
     * @var int
     *
     * @ORM\Column(name="orderid", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="work_order_orderid_seq", allocationSize=1, initialValue=1)
     */
    private $orderid;

    /**
     * @var int|null
     *
     * @ORM\Column(name="work_vehicle_id", type="integer", nullable=true)
     */
    private $workVehicleId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="park_nummber", type="text", nullable=true)
     */
    private $parkNummber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="penta_number", type="text", nullable=true)
     */
    private $pentaNumber;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="date_update", type="datetime", nullable=true)
     */
    private $dateUpdate;

    /**
     * @var string|null
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    private $message;

    /**
     * @var int|null
     *
     * @ORM\Column(name="userid2", type="integer", nullable=true)
     */
    private $userid2;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="date_insert", type="datetime", nullable=true)
     */
    private $dateInsert;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="start_time", type="datetime", nullable=true)
     */
    private $startTime;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="end_time", type="datetime", nullable=true)
     */
    private $endTime;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="order_accepted_time", type="datetime", nullable=true)
     */
    private $orderAcceptedTime;

    /**
     * @var string|null
     *
     * @ORM\Column(name="destination_address", type="text", nullable=true)
     */
    private $destinationAddress;

    /**
     * @var string|null
     *
     * @ORM\Column(name="special_order", type="text", nullable=true)
     */
    private $specialOrder;

    /**
     * @var WorkUsers
     *
     * @ORM\ManyToOne(targetEntity="WorkUsers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="userid", referencedColumnName="usersid")
     * })
     */
    private $userid;

    /**
     * @var WorkStatus
     *
     * @ORM\ManyToOne(targetEntity="WorkStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     * })
     */
    private $status;

    /**
     * @var GpsMaps
     *
     * @ORM\ManyToOne(targetEntity="GpsMaps")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="park_id", referencedColumnName="map_id")
     * })
     */
    private $park;

    /**
     * @var WorkOrdertype
     *
     * @ORM\ManyToOne(targetEntity="WorkOrdertype")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="typeid", referencedColumnName="typeid")
     * })
     */
    private $typeid;

    /**
     * @var GpsMaps
     *
     * @ORM\ManyToOne(targetEntity="GpsMaps")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="park_destination", referencedColumnName="map_id")
     * })
     */
    private $parkDestination;

    /**
     * @var WorkUsers
     *
     * @ORM\ManyToOne(targetEntity="WorkUsers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="created_by", referencedColumnName="usersid")
     * })
     */
    private $createdBy;

    public function getOrderid(): ?int
    {
        return $this->orderid;
    }

    public function getWorkVehicleId(): ?int
    {
        return $this->workVehicleId;
    }

    public function setWorkVehicleId(?int $workVehicleId): self
    {
        $this->workVehicleId = $workVehicleId;

        return $this;
    }

    public function getParkNummber(): ?string
    {
        return $this->parkNummber;
    }

    public function setParkNummber(?string $parkNummber): self
    {
        $this->parkNummber = $parkNummber;

        return $this;
    }

    public function getPentaNumber(): ?string
    {
        return $this->pentaNumber;
    }

    public function setPentaNumber(?string $pentaNumber): self
    {
        $this->pentaNumber = $pentaNumber;

        return $this;
    }

    public function getDateUpdate(): ?\DateTimeInterface
    {
        return $this->dateUpdate;
    }

    public function setDateUpdate(?\DateTimeInterface $dateUpdate): self
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getUserid2(): ?int
    {
        return $this->userid2;
    }

    public function setUserid2(?int $userid2): self
    {
        $this->userid2 = $userid2;

        return $this;
    }

    public function getDateInsert(): ?\DateTimeInterface
    {
        return $this->dateInsert;
    }

    public function setDateInsert(?\DateTimeInterface $dateInsert): self
    {
        $this->dateInsert = $dateInsert;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeInterface $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getOrderAcceptedTime(): ?\DateTimeInterface
    {
        return $this->orderAcceptedTime;
    }

    public function setOrderAcceptedTime(?\DateTimeInterface $orderAcceptedTime): self
    {
        $this->orderAcceptedTime = $orderAcceptedTime;

        return $this;
    }

    public function getDestinationAddress(): ?string
    {
        return $this->destinationAddress;
    }

    public function setDestinationAddress(?string $destinationAddress): self
    {
        $this->destinationAddress = $destinationAddress;

        return $this;
    }

    public function getSpecialOrder(): ?string
    {
        return $this->specialOrder;
    }

    public function setSpecialOrder(?string $specialOrder): self
    {
        $this->specialOrder = $specialOrder;

        return $this;
    }

    public function getUserid(): ?WorkUsers
    {
        return $this->userid;
    }

    public function setUserid(?WorkUsers $userid): self
    {
        $this->userid = $userid;

        return $this;
    }

    public function getStatus(): ?WorkStatus
    {
        return $this->status;
    }

    public function setStatus(?WorkStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPark(): ?GpsMaps
    {
        return $this->park;
    }

    public function setPark(?GpsMaps $park): self
    {
        $this->park = $park;

        return $this;
    }

    public function getTypeid(): ?WorkOrdertype
    {
        return $this->typeid;
    }

    public function setTypeid(?WorkOrdertype $typeid): self
    {
        $this->typeid = $typeid;

        return $this;
    }

    public function getParkDestination(): ?GpsMaps
    {
        return $this->parkDestination;
    }

    public function setParkDestination(?GpsMaps $parkDestination): self
    {
        $this->parkDestination = $parkDestination;

        return $this;
    }

    public function getCreatedBy(): ?WorkUsers
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?WorkUsers $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }


}
