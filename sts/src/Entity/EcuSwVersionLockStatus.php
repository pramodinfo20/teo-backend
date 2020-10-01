<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuSwVersionLockStatus
 *
 * @ORM\Table(name="ecu_sw_version_lock_status", uniqueConstraints={@ORM\UniqueConstraint(name="ecu_sw_version_lock_status_ecu_sw_version_lock_status_name_key", columns={"ecu_sw_version_lock_status_name"})})
 * @ORM\Entity
 */
class EcuSwVersionLockStatus
{
    /**
     * @var int
     *
     * @ORM\Column(name="ecu_sw_version_lock_status_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ecu_sw_version_lock_status_ecu_sw_version_lock_status_id_seq", allocationSize=1,
     *                                                                                                     initialValue=1)
     */
    private $ecuSwVersionLockStatusId;

    /**
     * @var string
     *
     * @ORM\Column(name="ecu_sw_version_lock_status_name", type="text", nullable=false)
     */
    private $ecuSwVersionLockStatusName;

    public function getEcuSwVersionLockStatusId(): ?int
    {
        return $this->ecuSwVersionLockStatusId;
    }

    public function getEcuSwVersionLockStatusName(): ?string
    {
        return $this->ecuSwVersionLockStatusName;
    }

    public function setEcuSwVersionLockStatusName(string $ecuSwVersionLockStatusName): self
    {
        $this->ecuSwVersionLockStatusName = $ecuSwVersionLockStatusName;

        return $this;
    }


}
