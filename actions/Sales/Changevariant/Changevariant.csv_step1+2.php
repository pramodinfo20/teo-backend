<form method="post" enctype="multipart/form-data" name="mainForm" action="<?php echo $_SERVER['PHP_SELF']; ?>"
      id="id_Form">
    <input type="hidden" id="id_command" name="command" value=""/>
    <input type="hidden" name="action" value="<?php echo $this->action; ?>"/>


    <div style="position:relative;top:0px;left:10px">
        <div style="width:100%">
            <?php
            switch ($this->csvTool->step) {
                case 1:
                    echo '<h2 class="horiznt">Überprüfen Sie bitte die Zuordung der Spalten!</h2><br>';
                    break;
                case 2:
                    echo '<h2 class="horiznt">Fahrzeugdaten werden geändert!</h2><span class="SalesLabel">&nbsp;</span>Als Datensatz-Schlüssel wird die Spalte <span class="big">[' . $this->csvTool->S_identCol . ']</span> verwendet!<br>';
                    break;
            }
            ?>
        </div>
        <div class="horiznt">

            <?php
            $this->csvTool->CreateCsvValidationContent();
            echo $this->csvTool->GetHtml_ErrorBox();
            ?>
        </div>

        <div class="horiznt" style="margin-left:30px;width:320px;">

            <?php if ($this->csvTool->step == 2) { ?>

                <div class="untermahlt">
                    <h3>Fahrzeug-Farbe ändern</h3>
                </div>
                <div class="eingerueckt">
                    <div class="horiznt">
                        <input type="checkbox" name="set_color"
                               onClick="document.getElementById('id_color').disable=! this.checked">
                    </div>
                    <div class="horiznt eng">
                        Farbe für alle <br>Fahrzeuge ändern:
                    </div>
                    <div class="horiznt">
                        <select name="to_color" id="id_color"><?php
                            foreach ($this->allColors as $color_id => $Farbe) {
                                echo "<option value=\"$color_id\">$Farbe</option>";
                            } ?>
                        </select>
                    </div>
                </div>

                <div class="untermahlt">
                    <h3>Zusatzausstattung hinzufügen</h3>
                </div>
                <div class="eingerueckt">
                    <?php
                    echo $this->GetHtmlElement_CheckOptions([], [], 'addOptions', 0, "");
                    ?>
                </div>


            <?php } ?>

        </div>
    </div>
</form>
<p>&nbsp;</p>

