<?php

namespace App\Service\Ecu\Sw;

use App\Entity\ConfigurationEcus;
use App\Entity\ResponsibilityAssignments;
use App\Entity\ResponsibilityEcus;
use App\Entity\UserCompanyStructure;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

class ResponsiblePersons
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
     * Parameter constructor.
     *
     * @param ObjectManager          $manager
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ObjectManager $manager, EntityManagerInterface $entityManager)
    {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
    }

    /**
     * After lockin the ECU where the user is responsible or deputy or where the user has write permissions should
     * automatically be selected. If the user is responsible or deputy or write able for more than one ECU use this
     * order for auto selection:
     *      - responsible (if for more than one ECU select first one in alphabetical order)
     *      - deputy (if for more than one ECU select first one in alphabetical order)
     *      - write able (if for more than one ECU select first one in alphabetical order)
     *
     * @param int $userId
     *
     * @return int|null
     */
    public function loadResponsibleEcuByUserId(int $userId): ?int
    {
        $ecuId = null;

        $allResponsibilities = $this->getAllKindOfResponsibilitiesByUserId($userId);

        if (count($allResponsibilities['userResponsibilities'])) {
            $ecuId = $allResponsibilities['userResponsibilities'][0];
        } elseif (count($allResponsibilities['userDeputies'])) {
            $ecuId = $allResponsibilities['userDeputies'][0];
        } elseif (count($allResponsibilities['userWriteables'])) {
            $ecuId = $allResponsibilities['userWriteables'][0];
        }

        return $ecuId;
    }

    /**
     *  Get all kind of responsiblities by userId
     *
     * @param int $userId
     *
     * @return array
     */
    public function getAllKindOfResponsibilitiesByUserId(int $userId): array
    {
        list ($entityManagerUCS, $entityManagerRA, $entityManagerRE) = [
            $this->entityManager->getRepository(UserCompanyStructure::class),
            $this->entityManager->getRepository(ResponsibilityAssignments::class),
            $this->entityManager->getRepository(ResponsibilityEcus::class)
        ];

        // implement finding users company structure somewhere in user class
        $userCompanyStructure = $entityManagerUCS->findOneBy(['userId' => $userId]);
        if ($userCompanyStructure->getIsLeader() == "Yes") {
            $details = "leader";
        } elseif ($userCompanyStructure->getIsLeader() == "Deputy") {
            $details = "deputy";
        } else {
            $details = "all";
        }

        //all responsibilities assigned to current user's COMPANY STRUCTURE
        $allUserCsResponsibilities = [];

        // TODO: filter out if user does not fulfill STRUCTURE_DETAILS condition (all/deputy/leader)
        // TODO: check if leader > deputy > all, if being deputy means you are "all" and "deputy"
        $allStructureResponsibilities = $entityManagerRA->findBy(['isStructure' => false,
            'assignedUserId' => $userCompanyStructure->getStructureId(), 'structureDetails' => $details]);

        if (!is_null($allStructureResponsibilities)) {
            $allUserCsResponsibilities = array_merge($allUserCsResponsibilities, $allStructureResponsibilities);
        }

        $allUserResponsibilities = $entityManagerRA->findBy(['isStructure' => true,
            'assignedUserId' => $userId]);

        if (!is_null($allUserResponsibilities)) {
            $allUserCsResponsibilities = array_merge($allUserCsResponsibilities, $allUserResponsibilities);
        }

        $allUserCsResponsibilitiesMapped = [];
        $iterator = 0;
        foreach ($allUserCsResponsibilities as $mappedResponsibility) {
            $allUserCsResponsibilitiesMapped[$iterator]['assignedCategoryId'] = $mappedResponsibility->getAssignedCategoryId();
            $allUserCsResponsibilitiesMapped[$iterator]['isResponsible'] = $mappedResponsibility->getIsResponsible();
            $allUserCsResponsibilitiesMapped[$iterator]['isDeputy'] = $mappedResponsibility->getIsDeputy();
            ++$iterator;
        }

        // 3 types of responsibilities: Responsible person, Deputy person, Writable person - as "return" values
        $userResponsibilities = [];
        $userDeputies = [];
        $userWriteables = [];

        // all responsibilities that describes ECU managing
        $ecuResponsibilities = $entityManagerRE->findAll();
        // map it to 'resp_cat_id=>ecu_id' as 'key=>value'
        $ecuRespMapped = [];

        foreach ($ecuResponsibilities as $ecuResponsibility) {
            $ecuRespMapped[$ecuResponsibility->getRespCat()->getId()] = $ecuResponsibility->getEcuId();
        }

        // all ECU responsibilities assigned to current user
        foreach ($allUserCsResponsibilitiesMapped as $userResp) {
            $cat_id = $userResp['assignedCategoryId'];
            if (array_key_exists($cat_id, $ecuRespMapped)) {
                $ecu_id = $ecuRespMapped[$cat_id];
                if ($userResp['isResponsible'] == true) {
                    array_push($userResponsibilities, $ecu_id);
                } elseif ($userResp['isDeputy'] == true) {
                    array_push($userDeputies, $ecu_id);
                } else {
                    array_push($userWriteables, $ecu_id);
                }
            };
        }

        return [
            'userResponsibilities' => $userResponsibilities,
            'userDeputies' => $userDeputies,
            'userWriteables' => $userWriteables
        ];
    }

    /**
     *  Get ecu access by User Id
     *
     * @param int userId
     *
     * @return array
     */
    function getAllEcuAccessByUserIdWithOrder(int $userId): array
    {

        $allResponsibilities = $this->getAllKindOfResponsibilitiesByUserId($userId);

        $entityManagerEcu = $this->entityManager->getRepository(ConfigurationEcus::class);

        $ecus = $entityManagerEcu->findBy([], ['ecuName' => 'ASC']);

        $access = [];
        foreach ($ecus as $value) {
            if (in_array($value->getCeEcuId(), $allResponsibilities['userResponsibilities'])) {
                $access[$value->getCeEcuId()]['permission'] = 'responsible';
            } elseif (in_array($value->getCeEcuId(), $allResponsibilities['userDeputies'])) {
                $access[$value->getCeEcuId()]['permission'] = 'deputy';
            } elseif (in_array($value->getCeEcuId(), $allResponsibilities['userWriteables'])) {
                $access[$value->getCeEcuId()]['permission'] = 'writable';
            } else {
                $access[$value->getCeEcuId()]['permission'] = '';
            }

            $access[$value->getCeEcuId()]['name'] = $value->getEcuName();
        }

        return $access;
    }
}
//
//// data for choosing ECU sw version
//$this->availableECUs = $this->getAllECUs();
//$this->getAllKindOfResponsibilities();
//
//foreach ($this->availableECUs as &$ecu) {
//    $ecu['permission'] = $this->getEcuAccess($ecu['ecu_id']);
//}
//
//if (isset($_GET['method'])) {
//    $name = $_GET['method'];
//    $this->$name();
//}
//}
//
///**
// * Module related methods
// */
//protected function getAllKindOfResponsibilities()
//{
//    // TODO: do not get from session but from user login information!
//    $userId = $_SESSION['sts_userid'];
//
//    // implement finding users company structure somewhere in user class
//    $userCompanyStructure = $this->ladeLeitWartePtr->newQuery('user_company_structure')
//        ->where("user_id", "=", $userId)
//        ->getOne("structure_id, is_leader");
//    if ($userCompanyStructure['is_leader'] == "Yes") {
//        $details = "leader";
//    } elseif ($userCompanyStructure['is_leader'] == "Deputy") {
//        $details = "deputy";
//    } else {
//        $details = "all";
//    }
//
//    // all responsibilities assigned to current user's COMPANY STRUCTURE
//    $allUserCsResponsibilities = array();
//
//    // TODO: filter out if user does not fulfill STRUCTURE_DETAILS condition (all/deputy/leader)
//    // TODO: check if leader > deputy > all, if being deputy means you are "all" and "deputy"
//    $allStructureResponsibilities = $this->ladeLeitWartePtr->newQuery('responsibility_assignments')
//        ->where("is_structure", "=", "true")
//        ->where("assigned_user_id", "=", $userCompanyStructure['structure_id'])
//        ->where("structure_details", "=", $details)
//        ->get("assigned_category_id, is_responsible, is_deputy");
//    if (is_array($allStructureResponsibilities)) {
//        $allUserCsResponsibilities = array_merge($allUserCsResponsibilities, $allStructureResponsibilities);
//    }
//
//    // all responsibilities assigned to current USER
//    $allUserResponsibilities = $this->ladeLeitWartePtr->newQuery('responsibility_assignments')
//        ->where("is_structure", "=", "false")
//        ->where("assigned_user_id", "=", $userId)
//        ->get("assigned_category_id, is_responsible, is_deputy");
//    if (is_array($allUserResponsibilities)) {
//        $allUserCsResponsibilities = array_merge($allUserCsResponsibilities, $allUserResponsibilities);
//    }
//
//    // 3 types of responsibilities: Responsible person, Deputy person, Writable person - as "return" values
//    $this->userResponsibilities = array();
//    $this->userDeputies = array();
//    $this->userWriteables = array();
//
//    // all responsibilities that describes ECU managing
//    $ecuResponsibilities = $this->ladeLeitWartePtr->newQuery('responsibility_ecus')
//        ->get("resp_cat_id, ecu_id");
//    // map it to 'resp_cat_id=>ecu_id' as 'key=>value'
//    $ecuRespMapped = array_combine(
//        array_column($ecuResponsibilities, 'resp_cat_id'),
//        array_column($ecuResponsibilities, 'ecu_id')
//    );
//
//    // all ECU responsibilities assigned to current user
//    foreach ($allUserCsResponsibilities as $userResp) {
//        $cat_id = $userResp['assigned_category_id'];
//        if (array_key_exists($cat_id, $ecuRespMapped)) {
//            $ecu_id = $ecuRespMapped[$cat_id];
//            if ($userResp['is_responsible'] === 't') {
//                array_push($this->userResponsibilities, $ecu_id);
//            } elseif ($userResp['is_deputy'] === 't') {
//                array_push($this->userDeputies, $ecu_id);
//            } else {
//                array_push($this->userWriteables, $ecu_id);
//            }
//        };
//    }
//}
//
//protected function ajaxGetFirstResponsibility()
//{
//    $selectEcu = 0;
//
//    if (count($this->userResponsibilities)) {
//        $selectEcu = $this->userResponsibilities[0];
//    } elseif (count($this->userDeputies)) {
//        $selectEcu = $this->userDeputies[0];
//    } elseif (count($this->userWriteables)) {
//        $selectEcu = $this->userWriteables[0];
//    }
//
//    echo json_encode($selectEcu);
//    return;
//}
//
//protected function getEcuAccess($ecu_id)
//{
//    if (in_array($ecu_id, $this->userResponsibilities)) {
//        return 'responsible';
//    } elseif (in_array($ecu_id, $this->userDeputies)) {
//        return 'deputy';
//    } elseif (in_array($ecu_id, $this->userWriteables)) {
//        return 'writable';
//    } else {
//        return '';
//    }
//}