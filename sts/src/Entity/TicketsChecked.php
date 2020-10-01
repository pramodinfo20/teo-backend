<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TicketsChecked
 *
 * @ORM\Table(name="tickets_checked")
 * @ORM\Entity
 */
class TicketsChecked
{
    /**
     * @var int
     *
     * @ORM\Column(name="tickets_checked_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="tickets_checked_tickets_checked_id_seq", allocationSize=1, initialValue=1)
     */
    private $ticketsCheckedId;

    public function getTicketsCheckedId(): ?int
    {
        return $this->ticketsCheckedId;
    }


}
