<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ServiceStatus
 *
 * @ORM\Table(name="service_status")
 * @ORM\Entity
 */
class ServiceStatus
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="service_status_name_seq", allocationSize=1, initialValue=1)
     */
    private $name;

    /**
     * @var int|null
     *
     * @ORM\Column(name="timestamp", type="integer", nullable=true)
     */
    private $timestamp;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getTimestamp(): ?int
    {
        return $this->timestamp;
    }

    public function setTimestamp(?int $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }


}
