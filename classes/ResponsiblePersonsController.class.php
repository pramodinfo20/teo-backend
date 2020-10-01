<?php

/**
 * ResponsiblePersonsController.class.php
 * The main class..
 * @author Jakub Kotlorz, FEV
 */

/**
 * ResponsiblePersonsController Class, the main class
 */
class ResponsiblePersonsController extends PageController {
    public $msgs;
    public $oQueryHolder;
    protected $allStsUsers;
    protected $allStructures;
    private $responsibilityCategories;
  private $translate;

    public $sCreateDbTable = "";

    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
      parent::__construct($ladeLeitWartePtr, $container, $requestPtr, $user);

        $this->translate = parent::getTranslationsForDomain();
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->displayFooter = $container->getDisplayFooter();

        $this->user = $user;
        $this->msgs = null;
        $config_leitwarte = $GLOBALS['config']->get_db('leitwarte');
        $databasePtr = new DatabasePgsql (
            $config_leitwarte['host'],
            $config_leitwarte['port'],
            $config_leitwarte['db'],
            $config_leitwarte['user'],
            $config_leitwarte['password'],
            new DatabaseStructureCommon1()
        );
        $this->oDbPtr = $databasePtr;
        $this->oQueryHolder = new NewQueryPgsql($this->oDbPtr);

        $this->action = $this->requestPtr->getProperty('action');

        $this->displayHeader = $this->container->getDisplayHeader();
        $this->displayHeader->setTitle("StreetScooter Cloud Systems : " . $this->user->getUserRoleLabel());

        // FEV/JK: for user selection
        $this->displayHeader->enqueueJs("jquery-getuser", "js/jquery.user_cs_picker.js");

        $this->responsibilityCategories = $this->getRespCategoryByParent(0);

        $this->allStsUsers = $this->getAllStsUsers();
        $this->allStructures = $this->getAllCompanyStructures();

        if (isset($_GET['method'])) {
            $name = $_GET['method'];
            $this->$name();
        } else {
            $this->printContent();
        }
    }

    function printContent() {
        $this->displayHeader->printContent();
        include("pages/responsiblePersons.php");
        $this->displayFooter->printContent();
    }

    /**
     * AJAX queries
     */

    protected function ajaxGetSubcategory() {
        echo json_encode($this->getRespCategoryByParent($_GET['id']));
        exit;
    }

    protected function ajaxGetResponsiblePersonsAssignments() {
        echo json_encode($this->getRespAssignmentsById($_GET['id']));
        exit;
    }

    protected function ajaxAddResponsiblePersonsAssignment() {
        $msg = "";
        $item = $_GET['item'];
        $set_resp = 'false';
        $set_deputy = 'false';

        if (isset($_GET['set_as_responsible']) && $_GET['set_as_responsible'] === "true") {
            if ($this->isResponsibleSetForResponsibility($_GET['category'])) {
                $msg = "<div class=\"error-message\">This category already has responsible person assigned</div>";
            } else {
                $set_resp = 'true';
            }
        }
        if (isset($_GET['set_as_deputy']) && $_GET['set_as_deputy'] === "true") {
            if ($this->isDeputySetForResponsibility($_GET['category'])) {
                $msg = "<div class=\"error-message\">This category already has deputy person assigned</div>";
            } else {
                $set_deputy = 'true';
            }
        }

        if (isset($item['userid']) && isset($_GET['category'])) {
            // add user
            $storedUsers = $this->getResponsibilityUser($_GET['category'], $item['userid'], 'false');
            if (!is_null($storedUsers) && count($storedUsers) > 0) {
                $msg = "<div class=\"info-message\">User already exists on the this list</div>";
            } else {
                $this->addResponsibility($_GET['category'], $item['userid'], 'false', null, $set_resp, $set_deputy);
                $name = $this->getUserNameById($item['userid']);
                $msg = "<div class=\"success-message\">User <strong>$name</strong> added</div>";
            }
        } else if (isset($item['csid']) && isset($_GET['category'])) {
            // add company structure
            $storedStructures = $this->getResponsibilityUser($_GET['category'], $item['csid'], 'true');
            if (!is_null($storedStructures) && count($storedStructures) > 0) {
                $msg = "<div class=\"info-message\">Company structure already exists on this list</div>";
            } else {
                $this->addResponsibility($_GET['category'], $item['csid'], 'true', $_GET['structure_details'], $set_resp, $set_deputy);
                $name = $this->getCompanyStructureNameById($item['csid']);
                $msg = "<div class=\"success-message\">Company structure <strong>$name</strong> added</div>";
            }
        } else {
            $msg = "<div class=\"error-message\">No data provided!</div>";
        }
        echo json_encode($msg);
        exit;
    }

    protected function ajaxGetAllStsUsersCompanyStructures() {
        $stsUsers = array();
        foreach ($this->allStsUsers as $u) {
            array_push($stsUsers,
                array("label" => getFnameLnameEmail($u), "category" => "StreetScooter employees", "userid" => $u['id'])
            );
        }
        $stsStructures = array();
        foreach ($this->allStructures as $cs) {
            array_push($stsStructures,
                array("label" => $cs['name'], "category" => "Company structures", "csid" => $cs['id'])
            );
        }
        echo json_encode(array_merge($stsUsers, $stsStructures));
        return;
    }

    protected function ajaxRemoveResponsibility() {
        $msg = "";

        if (isset($_GET['id'])) {
            if ($this->removeResponsibility($_GET['id'])) {
                $msg = '<div class="success-message">Item removed</div>';
            } else {
                $msg = '<div class="error-message">Cannot remove this item</div>';
            }
        }

        echo json_encode($msg);
        exit;
    }

    protected function ajaxSwitchRole() {
        $msg = "";
        $isResponsible = $isDeputy = $isWritable = null;

        if (isset($_GET['id'])) {
            switch ($_GET['role']) {
                case 'responsible':
                    $isResponsible = $this->isResponsibleSetForResponsibility($_GET['category']);
                    break;
                case 'deputy':
                    $isDeputy = $this->isDeputySetForResponsibility($_GET['category']);
                    break;
                case 'writable':
                    $isWritable = $this->getResponsibilityUser($_GET['category'], $_GET['id'], false);
                    break;
            }

            if ($isResponsible) {
                echo json_encode('<div class="error-message">This category already has responsible person assigned</div>');
                exit;
            }

            if ($isDeputy) {
                echo json_encode('<div class="error-message">This category already has deputy person assigned</div>');
                exit;
            }

            if ($isWritable && count($isWritable) > 0) {
                echo json_encode('<div class="info-message">User already exists on the this list</div>');
                exit;
            }

            if ($this->switchUserRole($_GET['id'], $_GET['role'])) {
                $msg = '<div class="success-message">User role switched</div>';
            } else {
                $msg = '<div class="error-message">Cannot switch user role to this item</div>';
            }
        }

        echo json_encode($msg);
        exit;
    }


    /**
     * Database queries
     */

    /** table users or sts_organization_structure - related */
    protected function getAllCompanyStructures() {
        return $this->ladeLeitWartePtr->newQuery('sts_organization_structure')->get('id, name');
    }

    /** table users or sts_organization_structure - related */
    protected function getAllStsUsers() {
        return array_filter(
            $this->ladeLeitWartePtr->newQuery('users')->get('id, username, fname, lname, email'),
            "isStsValidEmail"
        );
    }

    /** table users or sts_organization_structure - related */
    function getUserNameById($id) {
        $user = $this->ladeLeitWartePtr->newQuery('users')
            ->where("id", "=", $id)
            ->get("fname, lname, username");
        return sprintf("%s %s (%s)", $user[0]['fname'], $user[0]['lname'], $user[0]['username']);
    }

    /** table users or sts_organization_structure - related */
    function getCompanyStructureNameById($id) {
        return $this->ladeLeitWartePtr->newQuery('sts_organization_structure')
            ->where("id", "=", $id)
            ->get("name")[0]['name'];
    }

    //
    //
    //
    private function addResponsibility($cat, $userid, $is_str, $details, $is_resp, $is_deputy) {
        if ($is_str === "true") {
            $username = $this->getCompanyStructureNameById($userid);
            $sts = "company structure";
        } else {
            $username = $this->getUserNameById($userid);
            $sts = "sts employee";
        }
        $catName = $this->getRespCategoryById($cat)['name']; //
        $this->logChange($this->user, $this->action, "[ADDED] $catName responsibility for $sts $username");
        return $this->addResponsibilityToDb($userid, $cat, $is_resp, $is_deputy, $is_str, $details);
    }

    private function getRespCategoryById($cat) {
        return $this->ladeLeitWartePtr->newQuery('responsibility_categories')
            ->where("id", "=", $cat)
            ->getOne("id, parent_id, name, code");
    }

    private function getRespCategoryByParent($cat) {
        return $this->ladeLeitWartePtr->newQuery('responsibility_categories')
            ->where("parent_id", "=", $cat)
            ->get("id, parent_id, name, code");
    }

    private function getRespAssignmentsById($id) {
        $entries = $this->ladeLeitWartePtr->newQuery('responsibility_assignments')
            ->where("assigned_category_id", "=", $id)
            ->orderBy("is_responsible", "DESC")
            ->orderBy("is_deputy", "DESC")
            ->get("id, assigned_user_id, is_structure, structure_details, is_responsible, is_deputy");

        if ($entries) {
            foreach ($entries as $key => $value) {
                if ($value['is_structure'] === 't') {
                    $entries[$key]['displayName'] = $this->getCompanyStructureNameById($value['assigned_user_id']);
                } else {
                    $entries[$key]['displayName'] = $this->getUserNameById($value['assigned_user_id']);
                }
            }
        }
        return $entries;
    }

    //
    // Check if user/company structure, print to change log, delete entry from db
    //
    private function removeResponsibility($id) {
        $item = $this->getResponsibilityById($id);
        if ($item) {
            $rp = $item['is_responsible'] === "t" ? "(responsible)" : "";
            $dp = $item['is_deputy'] === "t" ? "(deputy)" : "";
            $cat = $item['name'];
            if ($item['is_structure'] === "t") {
                $username = $this->getCompanyStructureNameById($item['assigned_user_id']);
            } else {
                $username = $this->getUserNameById($item['assigned_user_id']);
            }
            $this->logChange($this->user, $this->action, "[REMOVED] $cat responsibility for $rp $dp $username");
            return $this->deleteResponsibilityById($id);
        } else {
            return false;
        }
    }

    private function switchUserRole($id, $role) {
        $item = $this->getResponsibilityById($id);
        $update = '';
        if ($item) {
            switch ($role) {
                case 'responsible':
                    $update = 'is_responsible = TRUE, is_deputy = FALSE';
                    break;
                case 'deputy':
                    $update = 'is_deputy = TRUE, is_responsible = FALSE';
                    break;
                case 'writable':
                    $update = 'is_deputy = FALSE, is_responsible = FALSE';
                    break;
            }

            return $this->switchUserRoleResponsibilityById($id, $update);
        } else {
            return false;
        }
    }

    //
    // SQL to remove from DATABASE
    //
    private function deleteResponsibilityById($id) {
        $q = "DELETE FROM public.responsibility_assignments WHERE id=$id";
        return $this->oQueryHolder->query($q);
    }
    //
    // SQL to switch user role from DATABASE
    //
    private function switchUserRoleResponsibilityById($id, $update) {
        $q = "UPDATE public.responsibility_assignments SET {$update} WHERE id=$id";
        return $this->oQueryHolder->query($q);
    }
    //
    // SQL to add to DATABASE
    //
    private function addResponsibilityToDb($user, $cat, $is_resp, $is_deputy, $is_str, $details) {
        $q = "INSERT INTO public.responsibility_assignments (assigned_user_id, assigned_category_id, is_responsible, is_deputy, is_structure, structure_details) VALUES ($user, $cat, $is_resp, $is_deputy, $is_str, '$details')";
        return $this->oQueryHolder->query($q);
    }


    private function getResponsibilityById($id) {
        return $this->ladeLeitWartePtr->newQuery('responsibility_assignments')
            ->where("id", "=", $id)
            ->join("responsibility_categories", "responsibility_categories.id=responsibility_assignments.assigned_category_id", "LEFT JOIN")
            ->getOne("responsibility_categories.name, assigned_user_id, is_responsible, is_deputy, is_structure");
    }

    //
    // to check if already assigned
    //
    private function isDeputySetForResponsibility($category) {
        return $this->ladeLeitWartePtr->newQuery('responsibility_assignments')
            ->where("assigned_category_id", "=", $category)
            ->where("is_deputy", "=", "true")
            ->get("id");
    }

    //
    // to check if already assigned
    //
    private function isResponsibleSetForResponsibility($category) {
        return $this->ladeLeitWartePtr->newQuery('responsibility_assignments')
            ->where("assigned_category_id", "=", $category)
            ->where("is_responsible", "=", "true")
            ->get("id");
    }

    /**
     *
     * Returns id of responsibility_assignments table, where provided user/company structure is assigned to given category
     * To be used as checking if this user is already on this list
     *
     * @param $cat
     * @param $user
     * @param $is_s
     * @return mixed
     */
    function getResponsibilityUser($cat, $user, $is_s) {
        return $this->ladeLeitWartePtr->newQuery('responsibility_assignments')
            ->where("assigned_category_id", "=", $cat)
            ->where("assigned_user_id", "=", $user)
            ->where("is_structure", "=", $is_s)
            ->get("id");
    }

}  // end of ResponsiblePersonsController Class


/**
 * Other functions
 */

function isStsValidEmail($item) {
    if (preg_match('/@streetscooter.eu$/', $item['email'])) {
        return true;
    } else {
        return false;
    }
}

function getFnameLnameEmail($user) {
    return sprintf("%s %s (%s)", $user['fname'], $user['lname'], $user['email']);
}
