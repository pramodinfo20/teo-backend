<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsersPublicKeys
 *
 * @ORM\Table(name="users_public_keys", indexes={@ORM\Index(name="IDX_38663ACFA76ED395", columns={"user_id"})})
 * @ORM\Entity
 */
class UsersPublicKeys
{
    /**
     * @var int
     *
     * @ORM\Column(name="users_public_keys_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="users_public_keys_users_public_keys_id_seq", allocationSize=1, initialValue=1)
     */
    private $usersPublicKeysId;

    /**
     * @var string
     *
     * @ORM\Column(name="public_key", type="text", nullable=false)
     */
    private $publicKey;

    /**
     * @var \Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    public function getUsersPublicKeysId(): ?int
    {
        return $this->usersPublicKeysId;
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    public function setPublicKey(string $publicKey): self
    {
        $this->publicKey = $publicKey;

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
