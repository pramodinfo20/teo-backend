<?php
/**
 * Created by PhpStorm.
 * User: fev
 * Date: 3/13/19
 * Time: 11:25 AM
 */


class VehicleConfiguration {
    const EDIT_VariantData = 1;
    const EDIT_EcuVersion = 2;
    const EDIT_NewEcuVersion = 3;
    const EDIT_CopyEcuVersion = 4;
    const EDIT_Variant = 5;
    const EDIT_NewVariant = 6;
    const EDIT_CopyVariant = 7;
    const EDIT_Privileges = 8;
    const EDIT_CopyVariantEcuData = 9;
    const EDIT_NewGlobalVariable = 10;
    const EDIT_CopyGlobalVariable = 11;
    const EDIT_DistributeCocValues = 12;
    const EDIT_DistributeSWParameters = 13;

    const OT_ViewConfigurationDetails = 0;
    const OT_EditConfiguration = 1;
    const OT_NewConfiguration = 2;
    const OT_NewConfigurationViaCopy = 3;
    const OT_RemoveConfiguration = 4;


    private $selectedConfiguration = '';
    private $operationType;
    private $isConfigVersionD1702;
    private $ladeLeitWartePtr;

    private $HTML_ViewContainer = '';
    private $configInfo;
    private $cocHidden;
    private $parts;
    private $allPartGroups;
    private $advicingParts;
    private $engeneeringParts;
    private $ecus;
    private $ecusNames;

    /**
     * VehicleConfigurations constructor.
     *
     * @param $ladeLeitWartePtr
     * @param $configurationKey
     * @param $operationType
     */
    public function __construct($ladeLeitWartePtr, $configurationKey, $operationType) {
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->operationType = $operationType;

        $this->decodeConfigurationCombiKey($configurationKey);
        $this->checkConfigurationVersion();

        $this->parts = $this->ladeLeitWartePtr->newQuery('parts')->orderBy('group_id')->get('*', 'part_id');
        $this->ecus = $this->ladeLeitWartePtr->newQuery('ecus')
            ->where('ecu_id', '>', 0)
            ->orderBy('name')
            ->get('*', 'ecu_id');
        $this->ecusNames = array_combine(array_keys($this->ecus), array_column($this->ecus, 'name'));
    }

    private function decodeConfigurationCombiKey($combiKey) {
        $vehConfArray = $this->ladeLeitWartePtr->vehicleVariantsPtr->getVehicleVariantsByCombiId($combiKey);
        $this->selectedConfiguration = $vehConfArray[0];
        $this->selectedConfiguration['windchill_part_string'] = $this->makePartString($this->selectedConfiguration['v_parts']);
        $this->selectedConfiguration['penta_part_string'] = $this->makePartString($this->selectedConfiguration['p_parts']);
    }

    private function InfoHeaderView() {
        if ($this->isConfigVersionD1702) {
            $this->infoHeaderAfterD1702();
//            $this->vehicleSpecialParts();
            $this->vehicleSpecialProperties();
            $this->displayEcuTable();
            $this->noumbersOfVehiclesTable();
        } else
            $this->infoHeaderBeforeD1702();
    }

    public function returnView() {
        $this->operationType = self::OT_ViewConfigurationDetails;
        $this->InfoHeaderView();

        return $this->HTML_ViewContainer;
    }

    public function returnEditView() {
        $this->operationType = self::OT_EditConfiguration;
        $this->InfoHeaderView();

        return $this->HTML_ViewContainer;
    }

    public function updateVehicleConfiguration($parameterArray) {

        $updatedCount = $parameterArray;

        $vehicle_variant_id = '188';

        $devStatus = $_POST['id_dev_status'];
        $variant =
            $_POST['id_body'] .
            $_POST['id_number_drive_steered_axle'] .
            $_POST['id_engine_type'] .
            $_POST['id_stage_of_completion'];
        $version =
            $_POST['id_body_length'] .
            $_POST['id_front_axle'] .
            $_POST['id_rear_axle'] .
            $_POST['id_zgg'] .
            $_POST['id_type_of_fuel'] .
            $_POST['id_traction_battery'] .
            $_POST['id_charging_system'] .
            $_POST['id_v_max'] .
            $_POST['id_seat'] .
            $_POST['id_trailer_hitch'] .
            $_POST['id_super_structure'] .
            $_POST['id_energy_supply_superstructure'];
        $option =
            $_POST['id_steering'] .
            $_POST['id_rear_window'] .
            $_POST['id_air_conditioning'] .
            $_POST['id_passenger_airbag'] .
            $_POST['id_keyless_entry_go'] .
            $_POST['id_special_application_area'] .
            $_POST['id_radio'] .
            $_POST['id_sound_generator'] .
            $_POST['id_country_code'] .
            $_POST['id_color'] .
            $_POST['id_wheeling'];


        $sqlQuery = <<<SQL
        update public.vehicle_variants 
        set variant = $variant, 
            version = $version, 
            options = $option,
            dev_status = $devStatus
        where "tempLeitwarte".public.vehicle_variants.vehicle_variant_id = $vehicle_variant_id;


SQL;

//        $this->newQuery('vehicle_variants')->query($sqlQuery);

        $this->ladeLeitWartePtr->vehicleVariantsPtr->saveConfigurationToDB();

        return 'pass';
    }

    private function checkConfigurationVersion() {
        $vehicleName = $this->selectedConfiguration['v_name'];

        $type = substr($vehicleName, 0, 1);
        $year = substr($vehicleName, 1, 2);
        $series = substr($vehicleName, 3, 2);

        if ($type == 'D' && $year >= 17 && $series >= 2) {
            $this->isConfigVersionD1702 = true;
        } else {
            $this->isConfigVersionD1702 = false;
        }
    }

    function infoHeaderAfterD1702() {
        $result = '';

        $vname = $this->selectedConfiguration['v_name'];
        $params = $this->ladeLeitWartePtr->vehicleVariantsPtr->Decode_WC_Variant_Name($vname);

        $wcparts = $this->selectedConfiguration['windchill_part_string'];

        $devStatusParameter = $this->ladeLeitWartePtr->vehicleVariantsPtr->getDevStatusCompound($this->selectedConfiguration['dev_status']);
        $variantParameter = $this->ladeLeitWartePtr->vehicleVariantsPtr->getVariantParameterCompounds($this->selectedConfiguration['v_id']);
        $versionParameter = $this->ladeLeitWartePtr->vehicleVariantsPtr->getVersionParameterCompounds($this->selectedConfiguration['v_id']);
        $optionsParameter = $this->ladeLeitWartePtr->vehicleVariantsPtr->getOptionsParameterCompounds($this->selectedConfiguration['v_id']);

        switch ($this->operationType) {
            case $this::OT_ViewConfigurationDetails:

                $vname =
                    $this->selectedConfiguration['v_name'] . ' ' .
                    '??' . ' ' .
                    $this->selectedConfiguration['variant'] . ' ' .
                    $this->selectedConfiguration['version'] . ' ' .
                    $this->selectedConfiguration['options'];

                $type = $this->selectedConfiguration['type'];
                $series = substr($vname, 3, 2);
                $devStatus = key($devStatusParameter) . ' = ' . array_values($devStatusParameter)[0];

                $body = $this->combineKeyAndValueString($variantParameter, 'body');
                $numberDriveAxle = $this->combineKeyAndValueString($variantParameter, 'numberDriveSteeredAxle');
                $engineType = $this->combineKeyAndValueString($variantParameter, 'engineType');
                $stageOfCompletion = $this->combineKeyAndValueString($variantParameter, 'stageOfCompletion');

                $bodyLength = $this->combineKeyAndValueString($versionParameter, 'lengthBody');
                $frontAxle = $this->combineKeyAndValueString($versionParameter, 'frontAxle');
                $rearAxle = $this->combineKeyAndValueString($versionParameter, 'rearAxle');
                $ZGG = $this->combineKeyAndValueString($versionParameter, 'zgg');
                $typeOfFuel = $this->combineKeyAndValueString($versionParameter, 'typeOfFuel');
                $tractionBattery = $this->combineKeyAndValueString($versionParameter, 'tractionBattery');
                $chargingSystem = $this->combineKeyAndValueString($versionParameter, 'chargingSystem');
                $vMax = $this->combineKeyAndValueString($versionParameter, 'vMax');
                $seats = $this->combineKeyAndValueString($versionParameter, 'seats');
                $trailerHitch = $this->combineKeyAndValueString($versionParameter, 'trailerHitch');
                $superstructures = $this->combineKeyAndValueString($versionParameter, 'superstructures');
                $energySupplySuperstructure = $this->combineKeyAndValueString($versionParameter, 'energySupplySuperstructure');

                $steering = $this->combineKeyAndValueString($optionsParameter, 'steering');
                $rearWindow = $this->combineKeyAndValueString($optionsParameter, 'rearWindow');
                $airConditioning = $this->combineKeyAndValueString($optionsParameter, 'airConditioning');
                $passengerAirbag = $this->combineKeyAndValueString($optionsParameter, 'passengerAirbag');
                $keylessEntryGo = $this->combineKeyAndValueString($optionsParameter, 'keylessEntryGo');
                $specialApplicationArea = $this->combineKeyAndValueString($optionsParameter, 'specialApplicationArea');
                $radio = $this->combineKeyAndValueString($optionsParameter, 'radio');
                $soundGenerator = $this->combineKeyAndValueString($optionsParameter, 'soundGenerator');
                $countryCode = $this->combineKeyAndValueString($optionsParameter, 'countryCode');
                $color = $this->combineKeyAndValueString($optionsParameter, 'color');
                $wheelings = $this->combineKeyAndValueString($optionsParameter, 'wheelings');

                break;
            case $this::OT_EditConfiguration:

                $parametersMap = $this->ladeLeitWartePtr->vehicleVariantsPtr->getMapOfAllConfigurationParameters();


                $type = 'todo';
                $series = 'todo';
                $devStatus = $this->displayOptionsforParameters($parametersMap['devStatus'], key($devStatusParameter), 'id_dev_status');

                $body = $this->displayOptionsforParameters($parametersMap['body'], key($variantParameter['body']), 'id_body');
                $numberDriveAxle = $this->displayOptionsforParameters($parametersMap['numberDriveSteeredAxle'], key($variantParameter['numberDriveSteeredAxle']), 'id_number_drive_steered_axle');
                $engineType = $this->displayOptionsforParameters($parametersMap['engineType'], key($variantParameter['engineType']), 'id_engine_type');
                $stageOfCompletion = $this->displayOptionsforParameters($parametersMap['stageOfCompletion'], key($variantParameter['stageOfCompletion']), 'id_stage_of_completion');

                $bodyLength = $this->displayOptionsforParameters($parametersMap['bodyLength'], key($versionParameter['bodyLength']), 'id_body_length');
                $frontAxle = $this->displayOptionsforParameters($parametersMap['frontAxle'], key($versionParameter['frontAxle']), 'id_front_axle');
                $rearAxle = $this->displayOptionsforParameters($parametersMap['rearAxle'], key($versionParameter['rearAxle']), 'id_rear_axle');
                $ZGG = $this->displayOptionsforParameters($parametersMap['zgg'], key($versionParameter['zgg']), 'id_zgg');
                $typeOfFuel = $this->displayOptionsforParameters($parametersMap['typeOfFuel'], key($versionParameter['typeOfFuel']), 'id_type_of_fuel');
                $tractionBattery = $this->displayOptionsforParameters($parametersMap['tractionBattery'], key($versionParameter['tractionBattery']), 'id_traction_battery');
                $chargingSystem = $this->displayOptionsforParameters($parametersMap['chargingSystem'], key($versionParameter['chargingSystem']), 'id_charging_system');
                $vMax = $this->displayOptionsforParameters($parametersMap['vMax'], key($versionParameter['vMax']), 'id_v_max');
                $seats = $this->displayOptionsforParameters($parametersMap['seat'], key($versionParameter['seat']), 'id_seat');
                $trailerHitch = $this->displayOptionsforParameters($parametersMap['trailerHitch'], key($versionParameter['trailerHitch']), 'id_trailer_hitch');
                $superstructures = $this->displayOptionsforParameters($parametersMap['superStructure'], key($versionParameter['superStructure']), 'id_super_structure');
                $energySupplySuperstructure = $this->displayOptionsforParameters($parametersMap['energySupplySuperstructure'], key($versionParameter['energySupplySuperstructure']), 'id_energy_supply_superstructure');

                $steering = $this->displayOptionsforParameters($parametersMap['steering'], key($optionsParameter['steering']), 'id_steering');
                $rearWindow = $this->displayOptionsforParameters($parametersMap['rearWindow'], key($optionsParameter['rearWindow']), 'id_rear_window');
                $airConditioning = $this->displayOptionsforParameters($parametersMap['airConditioning'], key($optionsParameter['airConditioning']), 'id_air_conditioning');
                $passengerAirbag = $this->displayOptionsforParameters($parametersMap['passengerAirbag'], key($optionsParameter['passengerAirbag']), 'id_passenger_airbag');
                $keylessEntryGo = $this->displayOptionsforParameters($parametersMap['keylessEntryGo'], key($optionsParameter['keylessEntryGo']), 'id_keyless_entry_go');
                $specialApplicationArea = $this->displayOptionsforParameters($parametersMap['specialApplicationArea'], key($optionsParameter['specialApplicationArea']), 'id_special_application_area');
                $radio = $this->displayOptionsforParameters($parametersMap['radio'], key($optionsParameter['radio']), 'id_radio');
                $soundGenerator = $this->displayOptionsforParameters($parametersMap['soundGenerator'], key($optionsParameter['soundGenerator']), 'id_sound_generator');
                $countryCode = $this->displayOptionsforParameters($parametersMap['countryCode'], key($optionsParameter['countryCode']), 'id_country_code');
                $color = $this->displayOptionsforParameters($parametersMap['color'], key($optionsParameter['color']), 'id_color');
                $wheelings = $this->displayOptionsforParameters($parametersMap['wheeling'], key($optionsParameter['wheeling']), 'id_wheeling');

                break;
            default:
                return 'error operation type';
        }


        $result .= <<<HEREDOC
        <div class="infoFrame stdborder flexibleHeight">
          <div class="infoHeader">
            <h3>ConfigurationController:</h3><h2>$vname</h2>
            <hr>
            <h3>Basic informations</h3><br>
            <span class="Label150">Type</span><span class="LabelX">:                            $type</span></br>
            <span class="Label150">Series</span><span class="LabelX">:                          $series</span></br>
            <span class="Label150">Development status</span><span class="LabelX">:              $devStatus</span>
            <hr>
            <h3>Variant</h3><br>
            <span class="Label150">Body</span><span class="LabelX">:                            $body</span></br>
            <span class="Label150">Number/drive/steered axle</span><span class="LabelX">:       $numberDriveAxle</span></br>
            <span class="Label150">Enginge type, continous power</span><span class="LabelX">:   $engineType</span></br>
            <span class="Label150">Stage of completion</span><span class="LabelX">:             $stageOfCompletion</span>
            <hr>
            <h3>Version</h3><br>
            <span class="Label150">Body length</span><span class="LabelX">:                     $bodyLength</span></br>
            <span class="Label150">Front axle</span><span class="LabelX">:                      $frontAxle</span></br>
            <span class="Label150">Rear axle</span><span class="LabelX">:                       $rearAxle</span></br>
            <span class="Label150">ZGG</span><span class="LabelX">:                             $ZGG</span></br>
            <span class="Label150">Type of fuel</span><span class="LabelX">:                    $typeOfFuel</span><br>
            <span class="Label150">Traction battery</span><span class="LabelX">:                $tractionBattery</span></br>
            <span class="Label150">Charging system</span><span class="LabelX">:                 $chargingSystem</span></br>
            <span class="Label150">V max</span><span class="LabelX">:                           $vMax</span></br>
            <span class="Label150">Seats</span><span class="LabelX">:                           $seats</span></br>
            <span class="Label150">Trailer hitch</span><span class="LabelX">:                   $trailerHitch</span><br>
            <span class="Label150">Superstructures</span><span class="LabelX">:                 $superstructures</span><br>
            <span class="Label150">Energy supply superstructure</span><span class="LabelX">:    $energySupplySuperstructure</span>
            <hr>
            <h3>Options</h3><br>
            <span class="Label150">Steering</span><span class="LabelX">:                        $steering</span></br>
            <span class="Label150">Rear Window</span><span class="LabelX">:                     $rearWindow</span></br>
            <span class="Label150">Air conditioning</span><span class="LabelX">:                $airConditioning</span></br>
            <span class="Label150">Passenger airbag</span><span class="LabelX">:                $passengerAirbag</span></br>
            <span class="Label150">Keyless entry/go</span><span class="LabelX">:                $keylessEntryGo</span><br>
            <span class="Label150">Special application area</span><span class="LabelX">:        $specialApplicationArea</span></br>
            <span class="Label150">Radio</span><span class="LabelX">:                           $radio</span></br>
            <span class="Label150">Sound generator</span><span class="LabelX">:                 $soundGenerator</span></br>
            <span class="Label150">Country code</span><span class="LabelX">:                    $countryCode</span></br>
            <span class="Label150">Color</span><span class="LabelX">:                           $color</span><br>
            <span class="Label150">Wheelings</span><span class="LabelX">:                       $wheelings</span>
            <hr>
            
            <u><b>Optional</b> (Windchill)</u>:<br>
            <div>{$wcparts}</div>
          </div>
        </div>
        <!--<div class="infoFrame stdborder flexibleHeight">-->
          <!--<div>-->
HEREDOC;

        $this->HTML_ViewContainer .= $result;
    }

    function infoHeaderBeforeD1702() {
        $result = '';

        $vname = $this->selectedConfiguration['v_name'];
        $params = $this->ladeLeitWartePtr->vehicleVariantsPtr->Decode_WC_Variant_Name($vname);
        $wcparts = $this->selectedConfiguration['windchill_part_string'];

        if (empty($wcparts))
            $wcparts = '<span class="InactiveLink">{None}</span>';

        if ($params) {
            $vname = "{$params['type']} {$params['series']} {$params['layout-key']} {$params['feature-key']} {$params['battery-key']}";
            if (empty ($params['battery'])) {
                $params['battery-key'] = '?';
                $params['battery'] = $this->GetVariantBattery();
            }
        }

        $params['series'] = '?';
        $params['layout-key'] = 'BOX';
        $params['layout'] = 'Koffer';
        $params['feature-key'] = 'A';
        $params['feature'] = 'Beifahrersitz';
        $params['battery-key'] = 'X';
        $params['battery'] = 'V4/V5';

        $vname = "<h2>: $vname</h2>";
        $series = "{$params['series']     }";
        $layout = "{$params['layout-key'] } - {$params['layout'] }";
        $feature = "{$params['feature-key']} - {$params['feature']}";
        $battery = "{$params['battery-key']} - {$params['battery']}";
        $vinmethod = "{$this->selectedConfiguration['vin_method']}";

        $result .= <<<HEREDOC
          <div class="infoBlock">
              <div class="infoFrame stdborder flexibleHeight">
                  <div class="infoHeader">
                      <h3>ConfigurationController</h3>$vname<hr>
                      <span class="Label100">Serie</span><span class="LabelX">:       $series</span></br>
                      <span class="Label100">Ausführung</span><span class="LabelX">:  $layout</span></br>
                      <span class="Label100">Merkmal</span><span class="LabelX">:     $feature</span></br>
                      <span class="Label100">Batterie</span><span class="LabelX">:    $battery</span></br>
                      <span class="Label100">VIN Methode</span><span class="LabelX">: $vinmethod</span>
                      <hr>
                      <u><b>Optionen</b> (Windchill)</u>:<br>
                      <div>{$wcparts}</div>
                  </div>
              </div>
HEREDOC;

        $this->includeTableInfo();
        $tableInfo = $this->ladeLeitWartePtr->vehicleVariantsPtr->queryColumnInfo();
        $variantId = $this->selectedConfiguration['v_id'];
        $variant = $this->ladeLeitWartePtr->vehicleVariantsPtr->newQuery()->where('vehicle_variant_id', '=', $variantId)->getOne('*');

        $result .= <<<HEREDOC
    <div class="infoBlock">
        <div class="infoFrame stdborder flexibleHeight">
            <h3>Interne Fahrzeuginformation</h3>
            <table class="variantInfo">
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Value</th>
                      </tr>
                </thead>
            <tbody>
HEREDOC;

        foreach ($this->configInfo as $column => $set) {
            if (!$set)
                continue;

            $caption = safe_val($set, 'info', $column);
            $content = $this->writeCoCValue($variant[$column], $tableInfo[$column], $set);

            $result .= <<<HEREDOC
                <tr>
                    <td>$caption</td>
                    <td>$content</td>
                </tr>
HEREDOC;
        }

        $result .= <<<HEREDOC
            </tbody>
        </table>
HEREDOC;

        $this->getAdvicingVariantPartsList($this->selectedConfiguration['v_id'], $this->selectedConfiguration['p_id']);

        if (count($this->advicingParts)) {
            $result .= <<<HEREDOC
        <h3>Components Vehicle delivery note</h3>
        <table class="variantInfo">
            <thead>
                  <tr>
                      <th>Component</th>
                      <th>Use</th>
                  </tr>
            </thead>
            <tbody>
HEREDOC;

            foreach ($this->advicingParts as $groupId => &$list) {
                $group = $this->allPartGroups[$groupId];

                if (toBool($group['allow_multi'])) {
                    foreach ($list as $partId => &$set) {
                        $checked = $set['count'] ? "X" : "";
                        $result .= <<<HEREDOC
                                <tr>
                                    <td>{$set['name']}</td>
                                    <td>{$checked}</td>
                                </tr>
HEREDOC;
                    }
                } else {
                    $partId = $group['parts'][0];
                    $partName = $partId ? $this->parts[$partId]['name'] : "-- nicht benutzt --";

                    $result .= <<<HEREDOC
                    <tr>
                        <td>{$group['group_name']}</td>
                        <td>{$partName}</td>
                    </tr>
HEREDOC;
                }
            }
        }

        $result .= <<<HEREDOC
            </tbody>
        </table>
        <br>
        <span class="inactiveLink ttip">Link Baureiheninfo<span class="ttiptext">Die Baureiheninfo ist zur Zeit noch in Entwicklung<br>und wird in Kürze bereit gestellt.</span></span>
    </div>
HEREDOC;

        $result .= <<<HEREDOC
      <div class="infoFrame flexibleHeight">
          <h3>Used ECUs</h3>
          <table class="variantInfo">
              <thead>
                  <tr>
                      <th>ECU</th>
                      <th>SW-Version</th>
                      <th>Use</th>
                  </tr>
              </thead>
          <tbody>
HEREDOC;

        $revisions = $this->getAllRevisions($this->selectedConfiguration['windchill_id'], $this->selectedConfiguration['p_id']);

        foreach ($this->ecusNames as $ecuId => $name) {
            if (!isset ($revisions[$ecuId])) {
                $verwendet = sprintf('<input type="checkbox" name="ecu_used[%d]" onClick="onVariantPartChanged(true)"><span class=\"inactiveDescr\">kein Eintrag</span>', $ecuId);
                $version = "";
            } else
                if (!isset ($revisions[$ecuId]['rev_id'])) {
                    $used = toBool($revisions[$ecuId]['ecu_used']);
                    $verwendet = sprintf('<input type="checkbox" name="ecu_used[%d]" onClick="onVariantPartChanged(true)" %s>', $ecuId, $used ? " checked" : "");
                    $version = '<span class="inactiveDescr">keine SW Version ausgewählt</span>';
                } else
                    if (toBool($revisions[$ecuId]['use_uds'])) {
                        $used = toBool($revisions[$ecuId]['ecu_used']);
                        $verwendet = sprintf('<input type="checkbox" name="ecu_used[%d]" onClick="onVariantPartChanged(true)" %s>', $ecuId, $used ? " checked" : "");
                        $version = $revisions[$ecuId]['sts_version'];
                    } else {
                        $verwendet = "<span class=\"inactiveDescr\">kein UDS</span>";
                        $version = "";
                    }

            $result .= <<<HEREDOC
              <tr>
                <td>$name</td>
                <td>$version</td>
                <td>$verwendet</td>
              </tr>
HEREDOC;
        }

        $result .= <<<HEREDOC
            </tbody>
        </table>
HEREDOC;

        $result .= <<<HEREDOC
            </div>
          </div>
HEREDOC;

        $this->HTML_ViewContainer = $result;
    }

    private function displayOptionsforParameters($parameterMapArray, $defaultValue = false, $selectId = '') {
        return '<select name="' . $selectId . '">' . $this->GetHtml_SelectOptions($parameterMapArray, $defaultValue) . '</select>';
    }

    private function combineKeyAndValueString($parameterArray, $parameterName) {
        return key($parameterArray[$parameterName]) . ' = ' . array_values($parameterArray[$parameterName])[0];
    }

    private function vehicleSpecialParts() {

        switch ($this->operationType) {
            case self::OT_ViewConfigurationDetails:
                $radio = 'todo: view';
                $roadCameraMonitor = 'todo: view';
                $rotatingBacon = 'todo: view';
                $codriverPositionPart = 'todo: view';
                $foldingCodriverSeat = 'todo: view';
                $letterbox = 'todo: view';
                $codriverSeat = 'todo: view';
                break;

            case self::OT_EditConfiguration:
                $radio = 'todo: edit';
                $roadCameraMonitor = 'todo: edit';
                $rotatingBacon = 'todo: edit';
                $codriverPositionPart = 'todo: edit';
                $foldingCodriverSeat = 'todo: edit';
                $letterbox = 'todo: edit';
                $codriverSeat = 'todo: edit';
                break;
        }

        $result = <<<HEREDOC
      <!--<div class="infoBlock">-->
        <div class="infoFrame stdborder flexibleHeight">
          <h3>Additional Key components</h3>
          <table class="variantInfo eculist">
            <thead>
              <tr><th>Parameter</th><th>Value</th></tr>
            </thead>
            <tbody>                
                <tr><td>radio</td><td>$radio</td></tr>
                <tr><td>Road camera monitor</td><td>$roadCameraMonitor</td></tr>
                <tr><td>Rotating Bacon</td><td>$rotatingBacon</td></tr>
                <tr><td>Part at codriver position</td><td>$codriverPositionPart</td></tr>
                <tr><td>Folding codriver seat</td><td>$foldingCodriverSeat</td></tr>
                <tr><td>Letterbox</td><td>$letterbox</td></tr>
                <tr><td>Codriver seat</td><td>$codriverSeat</td></tr>
            </tbody>
          </table>
        </div>
      </div>
HEREDOC;

        $this->HTML_ViewContainer .= $result;
    }


    private function vehicleSpecialProperties() {

        switch ($this->operationType) {
            case self::OT_ViewConfigurationDetails:
                $stdPlaceOfProduction = 'todo: view';
                $espFunctionality = 'todo: view';
                $tirePressureFront = 'todo: view';
                $tirePressureRear = 'todo: view';
                $Comment = 'todo: view';
                break;

            case self::OT_EditConfiguration:
                $stdPlaceOfProduction = 'todo: edit';
                $espFunctionality = 'todo: edit';
                $tirePressureFront = 'todo: edit';
                $tirePressureRear = 'todo: edit';
                $Comment = 'todo: edit';
                break;
        }

        $result = <<<HEREDOC
      <!--<div class="infoBlock">-->
        <div class="infoFrame stdborder flexibleHeight">
          <h3>Additional Key features</h3>
          <table class="variantInfo eculist">
            <thead>
              <tr><th>Parameter</th><th>Value</th></tr>
            </thead>
            <tbody>                
                <tr><td>Standard place of production</td><td>$stdPlaceOfProduction</td></tr>
                <tr><td>ESP functionality</td><td>$espFunctionality</td></tr>
                <tr><td>Tire pressure front</td><td>$tirePressureFront</td></tr>
                <tr><td>Tire pressure rear</td><td>$tirePressureRear</td></tr>
                <tr><td>Comment</td><td>$Comment</td></tr>
            </tbody>
          </table>
        </div>
      <!--</div>-->
HEREDOC;

        $this->HTML_ViewContainer .= $result;
    }


    private function GetHtml_SelectOptions($values, $default = false, $indent = -1, $disabled = []) {
        $result = "";
        $lf = (($indent >= 0) ? lf : "");
        $indent++;
        foreach ($values as $value => $caption)
            $result .= sprintf("%$indent" . 'soption value="%s"%s%s>%s = %s</option>%s', "<",
                $value,
                ($value == $default ? " selected" : ""),
                (in_array($value, $disabled) ? ' disabled' : ''),
                $value,
                $caption,
                $lf);
        return $result;
    }

    private function WriteHtml_PentaInfo() {

    }

    private function makePartString($parts) {
        $listOfIds = substr($parts, 1, -1);

        if (empty($listOfIds))
            return;

        $arrayOfParts = explode(',', $listOfIds);

        foreach ($arrayOfParts as $index => $part) {
            $arrayOfParts[$index] = $this->parts[$part]['name'];
        }

        return implode(', ', $arrayOfParts);
    }

    private function includeTableInfo() {
        if (!isset ($this->cocTableInfo)) {
            $this->cocTableInfo = [];
            $this->cocHidden = [];
            $this->configInfo = [];

            $hidden = &$this->cocHidden;
            $config = &$this->configInfo;
            $coc = &$this->cocTableInfo;
            $db = &$this->ladeLeitWartePtr;

            include $_SERVER['STS_ROOT'] . "/actions/Engg/Parameterlist/Parameterlist.coc.php";
        }
    }

    private function writeCoCValue($value, $datatype, $colInfo) {
        if ($datatype == 'boolean')
            return toBool($value) ? 'JA' : 'NEIN';

        if (isset ($colInfo['map']))
            return $colInfo['map'][$value];

        if ($datatype == 'date')
            return to_locale_date($value);

        return nl2br(htmlspecialchars($value));
    }

    private function getAdvicingVariantPartsList($variantId, $pentaId) {
        $this->advicingParts = [];
        $this->engeneeringParts = [];
        $this->allPartGroups = $this->ladeLeitWartePtr->newQuery('part_groups')->get("*", 'group_id');

        $variantParts = $this->ladeLeitWartePtr
            ->newQuery('variant_parts_mapping')
            ->where('variant_id', '=', $variantId)
            ->get_no_parse("*, 'f' as is_penta_part", 'part_id');

        $pentaParts = $this->ladeLeitWartePtr
            ->newQuery('penta_number_parts_mapping')
            ->where('penta_number_id', '=', $pentaId)
            ->get_no_parse("*, 'f' as is_penta_part", 'part_id');

        if (is_array($variantParts) && is_array($pentaParts)) {
            $usedParts = array_merge($variantParts, $pentaParts);
        } elseif (is_array($variantParts)) {
            $usedParts = $variantParts;
        } else {
            $usedParts = $pentaParts;
        }

        foreach ($this->parts as $partId => $set) {
            if (toBool($set['visible_sales']))
                $list = &$this->advicingParts;
            else
                $list = &$this->engeneeringParts;

            $groupId = safe_val($set, 'group_id', 0);

            if (!isset($list[$groupId]))
                $list[$groupId] = [];

            if (isset ($usedParts[$partId])) {
                $set['count'] = $usedParts[$partId]['count'];
                $set['is_penta_part'] = toBool($usedParts[$partId]['is_penta_part']);

                if (!isset ($this->allPartGroups[$groupId]['parts']))
                    $this->allPartGroups[$groupId]['parts'] = [];

                $this->allPartGroups[$groupId]['parts'][] = $partId;
            } else {
                $set['count'] = 0;
                $set['is_penta_part'] = false;
            }

            $list[$groupId][$partId] = $set;
        }
    }

    private function getAllRevisions($winchChillId, $pentaId) {
        $revisionsQuery = $this->ladeLeitWartePtr->newQuery('variant_ecu_revision_mapping')
            ->join('ecu_revisions', 'rev_id=ecu_revision_id', 'left join')
            ->where('variant_id', '=', $winchChillId)
            ->where('penta_id', 'IN', [$pentaId, 0])
            ->orderBy('ecu_id')
            ->orderBy('penta_id', 'desc');
        return $revisionsQuery->get_no_parse('DISTINCT ON (ecu_id) variant_ecu_revision_mapping.*,hw,sw,sts_version,request_id,response_id,href_windchill,use_uds,use_xcp,sw_profile_ok,released,ecu_revisions.timestamp_last_change as revision_last_change,info_text,version_info', 'ecu_id');
    }

    private function displayEcuTable() {

        $columnHead = '';
        $rows = '';

        switch ($this->operationType) {
            case self::OT_ViewConfigurationDetails:
                $columnHead = '<tr><th>ECU</th><th>SW Version</th></tr>';
//                todo: Remove hardcoded function parameter
                $ecuList = $this->ladeLeitWartePtr->vehicleVariantsPtr->getEcuListWithSWForConfigurationPreview(10);
                foreach ($ecuList as $item) {
                    $rows .= '<tr><td>' . $item['name'] . '</td><td>' . $item['StsPartNumber'] . '</td></tr>';
                }
                break;

            case self::OT_EditConfiguration:
                $columnHead = '<tr><th>ECU</th><th>SW Version</th><th>Included</th></tr>';
                $ecuList = $this->ladeLeitWartePtr->vehicleVariantsPtr->getAllEcuListWithAppliedInformationInConfiguration(10);

                foreach ($ecuList as $item) {
                    $included = $item['included'] == true ? 'checked' : '';
                    $swVersion = $item['included'] == true ? $item['StsPartNumber'] : '';
                    $rows .= '
                        <tr>
                            <td>' . $item['name'] . '</td>
                            <td>' . $swVersion . '</td>
                            <td><input type="checkbox" name="cb_ecu_included[]" value="' . $item['EcuID'] . '" ' . $included . '></td>
                        </tr>';
                }
                break;
        }


        $result = <<<HTML
            <div class="infoFrame stdborder flexibleHeight">
                <h3>Present ECUs</h3>
                <table class="variantInfo eculist">
                    <thead>
                        $columnHead;
                    </thead>
                    <tbody>                
                        $rows;                        
                    </tbody>
                </table>
            </div>
HTML;

        $this->HTML_ViewContainer .= $result;
    }

    private function noumbersOfVehiclesTable() {

        $result = <<<HTML
            <div class="infoFrame stdborder flexibleHeight">
                <h3>Number of vehicles</h3>
                <table class="variantInfo eculist">
                    <thead>
                        <tr>
                            <th rowspan="2">Color</th>
                            <th rowspan="2">Number of vehicles created in database</th>
                            <th rowspan="2">Number of vehicles started assembly</th>
                            <th rowspan="2">Number of vehicles end of line</th>
                            <th rowspan="2">Number of vehicles QS approved</th>
                            <th colspan="3">Number of vehicles delivered</th>
                        </tr>
                        <tr>
                            <th>Total</th>
                            <th>To Deutsche Post</th>
                            <th>To 3rd customers</th>
                        </tr>
                    </thead>
                    <tbody>                
                        <tr><td>row</td>  </tr>                      
                    </tbody>
                </table>
            </div>
HTML;

        $this->HTML_ViewContainer .= $result;
    }
}



