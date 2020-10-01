
<?php


/**
 * quickformhelper.class.php
 * Helper Klasse für HTML_Quickform2
 * @author Pradeep Mohan
 */
function check_stationame($stationnameval)
{

    $pattern = "/^([0-9]){7}([rRlL])?$/";

    if (! preg_match($pattern, trim($stationnameval))) {
        echo $stationnameval . " ist nicht gültig";
        return false;
    }
    return true;

}

/**
 * QuickformHelper Klasse
 */
class QuickformHelper
{

    /**
     * form object
     *
     * @var HTML_QuickForm2 $form
     */
    public $form;

    /**
     * form renderer
     *
     * @var HTML_QuickForm2_Renderer $renderer
     */
    protected $plain_html;

    protected $renderer;


    /**
     * Konstruktor
     *
     * @param string $displayheader
     *            used to enqueue the javascript files
     * @param string $formname
     *            formname to be used as id of the form html tag
     */
    public function __construct($displayheader, $formname, $method = null, $attributes = null)
    {

        require_once 'HTML/QuickForm2.php';
        require_once 'HTML/QuickForm2/Renderer.php';
        require_once 'HTML/QuickForm2/JavascriptBuilder.php';

        $this->displayheader = $displayheader;

        // require_once 'HTML/QuickForm2/Rule/Required.php';
        // require_once 'HTML/QuickForm2/Rule/Regex.php';
        $this->form = new HTML_QuickForm2($formname, $method, $attributes);

        $this->renderer = HTML_QuickForm2_Renderer::factory('default');

        HTML_QuickForm2_Factory::registerElement('time', 'HTML_QuickForm2_Element_InputTime');
        HTML_QuickForm2_Factory::registerElement('number', 'HTML_QuickForm2_Element_InputNumber');
        HTML_QuickForm2_Factory::registerElement('staticNoLabel', 'HTML_QuickForm2_Element_StaticNoLabel');

        $this->renderer->setTemplateForClass('HTML_QuickForm2_Element_StaticNoLabel', '{element}');

        $this->renderer->setTemplateForId('fps_dep_add_edit_form', '<div class="quickform">{errors}<form{attributes}>{content}{hidden}</form></div>');
        $this->renderer->setTemplateForId('fps_dep_edit_form', '<div class="quickform">{errors}<form{attributes}>{content}{hidden}</form></div>');

        $this->renderer->setElementTemplateForGroupId('tableit', 'html_quickform2_element', '<td>{element}<qf:label><label for="{id}">{label}</label></qf:label></td>');

        $this->renderer->setTemplateForId('tableit', '<tr>{content}</tr>');

        $this->renderer->setElementTemplateForGroupId('wholeTd', 'html_quickform2_element', '{element}<qf:label><label for="{id}">{label}</label></qf:label>');

        $this->renderer->setTemplateForId('wholeTd', '<td>{content}</td>');

        $this->renderer->setElementTemplateForGroupId('withLabel', 'html_quickform2_element', '{element}<qf:label><label for="{id}">{label}</label></qf:label>');

        $this->renderer->setTemplateForId('withLabel', '<div class="row"><p>{content}</p></div>');

        // $this->renderer->setTemplateForClass('HTML_QuickForm2_Element_InputCheckbox','<div class="row">

        // <div class="element<qf:error> error</qf:error>"><qf:error><span class="error">{error}<br /></span></qf:error>{element}</div>
        // </div>');
        $this->renderer->setJavascriptBuilder(new HTML_QuickForm2_JavascriptBuilder('js/'));
        $this->renderer->setoption('required_note', '* Diese Felder sind erforderlich.');
        $this->renderer->setoption('errors_prefix', 'Ungültige Information : '); // invalid information entered
        $this->renderer->setoption('errors_suffix', 'Korrigieren Sie bitte diese Felder.'); // Please correct these fields
        foreach ($this->renderer->getJavascriptBuilder()->getLibraries() as $link) {
            $displayheader->enqueueScriptTags($link);
        }

        $this->renderer->setElementTemplateForGroupId('fps_ladepunkte', 'html_quickform2_element', '{label}</label><br>{element}');

        // $this->renderer->setTemplateForId(
        // 'nameGrp', '<div class="row templateid"><p class="label"><qf:required><span class="required">*</span></qf:required><qf:label><label>{label}</label></qf:label></p>{content}</div>'
        // );
    }


    public function addElement($elemType, $elemName = null, $elemAttribs = null)
    {

        return $this->form->addElement($elemType, $elemName, $elemAttribs);

    }


    public function fahrzeug_zuruck($kweeks, $kweek_quantities, $kweek_quantities_delivered, $variant_value)
    {

        $fs = $this->form->addElement('fieldset');
        $options = array();
        foreach ($kweeks as $kweek => $kweek_label) {
            if (! isset($kweek_quantities[$kweek]))
                continue;
            $options[$kweek] = $kweek_label;
            $fs->addHidden($kweek . '_check', array(
                'id' => $kweek . '_check'
            ))->setValue($kweek_quantities[$kweek] - $kweek_quantities_delivered[$kweek]);
        }
        $select = $fs->addSelect('return_week', null, array(
            'options' => $options
        ))->setLabel('Kalendar Woche');
        $fs->addElement('number', 'return_quantity', array(
            'min' => 1
        ))->setLabel('Anzahl');
        $fs->addHidden('action')->setValue('save_fahrzeug_zuruck');
        $fs->addHidden('variant_value')->setValue($variant_value);
        $fs->addSubmit('save_return', array(
            'value' => 'Bestätigen'
        ));

    }


    public function getAbfahrtszeit($vehicleid = null, $districts = null, $second_dep = null, $defaultDepot, $action, $defaultZspl = null)
    {

        $fs = $this->form->addElement('fieldset');

        if (isset($districts)) {
            if (sizeof($districts) > 1)
                return false; // edit in the future, but right now, we have only one district per vehicle. If not the case then there has to be an error in the data
            else
                $district = $districts[0];
        } else
            $district = null;

        $fs->addElement('staticNoLabel', null, null, array(
            'content' => '<table id="departure_times_table">'
        ));

        $group = $fs->addGroup('', array(
            'id' => 'tableit'
        ));

        $dayheadings = array(
            'mon' => 'Montag',
            'tue' => 'Dienstag',
            'wed' => 'Mittwoch',
            'thu' => 'Donnerstag',
            'fri' => 'Freitag',
            'sat' => 'Samstag',
            'sun' => 'Sonntag'
        );
        // $dayheadings=array('mon'=>'Montag','tue'=>'Dienstag','wed'=>'Mittwoch','thu'=>'Donnerstag','fri'=>'Freitag','sat'=>'Samstag');

        foreach ($dayheadings as $daykey => $day) {
            $params = array(
                'class' => 'active_check'
            );
            if (isset($district['departure_' . $daykey]) && ! empty($district['departure_' . $daykey])) {
                $params['checked'] = 'checked';
            } else if ($action == "save_departures_whole" && $daykey != 'sun')
                $params['checked'] = 'checked';

            $group->addElement('checkbox', 'active_' . $daykey, $params)->setLabel($day);
        }

        $days = array_keys($dayheadings);
        $group = $fs->addGroup('', array(
            'id' => 'tableit'
        ));
        foreach ($days as $day) {

            if (isset($district['departure_' . $day]) && ! empty($district['departure_' . $day])) {
                $dayinput = $group->addElement('time', 'departure_' . $day, array(
                    'id' => 'departure_' . $day
                ));
                $timeex = explode(':', $district['departure_' . $day]);
                $dayinput->setValue($timeex[0] . ':' . $timeex[1]);
            } else {
                if ($action == 'save_departures_whole' && $day != 'sun') {
                    $dayinput = $group->addElement('time', 'departure_' . $day, array(
                        'id' => 'departure_' . $day
                    ));
                    $dayinput->setValue('08:00');
                } else {
                    $dayinput = $group->addElement('time', 'departure_' . $day, array(
                        'id' => 'departure_' . $day,
                        'disabled' => 'disabled'
                    ));
                }
            }
        }
        $fs->addElement('staticNoLabel', null, null, array(
            'content' => '</table>'
        ));

        if (empty($second_dep))
            $hideclass = 'init_hidden';
        else
            $hideclass = '';

        $fs = $this->form->addElement('fieldset', null, array(
            'class' => 'second_departures ' . $hideclass
        ));

        $fs->addElement('staticNoLabel', null, null, array(
            'content' => '<table>'
        ));
        $group = $fs->addGroup('', array(
            'id' => 'tableit'
        ));
        foreach ($days as $day) {

            if (isset($second_dep['second_departure_' . $day]) && ! empty($second_dep['second_departure_' . $day])) {
                $dayinput = $group->addElement('time', 'second_departure_' . $day, array(
                    'id' => 'second_departure_' . $day
                ));
                $timeex = explode(':', $second_dep['second_departure_' . $day]);
                $dayinput->setValue($timeex[0] . ':' . $timeex[1]);
            } else {
                if ($action == 'save_departures_whole' && $day != 'sun') {
                    $dayinput = $group->addElement('time', 'second_departure_' . $day, array(
                        'id' => 'second_departure_' . $day
                    ));
                } else {
                    if (isset($district['departure_' . $day]) && ! empty($district['departure_' . $day]))
                        $dayinput = $group->addElement('time', 'second_departure_' . $day, array(
                            'id' => 'second_departure_' . $day
                        ));
                    else
                        $dayinput = $group->addElement('time', 'second_departure_' . $day, array(
                            'id' => 'second_departure_' . $day,
                            'disabled' => 'disabled'
                        ));
                }
            }
        }

        $fs->addElement('staticNoLabel', null, null, array(
            'content' => '</table>'
        ));

        $fs = $this->form->addElement('fieldset');

        if (empty($second_dep))
            $fs->addStatic()->setContent('<a href="#" data-target="second_departures" class="parent_hidden_text"><span class="genericon genericon-plus"> </span><span>2. Abfahrtszeiten pro Tag setzen</span></a>');

        if (isset($vehicleid))
            $fs->addHidden('vehicle_id')->setValue($vehicleid);
        if (isset($defaultDepot))
            $fs->addHidden('zsp')->setValue($defaultDepot);
        if (isset($defaultZspl))
            $fs->addHidden('zspl')->setValue($defaultZspl);

        $fs->addHidden('action')->setValue($action);
        $fs->addStatic()->setContent('<input type="submit" name="cancel" value="Abbrechen" class="sales">
                                        <input type="submit" name="savedeparturetimes" value="Speichern" class="sales">');

        // $fs->addSubmit('savedeparturetimes',array('value'=>'Speichern'))->setAttribute('class', 'sales');
        // $fs->addSubmit('cancel',array('value'=>'Abbrechen'))->setAttribute('class', 'sales');
    }


    public function getVehicleLateCharging($vehicles, $zsp)
    {

        $fs = $this->form->addElement('fieldset');
        $grouphead = $fs->addGroup('');
        $grouphead->addText('set_late_charge_time', array(
            'class' => 'set_late_charge_time'
        ))->setValue('20:00');
        $grouphead->addElement('static', '', array(
            'class' => 'set_late_charge_all'
        ))
            ->setContent('Gleiche Startzeit für alle Fahrzeuge eintragen')
            ->setTagName('a');

        $fs->addElement('staticNoLabel', null, null, array(
            'content' => '<br><table>'
        ));
        $fs->addElement('staticNoLabel', null, null, array(
            'content' => '<tr><th>VIN</th><th>AKZ</th><th>Ladepunkte</th><th>Setze Spätladen</th><th>Startzeit Spätladen</th></tr>'
        ));
        foreach ($vehicles as $vehicle) {
            $group = $fs->addGroup('', array(
                'id' => 'tableit'
            ));
            $group->addStatic()->setContent($vehicle['vin']);
            $group->addStatic()->setContent($vehicle['code']);
            $group->addStatic()->setContent($vehicle['sname']);
            $params = array();
            if ($vehicle['late_charging'] == 't')
                $params['checked'] = 'checked';
            $params['class'] = 'latecharging';
            $checkbox = $group->addCheckbox('latecharging_' . $vehicle['vehicle_id'], $params)->setLabel('Ja');

            $timeparams = array(
                'class' => 'latechargingtime'
            );
            if ($vehicle['late_charging'] == 'f')
                $timeparams['disabled'] = 'disabled';

            $latechargingTime = $group->addElement('time', 'latechargingtime_' . $vehicle['vehicle_id'], $timeparams);

            if ($vehicle['late_charging_time'])
                $latechargingTime->setValue(substr($vehicle['late_charging_time'], 0, 5));
            else
                $latechargingTime->setValue('');
        }
        $fs->addElement('staticNoLabel', null, null, array(
            'content' => '</table>'
        ));

        $fs->addHidden('zsp')->setValue($zsp);
        $fs->addHidden('action')->setValue('saveLateCharging');

        $fs->addStatic()->setContent('  <input type="submit" name="cancel" value="Abbrechen" class="sales">
                                        <input type="submit" name="submitlatecharging" value="Speichern" class="sales">');

        // $fs->addSubmit('submitlatecharging',array('value'=>'Speichern'));
    }


    // depots_nur_frei
    public function getVehicleMgmt($vehicles, $depots, $allstations, $freestations, $zsp, $vehicle_variants, $divisions = null, $depotforfreestations, $replacement = null) // depotforfreestations
    {

        $options_divisions = "";
        if ($divisions) {
            foreach ($divisions as $depot_id => $div)
                $options_divisions .= "<option value=\"$depot_id\">$div</option>";
        }

        $fs = $this->form->addElement('fieldset');
        if (empty($vehicles))
            $fs->addStatic()
                ->setContent('Keine Fahrzeuge gefunden!')
                ->setTagName('h2');
        else {
            $fs->addElement('staticNoLabel', null, null, array(
                'content' => '<table>'
            ));
            $fs->addElement('staticNoLabel', null, null, array(
                'content' => '<tr><th>Aktueller ZSP</th><th>VIN</th><th>AKZ</th><th>Ersatzfahrzeug</th><th>1/3 phasige Fahrzeug</th><th>Fahrzeugtyp<th>Neuer ZSP<sup>+</sup></th><th>Ladepunkte</th></tr>'
            ));
            foreach ($vehicles as $vehicle) {
                $group = $fs->addGroup('', array(
                    'id' => 'tableit'
                ));
                $group->addStatic()->setContent($vehicle['dname']);

                $classname = 'singlephase_vehicle';
                // $vehicle_phase='1-phasig';
                $vehicle_phase = ($vehicle['three_phase_charger'] == 't') ? '3-phasig' : '1-phasig';
                $checkphrase = "1-phasig";

                if (isset($vehicle_variants[$vehicle['vehicle_variant']])) {
                    $vehicletype = $vehicle_variants[$vehicle['vehicle_variant']];
                }

                $group->addStatic(null, array(
                    'class' => $classname
                ))
                    ->setContent($vehicle['vin'])
                    ->setTagName('span');
                $group->addStatic(null, array(
                    'class' => $classname
                ))
                    ->setContent($vehicle['code'])
                    ->setTagName('span');

                if ($vehicle['replacement_vehicles'] == 't' && $vehicle['station_id'] == '') {
                    $readonly = 'false';
                    $statusMsg = 'Für Ersatzfahrzeuge mit (kein Ladepunk) als Ladepunkt ist der Status nicht änderbar!';
                    $lp_not_empty = '';
                } else if ($vehicle['replacement_vehicles'] == 't' && $vehicle['station_id'] !== '') {
                    $readonly = 'true';
                    $statusMsg = 'Das Ändern ist nicht möglich, da mehr als ein Bestandsfahrzeug an einem LP! &#10;Bitte Ladupunkt erstmal freigeben.';
                    $lp_not_empty = 'lp_not_empty';
                    $enablecheckbox = 'enablecheckbox';
                } else {
                    $readonly = 'true';
                    $statusMsg = '';
                    $lp_not_empty = '';
                    $enablecheckbox = '';
                }
                // replacement_vehicles
                if ($vehicle['replacement_vehicles'] == 't') {
                    // $replacement_status = true;
                    $checked = 'checked';
                    $data_ersatz = 1;
                } else {
                    // $replacement_status = false;
                    $checked = '';
                    $data_ersatz = 0;
                }
                /*
                 * $replacement_vehicles = <<<HEREDOC
                 * <input type="checkbox" class="replacement_checkbox $lp_not_empty" name="replacement_{$vehicle['vehicle_id']}" id="replacement_{$vehicle['vehicle_id']}" value="t" title="$statusMsg" onclick="return $readonly;" $checked>
                 * HEREDOC;
                 */
                $replacement_vehicles = <<<HEREDOC
                    	<input type="checkbox" class="replacement_checkbox $lp_not_empty" name="replacement_{$vehicle['vehicle_id']}" id="replacement_{$vehicle['vehicle_id']}" value="t" title="$statusMsg" onclick="return $readonly;" $checked>
HEREDOC;

                $group->addStatic(null, array(
                    'class' => $classname
                ))
                    ->setContent($replacement_vehicles)
                    ->setTagName('span');

                $group->addStatic(null, array(
                    'class' => $classname
                ))
                    ->setContent($vehicle_phase)
                    ->setTagName('span');
                $group->addStatic(null, array(
                    'class' => $classname
                ))
                    ->setContent($vehicletype)
                    ->setTagName('span');

                if (! in_array($vehicle['depot_id'], array_keys($depots)))
                    $depots = [
                        $vehicle['depot_id'] => $vehicle['dname']
                    ] + $depots;

                if (! in_array($vehicle['depot_id'], array_keys($depotforfreestations)))
                    $depotforfreestations = [
                        $vehicle['depot_id'] => $vehicle['dname']
                    ] + $depotforfreestations;

                if ($vehicle['replacement_vehicles'] != 't') { // ersatz fahrzeuge und alle ZSP
                    $options_depots = "";
                    foreach ($depotforfreestations as $depot_id => $depotName)
                        $options_depots .= sprintf('<option value="%s"%s>%s</option>', $depot_id, ($depot_id == $vehicle['depot_id'] ? ' selected' : ''), $depotName);
                } else // kein ersatzfahrzeuge nur ZSPn mit ladesäulen
                {
                    $options_depots = "";
                    foreach ($depots as $depot_id => $depotName)
                        $options_depots .= sprintf('<option value="%s"%s>%s</option>', $depot_id, ($depot_id == $vehicle['depot_id'] ? ' selected' : ''), $depotName);
                }
                $select_depotsAnd_divisions = <<<HEREDOC
                    <select name="depotv_{$vehicle['vehicle_id']}" id="depotv_{$vehicle['vehicle_id']}" class="depot_for_vehicle" data-ersatz="{$data_ersatz}" data-depotid="{$vehicle['depot_id']}" data-vehicleid="{$vehicle['vehicle_id']}" data-stationid="{$vehicle['station_id']}">$options_depots</select><br>
HEREDOC;

                if ($options_divisions)
                    $select_depotsAnd_divisions .= <<<HEREDOC
                    <select name="depoto_{$vehicle['vehicle_id']}" id="depoto_{$vehicle['vehicle_id']}" class="depot_abgabe" data-vehicleid="{$vehicle['vehicle_id']}">$options_divisions</select>
HEREDOC;

                $group->addElement('staticNoLabel', null, null, [
                    'content' => $select_depotsAnd_divisions
                ]);

                /*
                 * $group->addSelect('depotv_'.$vehicle['vehicle_id'],
                 * array(
                 * 'data-depotid'=>$vehicle['depot_id'],
                 * 'data-vehicleid'=>$vehicle['vehicle_id'],
                 * 'data-stationid'=>$vehicle['station_id'],
                 * 'class'=>'__depot_for_vehicle',
                 * 'id'=>'depotv_'.$vehicle['vehicle_id']),array('options'=>$depots))
                 *
                 * ->setValue($vehicle['depot_id']); //continue here
                 */

                // if($vehicle['station_id'])
                // Restriction list for vehicles and replacement_vehicles
                if ($vehicle['station_id'] || $vehicle['replacement_vehicles'] == 't')
                    $stations = $allstations;
                else
                    $stations = $freestations;

                if (empty($stations))
                    $group->addSelect('stationv_' . $vehicle['vehicle_id'], array(
                        'data-allowedstationtype' => $checkphrase,
                        'data-vehicleid' => $vehicle['vehicle_id'],
                        'class' => 'station_for_vehicle',
                        'id' => 'stationv_' . $vehicle['vehicle_id']
                    ), array(
                        'options' => array(
                            'null' => 'Kein Ladepunkt (sehr niedrige Ladeleistung)'
                        )
                    ));

                // display Restriction list for replacement vehicles
                else if ($vehicle['replacement_vehicles'] == 't')
                    $group->addSelect('stationv_' . $vehicle['vehicle_id'], array(
                        'data-allowedstationtype' => $checkphrase,
                        'data-vehicleid' => $vehicle['vehicle_id'],
                        'class' => "station_for_replace_vehicle $enablecheckbox",
                        'id' => 'stationv_' . $vehicle['vehicle_id']
                    ), array(
                        'options' => array(
                            'null' => 'Kein Ladepunkt (sehr niedrige Ladeleistung)'
                        ) + $stations
                    ))->setValue($vehicle['station_id']);

                else
                    $group->addSelect('stationv_' . $vehicle['vehicle_id'], array(
                        'data-allowedstationtype' => $checkphrase,
                        'data-vehicleid' => $vehicle['vehicle_id'],
                        'class' => 'station_for_vehicle',
                        'id' => 'stationv_' . $vehicle['vehicle_id']
                    ), array(
                        'options' => array(
                            'null' => 'Kein Ladepunkt (sehr niedrige Ladeleistung)'
                        ) + $stations
                    ))->setValue($vehicle['station_id']);
                // + operation of arrays combines both the arrays without actually changing the existing keys of the arrays
            }
            $fs->addElement('staticNoLabel', null, null, array(
                'content' => '</table>'
            ));
            $fs->addHidden('vehicles')->setValue(implode(',', array_column($vehicles, 'vehicle_id')));
            $fs->addHidden('action')->setValue('fahrzeugverwaltung');
            $fs->addHidden('subaction')->setValue('saveVehiclesStations');
            if (is_numeric($zsp))
                $fs->addHidden('zsp')->setValue($zsp);

            $fs->addStatic()->setContent('* Sortiert nach Ladepunkte Name<br>');
            $fs->addStatic()->setContent('+ Sts Pool = Bundesweiter StreetScooter Pool<br>+ Unbekannt = Standort unbekannt');
            $fs->addStatic(null)->setContent('<strong>Legende</strong><br><span class="threephase_vehicle">3-phasige Fahrzeuge</span><br><span class="single_vehicle">1-phasige Fahrzeuge</span><br><br><span class="threephase_vehicle">3-phasige Ladepunkte</span><br><span class="single_vehicle">1-phasige Ladepunkte</span>');
            $fs->addSubmit('savevehiclemgmt', array(
                'id' => 'saveVehMgmt',
                'value' => 'Speichern'
            ));
        }

    }


    function zentraleexportcsv($vcols, $delimiters = null)
    {

        $fs = $this->form->addElement('fieldset');

        $fs->addElement('checkbox', 'filteroptions[depot_zero]', null)->setContent('Noch nicht ausgelieferte Fahrzeuge miteinbeziehen');
        $fs->addElement('checkbox', 'filteroptions[depot_foreign]', null)->setContent('Fahrzeuge im Ausland miteinbeziehen');
        $fs->addElement('checkbox', 'filteroptions[depot_third]', null)->setContent('Drittkundenfahrzeuge miteinbeziehen');

        if ($delimiters) {
            $group = $fs->addGroup();
            $group->addElement('staticNoLabel')->setContent('Trennzeichen ');
            $group->addSelect('delimiter', [
                'style' => 'width: 120px'
            ], [
                'options' => $delimiters
            ]);
        }

        $cnt = 0;
        foreach ($vcols as $key => $vcol) {
            if (! $vcol)
                continue;
            $fsSelect = $fs->addFieldset(null, array(
                'class' => 'columns one inline_elements',
                'style' => 'margin-left: 0'
            ))->addGroup();
            $fsSelect->addStatic()->setContent('Spalte ' . ($cnt + 1) . ' <br>');
            $fsSelect->addSelect('vcol[' . $cnt . ']', array(
                'class' => ''
            ), array(
                'options' => $vcols
            ))->setValue($key);
            $cnt ++;
        }
        $fs = $this->form->addElement('fieldset');

        $fs->addHidden('action')->setValue('exportcsv');
        $fs->addSubmit('saveexportcsv', array(
            'value' => 'Exportieren'
        ));

    }


    /**
     * depotSelectFPV
     *
     * @param array $depots
     *            list of depots in the format of depot_id as key and depot name as label
     * @param integer $defaultVal
     *            the default depot passed through a GET variable
     */
    public function depotSelectFPV($depots, $defaultVal = null)
    {

        $fsSelect = $this->form->addElement('fieldset');
        $fsSelect->addSelect('zsp', array(
            'class' => 'zsp_selector_fpv'
        ), array(
            'options' => $depots
        ))->setValue($defaultVal);

    }


    /**
     * depotSelectFPS
     *
     * @param array $depots
     *            list of depots in the format of depot_id as key and depot name as label
     * @param integer $defaultVal
     *            the default depot passed through a GET variable
     */
    public function depotSelectFPS($depots, $defaultVal = null, $classname)
    {

        $fsSelect = $this->form->addElement('fieldset');
        $fsSelect->addSelect('zsp', array(
            'class' => $classname
        ), array(
            'options' => $depots
        ))->setValue($defaultVal);

    }


    /**
     * depotSelect
     *
     * @param array $depots
     *            list of depots in the format of depot_id as key and depot name as label
     * @param integer $defaultVal
     *            the default depot passed through a GET variable
     */
    public function depotSelect($depots, $defaultVal = null, $classname = null)
    {

        $fsSelect = $this->form->addElement('fieldset');
        $fsSelect->addSelect('zsp', array(
            'class' => $classname
        ), array(
            'options' => $depots
        ))->setValue($defaultVal);

    }


    /**
     * vehicleSelect
     *
     * @param array $vehicles
     */
    public function vehicleSelect($vehicles, $defaultVal = null, $classname = null)
    {

        $fsSelect = $this->form->addElement('fieldset');
        $group = $this->form->addGroup();
        $group->addSelect('vehicle', array(
            'class' => $classname
        ), array(
            'options' => $vehicles
        ))->setValue($defaultVal);
        $group->addStatic(null, array(
            'class' => 'salesSelectVehicle',
            'href' => '#',
            'style' => "margin-left: 40px"
        ))
            ->setContent('<span class="genericon genericon-plus"></span><span>wählen</span>')
            ->setTagName('a');

    }


    /**
     * genSelect Adds a Select
     *
     * @param array $options
     */
    public function genSelect($selectName, $attributes = null, $options, $selectLabel, $defaultVal = null, $hiddenVars = null)
    {

        $fsSelect = $this->form->addElement('fieldset');
        if (! isset($attributes['id']))
            $attributes['id'] = $selectName;
        $newSelect = $fsSelect->addSelect($selectName, $attributes, array(
            'options' => $options
        ))->setLabel($selectLabel);
        if ($defaultVal !== null)
            $newSelect->setValue($defaultVal);

        if (is_array($hiddenVars)) {
            foreach ($hiddenVars as $key => $value)
                $fsSelect->addHidden($key)->setValue($value);
        }

    }


    /**
     * getRestrictionEditLimited Adds a Select
     *
     * @param array $options
     */
    public function getRestrictionEdit($currentRestriction, $allowedParentIds) // @todo do we need to pass attributes?
    {

        $fsRes = $this->form;

        $pid = $currentRestriction['parent_restriction_id'];

        // if(empty($pid))
        // {
        // $pname='Keine';
        // }
        // else $pname=$allowedParentIds[$pid];

        $id = $currentRestriction['restriction_id'];

        // $fsRes->addStatic(null,null,array('content'=>"\r\n<tr><td>"));
        $groupone = $fsRes->addGroup('grp_' . $id, array(
            'id' => "grp_" . $id
        ))->setSeparator("&nbsp;");

        $groupone->addHidden('restriction_id')->setValue($id);

        // if(!empty($pid)) ensures that top most parent is not shown
        if (! empty($pid)) {
            $groupone->addStatic(null, array(
                'class' => 'form_res_id'
            ))
                ->setContent($id)
                ->setTagName('span');

            if (preg_match('/(Phase )[1-3]{1}/', $currentRestriction['name'])) {
                $groupone->addStatic(null, array(
                    'class' => 'form_res_name'
                ))
                    ->setContent($currentRestriction['name'])
                    ->setTagName('span');
                $groupone->addHidden('name')->setValue($currentRestriction['name']);
            } else
                $groupone->addText('name', array(
                    'class' => 'form_res_name'
                ))->setValue($currentRestriction['name']);
        }

        $selectName = 'parent_restriction_id';
        if (! isset($attributes['id']))
            $attributes['id'] = $selectName;
        $attributes['class'] = 'form_res_parent';

        $options = $allowedParentIds;
        // $options['null']='Keine';

        // $groupone->addStatic(null,array('class'=>'form_res_name'))->setContent($pname)->setTagName('span'); @removing obergruppe display

        if (! preg_match('/(TempDepotRes.*)|(Phase [1-3]{1})/', $currentRestriction['name']) && ! empty($pid)) {
            $newSelect = $groupone->addSelect($selectName, $attributes, array(
                'options' => $options
            ))
                ->setLabel('Select Parents')
                ->setValue($currentRestriction['parent_restriction_id']);
            // $newSelect->addRule('required','Obergruppe muss gewählt werden',HTML_QuickForm2_Rule::CLIENT_SERVER);
        } else {
            $groupone->addHidden($selectName)->setValue($pid);
            $groupone->addStatic(null, array(
                'class' => 'form_res_parent'
            ))
                ->setContent('')
                ->setTagName('span');
        }

        $power = $currentRestriction['power'] / 215.0;
        if (! empty($allowedParentIds)) {
            $groupone->addElement('number', 'power', array(
                'step' => 1,
                'min' => 0,
                'max' => 9000000,
                'class' => 'form_res_power'
            ))->setValue($power);
        } else {
            $groupone->addHidden('power')->setValue($power);
            // $groupone->addStatic('power',array('class'=>'form_res_power'))->setContent(str_replace('.',',',$power))->setTagName('span');
        }
        if (! preg_match('/(TempDepotRes.*)|(Phase [1-3]{1})/', $currentRestriction['name']) && ! empty($pid))
            $groupone->addStatic('delete', array(
                'class' => 'del_restriction'
            ))
                ->setContent('<span class="genericon genericon-close"></span><span >Löschen</span>')
                ->setTagName('a');

        // $fsRes->addStatic(null,null,array('content'=>"</td></tr>\r\n"));
    }


    /**
     * addNewRestriction Adds a line for new restriction
     *
     * @param array $options
     */
    public function addNewRestriction($depot, $allowedParentIds) // @todo $depot is not being used!
    {

        $fsRes = $this->form;
        $fsRes->addStatic(null)
            ->setContent('Neue Ladegruppe (Unterverteilung) anlegen')
            ->setTagName('span');
        $id = 'new';
        $groupone = $fsRes->addGroup('grp_' . $id, array(
            'id' => "grp_" . $id
        ))->setSeparator("&nbsp;");

        $groupone->addStatic(null, array(
            'class' => 'form_res_id'
        ))
            ->setContent('Neue')
            ->setTagName('span');
        $groupone->addText('name', array(
            'class' => 'form_res_name'
        ))->setValue('');
        $selectName = 'parent_restriction_id';
        if (! isset($attributes['id']))
            $attributes['id'] = $selectName;

        $attributes['class'] = 'form_res_parent';

        $options = $allowedParentIds;

        $newSelect = $groupone->addSelect($selectName, $attributes, array(
            'options' => $options
        ))
            ->setLabel('Select Parents')
            ->setValue('null');
        // $newSelect->addRule('required','Obergruppe muss gewählt werden',null,HTML_QuickForm2_Rule::CLIENT_SERVER);
        $power = 16;
        $groupone->addElement('number', 'power', array(
            'step' => 1,
            'min' => 0,
            'max' => 9000000,
            'class' => 'form_res_power'
        ))->setValue($power);

    }


    public function autoGenStations()
    {

        $fsautogen = $this->form->addElement('fieldset')->setLabel('Ladepunkte automatisch generieren');
        $groupone = $fsautogen->addGroup()->setSeparator("&nbsp;");
        $groupone->addElement('number', 'autogencnt', array(
            'step' => 1,
            'min' => 1,
            'class' => 'form_auto_gen_cnt'
        ))->setValue(1);
        $groupone->addElement('hidden', 'action')->setValue("saveAutoGen");
        $groupone->addSubmit('autogen_ctrl', array(
            'id' => 'autogen_ctrl',
            'value' => 'Generieren'
        ));

    }


    /**
     * getStationEdit
     *
     * @param array $options
     */
    public function getStationEdit($currentStation, $options, $userrole)
    {

        $fsRes = $this->form;
        $id = $currentStation['station_id'];
        $groupone = $fsRes->addGroup('stn_' . $id, array(
            'id' => "stn_" . $id
        ))->setSeparator("&nbsp;");
        $groupone->addStatic(null, array(
            'class' => 'form_res_id'
        ))
            ->setContent($id)
            ->setTagName('span');
        $groupone->addHidden('station_id')->setValue($id);
        $groupone->addHidden('depot_id')->setValue($currentStation["depot_id"]);

        $stationname = $groupone->addText('name', array(
            'class' => 'form_res_name'
        ))->setValue($currentStation['name']);

        if ($userrole == 'chrginfra_ebg') {
            $stationname->addRule('minlength', 'Ladepunkte Name ist nicht gultig.', 7, HTML_QuickForm2_Rule::CLIENT_SERVER)->and_($stationname->createRule('callback', 'Ladepunkte Name ist nicht gultig', 'check_stationame'));

            $js_script_check_stationame = "
				function check_stationame(stationnameval)
				{
				flag=true;

				var re = /^([0-9]){7}([rRlL])?$/;

					if(!re.exec(stationnameval))
						{
							flag=false;
						}

				 return flag;
				}";

            $fsRes->addScript('check_stationame_script', null, array(
                'content' => $js_script_check_stationame
            ));
        }

        $selectName = 'restriction_id';
        if (! isset($attributes['id']))
            $attributes['id'] = $selectName;
        $attributes['class'] = 'form_res_parent';
        $options['null'] = 'Keine';
        if (empty($currentStation['restriction_id']))
            $currentStation['restriction_id'] = 'null';
        $newSelect = $groupone->addSelect($selectName, $attributes, array(
            'options' => $options
        ))
            ->setLabel('')
            ->setValue($currentStation['restriction_id']);

        $selectName = 'restriction_id2';
        if (! isset($attributes['id']))
            $attributes['id'] = $selectName;
        $attributes['class'] = 'form_res_parent';

        if (empty($currentStation['restriction_id2']))
            $currentStation['restriction_id2'] = 'null';

        $newSelect = $groupone->addSelect($selectName, $attributes, array(
            'options' => $options
        ))
            ->setLabel('')
            ->setValue($currentStation['restriction_id2']);

        $selectName = 'restriction_id3';
        if (! isset($attributes['id']))
            $attributes['id'] = $selectName;
        $attributes['class'] = 'form_res_parent';

        if (empty($currentStation['restriction_id3']))
            $currentStation['restriction_id3'] = 'null';

        $newSelect = $groupone->addSelect($selectName, $attributes, array(
            'options' => $options
        ))
            ->setLabel('')
            ->setValue($currentStation['restriction_id3']);

        $station_power = $currentStation['station_power'] / 215.0;

        $params = array();
        $params['style'] = 'width: 8%';
        if ($currentStation['deactivate'] == 't')
            $params['checked'] = 'checked';

        $params['data-vehicleid'] = $currentStation['vehicle_id'];
        $params['class'] = 'deactivate_station';

        $groupone->addElement('checkbox', 'deactivate', $params);
        $groupone->addElement('number', 'station_power', array(
            'step' => 1,
            'min' => 0,
            'max' => 9000000,
            'class' => 'form_res_power',
            'style' => 'width: 8%'
        ))->setValue($station_power);

        $groupone->addStatic('delete', array(
            'class' => 'del_station'
        ))
            ->setContent('<span class="genericon genericon-close"></span><span >Löschen</span>')
            ->setTagName('a');

    }


    /**
     * addNewStation Adds a new line to add a new station
     *
     * @param array $options
     */
    public function addNewStation($options, $userrole)
    {

        $fsRes = $this->form;
        $id = "new";
        $groupone = $fsRes->addGroup('stn_' . $id, array(
            'id' => "stn_" . $id
        ))->setSeparator("&nbsp;");
        $groupone->addStatic(null, array(
            'class' => 'form_res_id'
        ))
            ->setContent("Neue")
            ->setTagName('span');

        $stationname = $groupone->addText('name', array(
            'class' => 'form_res_name'
        ))->setValue("");

        if ($userrole == 'chrginfra_ebg') {
            $stationname->addRule('empty', 'empty..', null, HTML_QuickForm2_Rule::CLIENT_SERVER)->or_($stationname->createRule('callback', 'Ladepunkte Name ist nicht gultig', 'check_stationame'));
            $js_script_check_stationame = "
				function check_stationame(stationnameval)
				{

				flag=true;
				var re = /^([0-9]){7}([rRlL])?$/;

					if(!re.exec(stationnameval))
						{
							flag=false;
						}

				 return flag;
				}";

            $fsRes->addScript('check_stationame_script', null, array(
                'content' => $js_script_check_stationame
            ));
        }
        $attributes['id'] = $selectName = 'restriction_id';
        $attributes['class'] = 'form_res_parent';
        $options["null"] = 'Keine';
        $newSelect = $groupone->addSelect($selectName, $attributes, array(
            'options' => $options
        ))
            ->setLabel('')
            ->setValue("null");

        $attributes['id'] = $selectName = 'restriction_id2';
        $attributes['class'] = 'form_res_parent';
        $options["null"] = 'Keine';
        $newSelect = $groupone->addSelect($selectName, $attributes, array(
            'options' => $options
        ))
            ->setLabel('')
            ->setValue("null");

        $attributes['id'] = $selectName = 'restriction_id3';
        $attributes['class'] = 'form_res_parent';
        $options["null"] = 'Keine';
        $newSelect = $groupone->addSelect($selectName, $attributes, array(
            'options' => $options
        ))
            ->setLabel('')
            ->setValue("null");

        $params = array(
            'style' => 'width: 8%'
        );
        $groupone->addElement('checkbox', 'deactivate', $params)->setContent('');
        $groupone->addElement('number', 'station_power', array(
            'step' => 1,
            'min' => 0,
            'max' => 9000000,
            'class' => 'form_res_power',
            'style' => 'width: 8%'
        ))->setValue(16);
        $groupone->addStatic('delete', array(
            'class' => 'del_station'
        ))
            ->setContent('<span class="genericon genericon-close"></span><span >Löschen</span>')
            ->setTagName('a');

    }


    /**
     *
     * @todo not needed function!
     *      
     *       getVehicleAdd Adds the fahrzeug Add form
     * @param
     */
    public function getVehicleAdd()
    {



        function check_akz($akzval)
        {

            // $pattern = "/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/";
            $pattern = "/^[^0-9]$/";

            $emails_list = explode("\n", $emails);

            foreach ($emails_list as $thisemail) {

                if (! preg_match($pattern, trim($thisemail))) {
                    echo $thisemail . " ist nicht gültig";
                    return false;
                }
            }
            return true;

        }

        $fsRes = $this->form;
        $groupone = $fsRes->addGroup('', array(
            'id' => "fzadd"
        ))->setSeparator("&nbsp;");
        $groupone->addElement('number', 'cntvehicles', array(
            'step' => 1,
            'min' => 1,
            'max' => 9999,
            'class' => 'form_res_name'
        ))->setValue(1);
        $startikz = $groupone->addElement('text', 'startikz', array(
            'class' => 'form_res_parent',
            'placeholder' => '9999999',
            'maxlength' => 7
        ));
        $startikz->addRule('minlength', 'Start IKZ muss eine 7 stellige Nummer sein.', 7, HTML_QuickForm2_Rule::CLIENT_SERVER);
        $startikz->addRule('required', 'Start IKZ muss eine 7 stellige Nummer sein.', null, HTML_QuickForm2_Rule::CLIENT_SERVER);
        $startakz = $groupone->addElement('text', 'startakz', array(
            'class' => 'form_res_parent',
            'placeholder' => 'BN-PX-yyyy',
            'maxlength' => 10
        ))->setValue('BN-PX-yyyy');
        $startakz->addRule('minlength', 'Start AKZ ist nicht gültig.', 9, HTML_QuickForm2_Rule::CLIENT)->and_($startakz->createRule('callback', 'Start AKZ ist nicht gültig.', 'check_akz'));
        $js_script_checkakz = "
				function check_akz(akzval)
				{
				flag=true;

				var re = /(BN-P)[A-Z]{1}-[0-9]{4}/;

					if(!re.exec(akzval))
						{
							flag=false;
						}

				re = /(BN-P)[0-9]{4}E/;

					if(!re.exec(akzval))
						{
							flag=false;
						}
					else
						flag=true;

				 return flag;
				}";

        $fsRes->addScript('check_akz_script', null, array(
            'content' => $js_script_checkakz
        ));

        $groupone->addElement('hidden', 'action')->setValue("ikzakzsave");
        $fsRes->addElement('submit', 'fahrzeug_add_generieren', array(
            'id' => 'fahrzeug_add',
            'value' => 'Generieren'
        ));

    }


    /**
     * getVehicleAdd Adds the fahrzeug Add form
     *
     * @param
     */
    public function salesGetVehicleAdd_Step1($vehicle_variants = null, $allownoikz = FALSE, $allownoakz = FALSE, $wc_variants = null, $wc_subconfig = null, $thirdparty = false)
    {



        function check_akz($akzval)
        {

            // $pattern = "/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/";
            $pattern = "/^[^0-9]$/";

            $emails_list = explode("\n", $emails);

            foreach ($emails_list as $thisemail) {

                if (! preg_match($pattern, trim($thisemail))) {
                    echo $thisemail . " ist nicht gültig";
                    return false;
                }
            }
            return true;

        }

        $fsRes = $this->form->addFieldset();
        $fsRes->addElement('staticNoLabel')->setContent('<table>');

        $group = $fsRes->addGroup('', array(
            'id' => 'tableit'
        )); // start row
           // </th><th></th><th>Anzahl Fahrzeuge</th><th>';

        // $group->addElement('staticNoLabel')->setContent('Vin Verfahren'); // automatic <td> open and close
        $group->addElement('staticNoLabel')->setContent('Fahrzeugkonfiguration auswählen');
        $group->addElement('staticNoLabel')->setContent('Fahrzeug-subkonfiguration auswählen');
        $group->addElement('staticNoLabel')->setContent('Anzahl der Fahrzeuge');

        if ($allownoikz === FALSE)
            $group->addElement('staticNoLabel')->setContent('*Start IKZ ');

        if ($allownoakz === FALSE)
            $group->addElement('staticNoLabel')->setContent('Start AKZ');

        if ($allownoikz === FALSE) {
            $group->addElement('staticNoLabel')->setContent('Start TS Nummer'); // automatic <td> open and close
            $group->addElement('staticNoLabel')->setContent('Vorhaben Nummer'); // automatic <td> open and close
        }

        $group->addElement('staticNoLabel')->setContent('Start Penta Kennwort'); // automatic <td> open and close

        $group = $fsRes->addGroup('', array(
            'id' => 'tableit'
        )); // start new row
           // $group->addSelect('vehicle_vin_method',array(),array('options'=>$vin_methods));

        /**
         * remove ability to select the vin method
         */
        // $vin_method = array_keys($vin_methods)[0];
        // $fsRes->addElement('hidden','vehicle_vin_method')->setValue($vin_method);
        // $group->addElement('staticNoLabel')->setContent($vin_method);

        $group->addSelect('vehicle_variant_wc', array(), array(
            'options' => $wc_variants
        ));
        $group->addSelect('vehicle_subconfiguration_wc', array(), array(
            'options' => $wc_subconfig
        ));
        $group->addElement('number', 'cntvehicles', array(
            'step' => 1,
            'min' => 1,
            'max' => 9999,
            'class' => ''
        ))->setValue(1);

        if ($allownoikz === FALSE) {
            $startikz = $group->addElement('text', 'startikz', array(
                'class' => '',
                'placeholder' => '9999999',
                'value' => '3242423',
                'maxlength' => 7
            ));
            $startikz->addRule('minlength', 'Start IKZ muss eine 7 stellige Nummer sein.', 7, HTML_QuickForm2_Rule::CLIENT_SERVER);
            $startikz->addRule('required', 'Start IKZ muss eine 7 stellige Nummer sein.', null, HTML_QuickForm2_Rule::CLIENT_SERVER);
        }

        if ($allownoakz === FALSE) {
            $startakz = $group->addElement('text', 'startakz', array(
                'class' => '',
                'placeholder' => 'BN-PX-yyyy',
                'maxlength' => 10
            ))->setValue('BN-PZ-1111');
            $startakz->addRule('minlength', 'Start AKZ ist nicht gültig.', 9, HTML_QuickForm2_Rule::CLIENT)->and_($startakz->createRule('callback', 'Start AKZ ist nicht gültig.', 'check_akz'));

            // todo: wenn "BD-P" fest vorgegeben, worum dann eingeben? waere eine Eingabe nur über 1 Zeichen und Nummer nicht besser?: BN-P[X yyyy] ([...]= Eingabefeld)
            $js_script_checkakz = "
                    function check_akz(akzval)
                    {
                        flag=true;

                    	if(akzval.length==10)
                    	{
                    		var re = /(BN-P)[a-zA-Z][- ][0-9]{4}/;

                    		if(!re.exec(akzval))
                    		{
                    			flag=false;
                    		}

                    	}
                    	else if(akzval.length==9)
                    	{
                    	    re = /(BN-P)[0-9]{4}(E)/;

                    	    if(!re.exec(akzval))
                    		{
                    			flag=false;
                    		}

                    	}

                        return flag;
                    }";

            $fsRes->addScript('check_akz_script', null, array(
                'content' => $js_script_checkakz
            ));
        }

        if ($allownoikz === FALSE) {
            $group->addElement('text', 'tsnummer');
            $group->addElement('text', 'vorhaben');
        }
        $group->addElement('text', 'start_penta_kennwort');
        $fsRes->addElement('staticNoLabel')->setContent('</table >');

        $fsRes->addElement('hidden', 'action')->setValue("newvehicles");
        $fsRes->addElement('hidden', 'thirdparty')->setValue($thirdparty);
        $fsRes->addElement('hidden', 'vehicle_variant_config');
        $fsRes->addElement('hidden', 'vehicle_subconfiguraion');

        $fsRes->addElement('submit', 'fahrzeug_add_step2', array(
            'id' => 'fahrzeug_add_step2',
            'value' => 'Weiter >>'
        ));

        // $fsRes->addStatic()->setContent('<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>');
    }


    protected function GetPartListOptions($group, $parts, $show_all, $selected_options, $colDefault)
    {

        $group_id = $group['group_id'];
        $css_class = $show_all ? '"groupPartsAll"' : '"groupParts"';

        $default_wc = 0;
        $default_gr = 0;

        foreach ($parts as $part_id => $part_set) {
            if (isset($selected_options[$part_id]))
                $default_wc = $part_id;
            if (toBool($part_set[$colDefault]))
                $default_gr = $part_id;
        }

        $html_result = <<<HEREDOC
<input type="hidden" name="group_preset[$group_id]" value="$default_wc"><select class=$css_class name="group_value[$group_id]">
HEREDOC;

        if (toBool($group['allow_none']))
            $html_result .= '<option value="0">---</option>';

        $selected_id = 0;
        $selected_gr = 0;
        foreach ($parts as $part_id => $part_set) {
            if (($part_set['visible_sales']) || $show_all) {
                if ($part_id == $default_wc) {
                    $selected_id = $part_id;
                    $selected_gr = 0;
                    break;
                }

                if ($part_id == $default_gr) {
                    $selected_gr = $part_id;
                }
            }
        }

        foreach ($parts as $part_id => $part_set) {
            if (($part_set['visible_sales']) || $show_all) {
                $caption = $part_set['name'];
                $selected = "";

                if ($part_id == $selected_id) {
                    $part_id .= '*';
                    $caption .= ' *';
                    $selected = ' selected';
                } else if ($part_id == $selected_gr) {
                    $selected = ' selected';
                }
                $html_result .= "
            <option value=\"$part_id\"$selected>{$caption}</option>";
            }
        }

        $html_result .= "
      </select>
";
        return $html_result;

    }


    protected function GetPartGroupFormControl(&$group_def, $variant_options, $thirdparty)
    {

        $html_result = "";

        $group = &$group_def['group'];
        $parts = &$group_def['parts'];
        $colDefault = $thirdparty ? 'is_default' : 'is_default_dp';
        $default = 0;

        $classVisGroup = $group['group_hidden'] ? ' class="hiddenPartsGroup"' : "";

        if (toBool($group['allow_multi'])) {
            if (! empty($group['group_name'])) {
                $html_result = "
    <tr$classVisGroup>
      <th colspan=\"2\">{$group['group_name']}</th>
    </tr>";
            }

            foreach ($parts as $part_id => $part_set) {

                $classVisPart = toBool($part_set['visible_sales']) ? '' : ' class="hiddenPart"';
                $classVis = ($classVisGroup != "") ? $classVisGroup : $classVisPart;
                if (isset($variant_options[$part_id])) {
                    $variantFixed = " *";
                    $def_checked = " checked disabled";
                } else {
                    $variantFixed = "";
                    $def_checked = toBool($part_set[$colDefault]) ? " checked" : "";
                }

                $html_result .= "
    <tr$classVis>
      <td>{$part_set['name']}{$variantFixed}</td>
      <td><input type=\"checkbox\" name=\"vehicle_options[$part_id]\"{$def_checked}></td>
    </tr>";
            }
        } else {
            $variant_set_part = "";

            $selectControl = $this->GetPartListOptions($group, $parts, false, $variant_options, $colDefault);
            $hiddenControl = ""; // $this->GetPartListOptions ($group, $parts, true, $variant_options, $colDefault);

            $html_result .= "
     <tr$classVisGroup>
        <td>{$group['group_name']}</td>
        <td>{$selectControl}{$hiddenControl}
      </td>
    </tr>";
        }
        return $html_result;

    }


    public function salesGetVehicleAdd_Step2($thirdparty, $info_from_step1, $colors, $variant_set, $vehicle_vin_method, $herstellungwerke, $variant_options, $part_list, $part_groups, $default_battery, $group_battery)
    {

        $fsRes = $this->form->addFieldset();

        $fsRes->addStatic()->setContent($info_from_step1);

        if (! empty($vehicle_vin_method) && $vehicle_vin_method == 'ext_import') {
            $fsRes->addStatic()->setContent('
<div class="seitenteiler" style="border: 2px solid #888;padding-left: 20px; height: 170px;">
  <h2>VIN Datei hochladen</h2>
  <input type="file" name="csvfilevins" id="csvfilevins-0">
  <div class="row">
    Beispiel<br>
    WS5E17AAAAA100001<br>
    WS5E17AAAAA100002<br>
    WS5E17AAAAA100003<br>
  </div>
</div>
<div class="seitenteiler" style="margin-left: 20px;">
  <textarea name="vinliste" style="font-family: monospace; width:200px; margin: 0; height:170px; border: 2px solid #888; resize:none; overflow: scroll;" placeholder="VIN-Liste mit COPY/PASTE hier einfügen"></textarea>
</div>
<div class="seitenteiler" style="border: 2px solid #888;padding: 0 20px; margin-left: -5px; height:170px; width: 160px;">
  <h2>Copy/Paste</h2>
  Liste der VIN\' kann auch per Copy&Paste in das Textfeld eingefügt werden. Anschließend auf <b>[Generieren]</b> klicken.
</div>
');
        }

        $html_options = '  <table class="salesNewVehicleParts"><tr><th colspan="2" style="text-align:center;"><strong>Weitere Informationen</strong></th></tr>' . "
		    <tr><td>Herstellungswerk</td><td><select name=\"herstellungswerk\">";
        foreach ($herstellungwerke as $depot_id => $name) {
            $html_options .= '<option value="';
            if ($depot_id[0] == '*')
                $html_options .= substr($depot_id, 1) . '" selected>';
            else
                $html_options .= $depot_id . '">';
            $html_options .= "$name</option>";
        }
        $html_options .= "</select></td></tr></table>";
        
/*		    <tr><td>Farbe wählen</td><td><select name=\"vehicle_color\">";

        foreach ($colors as $color_id => $color_set) {
            $html_options .= "<option value=\"$color_id\"";
            if ($color_id == $variant_set['default_color'])
                $html_options .= " selected";
            $html_options .= ">{$color_set['name']}</option>";
        }
        $html_options .= "</select></td></tr>\n";

        if (substr($variant_set['windchill_variant_name'], 0, 3) != 'E17') {
            if (! empty($part_groups)) {
                foreach ($part_groups as $group_name => $group_def) {
                    $html_options .= $this->GetPartGroupFormControl($group_def, $variant_options, $thirdparty);
                }
            }

            $html_options .= '</td></tr></table>
<div>Die mit * gekennzeichneten Bauteile/Komponenten sind von Windchill fest vorgegeben.</div>
';
        } else {
            $html_options .= "</table>\n";
        }*/
        // display Fahrzeug Austatung
        $fsRes->addStatic()->setContent($html_options);

        $fsRes->addElement('hidden', 'action')->setValue("newvehicles");
        $fsRes->addElement('hidden', 'thirdparty')->setValue($thirdparty);

        $html_submit = '<input type="submit" name="fahrzeug_add_step1" id="fahrzeug_add_step1" style="width:200px;" value="<< Zurück">' . '<input type="submit" name="fahrzeug_add_generieren" id="fahrzeug_add_generieren" style="width:200px;" value = "Generieren">';

        $fsRes->addStatic()->setContent($html_submit);

    }


    /**
     * uploadVehicles Adds the fahrzeug Add form
     *
     * @param
     */
    public function uploadVehicles($startikz, $startakz, $cntvehicles)
    {

        $fsRes = $this->form;
        $groupone = $fsRes->addGroup('', array(
            'id' => "fzadd"
        ))->setSeparator("&nbsp;");
        $groupone->addElement('hidden', 'page')->setValue("ikzakzsave");
        $groupone->addElement('hidden', 'startikz')->setValue($startikz);
        $groupone->addElement('hidden', 'startakz')->setValue($startakz);
        $groupone->addElement('hidden', 'cntvehicles')->setValue($cntvehicles);
        $groupone->addElement('submit', 'ikzakzupload', array(
            'id' => 'ikzakzupload',
            'value' => 'Hochladen'
        ));
        $groupone->addStatic(null, array(
            'class' => 'sts_back',
            'href' => '#',
            'onClick' => 'window.close()'
        ), array(
            'content' => 'Zurück',
            'tagName' => 'a'
        ));

    }


    /**
     * exportVehiclesCSV Adds the fahrzeug Add form
     *
     * @param
     */
    public function exportVehiclesCSV($startikz, $startakz, $cntvehicles)
    {

        $fsRes = $this->form;
        $fsRes->addElement('submit', 'exportvehiclescsv', array(
            'id' => 'exportvehiclescsv',
            'value' => 'Als CSV Datei speichern'
        ));

        // $fsRes->addStatic(null,array('class'=>'sts_back','href'=>'#','onClick'=>'window.close()'),array('content'=>'Zurück','tagName'=>'a'));
    }


    public function wizard_existing_user($deputies, $privileges = null, $user = null, $depuser = null)
    {

        $fsDep = $this->form;

        if (isset($depuser) && ! empty($depuser))
            $role = $depuser['role'];
        else
            $role = $user->getUserRole();

        if (isset($depuser['id'])) {
            if ($depuser['id'] != $user->getUserId()) {
                $fsDep->addElement('staticNoLabel', null, null, array(
                    'content' => 'Mitarbeiter/in Konto',
                    'tagName' => 'h3'
                ));
                $fsDepSub = $fsDep->addElement('fieldset')->setLabel("Mitarbeiter/in Konto");
            } else {
                $fsDep->addElement('staticNoLabel', null, null, array(
                    'content' => 'Ihr Konto',
                    'tagName' => 'h3'
                ));
                $fsDepSub = $fsDep->addElement('fieldset')->setLabel("Ihr Konto");
            }
            $fsSelect = $fsDepSub->addElement('fieldset');
            $deputyprivileges = unserialize($depuser['privileges']);
            $fsSelect->addStatic()->setValue('<strong>Benutzername</strong> : ' . $depuser['username']);
            $fsSelect->addStatic()->setValue('<strong>Email</strong> : ' . $depuser['email']);
            $fsSelect->addHidden('deputy')
                ->setValue($depuser['id'])
                ->setLabel($depuser['username'] . $depuser['email']);
        } else if ($user->user_can('newusers')) {
            $fsDep->addElement('staticNoLabel', null, null, array(
                'content' => 'Mitarbeiter/in Konto wählen',
                'tagName' => 'h3'
            ));
            $fsDepSub = $fsDep->addElement('fieldset')->setLabel("Bitte wählen Sie ein Mitarbeiter/in Konto");
            $fsSelect = $fsDepSub->addElement('fieldset');
            $deputyselect = $fsSelect->addSelect('deputy', array(
                'class' => 'deputy_selector'
            ), array(
                'options' => $deputies
            ));
        } else {
            $fsDep->addElement('fieldset')->setLabel("");
            return;
        }
        $fsDep->addElement('staticNoLabel', null, null, array(
            'content' => 'Option wählen',
            'tagName' => 'h3'
        ));
        $fsDepSub = $fsDep->addElement('fieldset')->setLabel("Bitte wählen Sie ein Option");

        if ($depuser['id'] != $user->getUserId()) {
            $fsDepSub->addElement('radio', 'accountaction', array(
                'class' => 'accountaction',
                'value' => 'op_konto'
            ))->setContent("Konto zurücksetzen");
            $fsDepSub->addElement('radio', 'accountaction', array(
                'class' => 'accountaction',
                'value' => 'op_privileges'
            ))->setContent("Berechtigungen ändern");
        } else {
            $fsDepSub->addElement('radio', 'accountaction', array(
                'class' => 'accountaction',
                'value' => 'op_passwd'
            ))->setContent("Passwort ändern");
            $fsDepSub->addElement('radio', 'accountaction', array(
                'class' => 'accountaction',
                'value' => 'op_key'
            ))->setContent("Schlüssel herunterladen");
        }
        if ($role == 'fuhrparksteuer') {
            $fsDepSub->addElement('radio', 'accountaction', array(
                'class' => 'accountaction',
                'value' => 'op_notifications'
            ))->setContent("Benachrichtigungsschwelle ändern");
        }
        $fsDepSub->addElement('radio', 'accountaction', array(
            'class' => 'accountaction',
            'value' => 'op_useremail'
        ))->setContent("Email Adresse ändern");
        if ($depuser['id'] != $user->getUserId()) {
            $fsDepSub->addElement('radio', 'accountaction', array(
                'class' => 'accountaction',
                'value' => 'op_deleteuser'
            ))->setContent("Konto löschen");
        }
        $fsDep->addElement('staticNoLabel', null, null, array(
            'content' => 'Konto bearbeiten',
            'tagName' => 'h3'
        ));
        $fsDepSub = $fsDep->addElement('fieldset');

        if ($depuser['id'] != $user->getUserId()) {
            $fsDepSubOption = $fsDepSub->addElement('fieldset', null, array(
                'class' => 'konto_fs'
            ))->setLabel("Konto zürucksetzen");
            $genpasswd = new PWGen(16);
            $fsDepSubOption->addHidden('temppasswd', array(
                "id" => "temppasswd"
            ))->setValue($genpasswd);
            $fsDepSubOption->addElement('staticNoLabel', null, null, array(
                'content' => 'Neues Passwört wird generiert. Schlüsselloser Login möglich. <br><br>Im folgende Schritt bestätigen Sie bitte dass Sie dieses Konto zurücksetzen wollen.',
                'tagName' => 'h3'
            ));

            $fsDepSubOption = $fsDepSub->addElement('fieldset', null, array(
                'class' => 'privileges_fs'
            ))->setLabel("Berechtigungen");
            if (! empty($privileges))
                foreach ($privileges as $priv_name => $privilege) {
                    $params = array();
                    $params['class'] = $priv_name . ' showtooltip priv_checkbox';
                    $checkbox = $fsDepSubOption->addElement('checkbox', 'privileges[' . $priv_name . ']', $params)->setContent($privilege);
                    if (isset($deputyprivileges[$priv_name]) && $deputyprivileges[$priv_name] == 1) {
                        $checkbox->setAttribute('checked');
                    } else {
                        $checkbox->removeAttribute('checked');
                    }
                }
        } else {
            $fsDepSubOption = $fsDepSub->addElement('fieldset', null, array(
                'class' => 'passwd_fs'
            ))->setLabel("Passwort ändern");
            $fsDepSubOption->addPassword('passwdone', array(
                'id' => 'passwdone',
                'data-username' => $depuser['username']
            ))->setLabel('Passwort erstellen');
            $fsDepSubOption->addElement('staticNoLabel', null, [
                'id' => 'password-strength-meter',
                'min' => 0,
                'max' => 4
            ])->setTagName('meter');
            $fsDepSubOption->addElement('staticNoLabel', null, [
                'id' => 'password-strength-text',
                'class' => 'passwdnote'
            ])->setTagName('p');
            // <meter max="4" id="password-strength-meter"></meter>
            // <p id="password-strength-text"></p>

            $fsDepSubOption->addPassword('passwdtwo')->setLabel('Bitte bestätigen Sie Ihr neues Passwort');

            $fsDepSubOption = $fsDepSub->addElement('fieldset', null, array(
                'class' => 'key_fs'
            ))->setLabel("Sclüssel herunterladen");
            $fsDepSubOption->addElement('static', null, array(
                'class' => 'keyreset'
            ))
                ->setContent("Ihr Schlüssel wird zurückgesetzt und einen neuen erstellt")
                ->setTagName('span');
        }
        if ($role == 'fuhrparksteuer') {
            $fsDepSubOption = $fsDepSub->addElement('fieldset', null, array(
                'class' => 'notifications_fs'
            ))->setLabel("Benachrichtigungsschwelle bei Fehlern");
            $params = array();
            $notifications = $user->getUserNotifications();
            $params = array(
                'value' => 0,
                'class' => 'notifications_radio'
            );
            if ($notifications == 0)
                $params['checked'] = 'checked';
            $fsDepSubOption->addElement('radio', 'notifications', $params, array(
                'content' => 'Keine Auswirkungen auf Zustellung (Immer benachrichtigen)'
            ));
            $params = array(
                'value' => 1,
                'class' => 'notifications_radio'
            );
            if ($notifications == 1)
                $params['checked'] = 'checked';
            $fsDepSubOption->addElement('radio', 'notifications', $params, array(
                'content' => 'Minimale Auswirkungen (mindestens 1 Fahrzeug pro ZSP betroffen / ausgefallen)'
            ));
            $params = array(
                'value' => 2,
                'class' => 'notifications_radio'
            );
            if ($notifications == 2)
                $params['checked'] = 'checked';
            $fsDepSubOption->addElement('radio', 'notifications', $params, array(
                'content' => 'Mittlere Auswirkungen (mindestens 2 Fahrzeuge pro ZSP betroffen / ausgefallen)'
            ));
            $params = array(
                'value' => 3,
                'class' => 'notifications_radio'
            );
            if ($notifications == 3)
                $params['checked'] = 'checked';
            $fsDepSubOption->addElement('radio', 'notifications', $params, array(
                'content' => 'Schwere Auswirkungen (mindestens 4 Fahrzeuge pro ZSP betroffen / ausgefallen)'
            ));
            $params = array(
                'value' => 4,
                'class' => 'notifications_radio'
            );
            if ($notifications == 4)
                $params['checked'] = 'checked';
            $fsDepSubOption->addElement('radio', 'notifications', $params, array(
                'content' => 'Nie benachrichtigen'
            ));
        }

        $fsDepSubOption = $fsDepSub->addElement('fieldset', null, array(
            'class' => 'useremail_fs'
        ))->setLabel("Email ändern");
        $depuseremail = $fsDepSubOption->addElement('text', 'email', array(
            'class' => 'useremail',
            'style' => 'width: 350px'
        ))->setLabel('Externe E-Mail Adresse eingeben');
        if (isset($depuser['email']))
            $depuseremail->setValue($depuser['email']);

        if ($depuser['id'] != $user->getUserId()) {
            $fsDepSubOption = $fsDepSub->addElement('fieldset', null, array(
                'class' => 'deleteuser_fs'
            ))->setLabel("Konto löschen");
            $fsDepSubOption->addElement('staticNoLabel', null, null, array(
                'content' => 'Im folgende Schritt bestätigen Sie bitte dass Sie dieses Konto löschen wollen.',
                'tagName' => 'h3'
            ));
            $fsDepSubOption->addElement('static', null, array(
                'class' => 'sts_username'
            ), array(
                'content' => '',
                'tagName' => 'span'
            ));
            $fsDepSubOption->addElement('hidden', 'delete_user_confirm', array(
                'class' => 'delete_user_confirm'
            ));
        }

        $fsDep->addElement('staticNoLabel', null, null, array(
            'content' => 'Bestätigung',
            'tagName' => 'h3'
        ));
        $fsDepSub = $fsDep->addElement('fieldset', null, array(
            'data-panelaction' => 'saveform'
        ));

        $fsDepSubOption = $fsDepSub->addElement('fieldset', null, array(
            'class' => 'konto_fs'
        ))->setLabel("Möchten Sie wirklich dieses Konto zürucksetzen? <br><br> Neues Passwört wird generiert. Schlüsselloser Login möglich. ");
        $fsDepSubOption = $fsDepSub->addElement('fieldset', null, array(
            'class' => 'passwd_fs'
        ))->setLabel("Möchten Sie wirklich Ihr Passwort ändern?");
        $fsDepSubOption = $fsDepSub->addElement('fieldset', null, array(
            'class' => 'privileges_fs'
        ))->setLabel("Möchten Sie wirklich die Berechtigungen ändern?");
        $fsDepSubOption = $fsDepSub->addElement('fieldset', null, array(
            'class' => 'notifications_fs'
        ))->setLabel("Möchten Sie wirklich die Benachrichtigungsschwelle bei Fehlern ändern?");
        $fsDepSubOption = $fsDepSub->addElement('fieldset', null, array(
            'class' => 'useremail_fs'
        ))->setLabel("Möchten Sie wirklich die Email Adresse ändern?");
        $fsDepSubOption = $fsDepSub->addElement('fieldset', null, array(
            'class' => 'deleteuser_fs'
        ))->setLabel("Möchten Sie wirklich dieses Konto löschen?");
        $fsDepSubOption = $fsDepSub->addElement('fieldset', null, array(
            'class' => 'key_fs'
        ))->setLabel("Möchten Sie wirklich Ihr Schlüssel zurücksetzen und den neuen herunterladen?");

        $fsDepSub->addElement('static', null, array(
            'class' => 'sts_username'
        ), array(
            'content' => '',
            'tagName' => 'span'
        ));
        $fsDepSub->addElement('checkbox', 'confirmsts', array(
            'class' => ''
        ))->setContent('Ja');

        // $fsDep->addElement('staticNoLabel',null,null,array('content'=>'Status','tagName'=>'h3'));
        // $fsDepSub=$fsDep->addElement( 'fieldset',null,array('data-panelaction'=>'lastpanel'));
        // $fsDepSub->addElement('staticNoLabel',null,array('class'=>'save_status'),array('tagName'=>'span'));
        $fsDepSubOption->addHidden('passwd', array(
            "id" => "resetpass"
        ))->setValue('');
        $fsDepSub->addHidden('action')->setValue('saveexist');
        $fsDepSub->addHidden('page')->setValue('mitarbeiter');

    }


    /**
     * wizard_new_user
     * Generates the form for the wizard to create a new user
     *
     * @param array $privileges
     *            privileges relevant to this user
     * @param object $user
     *            the current user object
     * @param string $role
     *            is equal to 1. FPV if FPS is creating a FPV deputy 2. role of currentuser
     * @param integer $zspl
     *            if FPS is creating a new FPV deputy then the zspl id for the deputy is also passed
     * @param string $givenemail
     *            if FPS is creating a new FPV deputy then the email address of the new FPV deputy, else empty
     */
    public function wizard_new_user($privileges, $user, $role = null, $zspl = null, $givenemail = null)
    {

        $fsDep = $this->form;

        $this->displayheader->enqueueLocalStyle('

        .wizard > .content  {min-height: 30em;}

        .hinweis_accounterstellung {
            position:   absolute;
            left:       0px;
            top:        100px;
            border:     2px solid #666;
            width:      100%;
            height:     18em;
            padding:    0 10px;
            overflow-Y: scroll;
        }
        ');

        $privacyContent = file_get_contents($_SERVER['STS_ROOT'] . "/html/hinweis_accounterstellung.html");

        $fsDep->addElement('staticNoLabel', null, null, array(
            'content' => 'E-Mail Adresse eingeben',
            'tagName' => 'h3'
        ));
        $fsDepSub = $fsDep->addElement('fieldset')->setLabel("E-Mail Adresse");
        $fsDepSub->addElement('staticNoLabel', null, null, array(
            'content' => $privacyContent
        ));

        $default_email = $GLOBALS['IS_DEBUGGING'] ? 'test@streetscooter.eu' : '@deutschepost.de';
        $email = $fsDepSub->addText('email', array(
            'class' => 'wizard_user_email required',
            'style' => 'width: 350px'
        ))
            ->setValue($default_email)
            ->setLabel('Externe E-Mail Adresse eingeben');
        // $fsDepSub->addElement('staticNoLabel',null,null,array('content'=>'<input type="checkbox" name="noEmail" id="noEmail">Benutzer hat keine E-Mail<br>'));

        $fsDep->addElement('staticNoLabel', null, null, array(
            'content' => 'Benutzername',
            'tagName' => 'h3'
        ));
        $fsDepSub = $fsDep->addElement('fieldset')->setLabel("Benutzername");
        $username = $fsDepSub->addText('depusername', array(
            'class' => 'wizard_user_username required'
        ))
            ->setValue('')
            ->setLabel('Benutzer Name');

        if (! empty($givenemail)) {
            $email->setValue($givenemail);
            $splitemail = explode('@', $givenemail);
            if (isset($splitemail[0]))
                $username->setValue($splitemail[0]);
        }

        if ($user->getUserRole() == 'fuhrparksteuer' && $role != 'fpv') // $role is passed as fpv when creating user accounts from ZSPL email Adresses
        {
            $fsDep->addElement('staticNoLabel', null, null, array(
                'content' => 'Benachrichtigungsschwelle',
                'tagName' => 'h3'
            ));
            $fsDepSub = $fsDep->addElement('fieldset')->setLabel("Benachrichtigungsschwelle bei Fehlern");

            $notifications = $user->getUserNotifications();
            $params = array(
                'value' => 0
            );
            $params['checked'] = 'checked';
            $fsDepSub->addElement('radio', 'notifications', $params, array(
                'content' => 'Keine Auswirkungen auf Zustellung'
            ));
            $params = array(
                'value' => 1
            );
            $fsDepSub->addElement('radio', 'notifications', $params, array(
                'content' => 'Minimale Auswirkungen (maximal 1 Fahrzeug betroffen)'
            ));
            $params = array(
                'value' => 2
            );
            $fsDepSub->addElement('radio', 'notifications', $params, array(
                'content' => 'Mittlere Auswirkungen (maximal 4 Fahrzeug betroffen)'
            ));
            $params = array(
                'value' => 3
            );
            $fsDepSub->addElement('radio', 'notifications', $params, array(
                'content' => 'Schwere Auswirkungen (mehr als 4 Fahrzeug betroffen)'
            ));
            $params = array(
                'value' => 4
            );
            $fsDepSub->addElement('radio', 'notifications', $params, array(
                'content' => 'Nie benachrichtigen'
            ));
        }

        $fsDep->addElement('staticNoLabel', null, null, array(
            'content' => 'Berechtigungen',
            'tagName' => 'h3'
        ));
        $fsDepSub = $fsDep->addElement('fieldset', null, array(
            'data-panelaction' => 'saveform'
        ))->setLabel("Welche Berechtigungen darf der neuer Vertreter haben");

        if (! empty($privileges))
            foreach ($privileges as $priv_name => $privilege)
                $fsDepSub->addElement('checkbox', 'privileges[' . $priv_name . ']', null)->setContent($privilege);

        $myRoles = $user->getListOfUserRoles();
        if (count($myRoles) > 1) {
            $fsDep->addElement('staticNoLabel', null, null, array(
                'content' => 'Benutzerrollen',
                'tagName' => 'h3'
            ));
            $fsDepSub = $fsDep->addElement('fieldset', null, array(
                'data-panelaction' => 'saveform'
            ))->setLabel("Welche Benutzrollen übergeben Sie dem neuen Benutzer");

            $options = "";
            foreach ($myRoles as $role) {
                $label = $user->getUserRoleLabel($role);
                if (! empty($label))
                    $options .= "<option value=\"$role\" selected>$label</option>";
            }

            $fsDepSub->addElement('staticNoLabel', null, null, array(
                'content' => '<p><select name="inheritedRoles[]" size="10" style="width: 300px;" multiple>' . $options . '</select></p>'
            ));
        }

        if ($user->getAllowChangeDiv())
            $fsDepSub->addElement('staticNoLabel', null, null, [
                'content' => '<p><input type="checkbox" name="allowChangeDiv" checked> Benutzer kann Niederlassung auswälen</p>'
            ]);

        if ($user->getAllowChangeZspl())
            $fsDepSub->addElement('staticNoLabel', null, null, [
                'content' => '<p><input type="checkbox" name="allowChangeDepot" checked> Benutzer kann ZSP auswälen</p>'
            ]);

        if ($user->getAllowChangeWorkshop())
            $fsDepSub->addElement('staticNoLabel', null, null, [
                'content' => '<p><input type="checkbox" name="allowChangeWorkshop" checked> Benutzer kann Werkstatt auswälen</p>'
            ]);

        if ($role == 'fpv' && ! empty($zspl)) // passed only when the FPS user is creating a FPV user
        {
            $fsDepSub->addHidden('zspl_id_dep')->setValue($zspl);
            $fsDepSub->addHidden('role')->setValue($role);
        }

        // $fsDep->addElement('staticNoLabel',null,null,array('content'=>'Email Versand','tagName'=>'h3'));
        // $fsDepSub= $fsDep->addElement( 'fieldset',null,array('data-panelaction'=>'lastpanel'))->setLabel("Benutzer Konto erfolgreich erstellt. <br>Kein Schlüssel diesem Benutzer auf dem DeutschePost Keyserver gefunden.");

        // $fsDepSub->addElement('radio','endoption',null,array (
        // 'content' => 'S/Mime Schlüssel hochladen'
        // ) );
        // $fsDepSub->addElement('radio','endoption',null,array (
        // 'content' => 'PGP Schlüssel hochladen'
        // ) );

        // $fsDepSub->addElement('radio','endoption',array('value'=>'pass'),array (
        // 'content' => 'Kein email senden'
        // ) );

        // $fsDep->addElement('staticNoLabel',null,null,array('content'=>'Passwort','tagName'=>'h3'));
        // $fsDepSub= $fsDep->addElement( 'fieldset')->setLabel("Speichern/Kopieren Sie bitte dieses Passwort");

        $genpasswd = new PWGen(16);
        // $fsDepSub->addStatic(null,null,array (
        // 'label' => 'Neue Mitarbeiter/in Passwort : '.$genpasswd
        // ) );

        // $fsDep->addElement('staticNoLabel',null,null,array('content'=>'Passwort','tagName'=>'h3'));
        // $fsDepSub= $fsDep->addElement( 'fieldset')->setLabel("Speichern/Kopieren Sie bitte dieses Passwort");

        $fsDepSub->addHidden('passwd', array(
            'id' => 'password_field'
        ))->setValue($genpasswd);
        $fsDepSub->addHidden('action')->setValue('saveneu');
        $fsDepSub->addHidden('page')->setValue('mitarbeiter');

    }


    /**
     * zspl_add_edit_form Used to add a new ZSPL or edit existing ZSPL
     *
     * @param array $divisions_params
     * @param boolean $edit_zspl
     *            if true, then editing existing ZSPL, if false, then adding new ZSPL
     * @param array $editThisZspl
     *            data of existing ZSPL that is to be edited
     */
    public function zspl_add_edit_form($division_params = '', $edit_zspl = false, $editThisZspl = '', $listofzsps = '')
    {

        if (! function_exists('check_emails')) {


            function check_emails($emails)

            {

                // $pattern = "#^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$#";
                $pattern = "/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/";

                $emails_list = explode("\n", $emails);

                foreach ($emails_list as $thisemail) {

                    if (! preg_match($pattern, trim($thisemail))) {
                        echo $thisemail . " ist nicht gültig";
                        return false;
                    }
                }
                return true;

            }
        }
        $fsZspl = $this->form->addElement('fieldset');

        $fsZspl->addHidden('zsplname')->setValue($editThisZspl['name']);

        $js_script_emails = "
				function check_emails(emails)
				{
				flag=true;
				var re = /^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i;

				emails_list=emails.split('\\n');
				emails_list.forEach(function(thisemail,i) {

					if(!re.exec(thisemail))
						{
							flag=false;
							document.getElementById('zspemails_errors').innerHTML=thisemail+ ' ist nicht gültig!';
						}

				});

				 return flag;
				}";

        $fsZspl->addScript('check_emails_script', null, array(
            'content' => $js_script_emails
        ));

        $emails = $fsZspl->addTextarea('zsplemails', array(
            'rows' => 4,
            'cols' => 40,
            'id' => 'zspemails',
            'placeholder' => 'Max.Mustermann@deutschepost.de'
        ))
            ->setValue('@deutschepost.de')
            ->setLabel("Bitte tragen Sie eine E-Mail Adresse (Externe) pro Zeile ein, <br> z.B. Max.Mustermann@deutschepost.de. Alle ZSPL relevanten Informationen / Fehlermeldungen werden an diese Adressen gesendet.");

        // if($edit_zspl) //@todo added 2016-06-16 after compare with current check if neeed
        {
            $zspl_emails = unserialize($editThisZspl["emails"]);
            if (! empty($zspl_emails))
                $emails->setValue(implode("\r\n", $zspl_emails));
        }

        $emails->addRule('empty', 'Email Adresse Erforderlich', null, HTML_QuickForm2_Rule::CLIENT)->or_($emails->createRule('callback', '', 'check_emails'));

        $emails = $fsZspl->addStatic(null, array(
            'class' => 'error',
            'id' => 'zspemails_errors'
        ), array(
            'content' => '',
            'tagName' => 'span'
        ));

        $fsDepot = $fsZspl->addElement('fieldset')->setLabel('ZSP Emails: ');

        $depots = array();
        foreach ($listofzsps as $zsp) {
            $depots[$zsp["depot_id"]] = $zsp["name"] . " (OZ: " . $zsp["dp_depot_id"] . ") ";
        }

        // @todo 20160809 remove unbekannt from here too
        $fsDepot->addSelect('listdepots', array(
            "id" => "list_depots_ctrl"
        ), array(
            'options' => $depots
        ))->setLabel('ZSP wählen');
        $depot_emails_form = $fsDepot->addTextarea('depotemails', array(
            'rows' => 4,
            'cols' => 40,
            'id' => 'depotemails',
            'placeholder' => 'Max.Mustermann@deutschepost.de'
        ))->setLabel("Bitte tragen Sie eine E-Mail Adresse (Externe) pro Zeile ein, <br> z.B. Max.Mustermann@deutschepost.de. Alle ZSP relevanten Informationen / Fehlermeldungen werden an diese Adressen gesendet.");
        $depot_emails = unserialize($listofzsps[0]["emails"]); // add the zspemails column here and refresh it with javascript
        if (! empty($depot_emails))
            $depot_emails_form->setValue(implode("\r\n", $depot_emails));
        // $depot_emails_form->addRule('email','Email Adresse ist ungültig', null,HTML_QuickForm2_Rule::CLIENT);
        $depot_emails_error = $fsDepot->addStatic(null, array(
            'class' => 'error',
            'id' => 'depotemails_errors'
        ), array(
            'content' => '',
            'tagName' => 'span'
        ));

        $fsZspl->addHidden('action')->setValue('save_exist_zspl');
        $fsZspl->addHidden('zspl', array(
            'id' => 'zspl_id'
        ))->setValue($editThisZspl["zspl_id"]);
        $fsZspl->addHidden('division_id', array(
            'id' => 'division_id'
        ))->setValue($editThisZspl["division_id"]);
        $fsZspl->addHidden('div')->setValue($division_params["division_id"]);
        $fsZspl->addHidden('zspl')->setValue($editThisZspl["zspl_id"]);

        $named = $fsZspl->addGroup('');

        $named->addSubmit('submit', array(
            'value' => "Speichern"
        ));
        $named->addReset('reset', array());

        // $named->addStatic(null, array('class'=>'sts_back','href'=>'index.php'),array('content'=>'Zurück','tagName'=>'a'));
    }


    /**
     * depot_edit_form Used to edit emails for the depot
     *
     * @param array $divisions_params
     * @param array $divisions_params
     * @param boolean $edit_zspl
     *            if true, then editing existing ZSPL, if false, then adding new ZSPL
     * @param array $editThisZspl
     *            data of existing ZSPL that is to be edited
     */
    public function depot_edit_form($zspl_params = '', $edit_depot = false, $editThisDepot = '')
    {

        $fsDepot = $this->form->addElement('fieldset')->setLabel('ZSPL : ' . $zspl_params['name']);

        $depot_name = $fsDepot->addElement('static', 'depotnamelabel', array(
            'style' => ''
        ), array(
            'label' => 'ZSP Name : ' . $editThisDepot['name']
        ));

        $fsDepot->addHidden('depotname')->setValue($editThisDepot['name']);

        $emails = $fsDepot->addTextarea('depotemails', array(
            'rows' => 4,
            'cols' => 40,
            'id' => 'depotemails',
            'placeholder' => 'Max.Mustermann@deutschepost.de'
        ))->setLabel("Bitte tragen Sie eine E-Mail Adresse (Externe) pro Zeile ein, <br> z.B. Max.Mustermann@deutschepost.de");

        if ($edit_depot) {
            $depot_emails = unserialize($editThisDepot["emails"]);
            if (! empty($depot_emails))
                $emails->setValue(implode("\r\n", $depot_emails));
        }

        // $emails->addRule('required', 'Email Adresse Erforderlich', null,HTML_QuickForm2_Rule::CLIENT_SERVER)
        // ->and_($emails->createRule('callback', '','check_emails'));

        // $emails->addRule('email','Email Adresse ist ungültig', null,HTML_QuickForm2_Rule::CLIENT_SERVER);
        // $emails->addRule('required','Email Adresse Erforderlich', null,HTML_QuickForm2_Rule::CLIENT_SERVER);

        $emails = $fsDepot->addStatic(null, array(
            'class' => 'error',
            'id' => 'depotemails_errors'
        ), array(
            'content' => '',
            'tagName' => 'span'
        ));

        $fsDepot->addHidden('action')->setValue("save_exist_depot");
        $fsDepot->addHidden('zspl')->setValue($zspl_params["zspl_id"]);
        $fsDepot->addHidden('depot')->setValue($editThisDepot["depot_id"]);

        $named = $fsDepot->addGroup('');

        $named->addSubmit('submit', array(
            'value' => "Speichern"
        ));
        $named->addReset('reset', array());
        $named->addStatic(null, array(
            'class' => 'sts_back',
            'href' => 'index.php'
        ), array(
            'content' => 'Zurück',
            'tagName' => 'a'
        ));

    }


    /**
     * add_fahrzeug_suchen Adds Fahrzeug Suchen Box
     */
    public function add_fahrzeug_suchen()
    {

        // text input elements
        $fsText = $this->form->addElement('fieldset')->setLabel('Fahrzeug Suchen');
        if (isset($_REQUEST['action']))
            $fsText->addHidden('action')->setValue($_REQUEST['action']);

        $fsText->addElement('text', 'vin', array(
            'style' => 'width: 170px;'
        ), array(
            'label' => 'VIN Eingeben',
            'placeholder' => 'VIN Eingeben'
        ));

        $fsText->addElement('text', 'kennzeichen', array(
            'style' => 'width: 170px;'
        ), array(
            'label' => 'Kennzeichen Eingeben',
            'placeholder' => 'Kennzeichen Eingeben'
        ));

        $fsText->addElement('text', 'zsp', array(
            'style' => 'width: 170px;'
        ), array(
            'label' => 'ZSP Eingeben'
        ));
        $fsText->addElement('submit', 'testSubmit', array(
            'value' => 'Suchen'
        ));

    }


    /**
     * add_vehicle_search Adds Fahrzeug suchen form
     */
    public function add_vehicle_search($options, $defaultVal = null)
    {

        $fsSelect = $this->form->addElement('fieldset', null, array(
            "id" => "fs_vehicle_search"
        ));

        $newSelect = $fsSelect->addSelect("vehicle_search", array(
            "id" => "vehicle_search",
            'class' => 'fleet_search'
        ), array(
            'options' => $options
        ))->setLabel("Fahrzeug nach VIN/Kennzeichen suchen");
        if ($defaultVal !== null)
            $newSelect->setValue($defaultVal);
        $fsText = $this->form->addElement('fieldset', null, array(
            "id" => "fs_config_timestamp",
            "class" => "init_hidden"
        ));

        $fsText->addElement('text', 'showconfig_timestamp', array(
            'id' => 'showconfig_timestamp'
        ), array(
            'label' => 'Zeitpunkt'
        ));
        $fsText->addHidden("action")->setValue("abfrage");
        $fsText->addElement('submit', 'showconfig_submit', array(
            'id' => 'showconfig_submit',
            'value' => 'Generieren'
        ));

    }


    /**
     * add_fahrzeug_suchen Adds Vehicle Suchen Box
     */
    public function add_vehicle_attributes($options, $defaultVal = null)
    {

        // @todo do I need this?
        $this->genSelect("attributes", array(
            "id" => ""
        ), $options, "Fahrzeug nach VIN/Kennzeichen suchen");

    }


    /**
     * addlogin Adds a login form
     */
    public function addlogin($errors = '')
    {
        $fsText = $this->form->addElement('fieldset');
        $jsEnabled = null;
        $jsEnabled = '<noscript><div class="noscript">Um den vollen Leistungsumfang unseres STS Systems nutzen zu können, aktivieren Sie bitte JavaScript in Ihren Browsereinstellungen.</div><input name="js_enabled" type="hidden" value="1"></noscript>';

        if($jsEnabled) {
            $fsText->addStatic(null, array(
                    'class' => 'streetwelcome'
                ), array(
                    'tagName' => 'h3'
                ))->setContent($jsEnabled);
        }

        $fsText = $this->form->addElement('fieldset');
        $jsIECheck = null;

        $jsIECheck = '<div class="noscript"><h2>Ihr Browser wird offiziell nicht unterstützt</h2>Um zukunftsfähig zu sein, wurde unser aktuelles Cloud-System für die neuesten Technologien entwickelt. Daher können bei Internet Explorer Browser leider Probleme auftreten. Um den vollen Leistungsumfang unseres STS Systems nutzen zu können, empfehlen wir die Nutzung der Firefox, Chrome oder Edge Browser.</div>';
        
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'msie') !== FALSE || strpos($_SERVER['HTTP_USER_AGENT'], 'rv:11.0') !== FALSE || strpos($_SERVER['HTTP_USER_AGENT'], 'trident/7.0') !== FALSE) {
            $fsText->addStatic(null, array(
                    'class' => 'streetwelcome'
                ), array(
                    'tagName' => 'h3'
                ))->setContent($jsIECheck);
        }

        $fsText = $this->form->addElement('fieldset');

        $fsText->addStatic(null, array(
            'class' => 'streetwelcome'
        ), array(
            'tagName' => 'h1'
        ))->setContent('StreetScooter Cloud System');

        $fsText->addElement('text', 'benutzerName', array(
            'style' => 'width: 300px;'
        ), array(
            'label' => 'Benutzer Name (Vorname.Nachname)'
        ));

        /*
         * $fsText->addElement ( 'password', 'benutzerPwd', array (
         * 'style' => 'width: 300px;'
         * ), array (
         * 'label' => 'Benutzer Passwort'
         * ) );
         */
        $fsText->addElement('staticNoLabel')->setContent('    <div class="row"><p class="label"><label for="benutzerPwd-0">Benutzer Passwort</label></p><div class="element"><input type="password" style="width: 300px;" name="benutzerPwd" id="benutzerPwd-0" value="" /></div></div>');

        if ($errors == 'Kein Schüssel gefunden!')
            $fsText->addFile('keyfileupload', null, array(
                'language' => 'de'
            ))->setLabel('Schlüssel hochladen');

        foreach ($_POST as $key => $postvar) {
            if (($key != 'page' || ($key == 'page' && $postvar != 'logout')) && $key != 'benutzerPwd' && $key != 'benutzerName')
                $fsText->addHidden($key)->setValue(filter_var($postvar, FILTER_SANITIZE_STRING));
        }

        foreach ($_GET as $key => $getvar) {
            if ($key != 'page' || ($key == 'page' && $getvar != 'logout')) // disabled since its also copying page=logout and redirects to the same page displaying the message
                $fsText->addHidden($key)->setValue($getvar);
        }

        $fsText->addElement('submit', '_qf_testSubmit', array(
            'value' => 'Login'
        ));

    }


    public function genMonthYearSelect($monthyear, $action)
    {

        $fsDiv = $this->form->addElement('fieldset');
        $group = $fsDiv->addGroup('', array(
            'id' => 'withLabel'
        ));
        $select = $group->addSelect('yearmonth', array(
            'class' => ''
        ), array(
            'options' => $monthyear
        ))->setLabel(" Monat und Jahr auswählen");
        if (isset($_POST['yearmonth']))
            $select->setValue($_POST['yearmonth']);
        $fsDiv->addHidden('action')->setValue($action);
        $fsDiv->addSubmit("save_month_select", array(
            'value' => "Auswählen"
        ));

    }


    /**
     * generates a form for selecting the month year and the variant, used in SalesController.Class.php
     *
     * @param
     *            date string $monthyear
     * @param
     *            array of variant_values as keys and variant_names $variants
     * @param string $action
     */
    public function genMonthYearVariantSelect($monthyear, $variants, $action, $multiple = false)
    {

        $fsDiv = $this->form->addElement('fieldset');

        $select_attrs = array(
            'class' => ''
        );
        if ($multiple)
            $select_attrs += array(
                'multiple' => 'multiple'
            );
        $select = $fsDiv->addSelect('yearmonth', $select_attrs, array(
            'options' => $monthyear
        ))->setLabel("Monat und Jahr auswählen");

        for ($w = date('W'); $w <= 52; $w ++) {
            $calendar_weeks['kw' . $w] = 'KW ' . $w;
        }

        $select_week = $fsDiv->addSelect('calendar_weeks', array(
            'multiple' => 'multiple',
            'size' => 8,
            'style="width: 120px "'
        ), array(
            'options' => $calendar_weeks
        ))->setLabel(" oder Kalendar Wochen auswählen");

        if (isset($_POST['yearmonth']))
            $select->setValue($_POST['yearmonth']);

        $select = $fsDiv->addSelect('variant_value', array(
            'class' => ''
        ), array(
            'options' => $variants
        ))->setLabel(" Fahrzeug Variante auswählen");
        if (isset($_POST['variant_value']))
            $select->setValue($_POST['variant_value']);

        $fsDiv->addHidden('action')->setValue($action);
        $fsDiv->addSubmit("save_month_select", array(
            'value' => "Auswählen"
        ));

    }


    public function getProPlanForm($productionPlan, $variants, $yearmonth, $weeks)
    {

        foreach ($weeks as $weekkey => $weeklabel) {
            $head[$weekkey] = '<th style="">' . $weeklabel . '</th>';
        }
        $fs = $this->form->addElement('fieldset');
        $fs->addElement("staticNoLabel")->setContent('<h2>' . strftime('%B %Y', strtotime($yearmonth)) . '</h2>');
        $fs->addSubmit("submitproplan", array(
            'value' => "Speichern"
        ));

        $fs->addElement('staticNoLabel', null, null, array(
            'content' => '<br><table id="production_plan" style="overflow-x: scroll; max-height: 550px; display: block">'
        ));
        $fs->addElement('staticNoLabel')->setContent('<thead><tr><th>Fahrzeugvariante</th>' . implode('', $head) . '</tr></thead>');
        $fs->addElement('staticNoLabel', null, null, array(
            'content' => '<tbody>'
        ));

        $fs->addElement('staticNoLabel')->setContent('<tr>');
        $group = $fs->addGroup('', array(
            'id' => 'wholeTd'
        ));
        foreach ($variants as $variant)
            $group->addElement('staticNoLabel', null, array(
                'class' => 'production_plan_variant_label'
            ))
                ->setTagName('span')
                ->setContent($variant);
        $processedProPlan = array();
        if (! empty($productionPlan)) {
            foreach ($productionPlan as $proplanentry) {
                if (isset($proplanentry['variant_quantities']))
                    $processedProPlan[$proplanentry['production_week']] = json_decode($proplanentry['variant_quantities'], true);
            }
        }

        foreach ($head as $kweek => $singletd) {
            $group = $fs->addGroup('', array(
                'id' => 'wholeTd'
            ));
            foreach ($variants as $variant_value => $variant) {
                if (isset($processedProPlan[$kweek][$variant_value]))
                    $defaultVal = $processedProPlan[$kweek][$variant_value];
                else
                    $defaultVal = 0;

                $quantity_element = $group->addElement('number', 'quantities[' . $kweek . '][' . $variant_value . ']', array(
                    'class' => 'production_plan_variant_input',
                    'min' => $defaultVal
                ));
                if ($defaultVal)
                    $quantity_element->setValue($defaultVal);
            }
        }
        $fs->addElement('staticNoLabel')->setContent('</tr>');

        $fs->addElement('staticNoLabel', null, null, array(
            'content' => '</tbody></table>'
        ));
        $fs->addHidden("action", array(
            'value' => "saveProPlan"
        ));
        $fs->addHidden("yearmonth", array(
            'value' => $yearmonth
        ));
        $fs->addSubmit("submitproplan", array(
            'value' => "Speichern"
        ));

    }


    /**
     * *
     *
     * @param string,array $yearmonth
     *            can be a string in form 2017-08-01 or an array in form array('2017-08-01','2017-09-01')
     *
     *            Converts date to textual month,year
     */
    public function convertDateToText($yearmonth)
    {

        setlocale(LC_TIME, "de_DE.UTF-8");
        if (is_array($yearmonth)) {
            $months_list = array();
            foreach ($yearmonth as $single_yearmonth) {
                $months_list[] = strftime('%B %Y', strtotime($single_yearmonth));
            }

            return implode(',', $months_list);
        } else {
            return strftime('%B %Y', strtotime($yearmonth));
        }

    }


    public function getDeliveryPlanForm($deliveryPlan, $yearmonth, $variant_value, $variant_name, $productionPlanSum)
    {

        // $startweek=date('W',strtotime($yearmonth));
        // $endweek=date('W',strtotime('last day of '.date('F Y',strtotime($yearmonth))));

        // for($i=$startweek;$i<=$endweek;$i++)
        // {
        // $head["kw$i"]='<th style="">KW '.$i.'</th>';
        // }
        $fs = $this->form->addElement('fieldset');
        $fs->addElement("staticNoLabel")->setContent('<h2>' . $this->convertDateToText($yearmonth) . ' - ' . $variant_name . '</h2>');
        $fs->addElement("staticNoLabel")->setContent('Die Niederlassungen können nach Auslieferungswoche Wünsch sortiert werden.
				Die erste Niederlassungen in der Reihe werden früher ausgeliefert.');
        $fs->addSubmit("submitdelplan", array(
            'value' => "Speichern",
            'class' => 'submitdelplan'
        ));
        $fs->addElement("staticNoLabel")->setContent('<a class="reset_to_zero">Alle Zahlen als 0 setzen</a>');
        $fs->addElement('staticNoLabel', null, null, array(
            'content' => '<br><table id="deliver_to_divisions" style="overflow-x: scroll; max-height: 550px; display: block">'
        ));
        $fs->addElement('staticNoLabel')->setContent('<thead><tr><th>Niederlassung</th>
																<th>Mobilitätsplanung - ' . $this->convertDateToText($yearmonth) . '</th>
																<th>Vom Vormonat</th><th>Summe noch verarbeitende Fahrzeuge</th>
																<th>Auslieferung</th></tr></thead>');

        $fs->addElement('staticNoLabel', null, null, array(
            'content' => '<tbody id="sortable_delivery">'
        ));
        setlocale(LC_TIME, "de_DE.UTF-8");
        foreach ($deliveryPlan as $division) {
            $group = $fs->addGroup('', array(
                'id' => 'tableit'
            ));
            if (isset($division['yearmonth']))
                $division['name'] .= ' : ' . $this->convertDateToText($division['yearmonth']);

            $group->addElement('staticNoLabel')->setContent($division['name'] . ' (' . $division['dp_division_id'] . ') ');
            $group->addElement('staticNoLabel')->setContent($division['actual_delivery_plan_quantity']);
            $group->addElement('staticNoLabel')->setContent($division['pendingqty']);
            $group->addElement('staticNoLabel')->setContent($division['quantity']);

            $division_id = $division['division_id'];

            // if(isset($toDeliverPlan[$division_id]))
            // $delivery_quantities=json_decode($toDeliverPlan[$division_id]['delivery_quantities'],true);
            // if(isset($delivery_quantities[$kval]))
            // $val=$delivery_quantities[$kval];
            // else
            // $val=0;
            $val = 0;
            $kval = 'kw' . date('W');
            // there can be two instance of division_id (one from pending month and one from this month, so its important to avoid having division id as key
            // probably use key month as division_id+yearmonth
            if (isset($division['yearmonth']))
                $key = $division['division_id'] . '_' . $division['yearmonth'];
            else {
                $key = $division['division_id'] . '_' . implode('_', $yearmonth);
            }
            $group->addElement('number', 'quantities[' . $key . ']', array(
                'class' => 'check_for_sum',
                'style' => 'width: 100px',
                'min' => 0
            ))->setValue($division['delivery_to_division_quantity']);
            // removed ,'max'=>$division['quantity'] quick fix for March 2017 delivery

            // foreach($head as $kval=>$kweek)
            // {
            // if(isset($delivery_quantities[$kval]))
            // $val=$delivery_quantities[$kval];
            // else
            // $val=0;
            // $group->addElement('number','quantities['.$division['division_id'].']['.$kval.']',array('style'=>'width: 40px'))->setValue($val);

            // }
        }

        $fs->addElement('staticNoLabel', null, null, array(
            'content' => '</tbody></table>'
        ));
        $fs->addHidden("action", array(
            'value' => "saveDivisionsDeliveryPlan"
        ));
        $fs->addHidden("yearmonth", array(
            'value' => serialize($yearmonth)
        ));
        $fs->addHidden("production_plan_sum", array(
            'class' => 'production_plan_sum',
            'value' => $productionPlanSum
        ));
        $fs->addHidden("delivery_plan_sum", array(
            'class' => 'delivery_plan_sum',
            'value' => array_sum(array_column($deliveryPlan, 'delivery_to_division_quantity'))
        ));
        $fs->addHidden("variant_value", array(
            'value' => $variant_value
        ));
        $fs->addSubmit("submitdelplan", array(
            'value' => "Speichern",
            'class' => 'submitdelplan'
        ));

        // $this->listObjectsTableHeadings=array_merge(array('OZ','Niederlassung Name',date('M')),$head);
    }


    public function getQSFertigForm($vehicles, $qm_qs_users = null, $action)
    {

        if (isset($qm_qs_users))
            $qm_qs_users[""] = "Benutzername wählen";
        $fs = $this->form->addElement('fieldset');

        if ($action == 'saveQM')
            $lastcol = '<th>QM gesperrt</th>';
        else
            $lastcol = '<th>QS Fertig Status</th><th>QM gesperrt</th><th>Drucken</th>';

        $fs->addElement('staticNoLabel', null, null, array(
            'content' => '<br><table id="sort_filter_table" style="overflow-x: scroll; max-height: 550px; display: block">'
        ));
        $fs->addElement('staticNoLabel')->setContent("<thead><tr><th>VIN</th><th>IKZ</th><th>AKZ</th><th>C2CBox Id</th><th>TEO Status</th><th>Sondergenehmigung</th>
				$lastcol</tr></thead>");
        $fs->addElement('staticNoLabel', null, null, array(
            'content' => '<tbody>'
        ));
        $todays_vehicles = array();
        foreach ($vehicles as $vehicle) {
            $group = $fs->addGroup('', array(
                'id' => 'tableit',
                'data-vehicleid' => $vehicle['vehicle_id']
            ));
            $group->addElement('staticNoLabel')->setContent($vehicle['vin']);
            $group->addElement('staticNoLabel')->setContent($vehicle['ikz']);
            $group->addElement('staticNoLabel')->setContent($vehicle['code']);
            $group->addElement('staticNoLabel')->setContent($vehicle['c2cbox']);

            if (! empty($vehicle['diagnose_status_time']))
                $vehicle['diagnose_status_time'] = date('Y-m-d H:i', strtotime($vehicle['diagnose_status_time']));
            else
                $vehicle['diagnose_status_time'] = '';

            if (empty($vehicle['status_extra_data']))
                $vehicle['status_extra_data'] = 'Keine Daten Verfügbar!';

            if ($vehicle['diagnose_status'] == 'DEFECTIVE' || $vehicle['diagnose_status'] == 'PASSED*') {
                $status_content = '<a href="#" class="open_status_control" data-targetid="status_data_' . $vehicle['vehicle_id'] . '">' . $vehicle['diagnose_status'] . '</a><br>' . $vehicle['diagnose_status_time'];

                $status_content .= '<span class="init_hidden" id="status_data_' . $vehicle['vehicle_id'] . '" title="' . $vehicle['vin'] . '" >' . $vehicle['status_extra_data'] . '</span>';

                $group->addElement('staticNoLabel')->setContent($status_content);
            } else {
                $status_content = $vehicle['diagnose_status'] . '<br>' . $vehicle['diagnose_status_time'];
                $group->addElement('staticNoLabel')->setContent($status_content);
            }
            if ($vehicle['special_qs_approval'] == 't') {
                $special_qs_approval = 'ja';
            } else
                $special_qs_approval = 'nein';

            $group->addElement('staticNoLabel')->setContent($special_qs_approval);

            $params = array();

            if ($action == 'saveQS') {
                if ($vehicle['finished_status'] == 't')
                    $params['checked'] = 'checked';
                if (empty($vehicle['c2cbox']))
                    $params['disabled'] = 'disabled';

                if ($vehicle['diagnose_status'] != 'PASSED' && $vehicle['diagnose_status'] != 'PASSED*' && $vehicle['special_qs_approval'] != 't')
                    $params['disabled'] = 'disabled';
                if ($vehicle['qmlocked'] == 't') {
                    $params['disabled'] = 'disabled';
                    $qm_locked = 'ja';
                } else
                    $qm_locked = 'nein';

                $group->addElement('checkbox', 'finishedstatus_' . $vehicle['vehicle_id'], $params);
                $group->addElement('staticNoLabel')->setContent($qm_locked);
                $group->addElement('staticNoLabel')->setContent('<a href="#" class="print_vehicle_details" data-vehicleid="' . $vehicle['vehicle_id'] . '">Drucken</a>');
            } else if ($action == 'saveQM') {
                $checkbox = $group->addElement('checkbox', 'qmlocked_' . $vehicle['vehicle_id']);

                if ($vehicle['qmlocked'] == 't') {
                    $checkbox->setAttribute('checked');
                } else {
                    $checkbox->removeAttribute('checked');
                }

                if (date('Y-m-j') == date('Y-m-j', strtotime($vehicle['diagnose_status_time']))) {
                    $todays_vehicles[] = $vehicle['vehicle_id'];
                }
            }
        }

        $fs->addElement('staticNoLabel', null, null, array(
            'content' => '</tbody></table><br>'
        ));
        $fsDiv = $fs->addElement('fieldset', null, array(
            'class' => 'row'
        ));

        $fsOne = $fsDiv->addElement('fieldset', null, array(
            'class' => 'columns four inline_elements'
        ));
        $fsTwo = $fsDiv->addElement('fieldset', null, array(
            'class' => 'columns four inline_elements'
        ));
        $fsThree = $fsDiv->addElement('fieldset', null, array(
            'class' => 'columns four inline_elements'
        ));

        if ($action == 'saveQM') {
            $fsThree->addElement('static')->setContent('<span class="all_lock_status error_msg"></span>');
            $fsTwo->addElement('static')->setContent('<a href="#" class="qm_func_ctrl" data-action="lockAllToday" ><span class="genericon genericon-lock"></span>Alle Fahrzeuge des Tages sperren</a>');
            $fsTwo->addElement('static')->setContent('<a href="#" class="qm_func_ctrl" data-action="unlockAllToday"><span class="genericon genericon-key"></span>Alle Fahrzeuge des Tages entsperren</a>');
            $fsTwo->addElement('static')->setContent('<a href="#" class="qm_func_ctrl" data-action="saveQM"><span class="genericon genericon-refresh"></span>Zürucksetzen</a>');
        }

        if (isset($qm_qs_users)) {
            $fsOne->addSelect('qs_qm_user', array(
                'id' => 'qs_qm_user'
            ), array(
                'options' => $qm_qs_users
            ))->setValue("");
            $fsOne->addPassword('qs_qm_pass', array(
                'id' => 'qs_qm_pass'
            ))->setValue("");
        }

        $fs->addElement('hidden', 'todays_vehicles')->setValue(implode(',', $todays_vehicles));
        $fs->addHidden('action', array(
            'id' => 'qs_qm_action'
        ))->setValue($action);
        $fsOne->addSubmit('', array(
            'value' => 'Speichern'
        ));

    }


    public function genVariantSelect($variants, $action = null)
    {

        $fsDiv = $this->form->addElement('fieldset')->setLabel("Fahrzeug Variante auswählen");

        $select = $fsDiv->addSelect('variantselect', array(
            'class' => 'vehicle_variant_selector'
        ), array(
            'options' => $variants
        ))->setLabel('Fahrzeug variante wählen');

        $current_year = date('Y');
        $next_year = $current_year + 1;
        $years = $fsDiv->addSelect('yearselect', array(
            'class' => 'year_selector'
        ), array(
            'options' => array(
                $current_year => $current_year,
                $next_year => $next_year
            )
        ))->setLabel('Jahr wählen');

        if ($action)
            $fsDiv->addHidden('action')->setValue($action);

        $fsDiv->addSubmit("submitVariantSelect", array(
            'value' => "Auswählen"
        ));

    }


    public function deliveryplan_upload($label, $key, $action = null, $variants)
    {

        $fsDiv = $this->form->addElement('fieldset')->setLabel("$label als CSV hochladen");

        $select = $fsDiv->addSelect('variantselect', array(
            'class' => 'vehicle_variant_selector'
        ), array(
            'options' => $variants
        ))->setLabel('Fahrzeug variante wählen');
        $current_year = date('Y');
        $next_year = $current_year + 1;
        $years = $fsDiv->addSelect('yearselect', array(
            'class' => 'year_selector'
        ), array(
            'options' => array(
                $current_year => $current_year,
                $next_year => $next_year
            )
        ))->setLabel('Jahr wählen');

        $fsDiv->addFile('csvfile', null, array(
            'language' => 'de'
        ))->setLabel('CSV Datei');
        $named = $fsDiv->addGroup('');
        if ($action)
            $fsDiv->addHidden('action')->setValue($action);
        else
            $fsDiv->addHidden('action')->setValue("save_" . $key . "_upload");
        $named->addSubmit("save_" . $key . "_upload_submit", array(
            'value' => "Hochladen"
        ));

    }


    /**
     * Niederlassung hochladen
     */
    public function csvUpload($label, $key, $action = null)
    {

        $fsDiv = $this->form->addElement('fieldset')->setLabel("$label als CSV hochladen");
        $fsDiv->addFile('csvfile', null, array(
            'language' => 'de'
        ))->setLabel('CSV Datei');

        // $fsDiv->addHidden()->setValue("y");

        $named = $fsDiv->addGroup('');
        if ($action)
            $fsDiv->addHidden('action')->setValue($action);
        else
            $fsDiv->addHidden('action')->setValue("save_" . $key . "_upload");
        $named->addSubmit("save_" . $key . "_upload_submit", array(
            'value' => "Hochladen"
        ));

        // $named->addReset('reset', array());
        // $named->addStatic(null, array('class'=>'sts_back','href'=>'index.php'),array('content'=>'Zurück','tagName'=>'a'));
    }


    /**
     *
     * @param array $stations
     * @param array $vehicle_variants
     * @param integer $zsp
     * @param string $possibleCombos
     */
    public function getDepotStationsVehicleVariants($zsp, $stations, $vehicle_variants, $single_phase_vehicles)
    {

        $fsDiv = $this->form->addElement('fieldset');
        $cnt = 1;

        $fsOne = $fsDiv->addElement('fieldset', null, array(
            'id' => 'fps_ladepunkte',
            'class' => 'columns five inline_elements'
        ));
        $fsTwo = $fsDiv->addElement('fieldset', null, array(
            'id' => 'fps_ladepunkte',
            'class' => 'columns five inline_elements'
        ));

        foreach ($stations as $station) {

            if ($cnt % 2 == 0)
                $group = $fsTwo;
            else
                $group = $fsOne;
            if (! empty($station['restriction_id2']) && ! empty($station['restriction_id3'])) {
                $stationame = '<span class="threephase_station">' . $station['name'] . '</span>';
                $singlephase_str = '';
            } else {
                $stationame = '<span class="singlephase_station">' . $station['name'] . '</span>';
                $singlephase_str = implode(',', $single_phase_vehicles);
            }
            $params = array(
                'class' => 'vehicle_variant_selector',
                'data-restriction_id' => $station['restriction_id'], // @todo oddeven passing only the first restriction .. should check for three phases
                'data-allowedvehicle' => $singlephase_str
            );
            if ($station['deactivate'] == 't') {
                $params['disabled'] = 'disabled';
                $stationame .= ' (deaktiviert)';
            }

            if ($station['transporter_date']) {
                $params['disabled'] = 'disabled';
                $stationame .= '<sup>[1]</sup>';
            }

            $select = $group->addSelect('vtype_' . $station['station_id'], $params)->setLabel($stationame);
            $select->addOption('', 'null');
            foreach ($vehicle_variants as $variant_value => $variant) {

                // //if the station does not already have a vehicle_variant_allowed_value, then allow user to select only those variants that need to be delivered this month
                // if(!$station['vehicle_variant_value_allowed'])
                // {
                // if($variant['quantity']!=0) //if quantity of this variant to be delivered is zero the don't add it to the options
                // $select->addOption($variant['variant_label'],$variant_value,array('data-vtype'=>$variant['vehicle_type']));
                // }
                // else
                // {
                // $select->addOption($variant['variant_label'],$variant_value,array('data-vtype'=>$variant['vehicle_type']));
                // }
                $select->addOption($variant['variant_label'], $variant_value, array(
                    'data-vtype' => $variant['vehicle_type']
                ));
            }
            if ($station['vehicle_variant_value_allowed']) {
                $select->setValue($station['vehicle_variant_value_allowed']);
            } else
                $select->setValue('null');

            $cnt ++;
        }

        // dont display mögliche kombination rather just validate and display legend (this combo is not possible and why
        //
        $fsThree = $fsDiv->addElement('fieldset', null, array(
            'id' => 'ladepunkteTableErrors',
            'class' => 'columns four inline_elements'
        ));
        $fsDiv = $this->form->addElement('fieldset', array(
            'class' => 'columns twelve inline_elements'
        ));
        $fsDiv->addHidden('action', array(
            'value' => "saveLadepunkte"
        ));
        $fsDiv->addHidden('zsp', array(
            'value' => $zsp
        ));
        $fsDiv->addStatic(null)->setContent('<strong>Legende</strong><br><sup>[1]</sup>Ein Transport für dieses Fahrzeug ist bereits angefragt. Änderung nicht mehr möglich.<br><span class="threephase_station">3-phasige Ladepunkte/Fahrzeuge</span><br><span class="single_station">1-phasige Ladepunkte/Fahrzeuge</span>');
        $fsDiv->addSubmit('savelp', array(
            'value' => "Speichern",
            'id' => 'savelp_variant'
        ));

    }


    /**
     * Niederlassung hochladen
     */
    public function fileUpload($label, $key)
    {

        $fsDiv = $this->form->addElement('fieldset');
        $fsDiv->addFile('sharefile', null, array(
            'language' => 'de'
        ));
        $fsDiv->addHidden('page')->setValue('fileshare');
        $named = $fsDiv->addGroup('');
        // @todo need to add action for this
        $named->addSubmit("save_" . $key . "_upload", array(
            'value' => "Hochladen"
        ));

    }


    /**
     * vehicle attribute edit form
     * called from AftersalesController.php
     */
    public function getVehicleAttribEdit($vehicleId, $configTimestamp, $username, $vehicleAttributes)
    {

        $fsConfigOne = $this->form->addFieldset(null, array(
            'class' => 'columns four inline_elements'
        ));
        $fsConfigOne->addStatic()
            ->setContent($username)
            ->setTagName('span')
            ->setLabel('Angepasst von :');
        $fsConfigOne->addText('timestamp_selector', array(
            'class' => 'timestamp_selector'
        ))
            ->setValue(date('d.m.Y H:i', $configTimestamp))
            ->setLabel('Zeitpunkt');
        $fsConfigOne->addHidden('update_user', array(
            'class' => 'update_user'
        ))->setValue($username);
        $fsConfigOne->addStatic()
            ->setContent($username)
            ->setTagName('span')
            ->setLabel('Angepasst von :');
        $fsConfigOne->addCheckbox('zutat', null)->setLabel('Unfall');
        $fsConfigOne->addCheckbox('zutat', null)->setLabel('Wartung');

        $fsConfigTwo = $this->form->addFieldset(null, array(
            'class' => 'columns four inline_elements'
        ));
        // $fsConfigTwo->
        $selectAttribs = $fsConfigTwo->addSelect('attribute_selector', array(
            'class' => 'attribute_selector'
        ), array(
            'options' => $vehicleAttributes
        ))->setLabel('Merkmale');

        $showText = 'Neue Merkmal Hinzufügen (ein)';
        $hideText = 'Neue Merkmal Hinzufügen (aus)';
        $fsConfigTwo->addInputbutton(null, array(
            'id' => 'popup',
            'class' => 'popup_ctrl',
            'value' => $showText,
            'data-target' => 'popupContainer',
            'data-showtext' => $showText,
            'data-hidetext' => $hideText
        ));

        $fsConfigTwoSub = $fsConfigTwo->addFieldset(null, array(
            'id' => 'popupContainer',
            'class' => 'popUpForm'
        ));

        $fsConfigTwoSub->addText('merkmale', array(
            'id' => 'merkmale',
            'class' => "addValueInput merkmal_selector"
        ))->setLabel('Merkmal');
        $fsConfigTwoSub->addText('merkmale', array(
            'id' => 'description_merkmal',
            'class' => "addValueInput description_merkmal_selector"
        ))->setLabel('Beschreibung');
        $fsConfigTwoSub->addInputButton('Hinzufügen', array(
            'class' => 'add_value',
            'value' => 'Hinzufügen'
        ));

        $fsConfigFour = $this->form->addFieldset(null, array(
            'class' => 'columns twelve'
        ));
        $fsConfigFour->addText('update_description', array(
            'class' => 'update_description',
            'style' => 'width:600px; height:300px;'
        ))->setLabel('Bemerkung : ');

        $fsConfigFour->addSubmit('saveAttribute', array(
            'value' => 'Speichern'
        ));

    }


    /**
     * getSalesExportCSVOptions()
     */
    public function getSalesExportCSVOptions($selectCols, $templates)
    {

        // $fsConfigOne->addStatic()->setContent($username)->setTagName('span')->setLabel('Angepasst von :');
        // $fsConfigOne->addText('timestamp_selector',array('class'=>'timestamp_selector'))->setValue(date('d.m.Y H:i', $configTimestamp))->setLabel('Zeitpunkt');
        // $fsConfigOne->addHidden('update_user',array('class'=>'update_user'))->setValue($username);
        // $fsConfigOne->addStatic()->setContent($username)->setTagName('span')->setLabel('Angepasst von :');
        // $fsConfigOne->addCheckbox('zutat',null)->setLabel('Unfall');
        $divider = sizeof($selectCols) / 3;

        $count = 0;

        foreach ($selectCols as $key => $option) {

            if ($count % 3 == 0 && $count < sizeof($selectCols)) {
                $fsConfig = $this->form->addFieldset(null, array(
                    'class' => 'columns three inline_elements'
                ));
            }
            $fsConfig->addCheckbox('selectColsCheck[]', array(
                'id' => $key,
                'class' => 'selectColsCheck',
                'value' => $key
            ))->setLabel($option);
            $count ++;
        }

        $fsConfig = $this->form->addFieldset(null, array(
            'class' => 'columns twelve inline_elements'
        ));

        $newSelect = $fsConfig->addSelect('template', array(
            'class' => 'template'
        ), array(
            'options' => $templates
        ))->setLabel('Vorlage wählen');
        $fsConfig->addText('template_new_name', array(
            'class' => 'template_new_name'
        ))->setLabel('oder als neue Vorlage speichern');
        $fsConfig->addStatic(null, array(
            'class' => 'savetemplate_ctrl'
        ))
            ->setContent('<span class="genericon genericon-checkmark"></span>Vorlage speichern')
            ->setTagName('a');
        $fsConfig->addStatic(null, array(
            'class' => 'loadtemplate_ctrl'
        ))
            ->setContent('<span class="genericon genericon-refresh"></span>Vorlage aktualisieren')
            ->setTagName('a');
        $fsConfig->addStatic(null, array(
            'class' => 'templateAction'
        ))->setTagName('span');

        $db_col_options = array(
            'null' => '--',
            'code' => 'AKZ',
            'ikz' => 'IKZ',
            'vorhaben' => 'Vorhaben Nummer',
            'vin' => 'VIN'
        );
        $fsConfig->addSelect('db_col', array(
            'class' => 'db_col'
        ), array(
            'options' => $db_col_options
        ))->setLabel('Fahrzeug Eingenschaft wählen');

        $fsConfig->addElement('text', 'startval', array(
            'class' => ' '
        ))->setLabel('Von :');
        $fsConfig->addElement('text', 'endval', array(
            'class' => ''
        ))->setLabel('Bis :');
        $fsConfig->addHidden('action', array(
            'value' => 'saveexportcsv',
            'class' => ''
        ));
        $fsConfig->addSubmit('', array(
            'value' => 'Exportieren'
        ));

    }


    function genPdfLink($vehicles)
    {

        $fsConfig = $this->form->addFieldset(null, array(
            'class' => 'columns twelve inline_elements'
        ));
        $vehicleids = array();
        foreach ($vehicles as $vehicle) {
            foreach ($vehicle as $key => $val) 
            {
                $fsConfig->addHidden('v' . $vehicle['vehicle_id'] . '_' . $key)->setValue($val);
            }
            $vehicleids[] = $vehicle['vehicle_id'];
        }
        $fsConfig->addHidden('vehicle_ids')->setValue(implode(',', $vehicleids));
        $fsConfig->addSubmit('genpdfsubmit', array(
            'value' => 'Herunterladen'
        ));

    }


    function genLieferschein($vehicles)
    {

        $fsConfig = $this->form->addFieldset(null, array(
            'class' => 'columns twelve inline_elements'
        ));
        $vehicleids = array();
        foreach ($vehicles as $vehicle) {
            foreach ($vehicle as $key => $val) 
            {
                $fsConfig->addHidden('v' . $vehicle['vehicle_id'] . '_' . $key)->setValue($val);
            }
            $vehicleids[] = $vehicle['vehicle_id'];
        }
        $fsConfig->addHidden('vehicle_ids')->setValue(implode(',', $vehicleids));
        $fsConfig->addSubmit('genpdfsubmit', array(
            'value' => 'Herunterladen'
        ));

    }


    // function exportPDFoptions($action)
    // {

    //     $fsConfig = $this->form->addFieldset(null, array(
    //         'class' => 'columns twelve inline_elements'
    //     ));
    //     $db_col_options = array(
    //         'null' => '--',
    //         'code' => 'AKZ',
    //         'ikz' => 'IKZ',
    //         'vorhaben' => 'Vorhaben Nummer',
    //         'vin' => 'VIN'
    //     );
    //     $fsConfig->addSelect('db_col', array(
    //         'class' => 'db_col'
    //     ), array(
    //         'options' => $db_col_options
    //     ))->setLabel('Fahrzeug Eingenschaft wählen');

    //     $fsConfig->addElement('text', 'startval', array(
    //         'class' => ' '
    //     ))->setLabel('Von :');
    //     $fsConfig->addElement('text', 'endval', array(
    //         'class' => ''
    //     ))->setLabel('Bis :');
    //     $fsConfig->addHidden('action', array(
    //         'value' => $action
    //     ));
    //     $fsConfig->addSubmit(null, array(
    //         'value' => 'Exportieren'
    //     ));

    // }

    function exportXMLoptions($action)
    {
        $fsConfig=$this->form->addFieldset(null,array('class'=>'columns twelve inline_elements') );
        $auth_signatory=array('Ralf Steffes, CEO'=>'Ralf Steffes, CEO','Ulrich Stuhec, CTO'=>'Ulrich Stuhec, CTO','Arndt Stegmann, CFO'=>'Arndt Stegmann, CFO');
        $db_col_options=array('vin'=>'VIN','vehicle_configuration'=>'Vehicle configuration');
        $fsConfig->addSelect('db_col',array('class'=>'db_col'),array('options'=>$db_col_options))->setLabel('Fahrzeug Eingenschaft wählen');
        
        $fsConfig->addElement('text','startval',array('class'=>'startval', 'required'=>'required'))->setLabel('VIN ');
        // $fsConfig->addElement('text','endval',array('class'=>'endval', 'required'=>'required'))->setLabel('Bis ');
        $fsConfig->addElement('text','cocdate',array('class'=>'cocdate', 'placeholder'=>'TT.MM.JJJJ', 'maxlength'=>'10', 'size'=>'12', 'required'=>'required'))->setLabel('Datum in CoC ');
        $fsConfig->addSelect('auth_signatory',array('class'=>'db_col'),array('options'=>$auth_signatory))->setLabel('Autorisiert unterzeichner');
        $fsConfig->addHidden('action',array('value'=>$action));
        $fsConfig->addSubmit(null,array('value'=>'Exportieren'));
    }

    function exportPDFoptions($action)
    {
        $fsConfig=$this->form->addFieldset(null,array('class'=>'columns twelve inline_elements') );
        $db_col_options=array('vin'=>'VIN','code'=>'AKZ','ikz'=>'IKZ','vorhaben'=>'Vorhaben Nummer');
        $fsConfig->addSelect('db_col',array('class'=>'db_col'),array('options'=>$db_col_options))->setLabel('Fahrzeug Eingenschaft wählen');
        
        $fsConfig->addElement('text','startval',array('class'=>'startval', 'required'=>'required'))->setLabel('Von :');
        $fsConfig->addElement('text','endval',array('class'=>'endval', 'required'=>'required'))->setLabel('Bis :');
        $fsConfig->addHidden('action',array('value'=>$action));
        $fsConfig->addSubmit(null,array('value'=>'Exportieren'));
    }

    function exportCOCoptions($action, $person_designation)
    {

        $fsConfig = $this->form->addFieldset(null, array(
            'class' => 'columns twelve inline_elements'
        ));

        $fsConfig->addElement('hidden', 'db_col')->setValue('vin');
        $fsConfig->addElement('text', 'startval', array(
            'class' => ' '
        ))->setLabel('Von VIN :');
        $fsConfig->addElement('text', 'endval', array(
            'class' => ''
        ))->setLabel('Bis VIN:');
        $num_seats = array(
            'einsitz' => 'Einsitzer',
            'zweisitz' => 'Zweisitzer'
        );
        $fsConfig->addSelect('sitzer', null, array(
            'options' => $num_seats
        ))->setLabel('Sitzplatz');
        $aufbau = array(
            'koffer' => 'Koffer',
            'fahrgestell' => 'Fahrgestell'
        );
        $fsConfig->addSelect('aufbau', null, array(
            'options' => $aufbau
        ))->setLabel('Aufbau');
        $person_designation_options = array_combine(array_keys($person_designation), array_column($person_designation, 'person'));
        $fsConfig->addSelect('person_designation', null, array(
            'options' => $person_designation_options
        ))->setLabel('Unterschrift berechtigte Person');
        $fsConfig->addElement('text', 'startcoc', array(
            'class' => ''
        ))->setLabel('Fortlaufende Nummer : ');

        $fsConfig->addHidden('action', array(
            'value' => $action
        ));
        $fsConfig->addSubmit(null, array(
            'value' => 'Exportieren'
        ));

    }


    function selectPvsSopCombo($allowedCombos, $defaultVal, $zsp)
    {

        $fsConfig = $this->form->addFieldset(null, array(
            'class' => 'columns twelve inline_elements'
        ));

        foreach ($allowedCombos as $combo) {
            $attributes = array(
                'value' => $combo['val']
            );
            if ($defaultVal == $combo['val'])
                $attributes['checked'] = 'checked';
            $fsConfig->addRadio('pvs_sop_combo', $attributes)->setLabel($combo['label']);
        }

        $fsConfig->addHidden('action', array(
            'value' => 'savecombo',
            'class' => ''
        ));
        $fsConfig->addHidden('zsp', array(
            'value' => $zsp
        ));
        $fsConfig->addSubmit('saveAllowedCombo', array(
            'id' => '',
            'value' => 'Speichern'
        ));

    }


    function qform_delivery_sales_filter()
    {

        setlocale(LC_TIME, "de_DE.UTF-8");
        for ($i = 0; $i < 3; $i ++) {
            $thistime = strtotime("- $i months");
            $months[date('Y-m-01', $thistime)] = strftime('%B %Y', $thistime);
        }

        $fsDiv = $this->form->addElement('fieldset');
        $group = $fsDiv->addGroup('', array(
            'id' => 'withLabel'
        ));
        $select = $fsDiv->addSelect('yearmonth', array(
            'class' => ''
        ), array(
            'options' => $months
        ))->setLabel("Monat und Jahr auswählen<br>");
        if (isset($_POST['yearmonth']))
            $select->setValue($_POST['yearmonth']);
        else
            $select->setValue(date('Y-m-01'));
        $delivered_select = $fsDiv->addSelect('delivered_filter', array(
            'class' => ''
        ), array(
            'options' => array(
                'delivered' => 'Ausgeliefert',
                'undelivered' => 'Noch nicht ausgeliefert'
            )
        ))->setLabel("Ausgelieferte Fahrzeuge? <br>");
        if (isset($_POST['delivered_filter']))
            $delivered_select->setValue($_POST['delivered_filter']);
        else
            $delivered_select->setValue('undelivered');
        $fsDiv->addHidden('action')->setValue('delivery');
        $fsDiv->addSubmit("save_month_select", array(
            'value' => "Auswählen"
        ));

    }


    function getVehiclesToDeliverForm()
    {

        $fsOuter = $this->form->addElement('fieldset');
        $fsOne = $fsOuter->addFieldset(null, array(
            'class' => 'columns four inline_elements'
        ));
        $fsOne->addElement('staticNoLabel')
            ->setContent('1. Auszulieferende Fahrzeuge auswählen')
            ->setTagname('h3');

        $fsTwo = $fsOuter->addFieldset(null, array(
            'class' => 'columns four inline_elements'
        ));
        $fsTwo->addElement('staticNoLabel')
            ->setContent('2. Auslieferungsdatum setzen')
            ->setTagname('h3');
        $grp = $fsTwo->addGroup('');
        $grp->addElement('text', 'date_selector', array(
            'class' => 'date_selector_sales_new '
        ));
        $grp->addElement('static', '', array(
            'class' => 'date_selector_sales_new_set',
            'style' => 'margin-left: 20px'
        ))
            ->setContent('Auslieferungsdatum setzen')
            ->setTagname('a');

        $fsThree = $fsOuter->addFieldset(null, array(
            'class' => 'columns four inline_elements'
        ));

        $fsThree->addElement('staticNoLabel')
            ->setContent('3. Möchten Sie die Emails an FPS/FPV schicken?')
            ->setTagname('h3');
        $fsThree->addElement('checkbox', 'send_notification_emails', array(
            'checked' => 'checked'
        ))->setContent('Ja, Emails an FPS,FPV schicken!');
        $fsThree->addSubmit('saveDeliverySubmit', array(
            'id' => '',
            'value' => 'Speichern'
        ));
        $fsOuter = $this->form->addElement('fieldset');

        $fsOuter->addHidden('action', array(
            'value' => 'saveDeliveryDateNew'
        ));
        $fsOuter->addHidden('deliver_vehicles', array(
            'id' => 'deliver_vehicles_list'
        ));
        $fsOuter->addHidden('deliver_vehicles_date', array(
            'id' => 'deliver_vehicles_date'
        ));

    }


    /**
     * *
     * used in SalesController.class.php for the 5)Auslieferung option
     * generates a list of vehicles to be delivered in a table
     * allows to set the delivery_date and send emails to FPS/FPV with information on delivery_date of vehicle
     * generates the required PDF for the Auslieferung
     *
     * @param array $deliveryToDivisionResults
     */
    function getVehiclesToDeliver($deliveryToDivisionResults)
    {

        $fsOuter = $this->form->addElement('fieldset');
        $fs = $fsOuter->addFieldset(null, array(
            'class' => 'columns eight inline_elements',
            'style'
        ));

        if (! empty($deliveryToDivisionResults)) {
            $fs->addElement('staticNoLabel', null, null, array(
                'content' => '<table id="ajax_delivery_print" style="overflow-y: min-height: 600px; scroll; display: block">'
            ));
            $setall = '<a href="" class="set_all_checks"><span class="genericon genericon-checkmark"></span><span>alle</span></a><br>';
            $clearall = '<a href="" class="clear_all_checks"><span class="genericon genericon-close"></span><span>alle</span></a>';
            $fs->addElement('staticNoLabel')->setContent('<thead><tr><th>VIN</th><th>AKZ</th><th>ZSP</th><th>Auslieferungswoche</th><th>Anlieferungsdatum</th><th>AfterSales/Produktion</th><th>' . $setall . $clearall . '</th>
					<th>Ausgeliefert?</th><th>Fahrzeug zurücksetzen</th><th>Fahrzeug tauschen</th></tr></thead>');
            $fs->addElement('staticNoLabel', null, null, array(
                'content' => '<tbody>'
            ));

            foreach ($deliveryToDivisionResults as $vehicle) {

                $group = $fs->addGroup('', array(
                    'id' => 'tableit'
                ));
                $group->addElement('staticNoLabel')->setContent($vehicle['vin']);
                $group->addElement('staticNoLabel')->setContent($vehicle['code']);
                $group->addElement('staticNoLabel')->setContent($vehicle['depot_name'] . '(' . $vehicle['dp_depot_id'] . ')');
                $group->addElement('staticNoLabel')->setContent(strtoupper($vehicle['delivery_week']));

                $delivery_date_input = $group->addElement('text', 'deliverydate[' . $vehicle['vehicle_id'] . ']', array(
                    'id' => 'delivery_date_' . $vehicle['vehicle_id']
                ));
                if (isset($vehicle['delivery_date']))
                    $delivery_date_input->setValue(date('d.m.Y', strtotime($vehicle['delivery_date'])));
                if (isset($vehicle['production_date']) && isset($vehicle['qs_user'])) {
                    if ($vehicle['qs_user'] == - 1)
                        $group->addElement('staticNoLabel')->setContent('Aftersales');
                    else
                        $group->addElement('staticNoLabel')->setContent('Produktion');
                } else
                    $group->addElement('staticNoLabel')->setContent('Aftersales');

                $group->addElement('checkbox', 'save_delivery_ctrl' . $vehicle['vehicle_id'], array(
                    'class' => 'save_delivery_date_ctrl',
                    'data-vehicleid' => $vehicle['vehicle_id']
                ))->setLabel('wählen');
                if (isset($vehicle['delivery_status']) && $vehicle['delivery_status'] == 't')
                    $group->addElement('staticNoLabel')->setContent('Ja');
                else
                    $group->addElement('staticNoLabel')->setContent(' ');
                // if($_SESSION['sts_username']=='Sts.Sales')
                $group->addElement('staticNoLabel')->setContent('<a href="?action=fahrzeug_zuruck&vehicle_id=' . $vehicle['vehicle_id'] . '" class="require_confirm" data-confirmtxt="Fahrzeug wird wieder ins Produktion/Aftersales zurückgenommen und wird wieder in QS geprüfte Fahrzeuge auftauchen.">Zurücksetzen</a>');
                $group->addElement('staticNoLabel')->setContent('<a href="?action=fahrzeug_tauschen&vehicle_id=' . $vehicle['vehicle_id'] . '" >Tauschen</a>');
            }
            $fs->addElement('staticNoLabel', null, null, array(
                'content' => '</tbody></table>'
            ));

            $fsTwo = $fsOuter->addFieldset(null, array(
                'class' => 'columns three inline_elements'
            ));

            $fsTwo->addElement('text', 'date_selector', array(
                'class' => 'date_selector_sales_new'
            ));
            $fsTwo->addElement('static', '', array(
                'class' => 'date_selector_sales_new_set'
            ))
                ->setContent('Auslieferungsdatum setzen')
                ->setTagname('a');
            $fsTwoSub = $fsTwo->addFieldset(null, array(
                'style' => 'padding-left: 1em'
            ));
            $fsTwoSub->addElement('checkbox', 'send_notification_emails', array(
                'checked' => 'checked',
                'style' => 'margin-left: 20px'
            ))->setLabel(' Emails an FPS,FPV schicken?');
            $fs->addHidden('action', array(
                'value' => 'saveDeliveryDateNew'
            ));
            $fsTwoSub->addSubmit('saveDeliverySubmit', array(
                'id' => '',
                'value' => 'Speichern'
            ));
        } else
            $fs->addElement('staticNoLabel')->setContent('<strong>Keine auszulieferende Fahrzeuge gefunden!</strong>');

    }


    function getSalesVehicleExchangeForm($vehicle, $eol_vehicles, $action, $return_to)
    {

        $fsOuter = $this->form->addElement('fieldset');
        $fsOne = $fsOuter->addFieldset(null, array(
            'class' => 'columns four inline_elements',
            'style'
        ));
        $fsTwo = $fsOuter->addFieldset(null, array(
            'class' => 'columns four inline_elements',
            'style'
        ));
        $options = array();
        foreach ($eol_vehicles as $eol_vehicle) {
            $options[$eol_vehicle['vehicle_id']] = $eol_vehicle['vin'] . '/' . $eol_vehicle['code'];
        }
        $fsOne->addStatic('')
            ->SetContent($vehicle['vin'] . '/' . $vehicle['code'])
            ->setLabel('Fahrzeug');
        $fsTwo->addSelect('exchange_vehicle', null, array(
            'options' => $options
        ))->setLabel('Tauschen mit');
        $fsOuter = $this->form->addElement('fieldset');
        $fsOuter->addHidden('vehicle_id', array(
            'value' => $vehicle['vehicle_id']
        ));
        $fsOuter->addHidden('action', array(
            'value' => $action
        ));
        $fsOuter->addHidden('return_to', array(
            'value' => $return_to
        ));
        $fsOuter->addSubmit('save_vehicle_exchange', array(
            'value' => 'Tauschen'
        ));

    }


    function genThirdPartyDelivery($order, $vehicles)
    {

        $fs = $this->form; // ->addFieldset(null,array('class'=>'columns eight inline_elements','style') );
        $group = $fs->addGroup('', array(
            'id' => 'tableit'
        ));
        $group->addElement('staticNoLabel')->setContent($order['order_num']);
        $group->addElement('staticNoLabel')->setContent($order['delivery_date']);
        $group->addElement('staticNoLabel')->setContent($order['vehicle_variant_label']);
        $group->addElement('staticNoLabel')->setContent($order['vehicle_color']);
        $group->addElement('staticNoLabel')->setContent($order['name']);
        $group->addElement('staticNoLabel')->setContent($order['street'] . ' ' . $order['housenr'] . ' ' . $order['place'] . ' ' . $order['postcode']);
        $group->addElement('staticNoLabel')->setContent($order['pr_contact']);
        $group->addElement('staticNoLabel')->setContent($order['pr_tel']);
        $group->addSelect('vehicle_id', array(
            'class' => 'thirdparty_vehicle_select'
        ), array(
            'options' => $vehicles
        ));
        $group->addHidden('action', array(
            'value' => 'save_thirdparty_vehicle'
        ));
        $group->addElement('staticNoLabel')->setContent($order['vehicle_delivered']);
        $group->addHidden('order_num', array(
            'value' => $order['order_num']
        ));
        $group->addSubmit('saveThirdPartyDelivery', array(
            'id' => '',
            'value' => 'Ausliefern'
        ));

    }


    function manual_delivery_form($depot, $division_name, $division, $available_vehicle_count)
    {

        $variant_count = array();
        if (! empty($available_vehicle_count))
            foreach ($available_vehicle_count as $variant) {
                $variant_count[$variant['vehicle_variant']] = $variant['vcnt'];
            }
        // $fs=$this->form->addFieldset(null,array('class'=>'columns twelve inline_elements','style') );
        // $division_string='<strong>Niederlassung '.$division_name.'</strong> ('.$division['delivery_week'].') Noch auszulieferende Fahrzeuge : '.($division['delivery_quantity']-$division['vehicles_delivered_quantity']).'<br>';
        // $fs->addElement('staticNoLabel',null,null,array('content'=>$division_string));
        // $fs->addElement('staticNoLabel',null,null,array('content'=>'<br><table id="">')); //<th>Auslieferungsdatum</th>
        // $fs->addElement('staticNoLabel')->setContent('<thead><tr><th>ZSP</th><th>Fahrzeug Variante</th><th>Freie Ladesäulen</th><th>Anzahl der auszulieferende Fahrzeuge</th><th></th></tr></thead>');
        // $fs->addElement('staticNoLabel',null,null,array('content'=>'<tbody>'));
        // foreach($depots as $depot)
        // {
        // $group=$fs->addGroup('',array('id'=>'tableit'));
        // $group->addElement('staticNoLabel')->setContent($depot['depname']);
        // $group->addElement('staticNoLabel')->setContent($depot['vehicle_variant_value_allowed_label']);
        // $group->addElement('staticNoLabel')->setContent($depot['scnt']);
        // $group->addElement('number','count_vehicles');
        // $group->addHidden('action',array('value'=>'save_manuell_auslieferung'));
        // $group->addHidden('variant_value',array('value'=>$depot['vehicle_variant_value_allowed']));
        // $group->addHidden('zsp',array('value'=>$depot['depot_id']));
        // $group->addHidden('delivery_id',array('value'=>$depot['delivery_id']));
        // if(isset($variant_count[$depot['vehicle_variant_value_allowed']]) && $variant_count[$depot['vehicle_variant_value_allowed']]!=0)
        // $group->addSubmit('saveDeliverySubmit',array('id'=>'','value'=>'Ausliefern'));
        // else
        // $group->addElement('staticNoLabel')->setContent('Nicht genug Fahrzeuge vorhanden!');
        // }

        // $fs->addElement('staticNoLabel',null,null,array('content'=>'</tbody></table><br>'));

        // one depot, one button
        $fs = $this->form; // ->addFieldset(null,array('class'=>'columns eight inline_elements','style') );

        $group = $fs->addGroup('', array(
            'id' => 'tableit'
        ));
        $group->addElement('staticNoLabel')->setContent($depot['depname']);
        $group->addElement('staticNoLabel')->setContent($depot['vehicle_variant_value_allowed_label']);
        $group->addElement('staticNoLabel')->setContent($depot['scnt']);
        $group->addElement('number', 'count_vehicles')->setValue('');
        $group->addHidden('action', array(
            'value' => 'save_manuell_auslieferung'
        ));
        $group->addHidden('variant_value', array(
            'value' => $depot['vehicle_variant_value_allowed']
        ));
        $group->addHidden('zsp', array(
            'value' => $depot['depot_id']
        ));
        $group->addHidden('delivery_id', array(
            'value' => $depot['delivery_id']
        ));
        $group->addSubmit('saveDeliverySubmit', array(
            'id' => '',
            'value' => 'Ausliefern'
        ));

    }


    function vehicles_deliver_variant_select($vehicle_variants)
    {

        $fs = $this->form->addElement('fieldset');
        foreach ($vehicle_variants as $vehicle_variant_value => $vehicle_variant) {
            /*
             * $checkbox=$fs->addElement('checkbox','vehicle_variants[]',array('value'=>$vehicle_variant_value))->setLabel($vehicle_variant);
             * if(strpos($vehicle_variant,'B16')!==false)
             * $checkbox->setAttribute('checked');
             * else
             * $checkbox->removeAttribute('checked');
             */
        }
        $fs->addElement('select', 'vehicle_variant', null, array(
            'options' => $vehicle_variants
        ))->setLabel('Fahrzeuge Variante auswählen');
        $fs->addHidden('action')->setValue('auto_fahrzeuge_zuweisen');
        $fs->addSubmit('save_vehicle_variant', array(
            'value' => 'Wählen'
        ));

    }


    function qform_vehicles_deliver_request($action, $processedDivs = null, $productionLocations = null)
    {

        $fs = $this->form->addElement('fieldset');
        $fs->addElement('text', 'date_selector_delivery', array(
            'class' => 'date_selector_sales_new'
        ))->setLabel('Anlieferungsdatum wählen');
        $fs->addElement('number', 'count_vehicles')->setLabel('Anzahl der zuzuweisende Fahrzeuge');
        if (! empty($productionLocations)) {
            $fs->addElement('select', 'selected_production', array(
                'class' => 'production_loc'
            ), array(
                'options' => $productionLocations
            ))->setLabel('Produktionsstandort wählen');
            $fs->addStatic()->setContent('*Nicht zugewiesen = Aachen, Sts_Pool = Würselen');
        }
        if (! empty($processedDivs))
            $fs->addElement('select', 'selected_div', null, array(
                'options' => $processedDivs
            ))->setLabel('Niederlassung wählen');
        $fs->addHidden('action')->setValue($action);
        $fs->addHidden('exclude_vehicles', array(
            'class' => 'exclude_vehicles'
        ))->setValue('');
        $fs->addSubmit('save_request', array(
            'id' => '',
            'value' => 'Bestätigen'
        ));

    }


    /**
     * Generates the form for the PPS Role 'Transporter Anfrage Datum' function
     *
     * @param array $transporters_list
     */
    function set_transporter_date($transporters_list)
    {

        $fs = $this->form->addFieldset();
        $fs->addSelect('transporter_id', array(
            'class' => 'transporter_id'
        ), array(
            'options' => $transporters_list
        ))->setLabel('Spediteur');
        $fs->addText('transporter_order_date', array(
            'class' => 'transporter_order_date'
        ))
            ->setValue(date('d.m.Y'))
            ->setLabel('Angefragt am');
        $fs->addSubmit('save_request', array(
            'id' => 'save_transporter_order_date',
            'value' => 'Speichern '
        ));

    }


    /**
     *
     * @param mixed $vehicles
     * @param mixed $workshops
     */
    function gen_workshop_delivery($vehicles, $workshops, $workshop_deliveries)
    {

        $workshop_delivery_ids = array();
        $fsDiv = $this->form->addFieldset();
        $fsOne = $fsDiv->addElement('fieldset', null, array(
            'class' => 'columns four inline_elements'
        ));
        $fsTwo = $fsDiv->addElement('fieldset', null, array(
            'class' => 'columns four inline_elements'
        ));

        $fsOne->addSelect('vehicle', array(
            'class' => 'init_combobox vehicle_workshop_select'
        ), array(
            'options' => $vehicles
        ))->setLabel('Fahrzeug wählen');

        $group = $fsTwo->addGroup('');
        $group->addSelect('workshop', array(
            'class' => 'init_combobox workshop_select'
        ), array(
            'options' => $workshops
        ))->setLabel('Werkstatt wählen');
        $group->addElement('staticNoLabel')->setContent('<a href="#" class="save_assign_workshop" style="margin-left: 60px"><span class="genericon genericon-checkmark"></span>Zuweisen</a>');
        $fs = $this->form->addFieldset();
        $fs->addElement('staticNoLabel', null, null, array(
            'content' => '<br><table id="workshop_assigned_vehicles">'
        ));
        $fs->addElement('staticNoLabel', null, null, array(
            'content' => '<tr><th>IKZ</th><th>VIN</th><th>AKZ</th><th>Werkstatt</th><th>Anlieferungsdatum</th><th>ZSP</th><th>Auswählen</tr>'
        ));
        foreach ($workshop_deliveries as $delivery) {
            $group = $fs->addGroup('', array(
                'id' => 'tableit'
            ));
            $group->addStatic()->setContent($delivery['ikz']);
            $group->addStatic()->setContent($delivery['vin']);
            $group->addStatic()->setContent($delivery['code']);
            $group->addStatic()->setContent($delivery['wname'] . ',' . $delivery['street'] . ',' . $delivery['location'] . ' ' . $delivery['zip_code']);
            $group->addStatic()->setContent($delivery['delivery_date']);
            $group->addStatic()->setContent($delivery['dname']);
            $group->addCheckbox('select_vehicle_' . $delivery['workshop_delivery_id'])->setContent('auswählen');
            $workshop_delivery_ids[] = $delivery['workshop_delivery_id'];
        }

        $fs->addElement('staticNoLabel', null, null, array(
            'content' => '</table>'
        ));
        $fs->addText('workshop_delivery_date', array(
            'class' => 'workshop_delivery_date'
        ))
            ->setValue(date('d.m.Y'))
            ->setLabel('Anlieferdatum');
        $fs->addHidden('workshop_delivery_ids', array(
            'id' => 'workshop_delivery_ids'
        ))->setValue(implode(',', $workshop_delivery_ids));
        $fs->addHidden('action')->setValue('print_workshop_delivery');
        $fs->addSubmit('save_request', array(
            'id' => 'assign_workshop',
            'value' => 'Drucken'
        ));

    }


    /**
     * printContent Prints the form content
     */
    public function printContent()
    {

        $this->form->render($this->renderer);

        echo $this->renderer;

    }


    /**
     * formValidate Validates the form and returns true or false
     */
    public function formValidate()
    {

        if ($this->form->validate()) {
            return true;
        } else {
            return false;
        }

    }


    /**
     * getContent Returns the form content
     */
    public function getContent()
    {

        $this->form->render($this->renderer);

        return $this->renderer;

    }


    /**
     * getValue
     */
    public function getValue()
    {

        return $this->form->getValue();

    }


    /**
     * toggleFrozen
     */
    public function toggleFrozen()
    {

        $this->form->toggleFrozen(true);

    }

}

?>
