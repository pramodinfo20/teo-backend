<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ObsoleteOptionsGroups
 *
 * @ORM\Table(name="obsolete_options_groups")
 * @ORM\Entity
 */
class ObsoleteOptionsGroups
{
    /**
     * @var int
     *
     * @ORM\Column(name="group_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="obsolete_options_groups_group_id_seq", allocationSize=1, initialValue=1)
     */
    private $groupId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    public function getGroupId(): ?int
    {
        return $this->groupId;
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
