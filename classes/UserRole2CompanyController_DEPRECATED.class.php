<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 1/16/19
 * Time: 10:50 AM
 */

class UserRole2CompanyController_DEPRECATED extends PageController {
    /**
     * @var String
     */
    public $sContent;
    public $msgs;
    /**
     * @var NewQueryPgsql
     */
    public $oQueryHolder;
    /**
     * @var Array
     */

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
    protected $mfListForm;
    protected $buildTreeResult;
    protected $getResponseResult;
    protected $aUserroles;
    protected $assignedCompanyStructures;
    protected $action;

    private $translate;

    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        parent::__construct($ladeLeitWartePtr, $container, $requestPtr, $user);

        $this->translate = parent::getTranslationsForDomain();
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->displayFooter = $container->getDisplayFooter();
        $this->container = $container;
        $this->requestPtr = $requestPtr;
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

        // company structure tree
        $this->getResponseResult = $this->getOrganizationStructure();
        $this->buildTreeResult = $this->buildTree($this->getResponseResult);

        // all user roles
        $this->aUserroles = $this->ladeLeitWartePtr->newQuery('user_roles')->orderBy('name', 'ASC')->get('*');

        if (isset($_GET['method'])) {
            $name = $_GET['method'];
            $this->$name();
        } else {
            $this->printContent();
        }
    }


    //FUNCTIONS

    private function buildTree(array $elements, $parentId = '0') {
        $branch = array();
        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, $element['id']);

                if ($children) {
                    $element['children'] = $children;
                }

                $branch[] = $element;
            }
        }
        return $branch;
    }

    function printContent() {
        $this->displayHeader->printContent();
        include("pages/userrole2Company.php");
        $this->displayFooter->printContent();
    }

    private function removeFromDbIfExist($element) {
        $this->sqlRemoveCompanyStructureFromUserRole(0, $element);
    }

    private function getResponseIntResult($inputArray) {
        $resultArray = array();

        foreach ($inputArray as $elementOfArray) {
            $oneRecord['id'] = (int)$elementOfArray['id'];
            $oneRecord['name'] = $elementOfArray['name'];
            $oneRecord['parent_id'] = (int)$elementOfArray['parent_id'];

            array_push($resultArray, $oneRecord);
        }

        return $resultArray;
    }

    // AJAX HANDLER FUNCTIONS

    public function ajaxGetCompStructuresByUserRoleId() {
        echo json_encode($this->sqlGetCompStructuresByUserRoleId($_GET['id']));
        exit;
    }

    public function ajaxGetAssignedStsOrganisationStructures() {
        echo json_encode($this->sqlGetStsOrganisationStructureId());
        exit;
    }

    public function ajaxUpdateStsOrganizationForUserRoles() {
        $countOfAdded = 0;
        $countOfRemoved = 0;
        $selectedUserRoleId = $_GET['id'];
        $arrayOfSelectedStsOrganization = (array)$_POST['cbid'];
        $recordsInDbForCurrentUserRole = (array)array_column($this->sqlGetCompStructuresByUserRoleId($selectedUserRoleId), 'sts_organization_structure_id');

        $arrayOfAdded = array_diff($arrayOfSelectedStsOrganization, $recordsInDbForCurrentUserRole);
        $arrayOfRemoved = array_diff($recordsInDbForCurrentUserRole, $arrayOfSelectedStsOrganization);

        foreach ($arrayOfAdded as $item) {
            $this->removeFromDbIfExist($item);
        }

        foreach ($arrayOfAdded as $item) {
            $this->removeFromDbIfExist($item);
            $this->sqlAddCompanyStructureToUserRole($selectedUserRoleId, $item);
            $countOfAdded++;
        }

        foreach ($arrayOfRemoved as $item) {
            $this->sqlRemoveCompanyStructureFromUserRole($selectedUserRoleId, $item);
            $countOfRemoved++;
        }

        $result = array();
        $result[0] = $countOfAdded;
        $result[1] = $countOfRemoved;

        echo json_encode($result);
        exit;
    }


    // SQL QUERIES

    private function getOrganizationStructure() {
        return $this->ladeLeitWartePtr
            ->newQuery('sts_organization_structure')
            ->orderBy("sts_organization_structure.id", "ASC")
            ->get("sts_organization_structure.id, sts_organization_structure.name, sts_organization_structure.parent_id");
    }


    private function getOrganizationStructureWithNames() {
        return $this->ladeLeitWartePtr
            ->newQuery('sts_organization_structure')
            ->join('sts_organization_structure sts2', 'sts_organization_structure.parent_id=sts2.id', 'LEFT JOIN')
            ->orderBy("sts_organization_structure.id", "ASC")
            ->get("sts_organization_structure.id, sts_organization_structure.name, sts_organization_structure.parent_id, sts2.name parentname");

    }

    private function sqlGetCompStructuresByUserRoleId($id) {
        return $this->ladeLeitWartePtr
            ->newQuery('user_role_company_structure')
            ->where("user_role_id", "=", $id)
            ->get("sts_organization_structure_id");
    }

    private function sqlRemoveCompanyStructureFromUserRole($userRoleId, $stsOrganizationStructureId) {
        if ($userRoleId == 0) {
            $q = "DELETE FROM user_role_company_structure WHERE sts_organization_structure_id=$stsOrganizationStructureId";
        } else {
            $q = "DELETE FROM user_role_company_structure WHERE user_role_id=$userRoleId AND sts_organization_structure_id=$stsOrganizationStructureId";
        }
        $this->oQueryHolder->query($q);

        $this->logChange($this->user, $this->action, "Remove Company structure: $stsOrganizationStructureId from User Role: $userRoleId.");
    }

    private function sqlAddCompanyStructureToUserRole($userRoleId, $stsOrganizationStructureId) {
        $q = "INSERT INTO public.user_role_company_structure (user_role_id, sts_organization_structure_id) VALUES ($userRoleId, $stsOrganizationStructureId)";
        $this->oQueryHolder->query($q);

        $this->logChange($this->user, $this->action, "Add Company structure: $stsOrganizationStructureId to User Role: $userRoleId.");
    }

    private function sqlGetStsOrganisationStructureId() {
        $dataFromDb = $this->ladeLeitWartePtr
            ->newQuery('user_role_company_structure')
            ->get("sts_organization_structure_id");

        $result = array();
        foreach ($dataFromDb as $record) {
            array_push($result, (int)$record['sts_organization_structure_id']);
        }

        return $result;
    }


    public function displayTreeStructure() {


        ?>

        <style>

            .Treant {
                position: relative;
                overflow: hidden;
                padding: 0 !important;
            }

            .Treant > .node,
            .Treant > .pseudo {
                position: absolute;
                display: block;
                visibility: hidden;
            }

            .Treant.loaded .node,
            .Treant.loaded .pseudo {
                visibility: visible;
            }

            .Treant > .pseudo {
                width: 0;
                height: 0;
                border: none;
                padding: 0;
            }

            .Treant .collapse-switch {
                width: 3px;
                height: 3px;
                display: block;
                border: 1px solid black;
                position: absolute;
                top: 1px;
                right: 1px;
                cursor: pointer;
            }

            .Treant .collapsed .collapse-switch {
                background-color: #868DEE;
            }

            .Treant > .node img {
                border: none;
                float: left;
            }


            body, div, dl, dt, dd, ul, ol, li, h1, h2, h3, h4, h5, h6, pre, form, fieldset, input, textarea, p, blockquote, th, td {
                margin: 0;
                padding: 0;
            }

            table {
                border-collapse: collapse;
                border-spacing: 0;
            }

            fieldset, img {
                border: 0;
            }

            address, caption, cite, code, dfn, em, strong, th, var {
                font-style: normal;
                font-weight: normal;
            }

            caption, th {
                text-align: left;
            }

            h1, h2, h3, h4, h5, h6 {
                font-size: 100%;
                font-weight: normal;
            }

            q:before, q:after {
                content: '';
            }

            abbr, acronym {
                border: 0;
            }

            body {
                background: #fff;
            }

            /* optional Container STYLES */
            .chart {
                height: 550px;
                margin: 5px;
                /*width: 900px;*/
            }

            .Treant > .node {
            }

            .Treant > p {
                font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
                font-weight: bold;
                font-size: 12px;
            }

            .node-name {
                font-weight: bold;
            }

            .nodeExample1 {
                padding: 2px;
                -webkit-border-radius: 3px;
                -moz-border-radius: 3px;
                border-radius: 3px;
                background-color: #ffffff;
                border: 1px solid #000;
                width: 200px;
                font-family: Tahoma;
                font-size: 12px;
            }

            .nodeExample1 img {
                margin-right: 10px;
            }

            .gray {
                background-color: #909090;
            }

            .light-gray {
                background-color: #D3D3C7;
            }

            .blue {
                background-color: #A2BDFD;
            }
        </style>

        <?php

        $this->displayHeader->printContent();

        include $_SERVER['STS_ROOT'] . "/pages/menu/engg.menu.php";

        $o = $this->getOrganizationStructureWithNames();

        $justnames[] = 'config';
        $jotson = array();
        $jotson[0] = "<script>\n
var config = {
        container: '#custom-colored',

        nodeAlign: 'BOTTOM',
        
        connectors: {
            type: 'step'
        },
        node: {
            HTMLclass: 'nodeExample1'
        }
    }";

        foreach ($o as $k => $comp) {
//        $jotson[] = array('id' => $comp['id'],
//            'parent_name' => $this->normalizeString($comp['parentname']),
//            'name' => $comp['name']);

            $escapedName = $this->normalizeString($comp['name']);
            $name = ($comp['name']);
            $escapedParent = $this->normalizeString($comp['parentname']);
            $justnames[] = $escapedName;

            if ($escapedParent) {
                $jotson[] = "$escapedName = {
          parent: $escapedParent,
          text: {
            name: '$name',
            title: 'Mr $name'
          }
        }";
            } elseif ($escapedName == 'ceo') {
                $jotson[] = "$escapedName = {
          parent: cto,
          text: {
            name: '$name',
            title: 'Mr $name'
          }
        }";
            } else {
                $jotson[] = "$escapedName = {
          text: {
            name: '$name',
            title: 'Mr $name'
          }
        }";
            }
//        r( $jotson);
        }

        $jotson[] = "chart_config = [\n" . implode(',', $justnames) . "\n]; \n</script>";

        echo(implode(",\n", $jotson));


        echo '<div class="chart" id="custom-colored"> --@-- </div>;
    <script src="http://fperucic.github.io/treant-js/vendor/raphael.js"></script>
    <script src="http://fperucic.github.io/treant-js/Treant.js"></script>';


        echo "      <script>
          new Treant( chart_config );
      </script>";

        $this->container->getDisplayFooter()->printContent();

    }


    public function normalizeString($str = '') {
        $str = strip_tags($str);
        $str = preg_replace('/[\r\n\t ]+/', ' ', $str);
        $str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $str);
        $str = strtolower($str);
        $str = html_entity_decode($str, ENT_QUOTES, "utf-8");
        $str = htmlentities($str, ENT_QUOTES, "utf-8");
        $str = preg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $str);
        $str = str_replace(' ', '_', $str);
        $str = rawurlencode($str);
        $str = str_replace('%', '_', $str);
        return $str;
    }

}

