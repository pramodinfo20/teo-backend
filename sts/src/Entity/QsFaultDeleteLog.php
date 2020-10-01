<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * QsFaultDeleteLog
 *
 * @ORM\Table(name="qs_fault_delete_log")
 * @ORM\Entity
 */
class QsFaultDeleteLog
{
    /**
     * @var int
     *
     * @ORM\Column(name="qs_fcat_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $qsFcatId;

    /**
     * @var int
     *
     * @ORM\Column(name="vehicle_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $vehicleId;

    /**
     * @var int
     *
     * @ORM\Column(name="fault_sno", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $faultSno;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="delete_ts", type="datetimetz", nullable=true)
     */
    private $deleteTs;

    /**
     * @var int|null
     *
     * @ORM\Column(name="delete_by", type="integer", nullable=true)
     */
    private $deleteBy;

    public function getQsFcatId(): ?int
    {
        return $this->qsFcatId;
    }

    public function getVehicleId(): ?int
    {
        return $this->vehicleId;
    }

    public function getFaultSno(): ?int
    {
        return $this->faultSno;
    }

    public function getDeleteTs(): ?\DateTimeInterface
    {
        return $this->deleteTs;
    }

    public function setDeleteTs(?\DateTimeInterface $deleteTs): self
    {
        $this->deleteTs = $deleteTs;

        return $this;
    }

    public function getDeleteBy(): ?int
    {
        return $this->deleteBy;
    }

    public function setDeleteBy(?int $deleteBy): self
    {
        $this->deleteBy = $deleteBy;

        return $this;
    }


}
