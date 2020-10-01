<?php
echo <<<EOTFORM
<span style="font-size:15px;font-weight:bold;">
</span>
<script>
    var numsel={$this->numsel}
    var prevsel={$this->prevsel}
    var instate={$this->InputState}
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

$attr_btn_back = $this->GetHtmlButtonAttributes('back');
$attr_btn_undo = $this->GetHtmlButtonAttributes('undo');
$attr_btn_rstfltr = $this->GetHtmlButtonAttributes('rstfltr');

$checkbox_dritt = "";
$selectbox_destination = "";

if ($this->S_dp_order) {
    $like_default_depot = "%Auslieferung%Dortmund%";
    $checked = ($this->b_show_dritt) ? ' checked' : '';
    $checkbox_dritt = '<input type="checkbox" name="show_dritt"' . $checked . ' OnClick="SubmitForm(\'go_first\')"> Drittkundenfahrzeuge anzeigen';
} else {
    $like_default_depot = "%pool%";
}

$default_depot = $this->vehiclesPtr->newQuery('depots')
    ->where('name', 'ilike', $like_default_depot)
    ->where('depot_id', 'in', array_keys($this->delivery_depots))
    ->getVal('depot_id');

if (count($this->delivery_depots) > 1)
    $selectbox_destination = '<select name="deliver_to">' . $this->GetHtml_SelectOptions($this->delivery_depots, $default_depot) . '</select>';
else
    $selectbox_destination = '<input type="hidden" name="deliver_to" value="' . $default_depot . '">';


switch ($this->InputState) {
    //====================================================================================
    case INPUT_STATE_SELECT:
        $attr_btn_deliver = $this->GetHtmlButtonAttributes('deliver1');

        echo <<<EOT_STATE_SELECT
<table class="buttontable noborder allcenter" style="border:3px solid #dddddd;">
  <tr >
    <td><input type="button" $attr_btn_undo></td>
    <td><input type="button" $attr_btn_rstfltr></td>
    <td><div class="W120"></div>$checkbox_dritt</td>
    <td><div class="W120"></div>$selectbox_destination</td>
    <td><input type="button" $attr_btn_deliver></td>
  </tr>
</table>
EOT_STATE_SELECT;
        break;

    //====================================================================================
    case INPUT_STATE_VALIDATE:

        $attr_btn_deliver = $this->GetHtmlButtonAttributes('deliver2');
        $deliver_to = $_REQUEST['deliver_to'];
        $shipping_date = date('d.m.Y', time() + ONE_DAY);
        $delivery_date = date('d.m.Y', time() + (8 * ONE_DAY));

        echo <<<EOT_STATE_SELECT
<input type="hidden" name="deliver_to" value="$deliver_to">
<table class="buttontable noborder allcenter" style="border:3px solid #dddddd; white-space: nowrap;">
  <tr >
    <td><input type="button" $attr_btn_back></td>
    <td></td>
    <td>
        <div class="seitenteiler W160">Abholdatum <br>(frühestens morgen)</div>
        <div class="seitenteiler W160"><input type="text" name="abholdatum" size="10" readonly class="abholddatum" value="$shipping_date"></div>
    </td>
    <td>
        <div class="seitenteiler W160">Anlieferung bis<br>(+2 Tage)</div>
        <div class="seitenteiler W160"><input type="text" name="lieferdatum" size="10" readonly class="lieferdatum" value="$shipping_date"></div>
    </td>
    <td></td>
    <td><div style="width: 320px;">Ausliefern an: <b>{$this->delivery_depots[$deliver_to]}</b></div></td>
    <td><input type="button" $attr_btn_deliver></td>
    <td></td>
  </tr>
</table>
EOT_STATE_SELECT;

        break;

    //====================================================================================
    case INPUT_STATE_SAVE_PRINT:
        $td_uebergabeprotokoll = $this->GetHtml_Download_Uebergabeprotokoll();
        $th_abholschein = $this->GetHtml_LinkAbholschein(1);
        $td_penta_csv = ($this->link_penta_csv)
            ? "<span>Penta CSV Liste</span><span>{$this->link_penta_csv}</span>"
            : "&nbsp;";


        echo <<<EOT_STATE_SAVE
<table class="buttontable noborder allcenter" style="border:3px solid #dddddd;">
  <tr>
    <td>$td_uebergabeprotokoll</td>
    <td>$th_abholschein</td>
    <td>$td_penta_csv</td>
  </tr>
</table>

EOT_STATE_SAVE;

        break;

}

echo "</form>\n<p>&nbsp;</p>\n";
?>


