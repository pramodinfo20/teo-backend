<?php

namespace App\Service\UserRole;

class UserRole
{
    /**
     * @var int
     */
    public $userRoleId;

    /**
     * @var array
     */
    public $assignedStructures;

    /**
     * UserRole constructor.
     *
     * @param int $userRoleId
     * @param array $assignedStructures
     */
    public function __construct(int $userRoleId, array $assignedStructures = [])
    {
        $this->userRoleId = $userRoleId;
        $this->assignedStructures = $assignedStructures;
    }
}