<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Endianness
 *
 * @ORM\Table(name="endianness", uniqueConstraints={@ORM\UniqueConstraint(name="endianness_endianness_name_key", columns={"endianness_name"})})
 * @ORM\Entity
 */
class Endianness
{
    /**
     * @var int
     *
     * @ORM\Column(name="endianness_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="endianness_endianness_id_seq", allocationSize=1, initialValue=1)
     */
    private $endiannessId;

    /**
     * @var string
     *
     * @ORM\Column(name="endianness_name", type="text", nullable=false)
     */
    private $endiannessName;

    public function getEndiannessId(): ?int
    {
        return $this->endiannessId;
    }

    public function getEndiannessName(): ?string
    {
        return $this->endiannessName;
    }

    public function setEndiannessName(string $endiannessName): self
    {
        $this->endiannessName = $endiannessName;

        return $this;
    }


}
