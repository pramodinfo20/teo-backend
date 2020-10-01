<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FunctionalityGroupUserRole
 *
 * @ORM\Table(name="functionality_group_user_role")
 * @ORM\Entity
 */
class FunctionalityGroupUserRole
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="functionality_group_user_role_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="functionality_group_id", type="integer", nullable=false)
     */
    private $functionalityGroupId;

    /**
     * @var int
     *
     * @ORM\Column(name="user_role_id", type="integer", nullable=false)
     */
    private $userRoleId;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="write_permissions", type="boolean", nullable=true)
     */
    private $writePermissions = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFunctionalityGroupId(): ?int
    {
        return $this->functionalityGroupId;
    }

    public function setFunctionalityGroupId(int $functionalityGroupId): self
    {
        $this->functionalityGroupId = $functionalityGroupId;

        return $this;
    }

    public function getUserRoleId(): ?int
    {
        return $this->userRoleId;
    }

    public function setUserRoleId(int $userRoleId): self
    {
        $this->userRoleId = $userRoleId;

        return $this;
    }

    public function getWritePermissions(): ?bool
    {
        return $this->writePermissions;
    }

    public function setWritePermissions(?bool $writePermissions): self
    {
        $this->writePermissions = $writePermissions;

        return $this;
    }


}
