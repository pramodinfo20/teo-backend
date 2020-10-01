<?php

namespace App\Service\UserRole;

use App\Entity\HistoryPersonsListHr;
use App\Entity\StsOrganizationStructure;
use App\Entity\UserRoleCompanyStructure;
use App\Entity\UserRoles;
use App\Model\UserRole\Assignment\CompanyStructureAssignmentModel;
use App\Model\UserRole\Assignment\CompanyStructureModel;
use App\Model\UserRole\Assignment\UserRoleModel;

use App\Utils\Choice;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

class UserRoleToCompanyStructure
{
    const USER_ROLE_ID = 'userRoleId';
    const USER_ROLE_VALUE = 'userRoleName';
    const COMPANY_STRUCTURE_ID = 'id';
    const COMPANY_STRUCTURE_VALUE = 'name';

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * Parameter constructor.
     *
     * @param ObjectManager $manager
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ObjectManager $manager, EntityManagerInterface $entityManager)
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
    }

    /**
     * @return CompanyStructureAssignmentModel
     */
    public function getCompanyStructAssignmentFromDb(): CompanyStructureAssignmentModel
    {
        // Get data from DB
        $userRoles = $this->manager->getRepository(UserRoles::class)->findAll();
        $organizationStructure = $this->manager->getRepository(StsOrganizationStructure::class)->findAll();
        $userRoleCompanyStructure = $this->manager->getRepository(UserRoleCompanyStructure::class)->getAssignedUserRoleToOrgStructure();

        $stsOrgStructureObjectsArray = [];
        foreach ($organizationStructure as $orgStruct) {
            $tmp = array_filter($userRoleCompanyStructure, function ($param) use ($orgStruct) {
                return $param['sts_org_struct'] == $orgStruct->getId();
            });

            $tmp = array_shift($tmp);

            $structureObject = new CompanyStructureModel();

            $structureObject->setCompanyStructureId($orgStruct->getId());
            $structureObject->setCompanyStructureName($orgStruct->getName());
            $structureObject->setCompanyStructureParentId($orgStruct->getParent() ? $orgStruct->getParent()->getId() : null);
            $structureObject->setUserRole($tmp['user_role']);

            $structureArray['id'] = $orgStruct->getId();
            $structureArray['name'] = $orgStruct->getName();
            $structureArray['parent_id'] = $orgStruct->getParent() ? $orgStruct->getParent()->getId() : null;
            $structureArray['user_role_id'] = $tmp;

            $companyStructuresArray[] = $structureArray;

            $stsOrgStructureObjectsArray[] = $structureObject;
        }

        $userRolesObjectsArray = [];
        foreach ($userRoles as $userRole) {
            $userRoleObject = new UserRoleModel();

            $userRoleObject->setUserRoleId($userRole->getId());
            $userRoleObject->setUserRoleName($userRole->getName());

            $userRolesObjectsArray[] = $userRoleObject;
        }

        $companyStructuresArray = $this->prepareCompanyStructuresArray($companyStructuresArray);

        // Return Company Structure Assignment Model
        $companyStructAssignment = new CompanyStructureAssignmentModel();

        $companyStructAssignment->setUserRoles($userRolesObjectsArray);
        $companyStructAssignment->setCompanyStructures($stsOrgStructureObjectsArray);
        $companyStructAssignment->setUserRolesChoice(Choice::transformToChoice($userRolesObjectsArray, self::USER_ROLE_ID, self::USER_ROLE_VALUE));
        $companyStructAssignment->setCompanyStructuresChoice(Choice::transformArrayToChoice($this->flattenTreeArray
        ($companyStructuresArray), self::COMPANY_STRUCTURE_ID, self::COMPANY_STRUCTURE_VALUE));
        $companyStructAssignment->setCompanyStructureTree($this->flattenTreeArray($companyStructuresArray));
        $companyStructAssignment->setCurrentRole(current(Choice::transformToChoice($userRolesObjectsArray, self::USER_ROLE_ID,
            self::USER_ROLE_VALUE)));
        $companyStructAssignment->setCompanyStructuresForCurrentRole($this->findCompanyStructuresIdsByUserRole($companyStructAssignment));


        return $companyStructAssignment;
    }

    /**
     * @param array $structures
     *
     * @return array
     */
    private function prepareCompanyStructuresArray(array $structures): array
    {
        $this->buildTreeStructure($structures, null);
        $this->fillDepth($structures);
        $this->sortStructures($structures);

        return $structures;
    }

    /**
     * @param array $rawStructures
     * @param int|null $parent
     * @return array
     */
    public function buildTreeStructure(array &$rawStructures, int $parent = null): array
    {
        $branch = [];

        foreach ($rawStructures as &$structure) {
            if ($structure['parent_id'] == $parent) {
                $children = $this->buildTreeStructure($rawStructures, $structure['id']);

                if ($children) {
                    $structure['children'] = $children;
                }

                $branch[$structure['id']] = $structure;
                unset($rawStructures[$structure['id']]);
            }
        }

        return $branch;
    }

    /**
     * @param array $structures
     * @param int $depth
     *
     * @return void
     */
    private function fillDepth(array &$structures, int $depth = 0): void
    {
        foreach ($structures as &$structure) {
            if (isset($structure['children'])) {
                $this->fillDepth($structure['children'], $depth + 1);
            }

            $structure['depth'] = $depth;

            if (is_array($structure)) {
                $structure['depth'] = $depth;
            }
        }
    }

    /**
     * @param array $structures
     *
     * @return void
     */
    private function sortStructures(array &$structures): void
    {
        $name = array_column($structures, 'name');
        array_multisort($name, SORT_ASC, $structures);
    }

    /**
     * @param array $structures
     *
     * @return array
     */
    private function flattenTreeArray(array $structures): array
    {
        $flatArray = [];

        foreach ($structures as $key => $node) {
            if (array_key_exists('children', $node)) {
                $flatArray[] = $node;
                $flatArray = array_merge($flatArray, $this->flattenTreeArray($node['children']));
                unset($node['children']);
            } else {
                $flatArray[] = $node;
            }
        }

        return $flatArray;
    }

    /**
     * @param CompanyStructureAssignmentModel $companyStructureAssignmentModel
     * @param int $role
     *
     * @return array
     */
    public function findCompanyStructuresIdsByUserRole(CompanyStructureAssignmentModel $companyStructureAssignmentModel, int $role = null): array
    {
        $companyStructures = [];
        if (is_null($role)) {
            $currentRole = $companyStructureAssignmentModel->getCurrentRole();
        } else {
            $currentRole = $role;
        }

        foreach ($companyStructureAssignmentModel->getCompanyStructures() as $structure) {
            if ($structure->getUserRole() == $currentRole) {
                $companyStructures[] = $structure->getCompanyStructureId();
            }
        }

        return $companyStructures;
    }

    /**
     * @param array $elements
     * @param int|null $parentId
     * @return array
     */
    public function buildTreeStructureForGraph(array &$elements, int $parentId = null): array
    {
        $branch = array();

        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTreeStructureForGraph($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $key = array_search($element['id'], array_column($elements, 'id'));

                $branch[$element['id']] = $element;

                unset($elements[$key]);
            }
        }

        return $branch;
    }

    /**
     * @param HistoryPersonsListHr $history
     *
     * @return CompanyStructureAssignmentModel
     */
    public function getCompanyStructAssignmentFromJSON(HistoryPersonsListHr $history): CompanyStructureAssignmentModel
    {
        foreach ($history->getHistoryData() as $value) {
            foreach ($value as $key => $val) {
                $json[$key] = $val;
            }
        }
        $userRolesHistory = $json['user_roles'];
        $organizationStructureHistory = $json['sts_organization_structure'];
        $userRoleCompanyStructureHistory = $json['user_role_company_structure'];

        $companyStructures = [];
        $companyStructuresArray = [];
        foreach ($organizationStructureHistory as $orgStruct) {
            $structure = new CompanyStructureModel();
            $structure->setCompanyStructureId($orgStruct['id']);
            $structure->setCompanyStructureName($orgStruct['name']);
            $structure->getCompanyStructureParentId($orgStruct['parent_id']);


            $tmp = array_filter($userRoleCompanyStructureHistory, function ($row) use ($orgStruct) {
                return $row['sts_organization_structure_id'] == $orgStruct['id'];
            });

            if (!empty($tmp)) {
                $tmp = array_map(function ($row) {
                    return $row['user_role_id'];
                }, $tmp);
            }
            $tmp = array_shift($tmp);

            $structure->setUserRole($tmp);

            $structureArray['id'] = $orgStruct['id'];
            $structureArray['name'] = $orgStruct['name'];
            $structureArray['parent_id'] = $orgStruct['parent_id'];
            $structureArray['user_role_id'] = $tmp;

            $companyStructuresArray[] = $structureArray;
            $companyStructures[] = $structure;
        }

        $userRoles = [];
        foreach ($userRolesHistory as $role) {
            $roleObj = new UserRoleModel();
            $roleObj->setUserRoleId($role['id']);
            $roleObj->setUserRoleName($role['name']);

            $userRoles[] = $roleObj;
        }

        $companyStructuresArray = $this->prepareCompanyStructuresArray($companyStructuresArray);

        // Return Company Structure Assignment Model
        $companyStructAssignment = new CompanyStructureAssignmentModel();

        $companyStructAssignment->setUserRoles($userRoles);
        $companyStructAssignment->setCompanyStructures($companyStructures);
        $companyStructAssignment->setUserRolesChoice(Choice::transformToChoice($userRoles, self::USER_ROLE_ID, self::USER_ROLE_VALUE));
        $companyStructAssignment->setCompanyStructuresChoice(Choice::transformArrayToChoice($this->flattenTreeArray
        ($companyStructuresArray), self::COMPANY_STRUCTURE_ID, self::COMPANY_STRUCTURE_VALUE));
        $companyStructAssignment->setCompanyStructureTree($this->flattenTreeArray($companyStructuresArray));
        $companyStructAssignment->setCurrentRole(current(Choice::transformToChoice($userRoles, self::USER_ROLE_ID,
            self::USER_ROLE_VALUE)));
        $companyStructAssignment->setCompanyStructuresForCurrentRole($this->findCompanyStructuresIdsByUserRole($companyStructAssignment));

        return $companyStructAssignment;
    }

    /**
     * @param CompanyStructureAssignmentModel $model
     * @param bool $overwrite
     *
     * @return UserRole
     * @throws \Exception
     */
    public function save(CompanyStructureAssignmentModel $model, bool $overwrite = false): UserRole
    {
        /* Start transaction */
        $this->entityManager->beginTransaction();

        try {
            $currentRole = $model->getCurrentRole();
            $companyStructuresForCurrentRole = $model->getCompanyStructuresForCurrentRole();
            $companyStructures = $this->entityManager->getRepository(UserRoleCompanyStructure::class)->findAll();
            $assignedStructures = [];
            $assignedStructuresToOverwrite = [];

            foreach ($companyStructuresForCurrentRole as $structure) {
                $companyStructuresForCurrentRoleDB = $this->entityManager->getRepository
                (UserRoleCompanyStructure::class)
                    ->findBy(['userRole' => $currentRole, 'stsOrganizationStructure' => $structure]);

                $companyStructure = array_filter($companyStructures, function ($value) use ($structure) {
                    return $value->getStsOrganizationStructure()->getId() == $structure;
                });


                if (empty($companyStructuresForCurrentRoleDB)) {
                    if (!empty($companyStructure)) {
                        $assignedStructures = array_unique(array_merge($assignedStructures, array_map(function ($value) {
                            return $value->getStsOrganizationStructure()->getName();
                        }, $companyStructure)));

                        $assignedStructuresToOverwrite = array_merge($assignedStructuresToOverwrite,
                            $companyStructure);
                    } else {
                        $userRoleCompanyStructure = new UserRoleCompanyStructure();

                        $userRoleCompanyStructure
                            ->setUserRole($this->entityManager->getRepository(UserRoles::class)->find($currentRole));
                        $userRoleCompanyStructure
                            ->setStsOrganizationStructure($this->entityManager->getRepository
                            (StsOrganizationStructure::class)->find($structure));

                        $this->entityManager->persist($userRoleCompanyStructure);
                    }
                }
            }

            if (!empty($assignedStructures)) {
                if ($overwrite) {
                    $role = $this->entityManager->getRepository(UserRoles::class)->find($currentRole);
                    foreach ($assignedStructuresToOverwrite as $toOverwrite) {
                        $overwritten = new UserRoleCompanyStructure();
                        $overwritten->setUserRole($role);
                        $overwritten->setStsOrganizationStructure($toOverwrite->getStsOrganizationStructure());

                        $this->entityManager->persist($overwritten);
                        $this->entityManager->remove($toOverwrite);
                    }
                } else {
                    return new UserRole($model->getCurrentRole(), $assignedStructures);
                }
            } else {
                $assignmentsToRemove = $this->entityManager->getRepository
                (UserRoleCompanyStructure::class)
                    ->findNotEqualById($currentRole, $companyStructuresForCurrentRole);

                foreach ($assignmentsToRemove as $toRemove) {
                    $this->entityManager->remove($toRemove);
                }
            }

            $this->entityManager->flush();

            /* Commit */
            $this->entityManager->commit();
        } catch (\Exception $exception) {
            /* Rollback */
            $this->entityManager->rollBack();
            throw $exception;
        }

        return new UserRole($model->getCurrentRole());
    }

    public function transformForGraphInput(array &$graphTreeStructure, int $level = 0): array
    {
        $resultArray = [];
        foreach ($graphTreeStructure as &$elementInTree) {
            $preparedGraph = [];

            if ($elementInTree['parent_id'] == null) {
                $preparedGraph['collapsed'] = false;
                $preparedGraph['otherPro'] = 1;
            } else {
                $preparedGraph['relationship'] = "100";
                $hasChildren = array_key_exists('children', $elementInTree);
                if ($hasChildren)
                    $preparedGraph['relationship'] = substr_replace($preparedGraph['relationship'], '1', 2, 1);

                if (count($graphTreeStructure) > 1) {
                    $preparedGraph['relationship'] = substr_replace($preparedGraph['relationship'], '1', 1, 1);
                }
            }

            $preparedGraph['title'] = $elementInTree['person'];
            $preparedGraph['content'] = $elementInTree['name'];
            $preparedGraph['className'] = 'level_' . $level;

            if (array_key_exists('children', $elementInTree)) {
                $preparedGraph['children'] = $this->transformForGraphInput($elementInTree['children'], $level + 1);
            }

            array_push($resultArray, $preparedGraph);
        }

        return $resultArray;
    }
}
