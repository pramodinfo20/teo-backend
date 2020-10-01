<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Zspl
 *
 * @ORM\Table(name="zspl", uniqueConstraints={@ORM\UniqueConstraint(name="unique_dp_zspl_id", columns={"dp_zspl_id"})})
 * @ORM\Entity
 */
class Zspl
{
    /**
     * @var int
     *
     * @ORM\Column(name="zspl_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="zspl_zspl_id_seq", allocationSize=1, initialValue=1)
     */
    private $zsplId;

    /**
     * @var int
     *
     * @ORM\Column(name="division_id", type="integer", nullable=false)
     */
    private $divisionId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="emails", type="text", nullable=true)
     */
    private $emails;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    /**
     * @var int|null
     *
     * @ORM\Column(name="dp_zspl_id", type="bigint", nullable=true)
     */
    private $dpZsplId;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="active", type="boolean", nullable=true)
     */
    private $active;

    public function getZsplId(): ?int
    {
        return $this->zsplId;
    }

    public function getDivisionId(): ?int
    {
        return $this->divisionId;
    }

    public function setDivisionId(int $divisionId): self
    {
        $this->divisionId = $divisionId;

        return $this;
    }

    public function getEmails(): ?string
    {
        return $this->emails;
    }

    public function setEmails(?string $emails): self
    {
        $this->emails = $emails;

        return $this;
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

    public function getDpZsplId(): ?int
    {
        return $this->dpZsplId;
    }

    public function setDpZsplId(?int $dpZsplId): self
    {
        $this->dpZsplId = $dpZsplId;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }


}
