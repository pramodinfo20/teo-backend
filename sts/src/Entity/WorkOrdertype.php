<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WorkOrdertype
 *
 * @ORM\Table(name="work_ordertype")
 * @ORM\Entity
 */
class WorkOrdertype
{
    /**
     * @var int
     *
     * @ORM\Column(name="typeid", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="work_ordertype_typeid_seq", allocationSize=1, initialValue=1)
     */
    private $typeid;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="text", nullable=false)
     */
    private $type;

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

    public function getTypeid(): ?int
    {
        return $this->typeid;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

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
