<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Transporter
 *
 * @ORM\Table(name="transporter")
 * @ORM\Entity
 */
class Transporter
{
    /**
     * @var int
     *
     * @ORM\Column(name="transporter_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="transporter_transporter_id_seq", allocationSize=1, initialValue=1)
     */
    private $transporterId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    public function getTransporterId(): ?int
    {
        return $this->transporterId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }


}
