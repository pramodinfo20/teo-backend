<?php

/**
 * EcuSwParameterManagementController_DEPRECATED.class.php
 * The main class..
 * @author Jakub Kotlorz, FEV
 */

/**
 * EcuSwParameterManagementController Class, the main class
 */
/*include ("Parameterlist.class.php");*/

class EcuSwParameterManagementController_DEPRECATED extends EcuSwConfigurationController {
    public $EcuSwTableController = null;

    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        parent::__construct($ladeLeitWartePtr, $container, $requestPtr, $user);

        $this->displayHeader = $this->container->getDisplayHeader();
        $this->displayHeader->setTitle("StreetScooter Cloud Systems : " . $this->user->getUserRoleLabel());


        $this->EcuSwTableController = new Parameterlist($this->isSoftwareLocked());
        //$this->EcuSwTableController = new Parameterlist(false);
        $this->EcuSwTableController->Init();
        $this->EcuSwTableController->Execute();
        $this->EcuSwTableController->SetupHeaderFiles($this->displayHeader);

        if (isset($_GET['call'])) {
            $name = $_GET['call'];
            $this->$name();
        } else {
            $this->printContent();
        }
    }

    function printContent() {
        $this->displayHeader->printContent();
        include("pages/ecu-parameter-management.php");
    }

    protected function ajaxValidateForm() {
        $ValidateForm = new ValidateFormParameterManagement($this->ladeLeitWartePtr, $_REQUEST);
        echo $ValidateForm->validateForm();
    }

    protected function ajaxGetOtherSW() {
        $protocol = $this->ladeLeitWartePtr->newQuery('ecu_revisions')
            ->where('ecu_id', '=', $_REQUEST['ecu'])
            ->where('ecu_revision_id', '=', $_REQUEST['sw'])
            ->get('use_uds, use_xcp');

        $query = "SELECT ecu_revision_id, sts_version, subversion_major, subversion_suffix, CASE WHEN (use_uds = " .
            ($protocol[0]["use_uds"] == 'f' ? "false" : "true") . ") AND (use_xcp = " . ($protocol[0]["use_xcp"] == "f" ? "false" : "true") . ") THEN '' ELSE 'disabled' END as disabled FROM ecu_revisions WHERE ecu_id = " . $_REQUEST['ecu'] .
            "ORDER BY sts_version ASC";

        $result = $this->ladeLeitWartePtr->query($query);

        $iterator = 0;
        $sw = [];
        while ($data = pg_fetch_row($result)) {
            $sw[$iterator]['ecu_revision_id'] = $data[0];
            $sw[$iterator]['sts_version'] = $data[1];
            $sw[$iterator]['subversion_major'] = $data[2];
            $sw[$iterator]['subversion_suffix'] = $data[3];
            $sw[$iterator]['disabled'] = $data[4];
            ++$iterator;
        }

        $data[0] = 'empty';
        echo !(empty($sw)) ? json_encode($sw) : json_encode($data);
    }

    protected function ajaxCheckConflicts() {
        $check_uds_current = $this->ladeLeitWartePtr->newQuery('ecu_revisions')
            ->where('ecu_id', '=', $_REQUEST['ecu'])
            ->where('ecu_revision_id', '=', $_REQUEST['sw_current'])
            ->get('use_uds')[0]['use_uds']; // f or t

        $check_uds_destination = $this->ladeLeitWartePtr->newQuery('ecu_revisions')
            ->where('ecu_id', '=', $_REQUEST['ecu'])
            ->where('ecu_revision_id', '=', $_REQUEST['sw_destination'])
            ->get('use_uds')[0]['use_uds']; // f or t

        $current_id_udsId = $this->ladeLeitWartePtr->newQuery('ecu_tag_configuration')
            ->where('ecu_revision_id', '=', $_REQUEST['sw_current'])
            ->where('ecu_parameter_set_id', 'IN', json_decode($_REQUEST['ecu_parameter_sets'], true))
            ->multipleAndWhere('tag', '=', 'id', 'OR', 'tag', '=', 'udsId')
            ->orderBy('tag')
            ->orderBy('ecu_parameter_set_id')
            ->orderBy('timestamp', 'DESC')
            ->get('DISTINCT ON (tag, ecu_parameter_set_id) tag, ecu_parameter_set_id, tag_value');

        $destination_id_udsId = $this->ladeLeitWartePtr->newQuery('ecu_tag_configuration')
            ->where('ecu_revision_id', '=', $_REQUEST['sw_destination'])
            ->multipleAndWhere('tag', '=', 'id', 'OR', 'tag', '=', 'udsId', 'OR', 'tag', '=', 'deleted')
            ->orderBy('tag')
            ->orderBy('ecu_parameter_set_id')
            ->orderBy('timestamp', 'DESC')
            ->get('DISTINCT ON (tag, ecu_parameter_set_id) tag, ecu_parameter_set_id, tag_value');

        $deleted_sets = array_filter($destination_id_udsId, function ($k) {
            return $k['tag'] == 'deleted';
        });

        foreach ($deleted_sets as $key => $tags) {
            $destination_id_udsId = array_filter($destination_id_udsId, function ($k) use ($tags) {
                return $k['ecu_parameter_set_id'] != $tags['ecu_parameter_set_id'];
            });
        }


        $find_id = function ($id) use ($current_id_udsId) {
            foreach ($current_id_udsId as $current_set => $current_tags) {
                if ($current_tags['ecu_parameter_set_id'] == $id && $current_tags['tag'] == 'id')
                    return $current_tags['tag_value'];
            }
            return NULL;
        };

        $conflicts_array = [];
        $set_array = [];
        if ($check_uds_current == $check_uds_destination) {
            foreach ($current_id_udsId as $current_set => $current_tags) {
                foreach ($destination_id_udsId as $destination_set => $destination_tags) {
                    if ($current_tags['tag'] == $destination_tags['tag']) {
                        if ($current_tags['tag_value'] == $destination_tags['tag_value']) {
                            if ($check_uds_destination == 't') {
                                if ($destination_tags['tag'] == 'udsId') {
                                    if (!(isset($set_array[$current_tags['ecu_parameter_set_id']]))) {
                                        array_push($conflicts_array, ['ecu_parameter_set_id' => $destination_tags['ecu_parameter_set_id'],
                                            'id' => $find_id($current_tags['ecu_parameter_set_id']),
                                            'ecu_parameter_set_id_current' => $current_tags['ecu_parameter_set_id']
                                        ]);
                                        $set_array[$current_tags['ecu_parameter_set_id']] = 1;
                                    }
                                    break;
                                }
                            }
                            if ($destination_tags['tag'] == 'id') {
                                if (!(isset($set_array[$current_tags['ecu_parameter_set_id']]))) {
                                    array_push($conflicts_array, ['ecu_parameter_set_id' => $destination_tags['ecu_parameter_set_id'],
                                        'id' => $find_id($current_tags['ecu_parameter_set_id']),
                                        'ecu_parameter_set_id_current' => $current_tags['ecu_parameter_set_id']
                                    ]);
                                    $set_array[$current_tags['ecu_parameter_set_id']] = 1;
                                }
                                break;
                            }
                        }
                    }
                }
            }
        }
        if (empty($conflicts_array)) {
            $this->copyParameterSetsToOtherSW(json_decode($_REQUEST['ecu_parameter_sets'], true), $_REQUEST['sw_current'], $_REQUEST['sw_destination'], $_REQUEST['ecu']);

            $data[0] = 'empty';
        }

        echo !(empty($conflicts_array)) ? json_encode($conflicts_array) : json_encode($data);
    }

    protected function ajaxResolveConflicts() {
        $without_conflict = json_decode($_REQUEST["sets_without_conflict"], true);

        (!(empty($without_conflict))) ? $this->copyParameterSetsToOtherSW($without_conflict, $_REQUEST["sw_current"],
            $_REQUEST["sw_destination"], $_REQUEST["ecu"]) : "";

        $conflict = json_decode($_REQUEST["sets_conflict"], true);

        if (!(empty($conflict))) {
            $this->overwriteParameters($conflict, $_REQUEST['sw_current'], $_REQUEST['sw_destination'], $_REQUEST['ecu']);
        }
    }

    protected function overwriteParameters($sets, $current_sw, $destination_sw, $ecu_id) {
        $order = $this->ladeLeitWartePtr->newQuery('ecu_parameters')
            ->where('ecu_parameter_set_id', 'IN', array_column($sets, 'destination'))
            ->where('ecu_id', '=', $ecu_id)
            ->get('ecu_parameter_set_id, "order"');
        $deleted_values = "";
        foreach ($sets as $key => $value) {
            $deleted_values .= " ('deleted', 'odx.sts.02', 'false', " . $value['destination'] . ", $destination_sw, 'false'), ";
        }

        $deleted_values = rtrim($deleted_values, ", ");

        $delete_query = "INSERT INTO ecu_tag_configuration (tag, tag_value, fill_tag_value, ecu_parameter_set_id, ecu_revision_id, is_odx_tag) VALUES $deleted_values";

        $this->ladeLeitWartePtr->query($delete_query);

        $insert1 = "INSERT INTO ecu_parameter_sets (odx_name, type_id, unit_id, comment, parent_id, odx_tag_name, write_value_to_tag, use_in_old_format)
    SELECT odx_name, type_id, unit_id, comment, parent_id, odx_tag_name, write_value_to_tag, use_in_old_format FROM ecu_parameter_sets WHERE
    ecu_parameter_set_id IN (" . implode(',', array_column($sets, 'current')) . ")
     ORDER BY POSITION(','||ecu_parameter_set_id::text||',' in '(," . implode(",", array_column($sets, 'current')) . ",)') 
     RETURNING ecu_parameter_set_id";

        $result1 = $this->ladeLeitWartePtr->query($insert1);

        $find_order = function ($set_id) use ($order) {
            foreach ($order as $key => $value) {
                if ($value['ecu_parameter_set_id'] == $set_id)
                    return $value['order'];
            }
        };

        $ecu_parameter_sets_id = [];
        $iterator = 0;
        while ($row = pg_fetch_row($result1)) {
            $ecu_parameter_sets_id[$row[0]] = $find_order($sets[$iterator]['destination']);
            ++$iterator;
        }

        $map = "";
        $insert2 = "INSERT INTO ecu_parameters (ecu_parameter_set_id, ecu_id, \"order\") VALUES ";
        $iterator = 0;
        foreach ($ecu_parameter_sets_id as $id => $order) {
            $insert2 .= "($id, $ecu_id, " . $order . "), ";
            $map .= " (" . $sets[$iterator]['current'] . ", " . $id . "), ";
            ++$iterator;
        }

        $insert2 = rtrim($insert2, ", ");
        $map = rtrim($map, ", ");

        $this->ladeLeitWartePtr->query($insert2);

        $insert3 = "INSERT INTO ecu_tag_configuration (tag, tag_value, fill_tag_value, ecu_parameter_set_id, ecu_revision_id, is_odx_tag) 
                  SELECT DISTINCT ON (tag, ecu_parameter_set_id) tag, tag_value, fill_tag_value, 
                  (SELECT i.destination_id FROM (VALUES $map) as i(current_id, destination_id) WHERE i.current_id = ecu_parameter_set_id), $destination_sw, is_odx_tag FROM ecu_tag_configuration 
                    WHERE ecu_revision_id =  $current_sw AND ecu_parameter_set_id IN (" . implode(",", array_column($sets, 'current')) . ")
                    ORDER BY tag ASC, ecu_parameter_set_id ASC, timestamp DESC";

        $this->ladeLeitWartePtr->query($insert3);
    }

    protected function copyParameterSetsToOtherSW($current_sets, $current_sw, $destination_sw, $ecu_id) {
        //Get max order from destination SW
        $all_sets_destination_sw_query = "SELECT DISTINCT ecu_parameter_set_id FROM ecu_tag_configuration WHERE ecu_revision_id = $destination_sw
                                      EXCEPT 
                                      SELECT DISTINCT ecu_parameter_set_id FROM ecu_tag_configuration WHERE ecu_revision_id = $destination_sw AND tag = 'deleted'";
        $all = $this->ladeLeitWartePtr->query($all_sets_destination_sw_query);

        $all_sets = [];
        while ($row = pg_fetch_row($all)) {
            array_push($all_sets, $row[0]);
        }

        $set_order = $this->ladeLeitWartePtr->newQuery('ecu_parameters')
            ->where('ecu_parameter_set_id', 'IN', $all_sets)
            ->where('ecu_id', '=', $ecu_id)
            ->orderBy('"order"', 'DESC')
            ->limit(1)
            ->getVal('"order"');
        //---------------------------------

        $insert1 = "INSERT INTO ecu_parameter_sets (odx_name, type_id, unit_id, comment, parent_id, odx_tag_name, write_value_to_tag, use_in_old_format)
    SELECT odx_name, type_id, unit_id, comment, parent_id, odx_tag_name, write_value_to_tag, use_in_old_format FROM ecu_parameter_sets WHERE
    ecu_parameter_set_id IN (" . implode(',', $current_sets) . ") 
    ORDER BY POSITION(','||ecu_parameter_set_id::text||',' in '(," . implode(",", $current_sets) . ",)') 
    RETURNING ecu_parameter_set_id";

        $result1 = $this->ladeLeitWartePtr->query($insert1);

        $ecu_parameter_sets_id = [];
        while ($row = pg_fetch_row($result1)) {
            array_push($ecu_parameter_sets_id, $row[0]);
        }

        $map = "";
        $insert2 = "INSERT INTO ecu_parameters (ecu_parameter_set_id, ecu_id, \"order\") VALUES ";
        foreach ($ecu_parameter_sets_id as $key => $value) {
            $insert2 .= "($value, $ecu_id, " . ++$set_order . "), ";
            $map .= " (" . $current_sets[$key] . ", " . $value . "), ";
        }

        $insert2 = rtrim($insert2, ", ");
        $map = rtrim($map, ", ");

        $this->ladeLeitWartePtr->query($insert2);

        $insert3 = "INSERT INTO ecu_tag_configuration (tag, tag_value, fill_tag_value, ecu_parameter_set_id, ecu_revision_id, is_odx_tag) 
                  SELECT DISTINCT ON (tag, ecu_parameter_set_id) tag, tag_value, fill_tag_value, 
                  (SELECT i.destination_id FROM (VALUES $map) as i(current_id, destination_id) WHERE i.current_id = ecu_parameter_set_id), $destination_sw, is_odx_tag FROM ecu_tag_configuration 
                    WHERE ecu_revision_id =  $current_sw AND ecu_parameter_set_id IN (" . implode(",", $current_sets) . ")
                    ORDER BY tag ASC, ecu_parameter_set_id ASC, timestamp DESC";

        $this->ladeLeitWartePtr->query($insert3);
    }

}
