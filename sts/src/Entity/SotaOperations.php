<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SotaOperations
 *
 * @ORM\Table(name="sota_operations")
 * @ORM\Entity
 */
class SotaOperations
{
    /**
     * @var int
     *
     * @ORM\Column(name="sota_operation", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="sota_operations_sota_operation_seq", allocationSize=1, initialValue=1)
     */
    private $sotaOperation;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    public function getSotaOperation(): ?int
    {
        return $this->sotaOperation;
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
