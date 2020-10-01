<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CocParametersUsersWithWritePermissions
 *
 * @ORM\Table(name="coc_parameters_users_with_write_permissions")
 * @ORM\Entity
 */
class CocParametersUsersWithWritePermissions
{
    /**
     * @var \Users
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cpuwwp_user_id", referencedColumnName="id")
     * })
     */
    private $cpuwwpUser;

    public function getCpuwwpUser(): ?Users
    {
        return $this->cpuwwpUser;
    }

    public function setCpuwwpUser(?Users $cpuwwpUser): self
    {
        $this->cpuwwpUser = $cpuwwpUser;

        return $this;
    }


}
