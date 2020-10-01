<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserRoleCompanyStructure
 *
 * @ORM\Table(name="user_role_company_structure", indexes={@ORM\Index(name="IDX_AB8E2293D5592B6B", columns={"sts_organization_structure_id"}), @ORM\Index(name="IDX_AB8E22938E0E3CA6", columns={"user_role_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\UserRoleCompanyStructureRepository")
 */
class UserRoleCompanyStructure
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="user_role_company_structure_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var StsOrganizationStructure
     *
     * @ORM\ManyToOne(targetEntity="StsOrganizationStructure")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sts_organization_structure_id", referencedColumnName="id")
     * })
     */
    private $stsOrganizationStructure;

    /**
     * @var UserRoles
     *
     * @ORM\ManyToOne(targetEntity="UserRoles")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_role_id", referencedColumnName="id")
     * })
     */
    private $userRole;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStsOrganizationStructure(): ?StsOrganizationStructure
    {
        return $this->stsOrganizationStructure;
    }

    public function setStsOrganizationStructure(?StsOrganizationStructure $stsOrganizationStructure): self
    {
        $this->stsOrganizationStructure = $stsOrganizationStructure;

        return $this;
    }

    public function getUserRole(): ?UserRoles
    {
        return $this->userRole;
    }

    public function setUserRole(?UserRoles $userRole): self
    {
        $this->userRole = $userRole;

        return $this;
    }


}
