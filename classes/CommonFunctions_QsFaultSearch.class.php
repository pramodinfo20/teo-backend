<?php

/**
 * CommonFunctions.class.php
 * 
 * @author Pradeep Mohan
 */

/**
 * Class to handle common functions
 */
class CommonFunctions_QsFaultSearch extends CommonFunctions
{

    protected $ladeLeitWartePtr;

    protected $user;

    protected $displayHeader;

    protected $requestPtr;


    function __construct($ladeLeitWartePtr, $displayHeader, $user, $requestPtr, $common_action, $filter_vehicles)
    {

        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->user = $user;
        $this->displayHeader = $displayHeader;
        $this->requestPtr = $requestPtr;
        $this->content = '';
        $this->ajaxContent = '';
        $this->filter_vehicles = $filter_vehicles;
        $this->displayHeader->enqueueJs("sts-custom-common-qs", "js/sts-custom-common-qs.js");
        if (isset($common_action) && method_exists($this, $common_action))
            call_user_func(array(
                $this,
                $common_action
            ));

    }


    function getAjaxContent()
    {

        return $this->ajaxContent;

    }


    function getJsonAjaxContent()
    {

        return json_encode($this->ajaxContent);

    }


    function genSearchForm()
    {

        $this->qs_fault_top = $this->ladeLeitWartePtr->newQuery('qs_fault_categories')
            ->where('parent_cat', '=', 0)
            ->get('qs_fcat_id=>cat_label');
        $this->qs_fault_top_select = '<select name="qs_fault_cat_search" class="qs_fault_cat_search"><option value="0">---</option>';
        $current_filter = '';
        foreach ($this->qs_fault_top as $qs_fcat_id => $qs_cat_label) {
            $this->qs_fault_top_select .= '<option value="' . $qs_fcat_id . '">' . $qs_cat_label . '</option>';
        }
        $this->qs_fault_top_select .= '</select>';

        if (isset($_POST['qs_faults_search_input'])) {
            $qs_faults_input = filter_var_array($_POST['qs_faults_search_input'], FILTER_SANITIZE_STRING);
            foreach ($qs_faults_input as $qs_cat_id => $qs_input) {
                $qs_cat_id_info = $this->ladeLeitWartePtr->newQuery('qs_fault_categories')
                    ->where('qs_fcat_id', '=', $qs_cat_id)
                    ->getOne('cat_label,parent_cat');

                $parent_cat_str = '';

                if (! empty($qs_cat_id_info['parent_cat'])) {
                    $checking_cat = $qs_cat_id_info['parent_cat'];
                    do {

                        $parent_cat = $this->ladeLeitWartePtr->newQuery('qs_fault_categories')
                            ->where('qs_fcat_id', '=', $checking_cat)
                            ->getOne('cat_label,parent_cat');
                        if (! empty($parent_cat))
                            $parent_cat_str = $parent_cat['cat_label'] . ' > ' . $parent_cat_str;
                        $checking_cat = $parent_cat['parent_cat'];
                    } while (! empty($checking_cat));
                }
                $cat_name = $parent_cat_str . $qs_cat_id_info['cat_label'];
                $current_filter .= 'Aktuelle Suche nach Kategorie : <strong>' . $cat_name . '</strong>';
                if (! empty(array_filter($qs_input))) {
                    $prev_label = '';
                    foreach ($qs_input as $field_key => $field_val) {

                        if (! empty($field_val)) {
                            $field_name = $this->ladeLeitWartePtr->newQuery('qs_fault_cat_inputs')
                                ->where('qs_fcat_id', '=', $qs_cat_id)
                                ->where('field_key', '=', $field_key)
                                ->getVal('field_label');
                            if (empty($field_name)) {
                                if (! empty($prev_label)) {
                                    $current_filter .= ' und ' . $prev_label . ' : <strong>' . $field_val . '</strong>';
                                    $prev_label = '';
                                } else
                                    continue;
                            } else if ($field_val == 'sonstiges') {
                                $prev_label = $field_name;
                            } else
                                $current_filter .= ' und ' . $field_name . ' : <strong>' . $field_val . '</strong>';
                        }
                    }
                }
            }
        } else if (isset($_POST['qs_fault_cat_search'])) {
            $qs_cat_id = filter_var($_POST['qs_fault_cat_search'], FILTER_SANITIZE_NUMBER_INT);
            $qs_cat_id_info = $this->ladeLeitWartePtr->newQuery('qs_fault_categories')
                ->where('qs_fcat_id', '=', $qs_cat_id)
                ->getOne('cat_label');
            $current_filter .= 'Aktuelle Suche nach Kategorie : <strong>' . $qs_cat_id_info['cat_label'] . '</strong>';
        }

        $notfound_msg = '';
        if ($this->filter_vehicles == 'error') {
            $notfound_msg = '<br>Keine treffende Fahrzeuge gefunden!';
        }
        if (! empty($this->filter_vehicles && is_array($this->filter_vehicles)))
            $filter_vehicles_str = implode(',', $this->filter_vehicles);
        else if ($this->filter_vehicles == 'error')
            $filter_vehicles_str = 'error';
        else
            $filter_vehicles_str = '';
        $this->content = '<form action="index.php" method="POST" id="qs_faults_search">
                        <fieldset class="qs_faults_tab_container">
                        <legend class="collapsible"><span class="genericon genericon-expand"></span>QS Fehler Filter</legend>
                        <div style="margin-left: 10px;">' . $current_filter . $notfound_msg . '</div>
                        <div class="collapsible_content" style="display: none" >
                        <div class="row" id="mainCat">
                        <div class="columns six"  >
                        Neue Suche - Kategorie wählen<br>
                        ' . $this->qs_fault_top_select . '
                        </div>
                        </div>
                        <div class="row">
                        <div class="columns eight nopad" id="ajaxFieldsWrap" class="genByAjax">
                        </div>
                        </div>
                        <div class="row">
                        <div class="columns eight nopad" class="genByAjax">
                        <input type="hidden" name="filtered_vehicles" id="filtered_vehicles" value="' . $filter_vehicles_str . '">
                        <input type="submit" name="qs_fault_search" value="Suchen">
                        <a href="' . $_SERVER['PHP_SELF'] . '"> Alle Filter löschen</a>
                        </div>
                        </div>
                        </div>
                        </fieldset>
                        </form>';

    }


    function ajaxGetFilterForm()
    {

        $this->ajaxContent['subcat'] = $this->getSubCat();
        $this->ajaxContent['qs_fields'] = $this->getQsFields();

    }


    function getSubCat()
    {

        $qs_fcat_id = filter_var($_POST['qs_fcat_id'], FILTER_SANITIZE_NUMBER_INT);
        $child_categories = $this->ladeLeitWartePtr->newQuery('qs_fault_categories')
            ->where('parent_cat', '=', $qs_fcat_id)
            ->get('qs_fcat_id=>cat_label');

        if (! empty($child_categories)) {
            $this->qs_child_cat_select = ' <div class="row genByAjax"><div class="columns six">Sub-Kategorie wählen<br>';
            $this->qs_child_cat_select .= '<select name="child_cat[' . $qs_fcat_id . ']" class="qs_fault_cat_search child_cat" ><option value=0>--</option>';
            foreach ($child_categories as $child_qs_fcat_id => $child_qs_fcat_label) {
                $this->qs_child_cat_select .= '<option value="' . $child_qs_fcat_id . '" >' . $child_qs_fcat_label . '</option>';
            }
            $this->qs_child_cat_select .= '</select></div></div>';
        }
        return $this->qs_child_cat_select;

    }


    function genFormElements($child_qs_cat_id, $fields)
    {

        $return_text = '<div="row"><div class="columns"><strong>Bitte Suchtext eingeben</strong></div></div>';
        $return_text .= '<div class="row">';
        foreach ($fields as $row) {
            if ($row['field_type'] == 'select') {
                $result = $this->ladeLeitWartePtr->newQuery('qs_fault_cat_inputs')
                    ->where('qs_fcat_id', '=', $child_qs_cat_id)
                    ->where('parent_field_key', '=', $row['field_key'])
                    ->where('field_type', '=', 'option')
                    ->orderBy('show_order')
                    ->get('*');
                $return_text .= '<div class="columns two two display-inline" >' . $row['field_label'] . '<select name="qs_faults_search_input[' . $child_qs_cat_id . '][' . $row['field_key'] . ']" data-qs_cat_id="' . $child_qs_cat_id . '" class="{has_misc}"><option value="0" >--</option>';
                $hasMisc = '';
                foreach ($result as $optiontext) {
                    if (stripos($optiontext['field_label'], 'sonstiges') !== false)
                        $hasMisc = 'has_misc';
                    $return_text .= '<option value="' . $optiontext['field_key'] . '">' . $optiontext['field_label'] . '</option>';
                }
                $return_text .= '</select>';
                if (! empty($hasMisc)) {
                    $result = $this->ladeLeitWartePtr->newQuery('qs_fault_cat_inputs')
                        ->where('qs_fcat_id', '=', $child_qs_cat_id)
                        ->where('parent_field_key', '=', $row['field_key'])
                        ->where('field_type', '=', 'text')
                        ->getOne('*');
                    $return_text .= '<input name="qs_faults_search_input[' . $child_qs_cat_id . '][' . $result['field_key'] . ']" id="has_misc_' . $child_qs_cat_id . '" type="text" style="display:none; margin-top: 4px;" value="">';
                }
                $return_text .= '</div>';
                $return_text = str_replace('{has_misc}', $hasMisc, $return_text);
                // $return_text.='</select></div>';
            } else {
                $return_text .= '<div class="columns two display-inline" >' . $row['field_label'] . '<input name="qs_faults_search_input[' . $child_qs_cat_id . '][' . $row['field_key'] . ']" type="' . $row['field_type'] . '" value="' . $default_value . '"></div>';
            }
        }
        return $return_text . '</div>';

    }


    function getQsFields()
    {

        $qs_fcat_id = filter_var($_POST['qs_fcat_id'], FILTER_SANITIZE_NUMBER_INT);
        $result = $this->ladeLeitWartePtr->newQuery('qs_fault_cat_inputs')
            ->where('qs_fcat_id', '=', $qs_fcat_id)
            ->where('parent_field_key', 'IS', 'NULL')
            ->orderBy('show_order')
            ->get('*');
        return $this->genFormElements($qs_fcat_id, $result);

    }


    function getHtml()
    {

        return $this->content;

    }


    function ajaxGetQSFaultForm()
    {

        $qs_fcat_id = filter_var($_POST['qs_fcat_id'], FILTER_SANITIZE_NUMBER_INT);
        $vehicle_id = filter_var($_POST['vehicle_id'], FILTER_SANITIZE_NUMBER_INT);
        $qs_fcat_label = filter_var($_POST['qs_fcat_label'], FILTER_SANITIZE_STRING);

        $child_categories = $this->ladeLeitWartePtr->newQuery('qs_fault_categories')
            ->where('parent_cat', '=', $qs_fcat_id)
            ->get('qs_fcat_id=>cat_label');

        $qs_fault_categories = array(
            $qs_fcat_id => $qs_fcat_label
        );

        if (! empty($child_categories))
            $qs_fault_categories += $child_categories;

        $form_elements_html = '<form action="index.php" id="qs_faults_list" method="POST">';

        foreach ($qs_fault_categories as $child_qs_cat_id => $child_qs_cat_label) {
            $result = $this->ladeLeitWartePtr->newQuery('qs_fault_cat_inputs')
                ->where('qs_fcat_id', '=', $child_qs_cat_id)
                ->orderBy('show_order')
                ->get('*');

            $form_elements_html .= '<div class="row"><h2>' . $child_qs_cat_label . '</h2></div><div class="row">';

            foreach ($result as $row) {
                $form_elements_html .= '<div class="columns four"><strong>' . $row['field_label'] . '</strong></div>';
            }
            $form_elements_html .= "</div>";

            $existing_data = $this->ladeLeitWartePtr->newQuery('qs_fault_list')
                ->groupBy('fault_sno')
                ->where('qs_fcat_id', '=', $child_qs_cat_id)
                ->where('vehicle_id', '=', $vehicle_id)
                ->get('fault_sno,json_object_agg(field_key,field_value) as data');

            if ($existing_data)
                $cnt = sizeof($existing_data) + 1;
            else
                $cnt = 1;

            $form_elements_html .= $this->genFormElements($child_qs_cat_id, $result, $cnt, $existing_data);
        }

        $form_elements_html .= '
                                <div class="row">
                                <br>
                                <input type="hidden" name="vehicle_id" value="' . $vehicle_id . '">
                                <input type="hidden" name="action" value="ajaxSaveQsFaults">
                                <input type="submit" name="submitsave" class="save_qs_faults" value="Speichern">
                                </div></form>';

        echo $form_elements_html;

        exit(0);

    }

}

