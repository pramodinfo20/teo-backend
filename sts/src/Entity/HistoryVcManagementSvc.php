<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * HistoryVcManagementSvc
 *
 * @ORM\Table(name="history_vc_management_svc", indexes={@ORM\Index(name="IDX_3C51C6F971F7E88B", columns={"event_id"}), @ORM\Index(name="IDX_3C51C6F9DE12AB56", columns={"created_by"})})
 * @ORM\Entity(repositoryClass="App\Repository\HistoryVcManagementSvcRepository")
 */
class HistoryVcManagementSvc
{
    /**
     * @var int
     *
     * @ORM\Column(name="h_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="history_vc_management_svc_h_id_seq", allocationSize=1, initialValue=1)
     */
    private $hId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="before_svc_model", type="text", nullable=true)
     */
    private $beforeSvcModel;

    /**
     * @var string|null
     *
     * @ORM\Column(name="after_svc_model", type="text", nullable=true)
     */
    private $afterSvcModel;

    /**
     * @var int|null
     *
     * @ORM\Column(name="key_type", type="integer", nullable=true)
     */
    private $keyType;

    /**
     * @var int|null
     *
     * @ORM\Column(name="fk_id", type="integer", nullable=true)
     */
    private $fkId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="fk_vc_id", type="integer", nullable=true)
     */
    private $fkVcId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="history_vc_fk_id", type="integer", nullable=true)
     */
    private $historyVcFkId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetimetz", nullable=false, options={"default"="now()"})
     */
    private $createdAt = 'now()';

    /**
     * @var HistoryEvents
     *
     * @ORM\ManyToOne(targetEntity="HistoryEvents")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_id", referencedColumnName="he_id")
     * })
     */
    private $event;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     * })
     */
    private $createdBy;

    public function getHId(): ?int
    {
        return $this->hId;
    }

    public function getBeforeSvcModel(): ?string
    {
        return $this->beforeSvcModel;
    }

    public function setBeforeSvcModel(?string $beforeSvcModel): self
    {
        $this->beforeSvcModel = $beforeSvcModel;

        return $this;
    }

    public function getAfterSvcModel(): ?string
    {
        return $this->afterSvcModel;
    }

    public function setAfterSvcModel(?string $afterSvcModel): self
    {
        $this->afterSvcModel = $afterSvcModel;

        return $this;
    }

    public function getKeyType(): ?int
    {
        return $this->keyType;
    }

    public function setKeyType(?int $keyType): self
    {
        $this->keyType = $keyType;

        return $this;
    }

    public function getFkId(): ?int
    {
        return $this->fkId;
    }

    public function setFkId(?int $fkId): self
    {
        $this->fkId = $fkId;

        return $this;
    }

    public function getFkVcId(): ?int
    {
        return $this->fkVcId;
    }

    public function setFkVcId(?int $fkVcId): self
    {
        $this->fkVcId = $fkVcId;

        return $this;
    }

    public function getHistoryVcFkId(): ?int
    {
        return $this->historyVcFkId;
    }

    public function setHistoryVcFkId(?int $historyVcFkId): self
    {
        $this->historyVcFkId = $historyVcFkId;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getEvent(): ?HistoryEvents
    {
        return $this->event;
    }

    public function setEvent(?HistoryEvents $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getCreatedBy(): ?Users
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Users $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }


}
