<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PostVehicleOrder
 *
 * @ORM\Table(name="post_vehicle_order", indexes={@ORM\Index(name="IDX_88F7AD67D6DD82A", columns={"order_by"})})
 * @ORM\Entity
 */
class PostVehicleOrder
{
    /**
     * @var int
     *
     * @ORM\Column(name="order_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="post_vehicle_order_order_id_seq", allocationSize=1, initialValue=1)
     */
    private $orderId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="division_id", type="integer", nullable=true)
     */
    private $divisionId;

    /**
     * @var int
     *
     * @ORM\Column(name="prio", type="integer", nullable=false)
     */
    private $prio = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="order_datetime", type="datetime", nullable=false, options={"default"="now()"})
     */
    private $orderDatetime = 'now()';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="desired_datetime", type="datetime", nullable=true)
     */
    private $desiredDatetime;

    /**
     * @var string|null
     *
     * @ORM\Column(name="request_notice", type="text", nullable=true)
     */
    private $requestNotice;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="order_by", referencedColumnName="id")
     * })
     */
    private $orderBy;

    public function getOrderId(): ?int
    {
        return $this->orderId;
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

    public function getPrio(): ?int
    {
        return $this->prio;
    }

    public function setPrio(int $prio): self
    {
        $this->prio = $prio;

        return $this;
    }

    public function getOrderDatetime(): ?\DateTimeInterface
    {
        return $this->orderDatetime;
    }

    public function setOrderDatetime(\DateTimeInterface $orderDatetime): self
    {
        $this->orderDatetime = $orderDatetime;

        return $this;
    }

    public function getDesiredDatetime(): ?\DateTimeInterface
    {
        return $this->desiredDatetime;
    }

    public function setDesiredDatetime(?\DateTimeInterface $desiredDatetime): self
    {
        $this->desiredDatetime = $desiredDatetime;

        return $this;
    }

    public function getRequestNotice(): ?string
    {
        return $this->requestNotice;
    }

    public function setRequestNotice(?string $requestNotice): self
    {
        $this->requestNotice = $requestNotice;

        return $this;
    }

    public function getOrderBy(): ?Users
    {
        return $this->orderBy;
    }

    public function setOrderBy(?Users $orderBy): self
    {
        $this->orderBy = $orderBy;

        return $this;
    }


}
