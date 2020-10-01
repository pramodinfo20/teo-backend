<?php
echo <<<EOTFORM
<span style="font-size:15px;font-weight:bold;">
</span>
<script>
    var numsel={$this->numsel};
    var prevsel={$this->prevsel};
    var instate={$this->InputState};
</script>
<form method="post" name="mainForm" action="{$_SERVER['PHP_SELF']}" id="id_Form">
<input type="hidden" name="action" value="{$this->action}">
<input type="hidden" name="command" id="id_command" value="">
<input type="hidden" name="changes" id="id_changes" value="">
<input type="hidden" name="page_size" id="id_size" value="">
<input type="hidden" name="goto_page" id="id_goto_page" value="{$this->S_currentPage}">
EOTFORM;

$this->nameprefix = "top";
if ($this->InputState == INPUT_STATE_SELECT)
    include $_SERVER['STS_ROOT'] . "/actions/Sales/Changevariant/Changevariant.pager.php";

$this->nameprefix = "table";
?>
<div style="height:5px;"></div>


<?php
// =========================================================================================================================
$this->WriteHtmlTable();
// =========================================================================================================================
?>
<div style="height:5px;"></div>

<?php

$this->nameprefix = "bot";
if ($this->InputState == INPUT_STATE_SELECT)
    include $_SERVER['STS_ROOT'] . "/actions/Sales/Changevariant/Changevariant.pager.php";

$convert_to = isset($_POST['convert_to']) ? $_POST['convert_to'] : 0;

$attr_btn_delete1 = $this->GetHtmlButtonAttributes('delete1');
$attr_btn_delete2 = $this->GetHtmlButtonAttributes('delete2');
$attr_btn_exports = $this->GetHtmlButtonAttributes('exports');
$attr_btn_back = $this->GetHtmlButtonAttributes('back');
$attr_btn_undo = $this->GetHtmlButtonAttributes('undo');
$attr_btn_rstfltr = $this->GetHtmlButtonAttributes('rstfltr');
$options_select_variant = $this->GetHtmlSelectOptions_SelectVariants($convert_to);
$options_select_color = $this->GetHtmlSelectOptions_Color();
$options_select_prodloc = $this->GetHtml_SelectOptions($this->primaryLocations);
$options_special_equip = $this->GetHtmlElement_SpecialEquip();
$attr_btn_change = $this->GetHtmlButtonAttributes('change');
$attr_btn_color = $this->GetHtmlButtonAttributes('color');
$attr_btn_add_options = $this->GetHtmlButtonAttributes('add_option');
$attr_btn_rm_options = $this->GetHtmlButtonAttributes('rm_option');
$attr_btn_edit1 = $this->GetHtmlButtonAttributes('edit1');
$attr_btn_save = $this->GetHtmlButtonAttributes('save');
$attr_btn_location = $this->GetHtmlButtonAttributes('location');


switch ($this->InputState) {
    //====================================================================================
    case INPUT_STATE_SELECT:
        echo <<<EOT_STATE_SELECT
<table class="buttontable noborder allcenter" style="border:3px solid #dddddd;">
  <tr >
    <td><input type="button" $attr_btn_undo></td>
    <td><input type="button" $attr_btn_rstfltr></td>
    <td><div class="W120"></div></td>
    <td><input type="button" $attr_btn_exports></td>
    <td><input type="button" $attr_btn_delete1></td>
    <td><input type="button" $attr_btn_edit1></td>
  </tr>
</table>
EOT_STATE_SELECT;
        break;

    //====================================================================================
    case INPUT_STATE_EDIT_1:
        $all_depots = $this->vehiclesPtr->newQuery('depots')->where('name', '!=', "")->orderBy('name')->get('depot_id=>name');
        $button_back_optional = ($this->execMode == CHANGEVARIANT_TABLESELECT ? "<input type=\"button\" $attr_btn_back>" : "&nbsp;");


        echo <<<EOT_STATE_EDIT1
<table class="noborder buttontable allcenter" style="">
  <tr >
    <td>&nbsp;</td>
    <td style="vertical-align:bottom;"><select style="width:200px;" id="id_convert_to" name="convert_to"><option value="0" selected disabled>(Wähle eine Fahrzeugvariante)</option>$options_select_variant</select></td>
	<td style="vertical-align:bottom;"><select style="width:200px;" name="color_to"><option value="0" selected disabled>(Wähle eine Farbe)</option>$options_select_color</select></td>
    <td style="vertical-align:bottom;"><select style="width:200px;" name="equip_to_add">$<option value="0" selected disabled>(Sonderausstattung...)</option>$options_special_equip</select></td>
    <td style="vertical-align:bottom;"><select style="width:200px;" name="to_location" id="id_to_location" onChange="ZeigAndereStandorte(this);"><option value="0" selected disabled>(Wähle ein Prod. Standort aus.)</option><option value="edit">- anderer Standort -</option>$options_select_prodloc</select></td>
    <td>&nbsp;</td>
  </tr>
  <tr class="silver SalesCmds">
    <td >$button_back_optional</td>
    <td ><input type="button" $attr_btn_change></td>
    <td ><input type="button" $attr_btn_color></td>
    <td>
        <input type="button" style="width:100px; min-width: 30px;" $attr_btn_add_options>
        <input type="button" style="width:100px;min-width: 30px;" $attr_btn_rm_options></td>
    <td ><input type="button" $attr_btn_location></td>
    <td ><input type="button" $attr_btn_save></td>
  </tr>
</table>
EOT_STATE_EDIT1;

        echo '<div id="id_all_depots" style="position:absolute;top:0px;left:0px;visibility:hidden;"><select size="20" style="width:200px;" onClick="HabAnderenStandort(this)">' . lf;
        foreach ($all_depots as $depot_id => $name)
            echo "<option value=\"$depot_id\">$name</option>\n";
        echo '</select><input type="hidden" id="id_othername" name="othername" value=""></div>' . lf;
        break;


    ///====================================================================================
    case INPUT_STATE_SAVE_PRINT:
        $ref_lieferschein = $this->GetHtml_Download_Uebergabeprotokoll();

        if ($this->pentacsv_fname)
            $ref_penacsv = $this->pentacsv_fname;
        else
            $ref_penacsv = '&nbsp';

        echo <<<EOT_STATE_SAVE
<table class="buttontable noborder allcenter" style="border:3px solid #dddddd;">
  <tr >
    <td>$ref_lieferschein</td>
    <td>$ref_penacsv</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>

EOT_STATE_SAVE;

        break;

    case INPUT_STATE_DELETE_1:
        echo <<<EOT_STATE_DELETE1
<table class="buttontable noborder allcenter" style="border:3px solid #dddddd;">
  <tr >
    <td class="error_msg"><h3>Sind Sie sicher, dass Sie die gelisteten Datenbankeinträge unwiederruflich löschen möchten?</h3></td>
  </tr >
</table>
<table class="buttontable noborder allcenter" style="border:3px solid #dddddd;">
  <tr >
    <td ><input type="button" $attr_btn_back></td>
    <td><div class="W120"></div></td>
    <td><div class="W120"></div></td>
    <td><input type="button" $attr_btn_delete2></td>
  </tr>
</table>
EOT_STATE_DELETE1;
        break;


    case INPUT_STATE_DELETED:
        echo <<<EOT_STATE_DELETE2
<table class="buttontable noborder allcenter" style="border:3px solid #dddddd;">
  <tr>
    <td ><input type="button" $attr_btn_back></td>
  </tr>
</table>
EOT_STATE_DELETE2;

        break;
}

?>
</form>
<p>&nbsp;</p>
<script type="text/javascript">
    // autocomplete
    $("#id_to_location").chosen();
    $("#id_convert_to").chosen();
</script>    
