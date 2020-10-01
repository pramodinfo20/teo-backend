<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ExchangeReasons
 *
 * @ORM\Table(name="exchange_reasons")
 * @ORM\Entity
 */
class ExchangeReasons
{
    /**
     * @var int
     *
     * @ORM\Column(name="reason_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="exchange_reasons_reason_id_seq", allocationSize=1, initialValue=1)
     */
    private $reasonId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="reason", type="text", nullable=true)
     */
    private $reason;

    public function getReasonId(): ?int
    {
        return $this->reasonId;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }


}
