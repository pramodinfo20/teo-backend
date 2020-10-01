<?php
namespace App\Model\UserRole\Assignment;


class CompanyStructureModel
{
    /**
     * @var int
     */
    private $companyStructureId;

    /**
     * @var string
     */
    private $companyStructureName;

    /**
     * @var int
     */
    private $companyStructureParentId;

    /**
     * @var int
     */
    private $userRole;


    /**
     * @return int
     */
    public function getCompanyStructureId(): ?int
    {
        return $this->companyStructureId;
    }

    /**
     * @param int $companyStructureId
     */
    public function setCompanyStructureId(int $companyStructureId = null): void
    {
        $this->companyStructureId = $companyStructureId;
    }

    /**
     * @return string
     */
    public function getCompanyStructureName(): ?string
    {
        return $this->companyStructureName;
    }

    /**
     * @param string $companyStructureName
     */
    public function setCompanyStructureName(string $companyStructureName = null): void
    {
        $this->companyStructureName = $companyStructureName;
    }

    /**
     * @return int
     */
    public function getCompanyStructureParentId(): ?int
    {
        return $this->companyStructureParentId;
    }

    /**
     * @param int $companyStructureParentId
     */
    public function setCompanyStructureParentId(int $companyStructureParentId = null): void
    {
        $this->companyStructureParentId = $companyStructureParentId;
    }

    /**
     * @return int|null
     */
    public function getUserRole() : ?int
    {
        return $this->userRole;
    }

    /**
     * @param int|null $userRole
     */
    public function setUserRole(int $userRole = null): void
    {
        $this->userRole = $userRole;
    }
}