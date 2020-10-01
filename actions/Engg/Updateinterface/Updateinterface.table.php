<?php
echo <<<EOTFORM
<span style="font-size:15px;font-weight:bold;">
</span>
<script>
    var numsel={$this->numsel}
    var prevsel={$this->prevsel}
    var instate={$this->InputState}
</script>
<h1>Update Interface</h1>
<br>
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
    include $_SERVER['STS_ROOT'] . "/actions/Engg/Updateinterface/Table.pager.php";

$attr_btn_back = $this->GetHtmlButtonAttributes('back');
$attr_btn_undo = $this->GetHtmlButtonAttributes('undo');
$attr_btn_rstfltr = $this->GetHtmlButtonAttributes('rstfltr');
$attr_btn_showselected = $this->GetHtmlButtonAttributes('showselected');
$attr_btn_save = $this->GetHtmlButtonAttributes('save');
$attr_btn_save_list = $this->GetHtmlButtonAttributes('save_list');


switch ($this->InputState) {
    //====================================================================================
    case INPUT_STATE_SELECT:

        echo <<<EOT_STATE_SELECT
<table class="buttontable noborder allcenter" style="border:3px solid #dddddd;">
  <tr >
    <td><input type="button" $attr_btn_undo></td>
    <td><input type="button" $attr_btn_rstfltr></td>
    <td><input type="button" $attr_btn_showselected></td>

  </tr>
</table>



EOT_STATE_SELECT;
        break;

    //====================================================================================
    case INPUT_STATE_EDIT_1:


        if (isset($this->selec1)) {
            echo '<select name="ecu_id" onchange="this.form.submit()">';
            echo '<option value="" selected disabled hidden>Steuergerät auswählen</option>';
            foreach ($this->selec1 as $s) {
                echo '<option value="' . $s['ecu_id'] . '"';
                if ($s['ecu_id'] == $_SESSION['ecu_id'])
                    echo ' selected';
                echo '>' . $s['name'] . '</option>';
            }
            echo '</select>';
        }

        if (isset($this->selec2)) {
            echo '<select name="task_type_id" onchange="this.form.submit()">';
            echo '<option value="" selected disabled hidden>Task auswählen</option>';
            foreach ($this->selec2 as $s) {
                if (!$s['task_type_id']) {
                    echo '<option value="' . $s['task_type_id'] . '" disabled>' . $s['task_name'] . '</option>';
                } else {

                    echo '<option value="' . $s['task_type_id'] . '"';
                    if ($s['task_type_id'] == $_SESSION['task_type_id']) {
                        echo ' selected';
                    }
                    echo '>' . $s['task_name'] . '</option>';
                }
            }
            echo '</select>';
        }

        if (isset($this->selects)) {
            $count = 0;
            foreach ($this->selects as $s) {
                echo '  ' . $s['name'] . ':';
                echo '<select name="' . $s['id'] . '">';
                echo '<option value="" selected disabled hidden>Option auswählen</option>';
                foreach ($s['data'] as $d) {
                    echo '<option value="' . $d['option_value'] . '">' . $d['option_value'] . '</option>';

                }
                echo '</select>';
            }

        }
        if (isset($this->msg)) {
            echo '<p>' . $this->msg . '</p>';
        }

        echo <<<EOT_STATE_EDIT_1
<input type="hidden" name="action" value="{$this->action}">
<table class="buttontable noborder allcenter" style="border:3px solid #dddddd;"><tr >


EOT_STATE_EDIT_1;
        if (isset($this->selects)) {
            echo 'Kommentar hinzufügen: <input name="comment">';
            echo '<td><input type="button"' . $attr_btn_save . '></td>';
        }
        echo <<<EOT_STATE_EDIT_1

            <td><input type="button" $attr_btn_back></td>

            <td>Auswahlliste bennen:<input name="listname"><input type="button" $attr_btn_save_list>{$this->listmsg}</td>




             </td>
          </tr>
        </table>
EOT_STATE_EDIT_1;


        break;

    //====================================================================================
    case INPUT_STATE_SAVE_PRINT:

        break;

}

echo "</form>\n<p>&nbsp;</p>\n";
?>


