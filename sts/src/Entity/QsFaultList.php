<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * QsFaultList
 *
 * @ORM\Table(name="qs_fault_list")
 * @ORM\Entity
 */
class QsFaultList
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
     * @var string
     *
     * @ORM\Column(name="field_key", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $fieldKey;

    /**
     * @var string|null
     *
     * @ORM\Column(name="field_value", type="text", nullable=true)
     */
    private $fieldValue;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="update_ts", type="datetimetz", nullable=true)
     */
    private $updateTs;

    /**
     * @var int|null
     *
     * @ORM\Column(name="addedby", type="integer", nullable=true)
     */
    private $addedby;

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

    public function getFieldKey(): ?string
    {
        return $this->fieldKey;
    }

    public function getFieldValue(): ?string
    {
        return $this->fieldValue;
    }

    public function setFieldValue(?string $fieldValue): self
    {
        $this->fieldValue = $fieldValue;

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

    public function getAddedby(): ?int
    {
        return $this->addedby;
    }

    public function setAddedby(?int $addedby): self
    {
        $this->addedby = $addedby;

        return $this;
    }


}
