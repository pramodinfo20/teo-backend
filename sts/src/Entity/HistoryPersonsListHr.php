<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * HistoryPersonsListHr
 *
 * @ORM\Table(name="history_persons_list_hr", indexes={@ORM\Index(name="IDX_6F177B01DE12AB56", columns={"created_by"})})
 * @ORM\Entity
 */
class HistoryPersonsListHr
{
    /**
     * @var int
     *
     * @ORM\Column(name="hplh_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="history_persons_list_hr_hplh_id_seq", allocationSize=1, initialValue=1)
     */
    private $hplhId;

    /**
     * @var json|null
     *
     * @ORM\Column(name="history_data", type="json", nullable=true)
     */
    private $historyData;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetimetz", nullable=true)
     */
    private $createdAt;

    /**
     * @var \Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     * })
     */
    private $createdBy;

    public function getHplhId(): ?int
    {
        return $this->hplhId;
    }

    public function getHistoryData(): ?array
    {
        return $this->historyData;
    }

    public function setHistoryData(?array $historyData): self
    {
        $this->historyData = $historyData;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

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

    public function __toString(): string
    {
        return "{$this->createdAt->format('Y-m-d H:i:s')} ($this->createdBy)";
    }
}
