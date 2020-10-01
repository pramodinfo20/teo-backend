<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SotaKeys
 *
 * @ORM\Table(name="sota_keys")
 * @ORM\Entity
 */
class SotaKeys
{
    /**
     * @var int
     *
     * @ORM\Column(name="server_key_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="sota_keys_server_key_id_seq", allocationSize=1, initialValue=1)
     */
    private $serverKeyId;

    /**
     * @var string
     *
     * @ORM\Column(name="key_type", type="text", nullable=false)
     */
    private $keyType;

    /**
     * @var int
     *
     * @ORM\Column(name="ecu_id", type="integer", nullable=false)
     */
    private $ecuId;

    /**
     * @var string
     *
     * @ORM\Column(name="key", type="text", nullable=false)
     */
    private $key;

    /**
     * @var string|null
     *
     * @ORM\Column(name="c2cbox", type="text", nullable=true)
     */
    private $c2cbox;

    public function getServerKeyId(): ?int
    {
        return $this->serverKeyId;
    }

    public function getKeyType(): ?string
    {
        return $this->keyType;
    }

    public function setKeyType(string $keyType): self
    {
        $this->keyType = $keyType;

        return $this;
    }

    public function getEcuId(): ?int
    {
        return $this->ecuId;
    }

    public function setEcuId(int $ecuId): self
    {
        $this->ecuId = $ecuId;

        return $this;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function getC2cbox(): ?string
    {
        return $this->c2cbox;
    }

    public function setC2cbox(?string $c2cbox): self
    {
        $this->c2cbox = $c2cbox;

        return $this;
    }


}
