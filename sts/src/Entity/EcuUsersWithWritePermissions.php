<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EcuUsersWithWritePermissions
 *
 * @ORM\Table(name="ecu_users_with_write_permissions", uniqueConstraints={@ORM\UniqueConstraint(name="ecu_users_with_write_permissions_ce_ecu_id_user_id_key", columns={"ce_ecu_id", "user_id"})}, indexes={@ORM\Index(name="IDX_4F1B729DA76ED395", columns={"user_id"}), @ORM\Index(name="IDX_4F1B729D8D3B41B6", columns={"ce_ecu_id"})})
 * @ORM\Entity
 */
class EcuUsersWithWritePermissions
{
    /**
     * @var int
     *
     * @ORM\Column(name="euwwp_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="ecu_users_with_write_permissions_euwwp_id_seq", allocationSize=1, initialValue=1)
     */
    private $euwwpId;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @var ConfigurationEcus
     *
     * @ORM\ManyToOne(targetEntity="ConfigurationEcus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ce_ecu_id", referencedColumnName="ce_ecu_id")
     * })
     */
    private $ceEcu;

    public function getEuwwpId(): ?int
    {
        return $this->euwwpId;
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

    public function getCeEcu(): ?ConfigurationEcus
    {
        return $this->ceEcu;
    }

    public function setCeEcu(?ConfigurationEcus $ceEcu): self
    {
        $this->ceEcu = $ceEcu;

        return $this;
    }


}
