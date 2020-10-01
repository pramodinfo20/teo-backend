<?php
namespace App\Model\UserRole\Assignment;

class CompanyStructureAssignmentModel
{
    /**
     * @var UserRoleModel[]
     */
    private $userRoles;

    /**
     * @var CompanyStructureModel[]
     */
    private $companyStructures;

    /**
     * @var array
     */
    private $userRolesChoice;

    /**
     * @var array
     */
    private $companyStructuresChoice;

    /**
     * @var array
     */
    private $companyStructureTree;

    /**
     * @var int
     */
    private $currentRole;

    /**
     * @var array
     */
    private $companyStructuresForCurrentRole;


    /**
     * @return UserRoleModel[]
     */
    public function getUserRoles(): ?array
    {
        return $this->userRoles;
    }

    /**
     * @param UserRoleModel[] $userRoles
     */
    public function setUserRoles(array $userRoles = null): void
    {
        $this->userRoles = $userRoles;
    }

    /**
     * @return CompanyStructureModel[]
     */
    public function getCompanyStructures(): ?array
    {
        return $this->companyStructures;
    }

    /**
     * @param CompanyStructureModel[] $companyStructures
     */
    public function setCompanyStructures(array $companyStructures = null): void
    {
        $this->companyStructures = $companyStructures;
    }

    /**
     * @return array
     */
    public function getUserRolesChoice(): ?array
    {
        return $this->userRolesChoice;
    }

    /**
     * @param array $userRolesChoice
     */
    public function setUserRolesChoice(array $userRolesChoice = null): void
    {
        $this->userRolesChoice = $userRolesChoice;
    }

    /**
     * @return array
     */
    public function getCompanyStructuresChoice(): ?array
    {
        return $this->companyStructuresChoice;
    }

    /**
     * @param array $companyStructuresChoice
     */
    public function setCompanyStructuresChoice(array $companyStructuresChoice = null): void
    {
        $this->companyStructuresChoice = $companyStructuresChoice;
    }

    /**
     * @return array
     */
    public function getCompanyStructureTree(): ?array
    {
        return $this->companyStructureTree;
    }

    /**
     * @param array $companyStructureTree
     */
    public function setCompanyStructureTree(array $companyStructureTree = null): void
    {
        $this->companyStructureTree = $companyStructureTree;
    }


    /**
     * @return int
     */
    public function getCurrentRole(): ?int
    {
        return $this->currentRole;
    }

    /**
     * @param int $currentRole
     */
    public function setCurrentRole(int $currentRole = null): void
    {
        $this->currentRole = $currentRole;
    }

    /**
     * @return array
     */
    public function getCompanyStructuresForCurrentRole(): ?array
    {
        return $this->companyStructuresForCurrentRole;
    }

    /**
     * @param array $companyStructuresForCurrentRole
     */
    public function setCompanyStructuresForCurrentRole(array $companyStructuresForCurrentRole = null): void
    {
        $this->companyStructuresForCurrentRole = $companyStructuresForCurrentRole;
    }
}