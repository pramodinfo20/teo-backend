<?php

/**
 * ManagementFunctionController.class.php
 * The main class..
 * @author Jakub Kotlorz, FEV
 */

/**
 * ManagementFunctionController Class, the main class
 */
class ManagementFunctionController extends PageController {
    public $msgs;
    protected $oQueryHolder;
    protected $aResults;
    protected $allStsUsers;
    protected $allStructures;
    protected $action;
    protected $addingResultString;

    private $managementFunctions;

    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        parent::__construct($ladeLeitWartePtr, $container, $requestPtr, $user);

        $this->translate = parent::getTranslationsForDomain();
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->displayFooter = $container->getDisplayFooter();
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->msgs = null;
        $this->user = $user;
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

        // function-related data
        $this->managementFunctions = $this->getManagementFunctions();

        $this->allStsUsers = $this->getAllStsUsers();
        $this->allStructures = $this->getAllCompanyStructures();

        // routing
        if (isset($_GET['method'])) {
            $name = $_GET['method'];
            $this->$name();
        } else {
            $this->printContent();
        }
    }

    function printContent() {
        $this->displayHeader->printContent();
        include("pages/manageFunctions.php");

        $this->displayFooter->printContent();
    }

    /**
     * AJAX queries
     */

    function ajaxAddManagementFunction() {
        $msg = "";
        $item = $_GET['item'];

        if (isset($item['userid']) && isset($_GET['func'])) {
            // add user
            $storedUsers = $this->getManagementFunctionsUsers($_GET['func'], $item['userid'], 'false');
            if (!is_null($storedUsers) && count($storedUsers) > 0) {
                $msg = '<div class="info-message">User already exists on the list</div>';
            } else {
                $this->addManagementFunction($_GET['func'], $item['userid'], 'false', null);
                $name = $this->getUserNameById($item['userid']);
                $msg = '<div class="success-message">User <strong>' . $name . '</strong> added</strong></div>';
            }
        } else if (isset($item['csid']) && isset($_GET['func'])) {
            // add company structure
            $storedStructures = $this->getManagementFunctionsUsers($_GET['func'], $item['csid'], 'true');
            if (!is_null($storedStructures) && count($storedStructures) > 0) {
                $msg = '<div class="info-message">Company structure already exists on the list</div>';
            } else {
                $this->addManagementFunction($_GET['func'], $item['csid'], 'true', $_GET['structure_details']);
                $name = $this->getCompanyStructureNameById($item['csid']);
                $msg = '<div class="success-message">Company structure <strong>' . $name . '</strong> added</div>';
            }
        } else {
            $msg = "<div class=\"error-message\">No data provided!</div>";
        }
        echo json_encode($msg);
        exit;
    }

    function ajaxGetManagementsFor() {
        $users = $this->getAllByManagementFunction($_GET['selectedFunction']);
        echo json_encode($users);
        exit;
    }

    function ajaxGetAllStsUsersCompanyStructures() {
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

    function ajaxRemoveManagementFunction() {
        $msg = "";

        if (isset($_GET['id'])) {
            $this->removeManagementFunction($_GET['id']);
            $msg = '<div class="success-message">Item removed</strong></div>';
        }

        echo json_encode($msg);
        exit;
    }

    /**
     * Database queries
     */

    function addManagementFunction($fnc, $n, $is_structure, $str_det) {
        $sts = $is_structure ? "company structure" : "sts employee";
        $this->logChange($this->user, $this->action, "Assignment for $sts $n to Management function $fnc ADDED");
        $q = "INSERT INTO public.management_functions_users (function_id, user_id, is_structure, structure_details) VALUES ($fnc, $n, $is_structure, '$str_det')";
        return $this->oQueryHolder->query($q);
    }

    protected function getAllCompanyStructures() {
        return $this->ladeLeitWartePtr->newQuery('sts_organization_structure')->get('id, name');
    }

    protected function getAllStsUsers() {
        return array_filter(
            $this->ladeLeitWartePtr->newQuery('users')->get('id, username, fname, lname, email'),
            "isStsValidEmail"
        );
    }

    function getAllByManagementFunction($functionId) {
        $entries = $this->ladeLeitWartePtr->newQuery('management_functions_users')
            ->where("management_functions_users.function_id", "=", $functionId)
//        ->orderBy("is_structure", "DESC")
            ->get("id, user_id, is_structure, structure_details");

        if ($entries) {
            foreach ($entries as $key => $value) {
                if ($value['is_structure'] === 't') {
                    $entries[$key]['displayName'] = $this->getCompanyStructureNameById($value['user_id']);
                } else {
                    $entries[$key]['displayName'] = $this->getUserNameById($value['user_id']);
                }
            }
        }
        return $entries;
    }

    function getCompanyStructureNameById($id) {
        return $this->ladeLeitWartePtr->newQuery('sts_organization_structure')
            ->where("id", "=", $id)
            ->get("name")[0]['name'];
    }

    function getManagementFunctions() {
        return $this->ladeLeitWartePtr->newQuery('management_functions ')
            ->orderBy("name", "ASC")
            ->get("id, name");
    }

    function getManagementFunctionsUsers($func, $user, $is_s) {
        return $this->ladeLeitWartePtr->newQuery('management_functions_users')
            ->where("function_id", "=", $func)
            ->where("user_id", "=", $user)
            ->where("is_structure", "=", $is_s)
            ->get("id");
    }

    function getUserNameById($id) {
        $user = $this->ladeLeitWartePtr->newQuery('users')
            ->where("id", "=", $id)
            ->get("fname, lname, username");
        return sprintf("%s %s (%s)", $user[0]['fname'], $user[0]['lname'], $user[0]['username']);
    }

    function removeManagementFunction($id) {
        $this->logChange($this->user, $this->action, "Assignment for user to Management function REMOVED, id: $id");
        $q = "DELETE FROM public.management_functions_users WHERE id=$id";
        return $this->oQueryHolder->query($q);
    }


} // end of ManagementFunctionController Class


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
