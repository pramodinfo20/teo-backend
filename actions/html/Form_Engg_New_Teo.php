<?php
$dtcs_tags = $log_name_tags = $hide_content = $dtcs_hidden = $log_name_hidden = $andSelect = $orSelect = $log_hidden = $log_tags = '';
?>
<div class="row">
    <div class="columns twelve">
        <div class="new_exception_dtcs quickform">
            <h2>DTC Abweicherlaubnis</h2>
            <form enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
                <fieldset class="row">
                    <fieldset class="columns five" style="min-height: 320px">
                        <p>
                            <label for="exception_name" class="label_small">Abweicherlaubnis ID:
                            </label>
                            <input name="exception_name" id="exception_name" type="text"/>
                        </p>
                        <p>
                            <label for="teo_ecu_select" class="label_small">Zutreffende ECU/DTC:</label>
                            <select name="ecu" id="teo_ecu_select">
                                <option value="">--ECU wählen--</option>
                                <?php foreach ($this->allEcus as $ecu) {
                                    $ecu = strtoupper($ecu);
                                    echo "<option value=\"$ecu\">$ecu</option>";
                                }
                                ?>
                            </select>
                            <label for="teo_dtcs_code">
                                <input type="text" id="teo_dtcs_code" value="" placeholder="DTCS Code eingeben"
                                       name="teo_dtcs_code">*<br>

                            </label>
                        </p>
                        <p>
                            <span style="color: #999">* Fahrzeuge mit diesen DTC Codes werden nach unten gegebene Teo Status Wandlung angepasst</span>
                        <p id="selected_dtcs_codes"><?php // $dtcs_hidden.$dtcs_tags ?>
                        </p>
                        <p>
                            <label for="teo_status" class="label_small">Entsprechende TEO Status Wandlung:</label>
                            <select name="teo_status">
                                <?php foreach ($this->teo_status_map as $key => $map) {
                                    echo "<option value=\"$key\">$map</option>";
                                }
                                ?>
                            </select>
                        </p>
                        <p>
                            <label for="applicable_vehicles" class="label_small">Zutreffende Fahrzeug Varianten:</label>
                            <select name="applicable_vehicles" id="applicable_vehicles" data-targetid="dtcs"
                                    class="applicable_vehicles">
                                <option value="alle">Alle Fahrzeuge</option>
                                <?php foreach ($this->availableVariantTypes as $variant) {
                                    if (preg_match('#^([A-Za-z]{1}[0-9]{2})#', $variant['name']))
                                        echo '<option value="' . $variant['name'] . '">' . $variant['name'] . '</option>';
                                }
                                ?>

                                <option value="wcvariant">Konfiguration auswählen</option>
                                <option value="byvin">Fahrzeuge nach VIN</option>
                            </select>
                        </p>
                    </fieldset>
                    <fieldset class="columns three">&nbsp;
                        <div class="variant_container_dtcs" style="display: none;"> Mit Umschalt/Strg Taste können
                            mehrere Konfigurationen ausgewählt werden<br><br>
                            <select multiple name="wcvariant_select[]" class="wcvariant_select" size="10"
                                    style="min-width: 240px">
                                <option value="">--Konfiguration auswählen--</option>
                            </select>
                        </div>
                        <div class="vin_container_dtcs" style="display: none;">
                            <label style="width: 48%; display: inline-block">Start VIN eingeben <br><input type="text"
                                                                                                           id="start_vin"
                                                                                                           name="start_vin"
                                                                                                           placeholder="Start VIN"></label>
                            <label style="width: 48%; display: inline-block"> End VIN eingeben <br><input type="text"
                                                                                                          id="end_vin"
                                                                                                          name="end_vin"
                                                                                                          placeholder="End VIN"></label><br><br>
                        </div>
                    </fieldset>
                    <fieldset class="columns three">
                        <input type="hidden" name="action" value="teoexceptions">
                        <input type="hidden" name="tab" value="1">
                        <p><label><strong>DTC Abweicherlaubnis als PDF hochladen</strong><br><br><input type="file"
                                                                                                        name="pdf_file"></label>
                        </p>
                        <p><input type="submit" name="submit_dtcs_excp" value="Hinzufügen"></p>
                    </fieldset>
                </fieldset>

            </form>
        </div>
    </div>
</div>
<div class="row">
    <div class="columns twelve">
        <div class="new_exception_log quickform">
            <h2>Log Abweicherlaubnis</h2>
            <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST" enctype="multipart/form-data">
                <fieldset class="row">
                    <fieldset class="columns five" style="min-height: 320px">
                        <p>
                            <label for="exception_name" class="label_small">Abweicherlaubnis ID:</label>
                            <input name="exception_name" id="exception_name" type="text"/>
                        </p>
                        <p>
                            <label for="log_name" class="label_small">Log Datei Eintrag:</label>
                            <input name="log_name" id="log_error_code" type="text">
                        </p>
                        <p id="selected_log_error"><?php // '.$log_hidden.$log_tags.'?>
                        </p>
                        <p>
                            <label for="teo_status" class="label_small">Entsprechende TEO Status Wandlung:</label>
                            <select name="teo_status">
                                <?php foreach ($this->teo_status_map as $key => $map) {
                                    echo "<option value=\"$key\">$map</option>";
                                }
                                ?>
                            </select>
                        </p>
                        <p>
                            <label for="applicable_vehicles" class="label_small">Zutreffende Fahrzeug Varianten:</label>
                            <select name="applicable_vehicles" id="applicable_vehicles" data-targetid="log"
                                    class="applicable_vehicles">
                                <option value="alle">Alle Fahrzeuge</option>
                                <?php foreach ($this->availableVariantTypes as $variant) {
                                    if (preg_match('#^([A-Za-z]{1}[0-9]{2})#', $variant['name']))
                                        echo '<option value="' . $variant['name'] . '">' . $variant['name'] . '</option>';
                                }
                                ?>

                                <option value="wcvariant">Konfiguration auswählen</option>
                                <option value="byvin">Fahrzeuge nach VIN</option>
                            </select>
                        </p>
                    </fieldset>
                    <fieldset class="columns three">&nbsp;
                        <div class="variant_container_log" style="display: none;"> Mit Umschalt/Strg Taste können
                            mehrere Konfigurationen ausgewählt werden<br><br>
                            <select multiple name="wcvariant_select[]" class="wcvariant_select" size="10"
                                    style="min-width: 240px">
                                <option value="">--Konfiguration auswählen--</option>
                            </select>
                        </div>
                        <div class="vin_container_log" style="display: none;">
                            <label style="width: 48%; display: inline-block">Start VIN eingeben <br><input type="text"
                                                                                                           id="start_vin"
                                                                                                           name="start_vin"
                                                                                                           placeholder="Start VIN"></label>
                            <label style="width: 48%; display: inline-block"> End VIN eingeben <br><input type="text"
                                                                                                          id="end_vin"
                                                                                                          name="end_vin"
                                                                                                          placeholder="End VIN"></label><br><br>
                        </div>
                    </fieldset>
                    <fieldset class="columns three">
                        <input type="hidden" name="log_names" id="log_names">
                        <input type="hidden" name="action" value="teoexceptions">
                        <input type="hidden" name="tab" value="1">
                        <p><label><strong>Log Abweicherlaubnis als PDF hochladen</strong><br><br><input type="file"
                                                                                                        name="pdf_file"></label>
                        </p>
                        <p><input type="submit" name="submit_log_excp" value="Hinzufügen"><br></p>
                    </fieldset>
                </fieldset>
            </form>
        </div>
    </div>
    <div class="columns five">
    </div>
</div>
</div>
