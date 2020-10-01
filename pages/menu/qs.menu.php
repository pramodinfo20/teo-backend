<?php
$selected = ["", "", "", "", "", "", "", "", "", ""];
$iSel = 0;

switch ($this->action) {
    case '':
    case 'home':
        $iSel = 1;
        break;
    case 'locationplan':
        $iSel = 2;
        break;
    case 'dtcverwaltung':
        $iSel = 3;
        break;
    case 'teoexceptions':
      case 'specialapp':
          $iSel = 4;
          break;        
        //todo: move to other place
//    case 'upload2':
//        $iSel = 5;
//        break;
}
$selected[$iSel] = " selected";


echo <<<HEREDOC
	<div class="row ">
		<div class="columns twelve">
			<ul class="submenu_ul" id="id_submenu">

				<li>
					<a href="?action=home" class="W150 sts_submenu{$selected[1]}">TEO Übersicht</a>
				</li>
				<li>
					<a href="?action=locationplan" class="W150 sts_submenu{$selected[2]}">Fahrzeug Lageplan</a>
				</li>
        <li>
           <a href="?action=dtcverwaltung" class="W150 sts_submenu{$selected[3]}">DTC Verwaltung</a>
        </li>
        <li>
          <a href="?action=teoexceptions&initPage" class="W150 sts_submenu{$selected[4]}">Abweicherlaubnisse</a>
        </li>          
    </ul>
		</div>
	</div>
HEREDOC;
//todo: move to other place
/*
 *
          <li class="dropdown">
              <div class="sts_submenu {$selected[5]}">Human Resources</div>
              <div class="dropdown-content">
                <a href="?action=upload2">List Upload</a>
              </div>
        </li>
 */
?>



