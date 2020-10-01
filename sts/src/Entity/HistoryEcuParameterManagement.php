<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * HistoryEcuParameterManagement
 *
 * @ORM\Table(name="history_ecu_parameter_management", indexes={@ORM\Index(name="IDX_D63F1B4B71F7E88B", columns={"event_id"}), @ORM\Index(name="IDX_D63F1B4BDE12AB56", columns={"created_by"})})
 * @ORM\Entity(repositoryClass="App\Repository\HistoryEcuParameterManagementRepository")
 */
class HistoryEcuParameterManagement
{
    /**
     * @var int
     *
     * @ORM\Column(name="h_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="history_ecu_parameter_management_h_id_seq", allocationSize=1, initialValue=1)
     */
    private $hId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="before_header_model", type="text", nullable=true)
     */
    private $beforeHeaderModel;

    /**
     * @var string|null
     *
     * @ORM\Column(name="after_header_model", type="text", nullable=true)
     */
    private $afterHeaderModel;

    /**
     * @var string|null
     *
     * @ORM\Column(name="before_collection_model", type="text", nullable=true)
     */
    private $beforeCollectionModel;

    /**
     * @var string|null
     *
     * @ORM\Column(name="after_collection_model", type="text", nullable=true)
     */
    private $afterCollectionModel;

    /**
     * @var int|null
     *
     * @ORM\Column(name="odx_version", type="integer", nullable=true)
     */
    private $odxVersion;

    /**
     * @var int|null
     *
     * @ORM\Column(name="fk_id", type="integer", nullable=true)
     */
    private $fkId;

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

    public function getBeforeHeaderModel(): ?string
    {
        return $this->beforeHeaderModel;
    }

    public function setBeforeHeaderModel(?string $beforeHeaderModel): self
    {
        $this->beforeHeaderModel = $beforeHeaderModel;

        return $this;
    }

    public function getAfterHeaderModel(): ?string
    {
        return $this->afterHeaderModel;
    }

    public function setAfterHeaderModel(?string $afterHeaderModel): self
    {
        $this->afterHeaderModel = $afterHeaderModel;

        return $this;
    }

    public function getBeforeCollectionModel(): ?string
    {
        return $this->beforeCollectionModel;
    }

    public function setBeforeCollectionModel(?string $beforeCollectionModel): self
    {
        $this->beforeCollectionModel = $beforeCollectionModel;

        return $this;
    }

    public function getAfterCollectionModel(): ?string
    {
        return $this->afterCollectionModel;
    }

    public function setAfterCollectionModel(?string $afterCollectionModel): self
    {
        $this->afterCollectionModel = $afterCollectionModel;

        return $this;
    }

    public function getOdxVersion(): ?int
    {
        return $this->odxVersion;
    }

    public function setOdxVersion(?int $odxVersion): self
    {
        $this->odxVersion = $odxVersion;

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
