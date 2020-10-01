<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CanVersions
 *
 * @ORM\Table(name="can_versions", uniqueConstraints={@ORM\UniqueConstraint(name="can_versions_name_key", columns={"name"})})
 * @ORM\Entity
 */
class CanVersions
{
    /**
     * @var int
     *
     * @ORM\Column(name="can_version_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="can_versions_can_version_id_seq", allocationSize=1, initialValue=1)
     */
    private $canVersionId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=false)
     */
    private $name;

    public function getCanVersionId(): ?int
    {
        return $this->canVersionId;
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


}
