<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GlobalParametersUsersWithWritePermission
 *
 * @ORM\Table(name="global_parameters_users_with_write_permission", uniqueConstraints={@ORM\UniqueConstraint(name="global_parameters_users_with_wr_user_id_global_parameter_id_key", columns={"user_id", "global_parameter_id"})}, indexes={@ORM\Index(name="IDX_FCC335757F9ADA8F", columns={"global_parameter_id"}), @ORM\Index(name="IDX_FCC33575A76ED395", columns={"user_id"})})
 * @ORM\Entity
 */
class GlobalParametersUsersWithWritePermission
{
    /**
     * @var int
     *
     * @ORM\Column(name="gpuwwp_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="global_parameters_users_with_write_permission_gpuwwp_id_seq", allocationSize=1, initialValue=1)
     */
    private $gpuwwpId;

    /**
     * @var \GlobalParameters
     *
     * @ORM\ManyToOne(targetEntity="GlobalParameters")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="global_parameter_id", referencedColumnName="global_parameter_id")
     * })
     */
    private $globalParameter;

    /**
     * @var \Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    public function getGpuwwpId(): ?int
    {
        return $this->gpuwwpId;
    }

    public function getGlobalParameter(): ?GlobalParameters
    {
        return $this->globalParameter;
    }

    public function setGlobalParameter(?GlobalParameters $globalParameter): self
    {
        $this->globalParameter = $globalParameter;

        return $this;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;

        return $this;
    }


}
