<form method="post" name="mainForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="id_Form">
    <input type="hidden" name="action" value="<?php echo $this->action; ?>">
    <input type="hidden" name="fzliste[typ][ist]" id="id_changes" value="">
    <div class="horiznt">

        <h2>Fahrzeuglisten erstellen</h2>
        <table class="sales" style="width:500px;">
            <tr>
                <th>
                    Fahrzeugliste über <select name="fzliste[typ][soll]" OnChange="SwapListType(this.value)">
                        <option value="vin">VIN</option>
                        <option value="ikz">IKz</option>
                        <option value="akz">AKz</option>
                        <option value="penta">Penta</option>
                    </select>
                </th>
                <th>
                    Vin Bereich
                </th>
                <th>
                    IKZ Bereich
                </th>
            </tr>
            <tr>
                <td rowspan="3">
                    <textarea id="id_fahrzeugliste" name="fahrzeugliste" style="width:200px; height:280px;"
                              OnInput="EnableElement('id_apply')"><?php echo $sortListe['text'] ?></textarea><br>
                    <span class="center">
	  <input type="submit" name="fzliste[command][clear]" value="Zurücksetzen">
	  <input id="id_apply" type="submit" name="fzliste[command][apply]" value="Übernehmen">
	</span>
                </td>
                <td style="height:120px;white-space:nowrap;">
                    <span class="SalesLabel"><b>Vin Typ:</b></span><input type="text" name="fzliste[vin][typ]" size="15"
                                                                          maxlength="12"
                                                                          value="<?php echo $fzliste['vin']['typ']; ?>"
                                                                          placeholder="WS5x##BAxxx1"/><br>
                    <span class="SalesLabel"><b>von:</b></span><input type="text" name="fzliste[vin][von]" size="7"
                                                                      maxlength="5"
                                                                      value="<?php echo $fzliste['vin']['von']; ?>"
                                                                      placeholder="#####"/><br>
                    <span class="SalesLabel"><b>bis:</b></span><input type="text" name="fzliste[vin][bis]" size="7"
                                                                      maxlength="5"
                                                                      value="<?php echo $fzliste['vin']['bis']; ?>"
                                                                      placeholder="#####"/><br>
                    <span class="SalesLabel"><b>&nbsp;</b></span><input type="submit" name="fzliste[command][add_vin]"
                                                                        value="&lt; hinzufügen"/>
                </td>
                <td style="height:120px;white-space:nowrap;">
                    <span class="SalesLabel"><b>von:</b></span><input type="text" name="fzliste[ikz][von]" size="20"
                                                                      maxlength="10"
                                                                      value="<?php echo $fzliste['ikz']['von']; ?>"/><br>
                    <span class="SalesLabel"><b>bis:</b></span><input type="text" name="fzliste[ikz][bis]" size="20"
                                                                      maxlength="10"
                                                                      value="<?php echo $fzliste['ikz']['bis']; ?>"/><br>
                    <span class="SalesLabel"><b>&nbsp;</b></span><input type="submit" name="fzliste[command][add_ikz]"
                                                                        value="&lt;&lt; hinzufügen"/>
                </td>
                <!--
                <td rowspan="3" style="white-space:nowrap;">
                  <span class="optionsSpc"><b>[X]</b>&nbsp;</span>Zweisitzer Umrüstung<br><span class="optionsSpc"><input type = "checkbox" name="option[6384][4]" /></span>rotating beacon<br><span class="optionsSpc"><input type = "checkbox" name="option[6384][5]" /></span>Notsitz<br><span class="optionsSpc"><input type = "checkbox" name="option[6384][6]" /></span>Beifahrersitz<br><span class="optionsSpc"><input type = "checkbox" name="option[6384][7]" /></span>Radio<br><span class="optionsSpc"><input type = "checkbox" name="option[6384][8]" /></span>Letterbox
                </td>
                 -->
            </tr>
            <tr>
                <th>
                    Penta Kennwort Bereich
                </th>
                <th>
                    Kfz Nummer Bereich
                </th>
            </tr>
            <tr>
                <td style="height:120px;white-space:nowrap;">
                    <span class="SalesLabel"><b>Typ:</b></span><input type="text" name="fzliste[penta][prefix]"
                                                                      size="12" maxlength="10"
                                                                      value="<?php echo $fzliste['penta']['prefix']; ?>"/><br>
                    <span class="SalesLabel"><b>von:</b></span><input type="text" name="fzliste[penta][von]" size="8"
                                                                      maxlength="7"
                                                                      value="<?php echo $fzliste['penta']['von']; ?>"/><br>
                    <span class="SalesLabel"><b>bis:</b></span><input type="text" name="fzliste[penta][bis]" size="8"
                                                                      maxlength="7"
                                                                      value="<?php echo $fzliste['penta']['bis']; ?>"/><br>
                    <span class="SalesLabel"><b>&nbsp;</b></span><input type="submit" name="fzliste[command][add_penta]"
                                                                        value="&lt;- hinzufügen"/>
                </td>
                <td style="height:120px;white-space:nowrap;">
                    <?php if (empty ($fzliste['akz']['letter'])) $fzliste['akz']['letter'] = '?'; ?>
                    <span class="SalesLabel">&nbsp;</span><span class="SalesLabel">&nbsp;von:</span><span
                            class="SalesLabel">&nbsp;bis:</span><br>
                    <span class="SalesLabel"><b style="font-size:120%;">BN-P&nbsp;</b><input name="fzliste[akz][letter]"
                                                                                             type="text" size="1"
                                                                                             maxlength="1"
                                                                                             value="<?php echo $fzliste['akz']['letter']; ?>"></span>
                    <input name="fzliste[akz][von]" type="text" size="4" maxlength="4" placeholder="1111"
                           value="<?php echo $fzliste['akz']['von']; ?>"/>
                    <input name="fzliste[akz][bis]" type="text" size="4" maxlength="4" placeholder="1111"
                           value="<?php echo $fzliste['akz']['bis']; ?>"/>
                    <select name="fzliste[akz][E]" style="width:40px;">
                        <option value="2"<?php if ($fzliste['akz']['E'] == 2) echo " selected" ?>>E</option>
                        <option value="1"<?php if ($fzliste['akz']['E'] == 1) echo " selected" ?>>?</option>
                        <option value="0"<?php if ($fzliste['akz']['E'] == 0) echo " selected" ?>>&nbsp;</option>
                    </select><br>
                    <span class="SalesLabel">&nbsp;</span><input type="submit" name="fzliste[command][add_akz]"
                                                                 value="&lt;= hinzufügen"/>
            </tr>
        </table>
    </div>
    <div class="horiznt">
        <div class="SalesCmds">
            <h2>Nach Fahrzeugen suchen </h2>
            <input type="submit" name="fzliste[command][go_table]" class="pager2" value="Suche über Tabelle">
        </div>
        <hr>
        <div>
            <h2>Fahrzeugvariant ändern</h2>
            <select style="width:160px;"
                    name="convert_to"><?php echo $this->GetHtmlSelectOptions_SelectVariants($_POST['convert_to']); ?></select>
            <br>
            <input type="submit" name="fzliste[command][run]" class="sales" value="ändern">
        </div>
        <hr>
        <div class="SalesCmds">
            <h2>Änderungsdatei (excel/csv)</h2>
            <input type="button" <?php echo $this->GetHtmlButtonAttributes('excel'); ?>>
        </div>

        <!--  <div style="font-size:120%;font-weight:bold;text-align:right;">
    <a href="<?php echo $_SERVER['PHP_SELF'] . "?action=" . $this->action . "&command=table"; ?>">Fahrzeuge einzelnd suchen und bearbeiten </a>
  </div>
-->
    </div>
</form>
<p>&nbsp;</p>

<div style="visibility: hidden;" id="id_data_vin"><?php echo $this->sortListe['vin']; ?></div>
<div style="visibility: hidden;" id="id_data_ikz"><?php echo $this->sortListe['ikz']; ?></div>
<div style="visibility: hidden;" id="id_data_akz"><?php echo $this->sortListe['akz']; ?></div>
<div style="visibility: hidden;" id="id_data_penta"><?php echo $this->sortListe['penta']; ?></div>
<script>EnableElement('id_apply', false);</script>
