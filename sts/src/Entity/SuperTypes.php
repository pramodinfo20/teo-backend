<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SuperTypes
 *
 * @ORM\Table(name="super_types")
 * @ORM\Entity
 */
class SuperTypes
{
    /**
     * @var int
     *
     * @ORM\Column(name="super_type_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="super_types_super_type_id_seq", allocationSize=1, initialValue=1)
     */
    private $superTypeId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="letter", type="string", length=1, nullable=true, options={"fixed"=true})
     */
    private $letter;

    public function getSuperTypeId(): ?int
    {
        return $this->superTypeId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLetter(): ?string
    {
        return $this->letter;
    }

    public function setLetter(?string $letter): self
    {
        $this->letter = $letter;

        return $this;
    }


}
