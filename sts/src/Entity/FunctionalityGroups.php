<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FunctionalityGroups
 *
 * @ORM\Table(name="functionality_groups")
 * @ORM\Entity
 */
class FunctionalityGroups
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="functionality_groups_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="always_read_only", type="boolean", nullable=true)
     */
    private $alwaysReadOnly = false;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="always_write", type="boolean", nullable=true)
     */
    private $alwaysWrite = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
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

    public function getAlwaysReadOnly(): ?bool
    {
        return $this->alwaysReadOnly;
    }

    public function setAlwaysReadOnly(?bool $alwaysReadOnly): self
    {
        $this->alwaysReadOnly = $alwaysReadOnly;

        return $this;
    }

    public function getAlwaysWrite(): ?bool
    {
        return $this->alwaysWrite;
    }

    public function setAlwaysWrite(?bool $alwaysWrite): self
    {
        $this->alwaysWrite = $alwaysWrite;

        return $this;
    }


}
