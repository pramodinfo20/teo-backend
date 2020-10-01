<?php

/**
 * EcuSwParameterManagementController_DEPRECATED.class.php
 * The main class..
 * @author Jakub Kotlorz, FEV
 */

/**
 * EcuSwConfigurationController Class, the main class
 */
class EcuSwConfigurationController_DEPRECATED extends PageController {

    public $oQueryHolder;
    public $msgs;

    protected $availableECUs;
    protected $userResponsibilities;
    protected $userDeputies;
    protected $userWriteables;
    protected $subversions;
    protected $translate;

    public $sCreateDbTable = "";

    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        parent::__construct();

        $this->translate = parent::getTranslationsForDomain();
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
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
        $this->displayHeader->enqueueStylesheet("parameterlist", "css/parameterlist.css");
        $this->displayHeader->enqueueStylesheet("css-infobox", "css/infobox.css");

        $this->displayHeader->setTitle("StreetScooter Cloud Systems : " . $this->user->getUserRoleLabel());


        // data for choosing ECU sw version
        $this->availableECUs = $this->getAllECUs();
        $this->getAllKindOfResponsibilities();

        foreach ($this->availableECUs as &$ecu) {
            $ecu['permission'] = $this->getEcuAccess($ecu['ecu_id']);
        }

        if (isset($_GET['method'])) {
            $name = $_GET['method'];
            $this->$name();
        }
    }

    /**
     * Module related methods
     */
    protected function getAllKindOfResponsibilities() {
        // TODO: do not get from session but from user login information!
        $userId = $_SESSION['sts_userid'];

        // implement finding users company structure somewhere in user class
        $userCompanyStructure = $this->ladeLeitWartePtr->newQuery('user_company_structure')
            ->where("user_id", "=", $userId)
            ->getOne("structure_id, is_leader");
        if ($userCompanyStructure['is_leader'] == "Yes") {
            $details = "leader";
        } elseif ($userCompanyStructure['is_leader'] == "Deputy") {
            $details = "deputy";
        } else {
            $details = "all";
        }

        // all responsibilities assigned to current user's COMPANY STRUCTURE
        $allUserCsResponsibilities = array();

        // TODO: filter out if user does not fulfill STRUCTURE_DETAILS condition (all/deputy/leader)
        // TODO: check if leader > deputy > all, if being deputy means you are "all" and "deputy"
        $allStructureResponsibilities = $this->ladeLeitWartePtr->newQuery('responsibility_assignments')
            ->where("is_structure", "=", "true")
            ->where("assigned_user_id", "=", $userCompanyStructure['structure_id'])
            ->where("structure_details", "=", $details)
            ->get("assigned_category_id, is_responsible, is_deputy");
        if (is_array($allStructureResponsibilities)) {
            $allUserCsResponsibilities = array_merge($allUserCsResponsibilities, $allStructureResponsibilities);
        }

        // all responsibilities assigned to current USER
        $allUserResponsibilities = $this->ladeLeitWartePtr->newQuery('responsibility_assignments')
            ->where("is_structure", "=", "false")
            ->where("assigned_user_id", "=", $userId)
            ->get("assigned_category_id, is_responsible, is_deputy");
        if (is_array($allUserResponsibilities)) {
            $allUserCsResponsibilities = array_merge($allUserCsResponsibilities, $allUserResponsibilities);
        }

        // 3 types of responsibilities: Responsible person, Deputy person, Writable person - as "return" values
        $this->userResponsibilities = array();
        $this->userDeputies = array();
        $this->userWriteables = array();

        // all responsibilities that describes ECU managing
        $ecuResponsibilities = $this->ladeLeitWartePtr->newQuery('responsibility_ecus')
            ->get("resp_cat_id, ecu_id");
        // map it to 'resp_cat_id=>ecu_id' as 'key=>value'
        $ecuRespMapped = array_combine(
            array_column($ecuResponsibilities, 'resp_cat_id'),
            array_column($ecuResponsibilities, 'ecu_id')
        );

        // all ECU responsibilities assigned to current user
        foreach ($allUserCsResponsibilities as $userResp) {
            $cat_id = $userResp['assigned_category_id'];
            if (array_key_exists($cat_id, $ecuRespMapped)) {
                $ecu_id = $ecuRespMapped[$cat_id];
                if ($userResp['is_responsible'] === 't') {
                    array_push($this->userResponsibilities, $ecu_id);
                } elseif ($userResp['is_deputy'] === 't') {
                    array_push($this->userDeputies, $ecu_id);
                } else {
                    array_push($this->userWriteables, $ecu_id);
                }
            };
        }
    }

    protected function ajaxGetFirstResponsibility() {
        $selectEcu = 0;

        if (count($this->userResponsibilities)) {
            $selectEcu = $this->userResponsibilities[0];
        } elseif (count($this->userDeputies)) {
            $selectEcu = $this->userDeputies[0];
        } elseif (count($this->userWriteables)) {
            $selectEcu = $this->userWriteables[0];
        }

        echo json_encode($selectEcu);
        return;
    }

    protected function getEcuAccess($ecu_id) {
        if (in_array($ecu_id, $this->userResponsibilities)) {
            return 'responsible';
        } elseif (in_array($ecu_id, $this->userDeputies)) {
            return 'deputy';
        } elseif (in_array($ecu_id, $this->userWriteables)) {
            return 'writable';
        } else {
            return '';
        }
    }

    /**
     * AJAX queries
     */

    protected function ajaxGetSwVersions() {
        $context = $_GET['ecu'];

        $sw_versions = $this->getSwVersionsForEcu($context);

        echo json_encode($sw_versions);
        return;
    }

    protected function ajaxGetSwHeader() {
        $header = $this->getSwVersionHeader($_GET['software']);
        echo json_encode($header);
        return;
    }

    protected function ajaxAddNewSwRevision() {
        $name = $_GET['selectedName'];
        $ecu = $_GET['selectedEcu'];

        // for copying sw version
        if (isset($_GET['selectedSwVersion'])) {
            $copyFrom = $_GET['selectedSwVersion'];

            // make sure that original exists
            if (!is_null($this->getSwVersionHeader($copyFrom))) {
                if ($this->addNewSwRevisionCopied($ecu, $name, $copyFrom)) {
                    $msg = "<div class=\"success-message\">New software version copied successfully</div>";
                    $inserted = $this->getSwVersionByName($name)['ecu_revision_id'];
                } else {
                    $msg = "<div class=\"error-message\">Problem with adding (copying) new software version</div>";
                    $inserted = null;
                }
            } else {
                $msg = "<div class=\"error-message\">Cannot find original (id: $copyFrom)</div>";
                $inserted = null;
            }
        } // creating empty
        else {
            if ($this->addNewSwRevisionHeaderEmpty($ecu, $name)) {
                $msg = "<div class=\"success-message\">New software version added successfully</div>";
                $inserted = $this->getSwVersionByName($name)['ecu_revision_id'];
            } else {
                $msg = "<div class=\"error-message\">Problem with adding new software version</div>";
                $inserted = null;
            }
        }

        echo json_encode(array('inserted' => $inserted, 'msg' => $msg));
        return;
    }

    protected function ajaxAddNewSubversion() {
        $parent = $this->getSwVersionHeader($_GET['major']);
        $suffix = $_GET['suffix'];

        if ($_GET['copyFromMain'] === "true") {
            $subAdded = $this->addNewSubversionCopied($parent['ecu_id'], $parent['sts_version'], $_GET['major'], $suffix);
        } else {
            $subAdded = $this->addNewSubversionEmpty($parent['ecu_id'], $parent['sts_version'], $_GET['major'], $suffix);
        }

        if ($subAdded) {
            $msg = "<div class=\"success-message\">New subversion added successfully</div>";
            $inserted = $this->getSwVersionByName($parent['sts_version'], $suffix)['ecu_revision_id'];
        } else {
            $msg = "<div class=\"error-message\">Problem with adding new subversion</div>";
            $inserted = null;
        }
        echo json_encode(array('inserted' => $inserted, 'msg' => $msg));
        return;
    }

    protected function ajaxGetConfigsForSwVersion() {
        $revision = $_GET['revision'];
        $configs = array();

        $variant_ecu_mappings = $this->ladeLeitWartePtr->newQuery('variant_ecu_revision_mapping')
            ->where("rev_id", "=", $revision)
            ->limit(20)
            ->get("variant_id");

        if ($variant_ecu_mappings) {
            foreach ($variant_ecu_mappings as $map) {
                $variant = $this->ladeLeitWartePtr->newQuery('vehicle_variants')
                    ->where("vehicle_variant_id", "=", $map['variant_id'])
                    ->getOne("windchill_variant_name");
                array_push($configs, $variant['windchill_variant_name']);
            }
        }

        echo json_encode($configs);
        return;
    }

    protected function ajaxGetSubversionsFor() {
        $header = $this->getSubversionsFor($_GET['parent']);
        echo json_encode($header);
        return;
    }

    protected function isSoftwareLocked() {
        $locked = true;
        $swVersion = null;

        // get currently selected software version
        if (isset($_REQUEST['ecuVersion']['selectedEcu'])) {
            $id = $_REQUEST['ecuVersion']['selectedEcu'];
            $swVersion = $_REQUEST['ecuVersion']['ecu'][$id];
        }

        // if software has been chosen
        if (!is_null($swVersion)) {
            $variant_ecu_mappings = $this->ladeLeitWartePtr->newQuery('variant_ecu_revision_mapping')
                ->where("rev_id", "=", $swVersion)
                ->where("variant_id", "!=", "1")
                ->limit(5)
                ->get("variant_id");

            if ($variant_ecu_mappings) {
                $locked = true;
            } else {
                $locked = false;
            }
        }

        return $locked;
    }

    //
    // Removing Sw version
    // TODO: extra checking if no subversions or configurations are assigned
    //
    protected function ajaxRemoveSwRevision() {
        $msg = "";

        if (isset($_GET['revision'])) {
            if ($this->removeEcuRevision($_GET['revision'])) {
                $msg = '<div class="success-message">Removed</div>';
            } else {
                $msg = '<div class="error-message">Error while removing</div>';
            }
        }

        echo json_encode($msg);
        exit;
    }

    /**
     * Database queries
     */

    protected function addNewSwRevisionHeaderEmpty($ecu, $name) {
        $emptyRevisionQuery = "INSERT INTO public.ecu_revisions (ecu_id, sts_version, timestamp_last_change, subversion_suffix) VALUES ($ecu, '$name', CURRENT_TIMESTAMP, '')";

        $emptyRevision = $this->oQueryHolder->query($emptyRevisionQuery);

        if ($emptyRevision) {
            $revisionId = $this->getSwVersionByName($name)['ecu_revision_id'];

            return $this->addNewTagsForEmptyRevision($revisionId);
        }
    }

    protected function addNewTagsForEmptyRevision($revisionId) {
        $parameters = [
            'hw' => [
                'action' => ['value' => 'rw', 'isOdxTag' => 'TRUE'],
                'byteCount' => ['value' => '', 'isOdxTag' => 'TRUE'],
                'id' => ['value' => 'SW_VERSION_STS', 'isOdxTag' => 'TRUE'],
                'order' => ['value' => '', 'isOdxTag' => 'FALSE'],
                'protocol' => ['value' => '', 'isOdxTag' => 'TRUE'],
                'type' => ['value' => 'ascii', 'isOdxTag' => 'TRUE'],
                'udsId' => ['value' => '0x0001', 'isOdxTag' => 'TRUE'],
                'value' => ['value' => 0, 'isOdxTag' => 'FALSE'],
                'version' => ['value' => 0, 'isOdxTag' => 'TRUE'],
            ],
            'sw' => [
                'action' => ['value' => 'rw', 'isOdxTag' => 'TRUE'],
                'byteCount' => ['value' => '', 'isOdxTag' => 'TRUE'],
                'id' => ['value' => 'HW_VERSION_STS', 'isOdxTag' => 'TRUE'],
                'order' => ['value' => '', 'isOdxTag' => 'FALSE'],
                'protocol' => ['value' => '', 'isOdxTag' => 'TRUE'],
                'type' => ['value' => 'ascii', 'isOdxTag' => 'TRUE'],
                'udsId' => ['value' => '0x0001', 'isOdxTag' => 'TRUE'],
                'value' => ['value' => 0, 'isOdxTag' => 'FALSE'],
                'version' => ['value' => 0, 'isOdxTag' => 'TRUE'],
            ]
        ];

        foreach ($parameters as $key => $tags) {
            foreach ($tags as $tag => $value) {
                $this->oQueryHolder->query(
                    "INSERT INTO public.ecu_tag_configuration (tag, tag_value, fill_tag_value, ecu_parameter_set_id, ecu_revision_id, is_odx_tag)
           VALUES ('{$tag}', '{$value['value']}', 'FALSE', {$this->getEcuParameterSetsByOdxTag($key)['ecu_parameter_set_id']}, {$revisionId}, '{$value['isOdxTag']}')");
            }
        }

        return true;
    }

    protected function addNewSwRevisionCopied($ecu, $name, $copyFrom) {
        $major = intval($copyFrom);
        $addRevisionQuery = "
INSERT INTO public.ecu_revisions 
(ecu_id, hw, sw, request_id, response_id, href_windchill, use_uds, use_xcp, sw_profile_ok, released, timestamp_last_change, info_text, version_info, sts_version)
SELECT ecu_revisions.ecu_id, ecu_revisions.hw, ecu_revisions.sw, ecu_revisions.request_id, ecu_revisions.response_id, 
  ecu_revisions.href_windchill, ecu_revisions.use_uds, ecu_revisions.use_xcp, ecu_revisions.sw_profile_ok, ecu_revisions.released, CURRENT_TIMESTAMP, 
  ecu_revisions.info_text, ecu_revisions.version_info, '$name'
FROM public.ecu_revisions 
WHERE ecu_revision_id=$major
";
        $revision = $this->oQueryHolder->query($addRevisionQuery);

        if ($revision) {
            $revisionId = $this->getSwVersionByName($name)['ecu_revision_id'];

            return $this->addNewSwTagsCopied($major, $revisionId);
        }
    }

    protected function addNewSwTagsCopied($major, $revisionId) {
        $tags = $this->ladeLeitWartePtr->newQuery('ecu_tag_configuration')
            ->where("ecu_revision_id", "=", $major)
            ->get("tag, tag_value, fill_tag_value, ecu_parameter_set_id, ecu_revision_id, is_odx_tag");

        foreach ($tags as $tag) {
            $this->oQueryHolder->query(
                "INSERT INTO public.ecu_tag_configuration (tag, tag_value, fill_tag_value, ecu_parameter_set_id, ecu_revision_id, is_odx_tag)
      VALUES ('{$tag['tag']}', '{$tag['tag_value']}', '{$tag['fill_tag_value']}', {$tag['ecu_parameter_set_id']}, {$revisionId}, '{$tag['is_odx_tag']}')");
        }

        return true;
    }

    protected function addNewSubversionCopied($ecu, $name, $major, $suffix) {
        $major = intval($major);
        $addSubRevisionQuery = "
INSERT INTO public.ecu_revisions 
(ecu_id, hw, sw, sts_version, request_id, response_id, href_windchill, use_uds, use_xcp, sw_profile_ok, released, timestamp_last_change, info_text, version_info, subversion_major, subversion_suffix)
SELECT ecu_revisions.ecu_id, ecu_revisions.hw, ecu_revisions.sw, ecu_revisions.sts_version, ecu_revisions.request_id, ecu_revisions.response_id, 
  ecu_revisions.href_windchill, ecu_revisions.use_uds, ecu_revisions.use_xcp, ecu_revisions.sw_profile_ok, ecu_revisions.released, CURRENT_TIMESTAMP, 
  ecu_revisions.info_text, ecu_revisions.version_info, $major, '$suffix'
FROM public.ecu_revisions 
WHERE ecu_revision_id=$major
";

        $subRevision = $this->oQueryHolder->query($addSubRevisionQuery);

        if ($subRevision) {
            $subRevisionId = $this->getSwVersionByName($name, $suffix)['ecu_revision_id'];

            return $this->addNewSubversionTagsCopied($major, $subRevisionId);
        }
    }

    protected function addNewSubversionTagsCopied($major, $subRevisionId) {
        $tags = $this->ladeLeitWartePtr->newQuery('ecu_tag_configuration')
            ->where("ecu_revision_id", "=", $major)
            ->get("tag, tag_value, fill_tag_value, ecu_parameter_set_id, ecu_revision_id, is_odx_tag");

        foreach ($tags as $tag) {
            $this->oQueryHolder->query(
                "INSERT INTO public.ecu_tag_configuration (tag, tag_value, fill_tag_value, ecu_parameter_set_id, ecu_revision_id, is_odx_tag)
      VALUES ('{$tag['tag']}', '{$tag['tag_value']}', '{$tag['fill_tag_value']}', {$tag['ecu_parameter_set_id']}, {$subRevisionId}, '{$tag['is_odx_tag']}')");
        }

        return true;
    }

    protected function addNewSubversionEmpty($ecu, $name, $major, $suffix) {
        $q = "
INSERT INTO public.ecu_revisions 
(ecu_id, sts_version, subversion_major, subversion_suffix, timestamp_last_change) 
VALUES 
(" . intval($ecu) . ", '$name', " . intval($major) . ", '$suffix', CURRENT_TIMESTAMP)
RETURNING ecu_revision_id";
        $result = $this->oQueryHolder->query($q);
        $revisionId = pg_fetch_row($result)[0];
        return $this->addNewTagsForEmptyRevision($revisionId);
    }

    protected function getAllECUs() {
        return $this->ladeLeitWartePtr->newQuery('ecus')
            ->orderBy("name")
            ->where("ecu_id", ">", "0")
            ->get("ecu_id, name, supports_odx02");
    }

    protected function getSwVersionsForEcu($id) {
        return $this->ladeLeitWartePtr->newQuery('ecu_revisions')
            ->where("ecu_id", "=", $id)
            ->get("ecu_revision_id, sts_version, subversion_major, subversion_suffix");
    }

    /**
     * @param $name Give sts_verion name you try to find
     * @param bool $suffix enter suffix name if search only in subversions, empty (default) when only major versions
     * @return mixed Whole object of ecu_revisions that matches name, null otherwise
     */
    protected function getSwVersionByName($name, $suffix = null) {
        if ($suffix) {
            return $this->ladeLeitWartePtr->newQuery('ecu_revisions')
                ->where("sts_version", "=", "$name")
                ->where("subversion_suffix", "=", "$suffix")
                ->getOne("*");
        } else {
            return $this->ladeLeitWartePtr->newQuery('ecu_revisions')
                ->where("sts_version", "=", "$name")
                ->getOne("*");
        }
    }

    protected function getSwVersionHeader($id) {
        return $this->ladeLeitWartePtr->newQuery('ecu_revisions')
            ->where("ecu_revision_id", "=", $id)
            ->getOne("*");
    }

    protected function removeEcuRevision($id) {
        $configurationQuery = "DELETE FROM public.ecu_tag_configuration WHERE ecu_revision_id=$id";
        $this->oQueryHolder->query($configurationQuery);
        $revisionQuery = "DELETE FROM public.ecu_revisions WHERE ecu_revision_id=$id";
        return $this->oQueryHolder->query($revisionQuery);
    }

    protected function getSubversionsFor($id) {
        return $this->ladeLeitWartePtr->newQuery('ecu_revisions')
            ->where("subversion_major", "=", $id)
            ->get("*");
    }

    protected function getEcuParameterSetsByOdxTag($odxTag) {
        return $this->ladeLeitWartePtr->newQuery('ecu_parameter_sets')
            ->where('odx_tag_name', '=', strtolower($odxTag))
            ->getOne("*");
    }

    protected function saveUserSettings() {
        $settings = $this->ladeLeitWartePtr->newQuery('user_settings')->where('sts_userid', '=', $_SESSION['sts_userid'])->getVal('settings');
        if ($settings) {
            $sw = unserialize($settings);
            $sw['sw_version']['ecuVersion'] = $_REQUEST['ecuVersion'];
            $sw['sw_version']['selectedEcu'] = $_REQUEST['selectedEcu'];

            $this->ladeLeitWartePtr->newQuery('user_settings')->where('sts_userid', '=', $_SESSION['sts_userid'])->update(array('settings'), array(serialize($sw)));

        } else {
            $sw = array(
                'sw_version' => array(
                    'ecuVersion' => $_REQUEST['ecuVersion'],
                    'selectedEcu' => $_REQUEST['selectedEcu']
                )
            );
            $this->ladeLeitWartePtr->newQuery('user_settings')->insert_multiple_new(['sts_userid', 'settings'], [[$_SESSION["sts_userid"], serialize($sw)]]);
        }
    }

    protected function ajaxCheckIfExistsInDB() {
        echo empty($this->ladeLeitWartePtr->newQuery('ecu_revisions')->where('sts_version', '=', $_REQUEST['sts_version'])->get('sts_version')) ? 0 : 1;
    }
}


