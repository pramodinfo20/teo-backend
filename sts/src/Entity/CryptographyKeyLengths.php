<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CryptographyKeyLengths
 *
 * @ORM\Table(name="cryptography_key_lengths")
 * @ORM\Entity
 */
class CryptographyKeyLengths
{
    /**
     * @var int
     *
     * @ORM\Column(name="cryptography_key_length_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="cryptography_key_lengths_cryptography_key_length_id_seq", allocationSize=1,
     *                                                                                                initialValue=1)
     */
    private $cryptographyKeyLengthId;

    /**
     * @var int
     *
     * @ORM\Column(name="cryptography_key_length", type="integer", nullable=false)
     */
    private $cryptographyKeyLength;

    public function getCryptographyKeyLengthId(): ?int
    {
        return $this->cryptographyKeyLengthId;
    }

    public function getCryptographyKeyLength(): ?int
    {
        return $this->cryptographyKeyLength;
    }

    public function setCryptographyKeyLength(int $cryptographyKeyLength): self
    {
        $this->cryptographyKeyLength = $cryptographyKeyLength;

        return $this;
    }


}
