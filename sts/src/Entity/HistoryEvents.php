<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * HistoryEvents
 *
 * @ORM\Table(name="history_events", uniqueConstraints={@ORM\UniqueConstraint(name="history_events_event_name_key", columns={"event_name"})})
 * @ORM\Entity
 */
class HistoryEvents
{
    /**
     * @var int
     *
     * @ORM\Column(name="he_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="history_events_he_id_seq", allocationSize=1, initialValue=1)
     */
    private $heId;

    /**
     * @var string
     *
     * @ORM\Column(name="event_name", type="text", nullable=false)
     */
    private $eventName;

    public function getHeId(): ?int
    {
        return $this->heId;
    }

    public function getEventName(): ?string
    {
        return $this->eventName;
    }

    public function setEventName(string $eventName): self
    {
        $this->eventName = $eventName;

        return $this;
    }


}
