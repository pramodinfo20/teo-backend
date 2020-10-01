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
    include $_SERVER['STS_ROOT'] . "/actions/Engg/Updateinterface/Table.pager.php";

$this->nameprefix = "table";
?>
<div style="height: 5px;"></div>


<?php
// =========================================================================================================================
if ($this->InputState == INPUT_STATE_SELECT)
    $this->WriteHtmlTable();
// =========================================================================================================================
?>
<div style="height: 5px;"></div>

<?php

$this->nameprefix = "bot";
if ($this->InputState == INPUT_STATE_SELECT)
    include $_SERVER['STS_ROOT'] . "/actions/Engg/Updateinterface/Table.pager.php";

$attr_btn_back = $this->GetHtmlButtonAttributes('back');
$attr_btn_undo = $this->GetHtmlButtonAttributes('undo');
$attr_btn_rstfltr = $this->GetHtmlButtonAttributes('rstfltr');
$attr_btn_showselected = $this->GetHtmlButtonAttributes('showselected');
$attr_btn_save = $this->GetHtmlButtonAttributes('save');
$attr_btn_save_list = $this->GetHtmlButtonAttributes('save_list');

switch ($this->InputState) {
    // ====================================================================================
    case INPUT_STATE_SELECT:

        echo <<<EOT_STATE_SELECT
<table class="buttontable noborder allcenter" style="border:3px solid #dddddd;">
  <tr >
    <td><input type="button" $attr_btn_rstfltr></td>
    <td><input type="button" $attr_btn_showselected></td>
    
  </tr>
</table>



EOT_STATE_SELECT;
        break;
    case INPUT_STATE_ERROR_LIST:
        echo <<<INPUT_STATE_ERRORLIST
                <table class="sales">
                    <thead>
                        <tr>
                            {$this->tablehead}
                        </tr>
                        
                    </thead>
                    <tbody>
                        {$this->tablebody}
                        
                    </tbody>
                    
                </table>
                <table class="buttontable noborder allcenter" style="border:3px solid #dddddd;">
                  <tr >
                    <td><input type="button" $attr_btn_back></td>
                    
                  </tr>
                </table>
INPUT_STATE_ERRORLIST;
        break;
    case ERROR_STATE:
        echo "Kein Postauto!";
        break;
}

echo "</form>\n<p>&nbsp;</p>\n";
?>


