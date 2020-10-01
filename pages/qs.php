<div class="inner_container">
    <?php
    include $_SERVER['STS_ROOT'] . "/pages/menu/qs.menu.php";
    ?>

    <div class="inner_container"></div>
    <div class="row ">
        <div class="columns six">
            <?php if (is_array($this->msgs)) echo implode('<br>', $this->msgs); ?>
        </div>
    </div>

    <div class="row ">
        <div id="qs_fehler_wrap" class="columns twelve">
            <div>
                <?php if (isset($this->qs_fault_search)) echo $this->qs_fault_search->getHtml(); ?>
            </div>
        </div>
        <div class="columns twelve">
            <?php if (isset($this->teo_search)) echo $this->teo_search->getHtml(); ?>
        </div>
        <div class="columns twelve">
            <?php echo $this->depotFilterContent; ?>
        </div>

    </div>
    <div class="row ">
        <div class="columns twelve">
            <?php
            // 			echo $this->qform_vehicles->getContent();
            $pageoptions = '
			<div class="pager">
			<a href="#" class="first"  title="Erste Seite" >Erste</a>
			<a href="#" class="prev"  title="Vorherige Seite" ><span class="genericon genericon-previous"></span>Vorherige Seite</a>
			<span class="pagedisplay"></span> <!-- this can be any element, including an input -->
			<a href="#" class="next" title="Nächste Seite" >Nächste Seite<span class="genericon genericon-next"></span></a>
			<a href="#" class="last"  title="Letzte Seite" >Letzte</a>
		
			Seite: <select class="gotoPage"></select>
			Zeile pro Seite: <select class="pagesize">
			<option value="50">50</option>
			<option value="100">100</option>
			<option value="300">300</option>
			</select>
			</div>';
            echo $pageoptions;
            ?>
            <div class="quickform">
                <form method="post" id="vehicle_fertig_status" action="index.php" novalidate="novalidate">
                    <input type="hidden" name="todays_vehicles" id="todays_vehicles-0" value="">
                    <input type="hidden" name="to_set_vehicles" id="to_set_vehicles" value="">
                    <input type="hidden" id="qs_qm_action" name="action" value="saveQS">
                    <div style="overflow-y:auto; height: 450px; position:relative;" class="wrapper">
                        <?php echo $this->qs_vehicles->getContent(); ?>
                    </div>
                    <fieldset class="row">
                        <fieldset class="columns four inline_elements" id="qfauto-3966">

                        </fieldset>
                        <fieldset class="columns four inline_elements" id="qfauto-3967">
                        </fieldset>
                        <fieldset class="columns four inline_elements" id="qfauto-3968">
                            <div class="row">
                                <div class="element">
                                    <input type="submit" value="Speichern" style="float: right; margin: 4px"
                                           name="">
                                </div>
                            </div>
                        </fieldset>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
    <div class="row ">
        <div class="columns twelve">
            <div>
                <form action="index.php" id="export_teo_form" method="POST">
                    <fieldset class="qs_faults_tab_container">
                        <legend class="collapsible"><span class="genericon genericon-expand"></span>Export als CSV
                        </legend>
                        <div class="collapsible_content" style="display: block; width: 100%">
                            <fieldset class="row">
                                <fieldset class="columns five">
                                    <legend>1. VIN oder Datum eingeben</legend>
                                    <label style="width: 48%; display: inline-block">Start VIN eingeben : <input
                                                type="text" id="start_vin" name="start_vin"
                                                placeholder="Start VIN"></label>
                                    <label style="width: 48%; display: inline-block"> End VIN eingeben : <input
                                                type="text" id="end_vin" name="end_vin"
                                                placeholder="End VIN"></label><br><br>
                                    <div style="padding-left:46%; margin: 4px 0;">-- oder --</div>
                                    <br>
                                    <label style="width: 48%; display: inline-block">Start TEO/SIA Datum : <input
                                                type="text" id="start_date" name="start_date"
                                                placeholder="Von"></label>
                                    <label style="width: 48%; display: inline-block">End TEO/SIA Datum : <input
                                                type="text" id="end_date" name="end_date" placeholder="Bis"></label><br><br>
                                    <div style="padding-left:46%; margin: 4px 0;">-- oder --</div>
                                    <br>
                                    <label style="width: 48%; display: inline-block">Start Produktionsdatum : <input
                                                type="text" id="start_prod" name="start_prod"
                                                placeholder="Von"></label>
                                    <label style="width: 48%; display: inline-block">End Produktionsdatum : <input
                                                type="text" id="end_prod" name="end_prod" placeholder="Bis"></label>
                                </fieldset>
                                <fieldset class="columns two" style="line-height: 2em">
                                    <legend>2. Spalten auswählen</legend>
                                    <?php foreach ($this->headers as $key => $header) {
                                        if (in_array($key, $this->ignore_for_export)) continue;
                                        echo '<label style="cursor: pointer;user-select: none" ><input type="checkbox" name="exportcols[]" value="' . $key . '" checked="checked" >' . $header[0] . '</label><br>';
                                    }
                                    foreach ($this->add_for_export as $key => $header) {
                                        echo '<label style="cursor: pointer;user-select: none" ><input type="checkbox" name="exportcols[]" value="' . $key . '" checked="checked" >' . $header[0] . '</label><br>';
                                    }
                                    ?>
                                </fieldset>
                                <fieldset class="columns two" style="line-height: 2em">
                                    <legend>3. Zusätzliche Filter</legend>
                                    <select name="export_filter[depots]">
                                        <option value="production">Produktion/Nacharbeit Standorte</option>
                                        <option value="all">Alle Standorte</option>
                                    </select>
                                    <br><br>
                                    <?php
                                    foreach ($this->available_status as $key => $label) {
                                        echo '<label style="cursor: pointer;user-select: none" ><input type="checkbox" name="export_filter[status][' . $key . ']" value="1" checked="checked" >' . $label . '</label><br>';
                                    } ?>

                                </fieldset>
                                <fieldset class="columns two">
                                    <legend>4. Datei Format</legend>
                                    <select name="file_format">
                                        <option value="xlsx">Office Open XML (.xlsx)</option>
                                        <option value="ods">Open Document (.ods)</option>
                                        <option value="csv">CSV (.csv)</option>
                                    </select><br><br>
                                    <input type="hidden" name="action" value="exportQsTeo">
                                    <input type="hidden" name="export_token" id="export_token"
                                           value="<?php echo $this->user->getUserId() . '_' . time(); ?>">
                                    <input type="submit" id="export_teo" value="Exportieren">
                                    <meter id="teo-export-meter" min="0" max="100"></meter>
                                    <span id="file-url"></span>
                                </fieldset>
                            </fieldset>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>
<div id="body-sn-input" style="display:none">

    <h2 id="h2-body-vin"></h2>
    <div style="margin: 10px 10px;">Body Seriennummer<br>
        <input type="hidden" name="vehicle_id" id="body-vehicle_id" value="">
        <input type="hidden" name="previous" id="body-previous" value="">
        <input type="text" id="body-sn" size="18" maxlength="32" value="">
    </div>
    <div style="margin: 10px 10px;">Datum des Einbaus<br>
        <input type="text" id="body-date" size="10" maxlength="10" value="<?php echo date('d.m.Y'); ?>">
    </div>
    <div style="margin: 20px 10px;">
        <button id="body-submit">Seriennummer speichern</button>
    </div>
</div>
<div id="dialog-form"></div>


