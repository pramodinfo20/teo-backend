<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 1/16/19
 * Time: 10:50 AM
 */

class UserRole2FunctionalityController extends PageController {
    protected $mfListForm;
    private $translate;

    /**
     * @var String
     */
    public $sContent, $removeMsg;
    protected $removeFuncGroups;

    /**
     * @var NewQueryPgsql
     */
    public $oQueryHolder;

    /**
     * @var Array
     */
    public $aResults;

    protected $aFuncGroups;
    protected $aAllFuncGroups;

    public $userrole = ['Zentrale', 'PPS', 'Post Fleet', 'WerkStätte', 'QS', 'Engineering', 'Aftersales', 'DB History',
        'Fuhrparksteurung', 'Fuhrparkverwaltung', 'Charging Infrastruktur: EBM Compleo',
        'Charging Infrastruktur: AixACCT', 'Charging Infrastruktur: Innogy'];

    public $aUserrole2FunctionalityGroups = array(
        'CTO' => 'Zentrale',
        'E/E' => 'PPS',
        'System Engineering' => 'Post Fleet',
        'DAC' => 'WerkStätte',
        'DIA' => 'QS',
        'Gesamstfahrzeug' => 'Engineering',
        'Testing & Homologation' => 'Aftersales',
        'CEO' => 'DB History',
        'Production' => 'Fuhrparksteurung',
        'Field Support' => 'Fuhrparkverwaltung',
        'Betrieb' => 'Charging Infrastruktur: EBM Compleo' // 'Charging Infrastruktur: AixACCT', 'Charging Infrastruktur: Innogy'
    );

    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        parent::__construct($ladeLeitWartePtr, $container, $requestPtr, $user);

        $this->translate = parent::getTranslationsForDomain();
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->displayFooter = $container->getDisplayFooter();
        $this->container = $container;
        $this->requestPtr = $requestPtr;
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

        // moved to refresh function - ajaxGet...
        //$this->prepareView();

        if (isset($_GET['method'])) {
            $name = $_GET['method'];
            $this->$name();
        } else if (isset($_GET['userroleid'])) {
            $userRoleId = $_GET['userroleid'];
            $this->aFuncGroups = $this->getFuncGroupsByUserroleId($userRoleId);
            $this->printContentForEdit();
        } else {
            $this->printContent();
        }
    }


    protected function edit() {
        $this->printContentForEdit();
    }

    function printContent() {
        $this->displayHeader->printContent();
        include("pages/userrole2Functionality.php");
        $this->displayFooter->printContent();
    }

    /**
     * To be called for refreshing main table on page loads and after user role add/remove
     */
    function ajaxGetUserRolesFuncGroups() {
        $this->prepareView();
        echo json_encode($this->aUserroles);
        return;
    }

    /**
     * To be called when new user role is to be added
     */
    function ajaxAddNewUserRole() {
        $msg = "";
        $urName = $_GET['userRoleName'];

        $id = $this->getUserRoleByName($urName);

        if (!is_null($id) && count($id) > 0) {
            $msg = "<div class=\"info-message\">User role <strong>$urName</strong> already exists</div>";
        } else {
            if ($this->addNewUserRole($urName)) {
                $msg = "<div class=\"success-message\">User <strong>$urName</strong> added</div>";
            } else {
                $msg = "<div class=\"error-message\">Cannot add user</div>";
            }
        }

        echo json_encode($msg);
        return;
    }

    function ajaxRemoveUserRole2FunctionalityGroup() {
        echo json_encode($this->deleteFuncGroupAssignedByID($_GET['remove_id']));
        return;
    }

    function ajaxUpdatePermissionFunctionalityGroupByID() {
        $localUpdate = $this->updatePermissionFunctionalityGroupByID($_POST['ids'], $_POST['permissions']);
        echo json_encode($localUpdate);
        return;
    }

    function ajaxPrepareAssignNewFuncGroupToUserRoleByID() {
        echo json_encode($this->allFunctionalityGroups($_POST['func_group_ids']));
        exit;
    }

    function ajaxAddAssignNewFunctionalityGroupByID() {
        echo json_encode($this->assignNewFuncGroup($_GET['userroleid'], $_GET['funcgroupid'], $_GET['permission']));
    }

    function removeUserRole() {
//    echo json_encode($this->removeUserROle());
        $userroleid = $_GET['userroleid'];
        $q = "DELETE FROM public.user_roles WHERE id=$userroleid AND $userroleid NOT IN (SELECT public.user_role_company_structure.user_role_id FROM public.user_role_company_structure)";
        $removeQuery = $this->oQueryHolder->query($q);

        $pg = pg_affected_rows($removeQuery);

        if ($pg) {
            $this->removeMsg = "deleted";
        } else {
            $this->removeMsg = "wrong";
        }
        $this->printContent();
    }

    function printContentForEdit() {
        $this->displayHeader->printContent();
        include("pages/userrole2Functionality-edit.php");
    }

    protected function getFuncGroupsByUserroleId($id) {
        return $this->ladeLeitWartePtr->newQuery('functionality_group_user_role')
            ->join('functionality_groups', 'functionality_groups.id=functionality_group_user_role.functionality_group_id', 'LEFT JOIN')
            ->where('user_role_id', '=', $id)
            ->get('functionality_groups.name, functionality_group_user_role.functionality_group_id, functionality_group_user_role.write_permissions, functionality_group_user_role.id, functionality_group_user_role.user_role_id');

    }

    protected function prepareView() {
        $aUserroles = $this->ladeLeitWartePtr->newQuery('user_roles')->get('*');

        $return = array();

        foreach ($aUserroles as $k => $v) {
            $return[$k]['header'] = $v;
            $return[$k]['fcg'] = $this->getFuncGroupsByUserroleId($v['id']);
        }
        $this->aUserroles = $return;

    }

    protected function deleteFuncGroupAssignedByID($id) {
        $q = "DELETE FROM public.functionality_group_user_role WHERE id=$id";
        return $this->oQueryHolder->query($q);
    }

    protected function updatePermissionFunctionalityGroupByID($ids, $permissions) {
        $arrayIDs = array_combine($ids, $permissions);
        foreach ($arrayIDs as $k => $v) {
            if ($v == 'write') {
                $q = "UPDATE public.functionality_group_user_role SET write_permissions='TRUE' WHERE id=$k";
                $this->oQueryHolder->query($q);
            } else {
                $q = "UPDATE public.functionality_group_user_role SET write_permissions='FALSE' WHERE id=$k";
                $this->oQueryHolder->query($q);
            }
        }
        return true;
    }

    protected function allFunctionalityGroups($func_id) {
        if (isset($_POST['func_group_ids'])) {
            return $this->aAllFuncGroups = $this->aResults = $this->ladeLeitWartePtr->newQuery('functionality_groups')->where('functionality_groups.id', 'NOT IN', $func_id)->get('functionality_groups.id, functionality_groups.name');
        } else {
            return $this->aAllFuncGroups = $this->aResults = $this->ladeLeitWartePtr->newQuery('functionality_groups')->get('functionality_groups.id, functionality_groups.name');
        }
    }

    protected function assignNewFuncGroup($userroleid, $functionality_group_id, $permission) {
        if ($permission == 'write') {
            $q = "INSERT INTO public.functionality_group_user_role (functionality_group_id, user_role_id, write_permissions) VALUES ($functionality_group_id, $userroleid, 'TRUE')";
        } else {
            $q = "INSERT INTO public.functionality_group_user_role (functionality_group_id, user_role_id, write_permissions) VALUES ($functionality_group_id, $userroleid, 'FALSE')";
        }
        $this->oQueryHolder->query($q);
        return true;
    }

    protected function addNewUserRole($n) {
        $q = "INSERT INTO public.user_roles (name) VALUES ('$n')";
        return $this->oQueryHolder->query($q);
    }

    protected function getUserRoleByName($n) {
        return $this->ladeLeitWartePtr->newQuery('user_roles')
            ->where('name', '=', $n)
            ->get('id');
    }

//  protected function removeUserROle()
//  {
//
//  }
}
