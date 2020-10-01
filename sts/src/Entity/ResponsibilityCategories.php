<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ResponsibilityCategories
 *
 * @ORM\Table(name="responsibility_categories")
 * @ORM\Entity(repositoryClass="App\Repository\ResponsibilityCategoriesRepository")
 */
class ResponsibilityCategories
{
    /**
     * @var int
     *
     * @ORM\Column(name="rc_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="responsibility_categories_rc_id_seq", allocationSize=1, initialValue=1)
     */
    private $rcId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     */
    private $parentId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=false)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="code", type="text", nullable=true)
     */
    private $code;

    public function getRcId(): ?int
    {
        return $this->rcId;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setParentId(?int $parentId): self
    {
        $this->parentId = $parentId;

        return $this;
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }


}
