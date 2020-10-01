<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * QmLockHistory
 *
 * @ORM\Table(name="qm_lock_history")
 * @ORM\Entity
 */
class QmLockHistory
{
    /**
     * @var int
     *
     * @ORM\Column(name="entry_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="qm_lock_history_entry_id_seq", allocationSize=1, initialValue=1)
     */
    private $entryId;

    /**
     * @var int
     *
     * @ORM\Column(name="vehicle_id", type="integer", nullable=false)
     */
    private $vehicleId;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="update_ts", type="datetimetz", nullable=true)
     */
    private $updateTs;

    /**
     * @var int|null
     *
     * @ORM\Column(name="userid", type="integer", nullable=true)
     */
    private $userid;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="old_status", type="boolean", nullable=true)
     */
    private $oldStatus;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="new_status", type="boolean", nullable=true)
     */
    private $newStatus;

    /**
     * @var string|null
     *
     * @ORM\Column(name="qmcomment", type="text", nullable=true)
     */
    private $qmcomment;

    public function getEntryId(): ?int
    {
        return $this->entryId;
    }

    public function getVehicleId(): ?int
    {
        return $this->vehicleId;
    }

    public function setVehicleId(int $vehicleId): self
    {
        $this->vehicleId = $vehicleId;

        return $this;
    }

    public function getUpdateTs(): ?\DateTimeInterface
    {
        return $this->updateTs;
    }

    public function setUpdateTs(?\DateTimeInterface $updateTs): self
    {
        $this->updateTs = $updateTs;

        return $this;
    }

    public function getUserid(): ?int
    {
        return $this->userid;
    }

    public function setUserid(?int $userid): self
    {
        $this->userid = $userid;

        return $this;
    }

    public function getOldStatus(): ?bool
    {
        return $this->oldStatus;
    }

    public function setOldStatus(?bool $oldStatus): self
    {
        $this->oldStatus = $oldStatus;

        return $this;
    }

    public function getNewStatus(): ?bool
    {
        return $this->newStatus;
    }

    public function setNewStatus(?bool $newStatus): self
    {
        $this->newStatus = $newStatus;

        return $this;
    }

    public function getQmcomment(): ?string
    {
        return $this->qmcomment;
    }

    public function setQmcomment(?string $qmcomment): self
    {
        $this->qmcomment = $qmcomment;

        return $this;
    }


}
