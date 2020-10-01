<?php
$selected = ["", ""];
$iSel = 0;

switch ($this->action) {
  case '':
  case 'home':
    $iSel = 0;
    break;
  case 'fehlerliste':
    $iSel = 1;
    break;


}
$selected[$iSel] = " selected";

echo <<<HEREDOC

    <div class="row ">
        <div class="columns twelve">
            <ul class="submenu_ul" id="id_submenu">
                <li>
                    <a href="?action=home" class="W150 sts_submenu{$selected[0]}">Home</a>
                </li>
                <li>
                    <a href="?action=fehlerliste&initPage" class="W150 sts_submenu{$selected[1]}">Fehlerliste</a>
                </li>
                
            </ul>
        </div>
    </div>
HEREDOC;
?>