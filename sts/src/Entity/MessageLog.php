<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MessageLog
 *
 * @ORM\Table(name="message_log", indexes={@ORM\Index(name="IDX_A60AE229545317D1", columns={"vehicle_id"})})
 * @ORM\Entity
 */
class MessageLog
{
    /**
     * @var int
     *
     * @ORM\Column(name="message_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $messageId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timestamp_sent", type="datetimetz", nullable=false, options={"default"="now()"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $timestampSent = 'now()';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="timestamp_ack", type="datetimetz", nullable=true)
     */
    private $timestampAck;

    /**
     * @var int|null
     *
     * @ORM\Column(name="message_type", type="integer", nullable=true)
     */
    private $messageType;

    /**
     * @var integer[]|null
     *
     * @ORM\Column(name="values", type="integer[]", nullable=true)
     */
    private $values;

    /**
     * @var int|null
     *
     * @ORM\Column(name="duration", type="integer", nullable=true)
     */
    private $duration;

    /**
     * @var Vehicles
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Vehicles")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_id", referencedColumnName="vehicle_id")
     * })
     */
    private $vehicle;

    public function getMessageId(): ?int
    {
        return $this->messageId;
    }

    public function getTimestampSent(): ?\DateTimeInterface
    {
        return $this->timestampSent;
    }

    public function getTimestampAck(): ?\DateTimeInterface
    {
        return $this->timestampAck;
    }

    public function setTimestampAck(?\DateTimeInterface $timestampAck): self
    {
        $this->timestampAck = $timestampAck;

        return $this;
    }

    public function getMessageType(): ?int
    {
        return $this->messageType;
    }

    public function setMessageType(?int $messageType): self
    {
        $this->messageType = $messageType;

        return $this;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function setValues($values): self
    {
        $this->values = $values;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getVehicle(): ?Vehicles
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicles $vehicle): self
    {
        $this->vehicle = $vehicle;

        return $this;
    }


}
