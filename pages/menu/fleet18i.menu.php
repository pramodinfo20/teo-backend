<?php
$selected = ["", "", "", "", "", "", "", ""];
$iSel = 0;

switch ($this->action) {
    case '':
    case 'home':
        $iSel = 1;
        break;
    case 'fahrzeugbestellungen':
        $iSel = 2;
        break;
    /*
    case 'verlaufsdaten':       $iSel=3;    break;
    case 'tagesstatistik':      $iSel=4;    break;
    case 'produzierte':         $iSel=5;    break;
    case 'ausgelieferte':       $iSel=6;    break;
    case 'werkstaettenlogin':   $iSel=7;    break;
    */
}
$selected[$iSel] = " selected";


echo <<<HEREDOC
	<div class="row ">
		<div class="columns twelve">
			<ul class="submenu_ul">

				<li>
					<a href="?action=home" class="W150 sts_submenu{$selected[1]}">Home</a>
				</li>
				<li>
					<a href="?action=fahrzeugbestellungen&initPage" data-target="fahrzeugbestellungen" class="W160 sts_submenu{$selected[2]}">Fahrzeugbestellung</a>
				</li>
			</ul>
		</div>
	</div>
HEREDOC;
?>


