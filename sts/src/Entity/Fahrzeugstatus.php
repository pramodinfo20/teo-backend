<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Fahrzeugstatus
 *
 * @ORM\Table(name="fahrzeugstatus")
 * @ORM\Entity
 */
class Fahrzeugstatus
{
    /**
     * @var int
     *
     * @ORM\Column(name="fahrzeugstatus_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="fahrzeugstatus_fahrzeugstatus_id_seq", allocationSize=1, initialValue=1)
     */
    private $fahrzeugstatusId;

    public function getFahrzeugstatusId(): ?int
    {
        return $this->fahrzeugstatusId;
    }


}
