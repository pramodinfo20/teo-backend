<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ResponsibilityModelRange
 *
 * @ORM\Table(name="responsibility_model_range", indexes={@ORM\Index(name="IDX_1088D3496267EB90", columns={"resp_assignments_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\ResponsibilityModelRangeRepository")
 */
class ResponsibilityModelRange
{
    /**
     * @var int
     *
     * @ORM\Column(name="rmr_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="responsibility_model_range_rmr_id_seq", allocationSize=1, initialValue=1)
     */
    private $rmrId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=false)
     */
    private $name;

    /**
     * @var \ResponsibilityAssignments
     *
     * @ORM\ManyToOne(targetEntity="ResponsibilityAssignments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="resp_assignments_id", referencedColumnName="ra_id")
     * })
     */
    private $respAssignments;

    public function getRmrId(): ?int
    {
        return $this->rmrId;
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

    public function getRespAssignments(): ?ResponsibilityAssignments
    {
        return $this->respAssignments;
    }

    public function setRespAssignments(?ResponsibilityAssignments $respAssignments): self
    {
        $this->respAssignments = $respAssignments;

        return $this;
    }


}
