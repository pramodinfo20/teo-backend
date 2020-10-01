<?php
$selected = ["", "", "", "", "", "", "", ""];
$iSel = 0;

switch ($this->action) {
    case '':
    case 'home':
        $iSel = 1;
        break;
    case 'locationplan':
        $iSel = 2;
        break;
    case 'schraubdaten_admin':
        $iSel = 3;
        break;
    case 'schraubdaten_messung':
        $iSel = 3;
        break;
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
                <li class="dropdown">
                    <div class="sts_submenu {$selected[3]}">Schraubdaten</div>
                    <div class="dropdown-content">
                        <a href="?action=schraubdaten_admin&initPage">Schraubendaten Verwalten</a>
                        <a href="?action=schraubdaten_messung&initPage">Drehmoment Messdaten</a>
                    </div>
                </li>
			</ul>
		</div>
	</div>
HEREDOC;
?>


