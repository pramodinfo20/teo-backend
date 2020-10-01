<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SotaLinkedKeys
 *
 * @ORM\Table(name="sota_linked_keys")
 * @ORM\Entity
 */
class SotaLinkedKeys
{
    /**
     * @var int
     *
     * @ORM\Column(name="ecu_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $ecuId;

    /**
     * @var int
     *
     * @ORM\Column(name="server_key_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $serverKeyId;

    /**
     * @var string
     *
     * @ORM\Column(name="key_type", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $keyType = '';

    /**
     * @var string
     *
     * @ORM\Column(name="ecu_key", type="text", nullable=false)
     */
    private $ecuKey;

    public function getEcuId(): ?int
    {
        return $this->ecuId;
    }

    public function getServerKeyId(): ?int
    {
        return $this->serverKeyId;
    }

    public function getKeyType(): ?string
    {
        return $this->keyType;
    }

    public function getEcuKey(): ?string
    {
        return $this->ecuKey;
    }

    public function setEcuKey(string $ecuKey): self
    {
        $this->ecuKey = $ecuKey;

        return $this;
    }


}
