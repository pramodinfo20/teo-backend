<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TranslationsLanguage
 *
 * @ORM\Table(name="translations_language", uniqueConstraints={@ORM\UniqueConstraint(name="translations_language_name_key", columns={"name"})})
 * @ORM\Entity
 */
class TranslationsLanguage
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="translations_language_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=false)
     */
    private $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }


}
