<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TcuUpdateTypes
 *
 * @ORM\Table(name="tcu_update_types")
 * @ORM\Entity
 */
class TcuUpdateTypes
{
    /**
     * @var int
     *
     * @ORM\Column(name="update_type", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="tcu_update_types_update_type_seq", allocationSize=1, initialValue=1)
     */
    private $updateType;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    public function getUpdateType(): ?int
    {
        return $this->updateType;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }


}
