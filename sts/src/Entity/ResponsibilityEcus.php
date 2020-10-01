<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ResponsibilityEcus
 *
 * @ORM\Table(name="responsibility_ecus", indexes={@ORM\Index(name="IDX_DDE31DC06267EB90", columns={"resp_assignments_id"}), @ORM\Index(name="IDX_DDE31DC0F2887E5B", columns={"ecu_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\ResponsibilityEcusRepository")
 */
class ResponsibilityEcus
{
    /**
     * @var int
     *
     * @ORM\Column(name="re_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="responsibility_ecus_re_id_seq", allocationSize=1, initialValue=1)
     */
    private $reId;

    /**
     * @var \ResponsibilityAssignments
     *
     * @ORM\ManyToOne(targetEntity="ResponsibilityAssignments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="resp_assignments_id", referencedColumnName="ra_id")
     * })
     */
    private $respAssignments;

    /**
     * @var \ConfigurationEcus
     *
     * @ORM\ManyToOne(targetEntity="ConfigurationEcus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ecu_id", referencedColumnName="ce_ecu_id")
     * })
     */
    private $ecu;

    public function getReId(): ?int
    {
        return $this->reId;
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

    public function getEcu(): ?ConfigurationEcus
    {
        return $this->ecu;
    }

    public function setEcu(?ConfigurationEcus $ecu): self
    {
        $this->ecu = $ecu;

        return $this;
    }


}
