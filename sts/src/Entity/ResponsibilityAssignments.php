<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ResponsibilityAssignments
 *
 * @ORM\Table(name="responsibility_assignments", indexes={@ORM\Index(name="IDX_8DC6F92A357C7D2B", columns={"assigned_category_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\ResponsibilityAssignmentsRepository")
 */
class ResponsibilityAssignments
{
    /**
     * @var int
     *
     * @ORM\Column(name="ra_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="responsibility_assignments_ra_id_seq", allocationSize=1, initialValue=1)
     */
    private $raId;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="is_structure", type="boolean", nullable=true)
     */
    private $isStructure = false;

    /**
     * @var string|null
     *
     * @ORM\Column(name="structure_details", type="text", nullable=true)
     */
    private $structureDetails;

    /**
     * @var string|null
     *
     * @ORM\Column(name="responsibility_role", type="text", nullable=true)
     */
    private $responsibilityRole;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="is_responsible", type="boolean", nullable=true)
     */
    private $isResponsible = false;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="is_deputy", type="boolean", nullable=true)
     */
    private $isDeputy = false;

   

    /**
     * @var \ResponsibilityCategories
     *
     * @ORM\ManyToOne(targetEntity="ResponsibilityCategories")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="assigned_category_id", referencedColumnName="rc_id")
     * })
     */
    private $assignedCategory;

    /**
     * @var \Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="assigned_user_id", referencedColumnName="id")
     * })
     */
    private $assignedUser;

    /**
     * @var \StsOrganizationStructure
     *
     * @ORM\ManyToOne(targetEntity="StsOrganizationStructure")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sts_os_id", referencedColumnName="id")
     * })
     */
    private $stsOs;

    public function getRaId(): ?int
    {
        return $this->raId;
    }

    public function getIsStructure(): ?bool
    {
        return $this->isStructure;
    }

    public function setIsStructure(?bool $isStructure): self
    {
        $this->isStructure = $isStructure;

        return $this;
    }

    public function getStructureDetails(): ?string
    {
        return $this->structureDetails;
    }

    public function setStructureDetails(?string $structureDetails): self
    {
        $this->structureDetails = $structureDetails;

        return $this;
    }

    public function getResponsibilityRole(): ?string
    {
        return $this->responsibilityRole;
    }

    public function setResponsibilityRole(?string $responsibilityRole): self
    {
        $this->responsibilityRole = $responsibilityRole;

        return $this;
    }

    public function getIsResponsible(): ?bool
    {
        return $this->isResponsible;
    }

    public function setIsResponsible(?bool $isResponsible): self
    {
        $this->isResponsible = $isResponsible;

        return $this;
    }

    public function getIsDeputy(): ?bool
    {
        return $this->isDeputy;
    }

    public function setIsDeputy(?bool $isDeputy): self
    {
        $this->isDeputy = $isDeputy;

        return $this;
    }

    public function getAssignedCategory(): ?ResponsibilityCategories
    {
        return $this->assignedCategory;
    }

    public function setAssignedCategory(?ResponsibilityCategories $assignedCategory): self
    {
        $this->assignedCategory = $assignedCategory;

        return $this;
    }

    public function getAssignedUser(): ?Users
    {
        return $this->assignedUser;
    }

    public function setAssignedUser(?Users $assignedUser): self
    {
        $this->assignedUser = $assignedUser;

        return $this;
    }

    public function getStsOs(): ?StsOrganizationStructure
    {
        return $this->stsOs;
    }

    public function setStsOs(?StsOrganizationStructure $stsOs): self
    {
        $this->stsOs = $stsOs;

        return $this;
    }


}
