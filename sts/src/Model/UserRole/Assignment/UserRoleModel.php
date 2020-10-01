<?php

namespace App\Model\UserRole\Assignment;

class UserRoleModel
{
    /**
     * @var int
     */
    private $userRoleId;

    /**
     * @var string
     */
    private $userRoleName;

    /**
     * @return int
     */
    public function getUserRoleId(): ?int
    {
        return $this->userRoleId;
    }

    /**
     * @param int $userRoleId
     */
    public function setUserRoleId(int $userRoleId = null): void
    {
        $this->userRoleId = $userRoleId;
    }

    /**
     * @return string
     */
    public function getUserRoleName(): ?string
    {
        return $this->userRoleName;
    }

    /**
     * @param string $userRoleName
     */
    public function setUserRoleName(string $userRoleName = null): void
    {
        $this->userRoleName = $userRoleName;
    }
}