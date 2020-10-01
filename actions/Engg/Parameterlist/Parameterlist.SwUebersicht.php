<!--  Parameterlist Status Übsichert  -->

<?php
$content_type = $_REQUEST['uebersicht']['table'];
$disabled['selEcu'] = ($content_type == 'ecuSwState') ? '' : ' disabled';
$selected['odxDownloadModes'] = ($content_type == 'odxDownloadModes') ? ' selected' : '';
$selected['ecuSwState'] = ($content_type == 'ecuSwState') ? ' selected' : '';

$str_odx_modes = ['X', 'file', 'DB'];
$selected_ecu = ($content_type == 'ecuSwState') ? intval($_REQUEST['uebersicht']['ecu']) : -1;

$this->m_table->setTableClass($content_type);

if ($selected_ecu == 0)
    $selected_ecu = $this->S_variantEcu;

echo $this->GetHtml_FormHeader();

$ecu_options = $this->GetHtml_SelectOptions($this->allEcuNames, $selected_ecu);

echo <<<HEREDOC

  <div class="mainframe">
    <div class="W160 uebersichtLinks">
      <div class="uebersicht-panel" id="idUebersicht-selTable">
        <div class="uebersicht-caption">
          <b>Auswahl Ansicht</b>
        </div><br>
        <select name="uebersicht[table]" class="W150" size="4" onClick="this.form.submit()">
            <option value="odxDownloadModes"{$selected['odxDownloadModes']}>ODX-Erzeugung</option>
            <option value="ecuSwState"{$selected['ecuSwState']}>SW Status je ECU</option>
        </select>
      </div>
      <br>
      <div class="uebersicht-panel" id="idUebersicht-selEcu">
        <div class="stdborder uebersicht-caption{$disabled['selEcu']}">
          <b>Auswahl ECU</b>
        </div><br>
        <select name="uebersicht[ecu]" class="W150" size="20" onClick="this.form.submit()"{$disabled['selEcu']}>
          $ecu_options
        </select>
      </div>
    </div>
    <div class="seitenteiler">
HEREDOC;

if ($content_type == 'odxDownloadModes') {
    $hEcus = array_values($this->allEcuNames);
    array_unshift($hEcus, 'Konfiguration / Ecu');


    $this->m_table->setHeight(600);
    $this->m_table->setHeader($hEcus);

    foreach ($this->DB_allVariantTypes as $vt) {
        foreach ($this->DB_indexAllVariant[$vt] as $combi_id => $variant_name) {
            sscanf($combi_id, "%d:%d", $windchill_id, $penta_id);

            $revisions = $this->QueryRevisionsFromPentaVariant($windchill_id, $penta_id);


            $this->m_table->newRow();

            $this->m_table->addCell($variant_name);

            foreach ($this->allEcuNames as $ecu_id => $ecu_name) {
                if ($revisions && isset ($revisions[$ecu_id])) {
                    $this_ecu_rev = &$revisions[$ecu_id];
                    $used = toBool($this_ecu_rev['ecu_used']);
                    $odx_mode = $this_ecu_rev['odx_download_mode'];
                    $str_odx_mode = $used ? $str_odx_modes[$odx_mode] : '- - -';
                    $str_odx_css = $used ? $str_odx_modes[$odx_mode] : 'not_used';
                } else {
                    $str_odx_mode = '- ? -';
                    $str_odx_css = 'not_used';
                }


                $this->m_table->addCell($str_odx_mode, ['class' => "ODX-$str_odx_css"]);
            }
        }
    }
    $this->m_table->WriteHtml_Content();
}

if (($content_type == 'ecuSwState') && $selected_ecu) {
    $header = ['&nbsp;', 'Odx Mode', 'Version', 'Status', 'Kopiert von'];
    $this->m_table->setHeight(600);
    $this->m_table->setHeader($header);

    foreach ($this->DB_allVariantTypes as $vt) {
        foreach ($this->DB_indexAllVariant[$vt] as $combi_id => $variant_name) {
            sscanf($combi_id, "%d:%d", $windchill_id, $penta_id);

            $revisions = $this->QueryRevisionsFromPentaVariant($windchill_id, $penta_id);


            $this->m_table->newRow();

            $this->m_table->addCell($variant_name);

            if ($revisions && isset ($revisions[$selected_ecu])) {
                $this_ecu_rev = &$revisions[$selected_ecu];

                $this->UpdateRevisionCopyState($this_ecu_rev, false);

                $used = toBool($this_ecu_rev['ecu_used']);
                $version = $this_ecu_rev['sts_version'];
                $odx_mode = $this_ecu_rev['odx_download_mode'];
                $check_ok = toBool($this_ecu_rev['parameters_check_ok']);
                $released = toBool($this_ecu_rev['parameters_released']);
                $status = $check_ok ?
                    ($released ? '<div class="status_released">RELEASED</div>'
                        : '<div class="status_prototyp">PROTOTYP</div>')
                    : '<div class="status_uncomplete">UNVOLLSTÄNDIG</div>';

                $copy_src = $this_ecu_rev['master'];
                $copy_state = "";

                if (isset ($copy_src)) {
                    if (isset ($this_ecu_rev['diff']))
                        $copy_state = "<div class=\"status_copy_ne\">&ne; $copy_src</div>";
                    else
                        $copy_state = "<div class=\"status_copy_eq\">= $copy_src</div>";
                }
            } else {
                $used = true;
                $version = '- ? -';
                $status = "";
                $odx_mode = 0;
                $copy_state = '';
            }


            // $input_odx  = "<select data-variant=\"$windchill_id\" OnChange=\"SetOdxMode($windchill_id)\">";

            $this->m_table->addCell($str_odx_modes[$odx_mode]);
            $this->m_table->addCell($version);
            $this->m_table->addCell($status);
            $this->m_table->addCell($copy_state);
        }
    }
    $this->m_table->WriteHtml_Content();
}

echo "
    </div>
  </div>
</form>
";