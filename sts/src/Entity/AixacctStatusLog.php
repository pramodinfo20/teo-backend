<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AixacctStatusLog
 *
 * @ORM\Table(name="aixacct_status_log")
 * @ORM\Entity
 */
class AixacctStatusLog
{
    /**
     * @var int
     *
     * @ORM\Column(name="logsno", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="aixacct_status_log_logsno_seq", allocationSize=1, initialValue=1)
     */
    private $logsno;

    /**
     * @var int
     *
     * @ORM\Column(name="vehicle_id", type="integer", nullable=false)
     */
    private $vehicleId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="changedparam", type="text", nullable=true)
     */
    private $changedparam;

    /**
     * @var string|null
     *
     * @ORM\Column(name="oldval", type="text", nullable=true)
     */
    private $oldval;

    /**
     * @var string|null
     *
     * @ORM\Column(name="newval", type="text", nullable=true)
     */
    private $newval;

    /**
     * @var string|null
     *
     * @ORM\Column(name="insertvals", type="text", nullable=true)
     */
    private $insertvals;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="update_ts", type="datetimetz", nullable=true)
     */
    private $updateTs;

    public function getLogsno(): ?int
    {
        return $this->logsno;
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

    public function getChangedparam(): ?string
    {
        return $this->changedparam;
    }

    public function setChangedparam(?string $changedparam): self
    {
        $this->changedparam = $changedparam;

        return $this;
    }

    public function getOldval(): ?string
    {
        return $this->oldval;
    }

    public function setOldval(?string $oldval): self
    {
        $this->oldval = $oldval;

        return $this;
    }

    public function getNewval(): ?string
    {
        return $this->newval;
    }

    public function setNewval(?string $newval): self
    {
        $this->newval = $newval;

        return $this;
    }

    public function getInsertvals(): ?string
    {
        return $this->insertvals;
    }

    public function setInsertvals(?string $insertvals): self
    {
        $this->insertvals = $insertvals;

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


}
