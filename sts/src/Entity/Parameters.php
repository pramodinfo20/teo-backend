<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Analysis.parameters
 *
 * @ORM\Table(name="parameters", schema="analysis")
 * @ORM\Entity
 */
class Parameters
{
    /**
     * @var string
     *
     * @ORM\Column(name="program", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $program;

    /**
     * @var string
     *
     * @ORM\Column(name="parameter", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $parameter;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", nullable=false)
     */
    private $value;

    /**
     * @var string|null
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    public function getProgram(): ?string
    {
        return $this->program;
    }

    public function getParameter(): ?string
    {
        return $this->parameter;
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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }


}
