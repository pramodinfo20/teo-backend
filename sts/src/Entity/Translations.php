<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Translations
 *
 * @ORM\Table(name="translations", uniqueConstraints={@ORM\UniqueConstraint(name="translations_key_domain_id_language_id_key", columns={"key", "domain_id", "language_id"})}, indexes={@ORM\Index(name="IDX_C6B7DA87115F0EE5", columns={"domain_id"}), @ORM\Index(name="IDX_C6B7DA8782F1BAF4", columns={"language_id"})})
 * @ORM\Entity
 */
class Translations
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="translations_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="key", type="text", nullable=false)
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", nullable=false)
     */
    private $value;

    /**
     * @var \TranslationsDomain
     *
     * @ORM\ManyToOne(targetEntity="TranslationsDomain")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="domain_id", referencedColumnName="id")
     * })
     */
    private $domain;

    /**
     * @var \TranslationsLanguage
     *
     * @ORM\ManyToOne(targetEntity="TranslationsLanguage")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="language_id", referencedColumnName="id")
     * })
     */
    private $language;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getDomain(): ?TranslationsDomain
    {
        return $this->domain;
    }

    public function setDomain(?TranslationsDomain $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    public function getLanguage(): ?TranslationsLanguage
    {
        return $this->language;
    }

    public function setLanguage(?TranslationsLanguage $language): self
    {
        $this->language = $language;

        return $this;
    }


}
