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

if ($this->InputState == INPUT_STATE_SELECT)
    include $_SERVER['STS_ROOT'] . "/actions/Engg/Updateinterface/Table.pager.php";

$this->nameprefix = "table";
?>
<div style="height:5px;"></div>


<?php
// =========================================================================================================================
if ($this->InputState != INPUT_STATE_CHANGEPART && $this->InputState != INPUT_STATE_SELECTMODE && $this->InputState != INPUT_STATE_EVALUATION) {
    $this->WriteHtmlTable();
}
// =========================================================================================================================
?>
<div style="height:5px;"></div>

<?php

$this->nameprefix = "bot";
if ($this->InputState == INPUT_STATE_SELECT)
    include $_SERVER['STS_ROOT'] . "/actions/Engg/Updateinterface/Table.pager.php";

$attr_btn_back = $this->GetHtmlButtonAttributes('back');
$attr_btn_rstfltr = $this->GetHtmlButtonAttributes('rstfltr');
$attr_btn_showselected = $this->GetHtmlButtonAttributes('showselected');
$attr_btn_save = $this->GetHtmlButtonAttributes('save');
$attr_btn_save_list = $this->GetHtmlButtonAttributes('save_list');
$attr_btn_commit = $this->GetHtmlButtonAttributes('commit');


switch ($this->InputState) {
    //====================================================================================
    case INPUT_STATE_SELECT:

        echo <<<EOT_STATE_SELECT
<table class="buttontable noborder allcenter" style="border:3px solid #dddddd;">
  <tr >
    <td><input type="button" $attr_btn_rstfltr></td>
    
  </tr>
</table>

  

EOT_STATE_SELECT;
        break;

    //====================================================================================

    case INPUT_STATE_SELECTMODE:
        echo <<<INPUT_STATE_SELECTMODE
            <p>Möchten sie ein Teil <a href="{$_SERVER['PHP_SELF']}?action=tauschinterface&vin={$_GET['vin']}&command=changePart">austauschen</a> oder eine neues Teil <a href="{$_SERVER['PHP_SELF']}?action=tauschinterface&vin={$_GET['vin']}">hinzufügen(in Planung)</a></p>
            <table class="buttontable noborder allcenter" style="border:3px solid #dddddd;">
                <tr >
                    <td><input type="button" $attr_btn_back></td>
                </tr>
            </table>

INPUT_STATE_SELECTMODE;
        break;

    case INPUT_STATE_CHANGEPART:

        if ($this->inputoptions) {
            echo <<<AuszutauschendesTeil
               <table style="width:auto">
                <tr>
                <td style="display:inline-block;"> 
                <h4>Auszutauschendes Teil</h4>   
                <input type="text" name="inputsearch" placeholder="Suchen.." value={$_POST['inputsearch']}><br>
                <select size=20 name="inputselect">
                        
                    {$this->inputoptions}
                  
                </select><br> 
                Welche Seriennummer hat das auszutauschende Teil?<input type="text" name="inputserial" value={$_POST['inputserial']}><br>
               
               </td>            
AuszutauschendesTeil;


            echo <<<AustauschTeil
                <td style="display:inline-block;">
                <h4>Teil durch das ausgetauscht werden soll:</h4>
                <input type="text" name="exchangesearch" placeholder="Suchen.." value="{$_POST['exchangesearch']}"><br>
                <select size=20 name="exchangeselect">
AustauschTeil;


            if ($this->exchangeoptions != false) {
                echo $this->exchangeoptions;
            } else {
                echo '<option disabled>Bitte suche nach einer gültigen Zeichenkette</option>';
            }
            echo <<<AustauschTeil
                </select> <br> 
                Welche Seriennummer hat das Teil durch das ausgetauscht wird?<input type="text" name="exchangeserial" value={$_POST['exchangeserial']}><br>
                </td>
AustauschTeil;

            if ($this->parentoptions) {
                echo <<<PARENTLIST
                <td style="display:inline-block;">
                    <h4>An welcher Stelle soll ausgetauscht werden</h4>
                    <select size=20 name="parentselect">
                        {$this->parentoptions}
                    </select><br>
                    {$this->msg}
                    <input type="button" $attr_btn_commit> 
                </td>      
PARENTLIST;
            }
            echo <<<OPTIONSELECT
                <td style="display:inline-block;">
                    <h4>Austauschgrund:</h4>
                    <select size=10 name="reasonselect">
                        {$this->reasonoptions}
                    </select><br>
                    <h4>Kommentar</h4>
                    <textarea name="commentary"></textarea>
                </td> 
OPTIONSELECT;
            echo '<input type="submit" style="visibility: hidden;" /></tr></table>';
            echo <<<back
            
            
                
                <input type="button" $attr_btn_save>
                
back;

        } else {
            echo '<p>Keine Ebom zu dieser Variante vorhanden.</p>';
        }
        echo '<input type="button"' . $attr_btn_back . '>';

        break;
    case INPUT_STATE_EVALUATION:
        echo 'Austausch wurde vermerkt.';
        echo '<input type="button"' . $attr_btn_back . '>';
        break;

    //====================================================================================

}


echo "</form>\n<p>&nbsp;</p>\n";
?>


