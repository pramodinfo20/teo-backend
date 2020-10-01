<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ComponentExchangesHistory
 *
 * @ORM\Table(name="component_exchanges_history", indexes={@ORM\Index(name="IDX_1873FF18A76ED395",
 *                                                columns={"user_id"}), @ORM\Index(name="IDX_1873FF1859BB1592",
 *                                                columns={"reason_id"})})
 * @ORM\Entity
 */
class ComponentExchangesHistory
{
    /**
     * @var int
     *
     * @ORM\Column(name="component_exchanges_history_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="component_exchanges_history_component_exchanges_history_id_seq", allocationSize=1,
     *                                                                                                       initialValue=1)
     */
    private $componentExchangesHistoryId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="vehicle_id", type="integer", nullable=true)
     */
    private $vehicleId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ebom_part_number", type="text", nullable=true)
     */
    private $ebomPartNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="exchange_part_number", type="text", nullable=true)
     */
    private $exchangePartNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="parent_id", type="text", nullable=true)
     */
    private $parentId;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="exchange_date", type="datetime", nullable=true)
     */
    private $exchangeDate;

    /**
     * @var string|null
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @var ExchangeReasons
     *
     * @ORM\ManyToOne(targetEntity="ExchangeReasons")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="reason_id", referencedColumnName="reason_id")
     * })
     */
    private $reason;

    public function getComponentExchangesHistoryId(): ?int
    {
        return $this->componentExchangesHistoryId;
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

    public function getEbomPartNumber(): ?string
    {
        return $this->ebomPartNumber;
    }

    public function setEbomPartNumber(?string $ebomPartNumber): self
    {
        $this->ebomPartNumber = $ebomPartNumber;

        return $this;
    }

    public function getExchangePartNumber(): ?string
    {
        return $this->exchangePartNumber;
    }

    public function setExchangePartNumber(?string $exchangePartNumber): self
    {
        $this->exchangePartNumber = $exchangePartNumber;

        return $this;
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function setParentId(?string $parentId): self
    {
        $this->parentId = $parentId;

        return $this;
    }

    public function getExchangeDate(): ?\DateTimeInterface
    {
        return $this->exchangeDate;
    }

    public function setExchangeDate(?\DateTimeInterface $exchangeDate): self
    {
        $this->exchangeDate = $exchangeDate;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getReason(): ?ExchangeReasons
    {
        return $this->reason;
    }

    public function setReason(?ExchangeReasons $reason): self
    {
        $this->reason = $reason;

        return $this;
    }


}
