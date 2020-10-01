<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PartGroups
 *
 * @ORM\Table(name="part_groups")
 * @ORM\Entity
 */
class PartGroups
{
    /**
     * @var int
     *
     * @ORM\Column(name="group_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="part_groups_group_id_seq", allocationSize=1, initialValue=1)
     */
    private $groupId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="group_name", type="text", nullable=true)
     */
    private $groupName;

    /**
     * @var bool
     *
     * @ORM\Column(name="allow_none", type="boolean", nullable=false)
     */
    private $allowNone = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="allow_multi", type="boolean", nullable=false)
     */
    private $allowMulti = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="show_in_production_protocol", type="boolean", nullable=false, options={"default"="1"})
     */
    private $showInProductionProtocol = true;

    public function getGroupId(): ?int
    {
        return $this->groupId;
    }

    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    public function setGroupName(?string $groupName): self
    {
        $this->groupName = $groupName;

        return $this;
    }

    public function getAllowNone(): ?bool
    {
        return $this->allowNone;
    }

    public function setAllowNone(bool $allowNone): self
    {
        $this->allowNone = $allowNone;

        return $this;
    }

    public function getAllowMulti(): ?bool
    {
        return $this->allowMulti;
    }

    public function setAllowMulti(bool $allowMulti): self
    {
        $this->allowMulti = $allowMulti;

        return $this;
    }

    public function getShowInProductionProtocol(): ?bool
    {
        return $this->showInProductionProtocol;
    }

    public function setShowInProductionProtocol(bool $showInProductionProtocol): self
    {
        $this->showInProductionProtocol = $showInProductionProtocol;

        return $this;
    }


}
