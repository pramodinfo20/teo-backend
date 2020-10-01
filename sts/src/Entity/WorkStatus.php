<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WorkStatus
 *
 * @ORM\Table(name="work_status")
 * @ORM\Entity
 */
class WorkStatus
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="work_status_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="text", nullable=false)
     */
    private $status;

    /**
     * @var string|null
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="is_activ", type="boolean", nullable=true)
     */
    private $isActiv;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

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

    public function getIsActiv(): ?bool
    {
        return $this->isActiv;
    }

    public function setIsActiv(?bool $isActiv): self
    {
        $this->isActiv = $isActiv;

        return $this;
    }


}
