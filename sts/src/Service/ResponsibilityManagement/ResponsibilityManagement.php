<?php

namespace App\Service\ResponsibilityManagement;


use App\Entity\ConfigurationEcus;
use App\Entity\ResponsibilityAssignments;
use App\Entity\ResponsibilityCategories;
use App\Entity\ResponsibilityEcus;
use App\Entity\ResponsibilityModelRange;
use App\Entity\StsOrganizationStructure;
use App\Entity\Users;
use App\Enum\ResponsibilityRoles;
use App\Service\AbstractService;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResponsibilityManagement extends AbstractService
{
    /**
     * @param ResponsibilityCategories $resp
     * @param string $subResp
     * @param int $user
     * @param bool $userType
     * @param int $role
     * @return void
     * @throws NonUniqueResultException
     */
    public function changeUserRole(ResponsibilityCategories $resp, string $subResp, int $user, bool $userType, int $role): void
    {
        $errorMessages = $this->getTranslation();

        $this->entityManager->beginTransaction();

        $responsibilityAssignmentManager = $this->entityManager
            ->getRepository(ResponsibilityAssignments::class);

        $selectedResponsibilityAssignment = null;

        if ($resp->getRcId() == 1) {
            $selectedResponsibilityAssignment = $responsibilityAssignmentManager
                ->find($responsibilityAssignmentManager->getRespAssignIdForSelectedEcu($subResp, $user, $userType));
        } elseif ($resp->getRcId() == 2) {
            $selectedResponsibilityAssignment = $responsibilityAssignmentManager
                ->find($responsibilityAssignmentManager->getRespAssignIdForSelectedModelRange($subResp, $user, $userType));
        } elseif ($resp->getRcId()) {
            $selectedResponsibilityAssignment = $responsibilityAssignmentManager
                ->find($responsibilityAssignmentManager->getRespAssignIdForSelected($user, $userType));
        }


        try {
            switch ($role) {
                // To responsible
                case ResponsibilityRoles::RESPONSIBLE:
                    $responsibleAssigned = [];
                    if ($resp->getRcId() == 1) {
                        $responsibleAssigned = $responsibilityAssignmentManager->checkIdForResponsibleAssignedForEcu($subResp);
                    } elseif ($resp->getRcId() == 2) {
                        $responsibleAssigned = $responsibilityAssignmentManager->checkIdForResponsibleAssignedForModelRange($subResp);
                    } elseif ($resp->getRcId()) {
                        $responsibleAssigned = $responsibilityAssignmentManager->checkIdForResponsibleAssigned();
                    }


                    if (!empty($responsibleAssigned)) {
                        if ($responsibleAssigned == $selectedResponsibilityAssignment->getRaId()) {
                            throw new Exception($errorMessages->currentUserIsAlreadyAssigned(!$userType, ResponsibilityRoles::RESPONSIBLE));
                            break;
                        } else {
                            throw new Exception($errorMessages->anotherUserIsAssigned(ResponsibilityRoles::RESPONSIBLE));
                            break;
                        }
                    }

                    $selectedResponsibilityAssignment->setIsResponsible(true);
                    $selectedResponsibilityAssignment->setIsDeputy(false);
                    break;

                // To deputy
                case ResponsibilityRoles::DEPUTY:

                    $deputyAssigned = [];
                    if ($resp->getRcId() == 1) {
                        $deputyAssigned = $responsibilityAssignmentManager->checkIdForDeputyAssignedForEcu($subResp);
                    } elseif ($resp->getRcId() == 2) {
                        $deputyAssigned = $responsibilityAssignmentManager->checkIdForDeputyAssignedForModelRange($subResp);
                    } elseif ($resp->getRcId()) {
                        $deputyAssigned = $responsibilityAssignmentManager->checkIdForDeputyAssigned();
                    }


                    if (!empty($deputyAssigned)) {
                        if ($deputyAssigned == $selectedResponsibilityAssignment->getRaId()) {
                            throw new Exception($errorMessages->currentUserIsAlreadyAssigned(!$userType, ResponsibilityRoles::DEPUTY));
                            break;
                        } else {
                            throw new Exception($errorMessages->anotherUserIsAssigned(ResponsibilityRoles::DEPUTY));
                            break;
                        }
                    }

                    $selectedResponsibilityAssignment->setIsResponsible(false);
                    $selectedResponsibilityAssignment->setIsDeputy(true);
                    break;

                // To writable
                case ResponsibilityRoles::WRITE:
                    $selectedResponsibilityAssignment->setIsResponsible(false);
                    $selectedResponsibilityAssignment->setIsDeputy(false);
                    break;
            }
            $this->entityManager->persist($selectedResponsibilityAssignment);
            $this->entityManager->flush();

            /* Commit */
            $this->entityManager->commit();
        } catch
        (Exception $exception) {
            /* Rollback */
            $this->entityManager->rollBack();
            throw $exception;
        }
    }

    private function getTranslation()
    {
        return new class($this->translator)
        {
            private $translator;

            /**
             *  constructor.
             * @param TranslatorInterface $translator
             */
            public function __construct(TranslatorInterface $translator)
            {
//                var_dump($translator);
                $this->translator = $translator;
            }

            private function checkUserType(bool $isStructure): string
            {
                if ($isStructure == true)
                    return $this->translator->trans('errorMessages.responsibilityManagement.responsibilityManagement.structure', [], 'services');
                else
                    return $this->translator->trans('errorMessages.responsibilityManagement.responsibilityManagement.user', [], 'services');
            }

            private function checkRole(int $role): string
            {
                switch ($role) {
                    // Write
                    case ResponsibilityRoles::WRITE:
                        return $this->translator->trans('errorMessages.responsibilityManagement.responsibilityManagement.writable', [], 'services');
                        break;
                    // Deputy
                    case ResponsibilityRoles::DEPUTY:
                        return $this->translator->trans('errorMessages.responsibilityManagement.responsibilityManagement.deputy', [], 'services');
                        break;
                    // Responsible
                    case ResponsibilityRoles::RESPONSIBLE:
                        return $this->translator->trans('errorMessages.responsibilityManagement.responsibilityManagement.responsible', [], 'services');
                        break;
                    default:
                        return null;
                }
            }

            private function checkPosition(int $manager): string
            {
                switch ($manager) {
                    // Write
                    case ResponsibilityRoles::DEPARTMENT:
                        return $this->translator->trans('errorMessages.responsibilityManagement.responsibilityManagement.writable', [], 'services');
                        break;
                    // Deputy
                    case ResponsibilityRoles::DIVISION:
                        return $this->translator->trans('errorMessages.responsibilityManagement.responsibilityManagement.deputy', [], 'services');
                        break;
                    // Responsible
                    case ResponsibilityRoles::COMPANY:
                        return $this->translator->trans('errorMessages.responsibilityManagement.responsibilityManagement.responsible', [], 'services');
                        break;
                    default:
                        return null;
                }
            }

            /**
             * @param bool $isStructure
             * @param int $role
             * @return string
             */
            public function currentUserIsAlreadyAssigned(bool $isStructure, int $role, int $manager)
            {
                $userTypeTranslation = $this->checkUserType($isStructure);

                $roleTranslation = $this->checkRole($role);

                $ManagerTranslation = $this->checkManager($manager);

                return $this->translator->trans(
                    'errorMessages.responsibilityManagement.responsibilityManagement.currentUserIsAlreadyAssigned',
                    ['%userType%' => $userTypeTranslation, '%role%' => $roleTranslation, '%manager%' => $managerTranslation],
                    'services');
            }

            /**
             * @param int $role
             * @return string
             */
            public function anotherUserIsAssigned(int $role, int $manager)
            {
                $roleTranslation = $this->checkRole($role);

                $ManagerTranslation = $this->checkManager($manager);

                return $this->translator->trans(
                    'errorMessages.responsibilityManagement.responsibilityManagement.anotherUserIsAssigned',
                    ['%role%' => $roleTranslation, '%manager%' => $managerTranslation], 'services');
            }

            /**
             * @param bool $isStructure
             * @return string
             */
            public function currentUserHasAnotherRole(bool $isStructure)
            {
                $userTypeTranslation = $this->checkUserType($isStructure);

                return $this->translator->trans(
                    'errorMessages.responsibilityManagement.responsibilityManagement.currentUserHasAnotherRole',
                    ['%userType%' => $userTypeTranslation], 'services');
            }

            public function allowedUsersLimitAchieved() {
                return $this->translator->trans(
                    'errorMessages.responsibilityManagement.responsibilityManagement.maxUserCountAchieved',
                    [],
                    'services');
            }
        };
    }

    /**
     * @param ResponsibilityCategories $resp
     * @param string $subResp
     * @param int $user
     * @param bool $userType
     * @return void
     * @throws Exception
     */
    public function removeUserRole(ResponsibilityCategories $resp, string $subResp, int $user, bool $userType): void
    {
        $this->entityManager->beginTransaction();

        try {
            $responsibilityAssignmentManager = $this->entityManager
                ->getRepository(ResponsibilityAssignments::class);

            $selectedResAsg = null;
            if ($resp->getRcId() == 1) {
                $selectedResAsg = $responsibilityAssignmentManager
                    ->find($responsibilityAssignmentManager->getRespAssignIdForSelectedEcu($subResp, $user, $userType));

                $respEcusManager = $this->entityManager->getRepository(ResponsibilityEcus::class);
                $selectedResEcu = $respEcusManager->findOneBy(['respAssignments' => $selectedResAsg]);

                $this->entityManager->remove($selectedResEcu);

            } elseif ($resp->getRcId() == 2) {
                $selectedResAsg = $responsibilityAssignmentManager
                    ->find($responsibilityAssignmentManager->getRespAssignIdForSelectedModelRange($subResp, $user, $userType));

                $respModelRangeManager = $this->entityManager->getRepository(ResponsibilityModelRange::class);
                $selectedResModelRange = $respModelRangeManager->findOneBy(['respAssignments' => $selectedResAsg]);

                $this->entityManager->remove($selectedResModelRange);

            } elseif ($resp->getRcId()) {
                $selectedResAsg = $responsibilityAssignmentManager
                    ->find($responsibilityAssignmentManager->getRespAssignIdForSelected($user, $userType));
            }

            $this->entityManager->remove($selectedResAsg);
            $this->entityManager->flush();

            /* Commit */
            $this->entityManager->commit();

        } catch (Exception $exception) {
            /* Rollback */
            $this->entityManager->rollBack();
            throw $exception;
        }
    }

    /**
     * @param ResponsibilityCategories $resp
     * @param string $subResp
     * @param Users $user
     * @param string $posDetails
     * @param int $role
     * @return void
     * @throws NonUniqueResultException
     */
    public function addUser(ResponsibilityCategories $resp, string $subResp, Users $user, string $posDetails, int $role): void
    {
        $this->entityManager->beginTransaction();

        $responsibilityAssignment = new ResponsibilityAssignments();

        $responsibilityAssignmentManager = $this->entityManager
            ->getRepository(ResponsibilityAssignments::class);

        $responsibleAssigned = [];
        $deputyAssigned = [];
        $userAlreadyAssigned = [];
        $deviationPermissionUserCount = 0;

        if ($resp->getRcId() == 1) {
            $responsibilityEcusManager = $this->entityManager->getRepository(ResponsibilityEcus::class);
            $responsibleAssigned = $responsibilityAssignmentManager->checkIdForResponsibleAssignedForEcu($subResp);
            $deputyAssigned = $responsibilityAssignmentManager->checkIdForDeputyAssignedForEcu($subResp);
            $userAlreadyAssigned = $responsibilityEcusManager->checkIfUserOrgAlreadyAssignedForEcu($user->getId(), $subResp, false);
        } elseif ($resp->getRcId() == 2) {
            $responsibilityModelRangeManager = $this->entityManager->getRepository(ResponsibilityModelRange::class);
            $responsibleAssigned = $responsibilityAssignmentManager->checkIdForResponsibleAssignedForModelRange($subResp);
            $deputyAssigned = $responsibilityAssignmentManager->checkIdForDeputyAssignedForModelRange($subResp);
            $userAlreadyAssigned = $responsibilityModelRangeManager->checkIfUserOrgAlreadyAssignedForModelRange($user->getId(), $subResp, false);
        } elseif ($resp->getRcId() == 8) {
            $deviationPermissionUserCount = $responsibilityAssignmentManager->count(['assignedCategory' => 8]);
        }


        $errorMessages = $this->getTranslation();

        if (!empty($responsibleAssigned) and $role == 2)
            throw new Exception($errorMessages->anotherUserIsAssigned(ResponsibilityRoles::RESPONSIBLE));

        if (!empty($deputyAssigned) and $role == 1)
            throw new Exception($errorMessages->anotherUserIsAssigned(ResponsibilityRoles::DEPUTY));

        if ($userAlreadyAssigned)
            throw new Exception($errorMessages->currentUserHasAnotherRole(false));

        if ($deviationPermissionUserCount >= 4) {
            throw new Exception($errorMessages->allowedUsersLimitAchieved());
        }

        try {
            $responsibilityAssignment->setAssignedCategory($resp);
            $responsibilityAssignment->setAssignedUser($user);
            $responsibilityAssignment->setIsStructure(false);
            $responsibilityAssignment->setResponsibilityRole(ucfirst($posDetails));

            switch ($role) {
                // As Responsible
                case ResponsibilityRoles::RESPONSIBLE:
                    $responsibilityAssignment->setIsResponsible(true);
                    break;

                // As Deputy
                case ResponsibilityRoles::DEPUTY:
                    $responsibilityAssignment->setIsDeputy(true);
                    break;
            }

            if ($resp->getRcId() == 1) {
                $configurationEcu = $this->entityManager->getRepository(ConfigurationEcus::class)->find($subResp);
                $responsibilityEcu = new ResponsibilityEcus();
                $responsibilityEcu->setEcu($configurationEcu);
                $responsibilityEcu->setRespAssignments($responsibilityAssignment);
                $this->entityManager->persist($responsibilityEcu);
            } elseif ($resp->getRcId() == 2) {
                $responsibilityModelRange = new ResponsibilityModelRange();
                $responsibilityModelRange->setName($subResp);
                $responsibilityModelRange->setRespAssignments($responsibilityAssignment);
                $this->entityManager->persist($responsibilityModelRange);
            }

            $this->entityManager->persist($responsibilityAssignment);

            $this->entityManager->flush();
            $this->entityManager->commit();

        } catch (Exception $exception) {
            /* Rollback */
            $this->entityManager->rollBack();
            throw $exception;
        }
    }

    /**
     * @param ResponsibilityCategories $resp
     * @param string|null $subResp
     * @param StsOrganizationStructure $structure
     * @param string $strDetails
     * @param int $role
     * @return void
     * @throws Exception
     */
    public function addStructure(ResponsibilityCategories $resp, ?string $subResp, StsOrganizationStructure $structure, string $strDetails, int $role): void
    {
        $this->entityManager->beginTransaction();

        $responsibilityAssignment = new ResponsibilityAssignments();

        $responsibilityAssignmentManager = $this->entityManager
            ->getRepository(ResponsibilityAssignments::class);

        $responsibleAssigned = [];
        $deputyAssigned = [];
        $orgAlreadyAssigned = [];
        $deviationPermissionUserCount = 0;

        if ($resp->getRcId() == 1) {
            $responsibilityEcusManager = $this->entityManager->getRepository(ResponsibilityEcus::class);
            $responsibleAssigned = $responsibilityAssignmentManager->checkIdForResponsibleAssignedForEcu($subResp);
            $deputyAssigned = $responsibilityAssignmentManager->checkIdForDeputyAssignedForEcu($subResp);
            $orgAlreadyAssigned = $responsibilityEcusManager->checkIfUserOrgAlreadyAssignedForEcu($structure->getId(), $subResp, true);
        } elseif ($resp->getRcId() == 2) {
            $responsibilityModelRangeManager = $this->entityManager->getRepository(ResponsibilityModelRange::class);
            $responsibleAssigned = $responsibilityAssignmentManager->checkIdForResponsibleAssignedForModelRange($subResp);
            $deputyAssigned = $responsibilityAssignmentManager->checkIdForDeputyAssignedForModelRange($subResp);
            $orgAlreadyAssigned = $responsibilityModelRangeManager->checkIfUserOrgAlreadyAssignedForModelRange($structure->getId(), $subResp, true);
        } elseif ($resp->getRcId() == 8) {
            $deviationPermissionUserCount = $responsibilityAssignmentManager->count(['assignedCategory' => 8]);
        }

        $errorMessages = $this->getTranslation();

        if (!empty($responsibleAssigned) and $role == 2)
            throw new Exception($errorMessages->anotherUserIsAssigned(ResponsibilityRoles::RESPONSIBLE));

        if (!empty($deputyAssigned) and $role == 1)
            throw new Exception($errorMessages->anotherUserIsAssigned(ResponsibilityRoles::DEPUTY));

        if ($orgAlreadyAssigned)
            throw new Exception($errorMessages->currentUserHasAnotherRole(true));

        if ($deviationPermissionUserCount >= 4) {
            throw new Exception('Maximum 4 users allowed!');
        }

        try {
            $responsibilityAssignment->setAssignedCategory($resp);
            $responsibilityAssignment->setStsOs($structure);
            $responsibilityAssignment->setIsStructure(true);
            $responsibilityAssignment->setStructureDetails(ucfirst($strDetails));

            switch ($role) {
                // As Responsible
                case ResponsibilityRoles::RESPONSIBLE:
                    $responsibilityAssignment->setIsResponsible(true);
                    break;

                // As Deputy
                case ResponsibilityRoles::DEPUTY:
                    $responsibilityAssignment->setIsDeputy(true);
                    break;
            }


            if ($resp->getRcId() == 1) {
                $configurationEcu = $this->entityManager->getRepository(ConfigurationEcus::class)->find($subResp);
                $responsibilityEcu = new ResponsibilityEcus();
                $responsibilityEcu->setEcu($configurationEcu);
                $responsibilityEcu->setRespAssignments($responsibilityAssignment);
                $this->entityManager->persist($responsibilityEcu);
            } elseif ($resp->getRcId() == 2) {
                $responsibilityModelRange = new ResponsibilityModelRange();
                $responsibilityModelRange->setName($subResp);
                $responsibilityModelRange->setRespAssignments($responsibilityAssignment);
                $this->entityManager->persist($responsibilityModelRange);
            }

            $this->entityManager->persist($responsibilityAssignment);

            $this->entityManager->flush();
            $this->entityManager->commit();

        } catch (Exception $exception) {
            /* Rollback */
            $this->entityManager->rollBack();
            throw $exception;
        }
    }
}


