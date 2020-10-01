<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AixacctStatus
 *
 * @ORM\Table(name="aixacct_status")
 * @ORM\Entity
 */
class AixacctStatus
{
    /**
     * @var int
     *
     * @ORM\Column(name="vehicle_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="aixacct_status_vehicle_id_seq", allocationSize=1, initialValue=1)
     */
    private $vehicleId;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="processed", type="boolean", nullable=true)
     */
    private $processed = false;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="workshop", type="boolean", nullable=true)
     */
    private $workshop = false;

    /**
     * @var string|null
     *
     * @ORM\Column(name="comment_aix", type="text", nullable=true)
     */
    private $commentAix;

    public function getVehicleId(): ?int
    {
        return $this->vehicleId;
    }

    public function getProcessed(): ?bool
    {
        return $this->processed;
    }

    public function setProcessed(?bool $processed): self
    {
        $this->processed = $processed;

        return $this;
    }

    public function getWorkshop(): ?bool
    {
        return $this->workshop;
    }

    public function setWorkshop(?bool $workshop): self
    {
        $this->workshop = $workshop;

        return $this;
    }

    public function getCommentAix(): ?string
    {
        return $this->commentAix;
    }

    public function setCommentAix(?string $commentAix): self
    {
        $this->commentAix = $commentAix;

        return $this;
    }


}
