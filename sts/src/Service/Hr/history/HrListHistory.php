<?php

namespace App\Service\Hr\history;

use App\Entity\HistoryPersonsListHr;
use App\Entity\StsOrganizationStructure;
use App\Entity\UserRoleCompanyStructure;
use App\Entity\UserRoles;
use App\Model\UserRole\Assignment\CompanyStructureAssignmentModel;
use App\Model\UserRole\Assignment\CompanyStructureModel;
use App\Model\UserRole\Assignment\UserRoleModel;

use App\Utils\Choice;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class HrListHistory
{

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * HrListHistory constructor.
     * @param ObjectManager $manager
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ObjectManager $manager, EntityManagerInterface $entityManager)
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
    }


    /**
     * Remap names for function that changes them to tree.
     * @param $historyData
     * @return array
     */
    public function remapKeysHistoryData($historyData): array
    {
        $result = [];

        foreach ($historyData as $dataElement) {
            $remappedElement['id'] = $dataElement['organization_id'];
            $remappedElement['parent_id'] = $dataElement['deputy_organization_id'];
            $remappedElement['name'] = $dataElement['business_unit'];
            $remappedElement['person'] = $dataElement['person'];

            // Add element to array
            $result[] = $remappedElement;
        }

        return $result;
    }


}