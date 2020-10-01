<?php
$num_types = 7; // == count ($this->DB_allVariantTypes);
$all_types = $this->GetHtml_SelectOptions($this->DB_allVariantTypes, $this->m_VariantType, 10);
$labelTr51 = "Konfiguration";
$linkNeu = ''; //' <a href="' . $this->GetHtml_Url().'&command=new_clear_variant">[neu]</a>';

if ($this->S_SearchList) {
    if (count($this->S_SearchList) > 1) {
        $selected = ($this->m_VariantType == 'suchliste') ? ' selected' : '';
        $all_types = "          <option value=\"suchliste\"$selected>Suchergebnis</option>\n" . $all_types;
        $labelTr51 = 'Suchergebnis';
    } else {
        $this->S_SearchList = null;
    }
}


$btCaptionSuchen = "suchen";

if (($this->m_EditMode == self::EDIT_CopyVariantEcuData)
    || ($this->m_EditMode == self::EDIT_DistributeCocValues)
    || ($this->m_EditMode == self::EDIT_CopyGlobalVariable)) {
    $nameSuchen = 'suche_ziel';
    $searchText = $this->m_SearchDestText;
    $labelTr32 = '<b>Zielkonfigurationen</b>';

    $Th42 = ' rowspan="3"';
    $Th32 = "";
} else {
    $nameSuchen = 'suchen';
    $searchText = $this->S_SearchText;
    $Th42 = '';
    $labelTr32 = '<b>Eigenschaften</b>';
}

$ID_Fahrzeuginfo = self::ID_Fahrzeuginfo;
$ID_Fahrzeugeigenschaften = self::ID_Fahrzeugeigenschaften;
$ID_GlobaleParameter = self::ID_GlobaleParameter;
$ID_AlleParameter = self::ID_AlleParameter;
$ID_GeraeteParameter = self::ID_GeraeteParameter;

$selected = ["", "", "", "", ""];
$selected[$this->S_ParamType] = " selected";


$ize_select = 13; //min ($ize_ecus;
$disabled = $this->m_EditMode ? " disabled" : "";
$hideParamType = $this->S_CombiID ? "" : ' style="visibility:hidden"';
$sucherror = "";

if ($this->suchFehler) {
    $suchmeldung = '<span class="errortext">' . $this->suchFehler . '</span>';
//             unset ($this->S_SearchList);
} else
    if ($this->suchMeldung) {
        $suchmeldung = '<span class="LabelXL"><b>Suche</b></span><span class="blueText">' . $this->suchMeldung . '</span>';
    } else {
        $suchmeldung = '<span class="LabelXL"><b>Suche</b></span><span class="comment">Fzg.-konfiguration, VIN oder Akz (* und ? möglich)</span>';
    }


$formheader = $this->GetHtml_FormHeader("idl_", 0, "suchform", "GET");

echo <<<HEREDOC
<div class="seitenteiler" id="idLinke">
$formheader
<table class="filtersection sales">
  <tbody>


    <tr id="filterTr1"><th colspan="2">$suchmeldung</th></tr>
    <tr id="filterTr2">
      <td class="schnellsuche" colspan="2">
        <input type="text" name="filter[suchtext]" id="suchtext_auto" value="$searchText">
        <input type="submit" name="filter[$nameSuchen]" value="$btCaptionSuchen">
      </td>
    </tr>

    <tr id="filterTr3"><th><b>Fahrzeug Typ</b></th><th>{$labelTr32}</th></tr>
    <tr id="filterTr4">
      <td>
        <select class="ParamFilterA" name="filter[variantType]" id="selectVariantType" size="$num_types"$disabled>$all_types</select>
      </td>
HEREDOC;


//----------------------------------------------------------------------
// Variantdaten koperen
// Aufbau der Liste aller Varianten die die ausgewaehle Konfiguration erhalten sollen
$printEcu = true;
if (($this->m_EditMode == self::EDIT_CopyVariantEcuData)
    || ($this->m_EditMode == self::EDIT_DistributeCocValues)
    || ($this->m_EditMode == self::EDIT_CopyGlobalVariable)) {
    echo '<td rowspan="3">';
    $printEcu = false;


    printf('        <select class="ParamFilterA" name="copyTo[]" multiple size="22">' . lf);

    // $vt = $this->m_VariantType;
    foreach ($this->DB_allVariantTypes as $vt) {
        foreach ($this->DB_indexAllVariant[$vt] as $combi_id => $windchill_name) {
            if ($combi_id == $this->S_CombiID)
                continue;

            if (isset ($this->m_SearchDest) && !isset($this->m_SearchDest[$combi_id]))
                continue;

            $set = &$this->DB_allVariants[$combi_id];
            if (($this->m_EditMode == self::EDIT_DistributeCocValues) && isset ($set['master']))
                continue;

            echo "<option value=\"$combi_id\">$windchill_name</option>";
            if (isset ($set['subvarianten'])) {
                foreach ($set['subvarianten'] as $sub_pid) {
                    $sub_set = $this->DB_allVariants[$sub_pid];
                    $penta_name = $sub_set['i_name'];
                    echo "<option value=\"$sub_pid\">$penta_name </option>";

                }
            }

        }
    }
    printf("        </select>\n");
} else {
    echo <<<HEREDOC
      <td>
        <select class="ParamFilterA" name="filter[paramType]" id="select_params" size="$num_types" OnChange="OnSelectParameterType(this)"$hideParamType$disabled>
      <!--    <option value="{$ID_Fahrzeuginfo}"{$selected[0]}>Fahrzeugeinformation</option> -->
          <option value="{$ID_Fahrzeugeigenschaften}"{$selected[1]}>COC Werte</option>
        <!--  <option value="{$ID_GlobaleParameter}"{$selected[2]}>globale Parameter</option> -->
     <!-- <option value="{$ID_AlleParameter}"{$selected[3]}>Alle ECU Parameter</option> -->
       <!--   <option value="{$ID_GeraeteParameter}"{$selected[4]}>ECU Parameter</option> -->
        </select>
HEREDOC;


    switch ($this->S_ParamType) {
        case self::ID_GeraeteParameter:
            $Th52 = "<th><b>ECU-Geräte</b></th>";
            break;

        case self::ID_GlobaleParameter:
            $Th52 = "<th><b>Globale Parameter</b></th>";
            break;

        default:
            $Th52 = "";
            break;
    }
}

echo <<<HEREDOC
      </td>
    </tr>
    <tr id="filterTr5"><th><b><span id="id_captionTr51">{$labelTr51}</span></b><span class="normalFont">{$linkNeu}</span></th>{$Th52}</tr>
    <tr id="filterTr6">
      <td>
        <span class="LabelX" style="width:12px;">&nbsp;</span><span class="LabelX" style="width:95px"><b>Konfiguration</b></span><span class="LabelX ttip"><b>Anzahl</b><span class="ttiptext">Anzahl der in Mopra angelegten Fahrzeuge</span></span><br>
HEREDOC;


// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
//  Windchill Varianten  // ECU Geräte


// --- Suchergebnis ----
if ($this->S_SearchList) {
    $visibility = ($this->m_VariantType == 'suchliste' ? "visible" : "invisible");
    $sucheNach = $this->S_SearchFor;

    printf('        <select name="filter[suchliste]" id="select_suche" class="ParamFilterA %s" OnClick="OnSelectVariant(this)" size="%d"%s>' . lf, $visibility, $ize_select - 1, $disabled);

    foreach ($this->S_SearchList as $vari_id => $list) {
        if (is_array($list)) {
            printf('<optgroup label="%s">', $vari_id);

            foreach ($list as $vehicle_id => $set) {
                $vehicle_id = $set['vehicle_id'];

                $selected = ($this->S_SearchVehicle == $vehicle_id) ? " selected" : "";
                if ($sucheNach == 'VIN')
                    printf('          <option value="#%s"%s>%s</option>',
                        $vehicle_id, $selected, $set['vin']);
                else
                    printf('          <option value="#%s"%s>%s</option>',
                        $vehicle_id, $selected, $set['code']);
            }
            echo '</optgroup>';
        } else {
            $selected = ($this->S_SearchItem == $vari_id) ? " selected" : "";
            echo "<option value=\"$vari_id\"$selected>$list</option>";
        }
    }
    echo "</select>\n";
}


// --- Alle Fahrzeugtypen ----
foreach ($this->DB_allVariantTypes as $vt) {
    $visibility = ($this->m_VariantType == $vt ? "visible" : "invisible");

    printf('        <select name="filter[%s]" id="select_%s" class="ParamFilterA %s" OnChange="OnSelectVariant(this)" size="%d"%s>' . lf,
        $vt, $vt, $visibility, $ize_select - 1, $disabled);

    foreach ($this->DB_indexAllVariant[$vt] as $combi_id => $variant_name) {
        $variante = &$this->DB_allVariants[$combi_id];

        if ($this->masterID && ($combi_id == $this->masterID)) {
            $selected = ($this->S_CombiID == $combi_id) ? " selected" : "";
            $namelen = strlen($variante['v_name']) + 1;
            $count_prod = $variante['count_prod'];
            $penta_cnt = ($count_prod) ? " &nbsp; ($count_prod)" : "";
            $name = $this->GetVariantNameForSelect($variant_name, true, true);

            echo "<option value=\"$combi_id\"$selected>$name $penta_cnt</option>";

            if (isset ($variante['subvarianten'])) {
                foreach ($variante['subvarianten'] as $subid) {
                    $subvariante = &$this->DB_allVariants[$subid];

                    $selected = ($this->S_CombiID == $subid) ? " selected" : "";
                    $suffix = substr($subvariante['i_name'], $namelen);

                    $partstr = "+ " . $subvariante['penta_part_string'];
                    if ($partstr == "+ ") $partstr = "";

                    $count_prod = $subvariante['count_prod'];
                    $penta_cnt = ($count_prod) ? " &nbsp; ($count_prod)" : "";

                    $name = $this->GetVariantNameForSelect($variant_name, false, false, "$suffix: $partstr");

                    echo "<option value=\"$subid\"$selected>$name $penta_cnt</option>";
                }
            } else {

            }
        } else {

            $count_prod = isset ($variante['count_all']) ? $variante['count_all'] : $variante['count_prod'];
            $penta_cnt = ($count_prod) ? " &nbsp; ($count_prod)" : "";

            $has_subvar = isset ($variante['subvarianten']);

            $selected = ($combi_id == $this->S_CombiID);
            $option_sel = ($selected ? " selected" : "");
            $name = $this->GetVariantNameForSelect($variant_name, $has_subvar, $selected);

            echo "<option value=\"$combi_id\"$option_sel>$name $penta_cnt</option>";
        }
    }


    echo "        </select>\n";
}

echo "      </td>\n";


$captionA = 'ODX Dateien';
$has_version = ($this->S_variantEcu && $this->S_Revisions);
$captionB = ($has_version) ? '<b>Zugewiesene Version</b>' : '';
$ecu_id = $this->S_variantEcu;
$ecu = $ecu_id ? $this->allEcuNames[$ecu_id] : '';
$downloadState = ' disabled';
$dwnl_vis_dummy = 'inline-block';
$dwnl_vis_link = 'none';
$copyState = ' disabled';
$copy_vis_dummy = 'inline-block';
$copy_vis_link = 'none';
$copy_caption = 'Parameter<br>kopieren';
$copy_command = 'startcopyparams';
$cbOnlySoftware = '';
$showCopyAbort = false;
$showEcuList = true;
$showVariables = false;
$allow_edit = false;

if ($this->DB_VariantSet) {
    if (($this->S_ParamType == self::ID_GeraeteParameter)
        && $this->S_variantEcu) {
        switch ($this->m_EditMode) {
            case 0:
                $dwnl_vis_dummy = 'none';
                $dwnl_vis_link = 'inline-block';
                $copy_vis_dummy = 'none';
                $copy_vis_link = 'inline-block';
                break;

            case self::EDIT_CopyVariantEcuData:
                $copyState = " selected";
                $showCopyAbort = true;
                $cbOnlySoftware = '<br><input type="checkbox" value="1" name="only_sw">nur Software Version';
                break;

            default:
                break;

        }
    }

    if ($this->S_ParamType == self::ID_GlobaleParameter) {
        $allow_edit = [];
        if ($this->S_currentVariable) {
            if (count($this->S_currentVariable) == 1) {
                $allow_edit = array_flip($this->S_currentVariable);
            } else {
                foreach ($this->S_currentVariable as $makro_id) {
                    $this->GetEnggPrivileges(self::PRIV_SET_VARIABLE, $makro_id);
                    if (toBool($this->m_Permission[self::PRIV_SET_VARIABLE]['current']))
                        $allow_edit[$makro_id] = true;
                }
            }
        }

        $copy_caption = "<span class=\"ttip\">$copy_caption<span class=\"ttiptext\">kopiert ausgewählten globalen Parameter<br>aus selektierte Fahrzeugkonfigurationen</span></span>";
        $copy_vis_link = $allow_edit ? 'inline-block' : 'none';
        $copy_vis_dummy = $allow_edit ? 'none' : 'inline-block';
        $showVariables = true;
        if ($this->m_EditMode == self::EDIT_CopyGlobalVariable) {
            $showVariables = false;
            $copyState = " selected";
            $showCopyAbort = true;
        };
        $showEcuList = false;
        $captionB = "";
    }

    if ($this->S_ParamType == self::ID_Fahrzeugeigenschaften) {
        $copy_caption = 'COC-Werte<br>kopieren';
        $copy_command = 'startcopycoc';

        switch ($this->m_EditMode) {
            case 0:
                $copy_vis_dummy = 'none';
                $copy_vis_link = 'inline-block';
                break;

            case self::EDIT_DistributeCocValues:
                $copyState = " selected";
                $showCopyAbort = true;
                break;

            default:
                break;
        }
    }
}

if ($showEcuList) {
    echo "      <td>\n";
    if ($printEcu && $this->S_ParamType == self::ID_GeraeteParameter) {
        printf('          <select class="ParamFilterA" name="filter[ecu]" OnChange="OnSelectEcu(this)" size="%d"%s>' . lf, $ize_select, $disabled);
        echo $this->GetHtml_SelectOptions($this->GetUsedEcus(), $this->S_variantEcu, 10);
        printf("          </select>\n");
        //var_dump($this->GetUsedEcus());
    }
    echo "      </td>\n";
}


if ($showVariables) {


    echo "      <td>\n";
    printf('        <select class="ParamFilterA" name="filter[makro][]" OnChange="OnSelectVariable(this)" multiple size="%d"%s>' . lf, $ize_select, $disabled);

    foreach ($this->DB_Variables as $param_id => $set) {
        if (count($this->S_currentVariable) > 1) {
            $this->GetEnggPrivileges(self::PRIV_SET_VARIABLE, $param_id);
            $disabled = (toBool($this->m_Permission[self::PRIV_SET_VARIABLE]['current']) ? '' : ' disabled');
        }

        $optSel = (isset ($allow_edit[$param_id]) ? " selected" : "");

        if (isset ($this->DB_VariantEcuData[$param_id])) {
            $type = '';
            $value = $this->GetValue_odx01($this->DB_VariantEcuData[$param_id], $set, $type);
            echo "          <option value=\"$param_id\"$optSel$disabled>{$set['odx_name']}={$value}</option>\n";
        } else {
            echo "          <option value=\"$param_id\"$optSel$disabled>{$set['odx_name']}</option>\n";
        }
    }
    echo "        </select>\n";
    echo "      </td>\n";

}

// --------------------------------------------------------------------------------------
// SubVarianten
//


echo <<<HEREDOC
    </tr>
    <tr id="filterTr7"><th><b>{$captionA}</b></th><th><b>{$captionB}</b></th></tr>
    <tr id="filterTr8">
      <td>
HEREDOC;

if ($this->m_OdxVerShow == 2)
    $ecu .= "_new";

$urlparams = $this->GetHtml_UrlParams();

echo <<<HEREDOC
            <div id="id_download_odc_dummy" style="display:$dwnl_vis_dummy" class="stsbutton$downloadState buttonsize_normal"><img src="/images/symbols/download_inactive.png"> ODX<br>.odx-Datei</div>
            <a id="id_download_odc_link" style="display:$dwnl_vis_link" href="{$_SERVER['PHP_SELF']}?$urlparams&command=odxdownload" id="id_odx_download">
              <div class="stsbutton buttonsize_normal">
                <img src="/images/symbols/download_active.png"> ODX<br>
                $ecu
              </div>
            </a>
HEREDOC;

if ($copy_caption)
    echo <<<HEREDOC
        <div id="id_copy_params_dummy" style="display:$copy_vis_dummy" class="stsbutton$copyState buttonsize_normal">$copy_caption</div>
        <a id="id_copy_params_link" style="display:$copy_vis_link" href="{$_SERVER['PHP_SELF']}?$urlparams&command=$copy_command">
        <div class="stsbutton buttonsize_normal">$copy_caption</div></a>
HEREDOC;

if ($showCopyAbort) {
    echo <<<HEREDOC
          </td>
          <td>
            <input type="hidden" name="filter[variant]" value="{$this->S_CombiID}">
            <input type="hidden" name="filter[paramType]" value="{$this->S_ParamType}">
            <input type="hidden" name="filter[ecu]" value="{$this->S_variantEcu}">
            <input type="hidden" name="copy_param_list" id="copy_param_list" value="">
            <a href="#" id="id_copyparams">
            <div class="stsbutton buttonsize_normal">Kopieren<br>ausführen</div></a>
            <a href="{$_SERVER['PHP_SELF']}?$urlparams&command=cancel">
            <div class="stsbutton buttonsize_normal">Abbruch<br>&nbsp;</div></a>
            $cbOnlySoftware
HEREDOC;

} else
    if ($showVariables) {
        echo <<<HEREDOC
          </td>
          <td>
            <a href="javascript:SaveParametersX('new_variable', 'idl');">
            <span class="stsbutton buttonsize_normal">neuer<br>Parameter</span></a>
            <!--
                    <span id="id_edit_var_inactive" style="display:inline-block" class="stsbutton disabled buttonsize_flat">ändern</span>
                    <a id="id_edit_var_active" style="display:none" href="javascript:SaveParametersX('edit_variable', 'idl');">
                    <span class="stsbutton buttonsize_flat">ändern</span></a>

                    <span id="id_delete_var_inactive" style="display:inline-block" class="stsbutton disabled buttonsize_flat">löschen</span>
                    <a id="id_delete_var_active" style="display:none" href="javascript:SaveParametersX('delete_variable', 'idl');">
                    <span class="stsbutton buttonsize_flat">löschen</span></a>
            -->
HEREDOC;
    } else {
// --------------------------------------------------------------------------------------
// Display/Selekt SW Version
//

        echo '
      </td>
      <td>';
        if ($this->S_variantEcu && !empty($this->S_Revisions)) {
            $only_profile_ok = ($this->S_OdxVerEcu == 2);
            $revList = $this->GetListOfDisplayedRevisionsNames($this->S_Revisions, $only_profile_ok);

            if ($this->S_variantRev) {
                $rev_id = $this->S_variantRev['ecu_revision_id'];
                if ($this->S_OdxVerEcu == 2)
                    $color_class = toBool($this->S_variantRev['released']) ? 'greenText' : 'redText';
                else
                    $color_class = "";

                $sts_version = "<span class=\"$color_class\">{$revList[$rev_id]}</span>";

                if ($this->S_variantRev['href_windchill'])
                    $sts_version = sprintf('<a href="%s">%s</a>', $this->S_variantRev['href_windchill'], $sts_version);
            } else if (isset($this->DB_allRevisions[$this->S_variantEcu])) {
                $sts_version = "<span class=\"greyText\">wird nicht verwendet</span>";
            } else {
                $sts_version = "<span class=\"redText\">{nicht zugeordnet}</span>";
            }

            $this->GetEnggPrivileges(self::PRIV_ECU_DATA, $this->S_variantEcu);
            $allow_edit = toBool($this->m_Permission[self::PRIV_ECU_DATA]['current']);

            echo "
            <div id=\"idDivRev\" class=\"version\"><b>Rev.</b><span>{$sts_version}</span>";


            // -------------------------------------
            if ($allow_edit) {
                $options = '<option value="0">wird nicht verwendet</option>' . $this->GetHtml_SelectOptions($revList, $rev_id, 10);
                echo <<<HEREDOC

              <select name="sw_rev" id="selectSwVersion">
                $options
              </select>
            </div>
            <div class="version">&nbsp;
              <a id="idEnableChangeSW" href="javascript:SetVersionEditMode(true)">SW Zuweisung ändern</a>
              <span id="idVersionButtons">
                <input type="button" value="abbrechen" OnClick="javascript:SetVersionEditMode(false)">
                <input type="button" value="speichen" OnClick="javascript:OnBtSaveSwClick()">
              </span>
HEREDOC;
            }
            // -------------------------------------

            echo "\n        </div>\n";
        }
    }
//
// Display/Selekt SW Version
// --------------------------------------------------------------------------------------

echo <<<HERE____TAG_DOWN___DOC
      </td>
    </tr>
  </tbody>
</table>
</form>
</div>
HERE____TAG_DOWN___DOC;
?>
