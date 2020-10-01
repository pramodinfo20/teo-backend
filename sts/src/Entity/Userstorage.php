<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Userstorage
 *
 * @ORM\Table(name="userstorage", indexes={@ORM\Index(name="IDX_C81BE78AA76ED395", columns={"user_id"})})
 * @ORM\Entity
 */
class Userstorage
{
    /**
     * @var int
     *
     * @ORM\Column(name="storage_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="userstorage_storage_id_seq", allocationSize=1, initialValue=1)
     */
    private $storageId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="storage_name", type="text", nullable=true)
     */
    private $storageName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * @var string|null
     *
     * @ORM\Column(name="context", type="text", nullable=true)
     */
    private $context;

    /**
     * @var Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    public function getStorageId(): ?int
    {
        return $this->storageId;
    }

    public function getStorageName(): ?string
    {
        return $this->storageName;
    }

    public function setStorageName(?string $storageName): self
    {
        $this->storageName = $storageName;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getContext(): ?string
    {
        return $this->context;
    }

    public function setContext(?string $context): self
    {
        $this->context = $context;

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
