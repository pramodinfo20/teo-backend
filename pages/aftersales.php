<?php
/**
 * aftersales.php temporarily engineering
 * Template für die Benutzer Rolle Aftersales
 * @author Pradeep Mohan
 */

$div = $this->requestPtr->getProperty('div');
$zspl = $this->requestPtr->getProperty('zspl');
$depot = $this->requestPtr->getProperty('depot');
$msgs = '';


$displayheader = $this->displayHeader;
$qform_fr = new QuickformHelper ($displayheader, "zspl_upload_form");
$qform_fr->add_fahrzeug_suchen();
$formprint = $qform_fr->getContent();


$vin = $this->requestPtr->getProperty('vin');
$vid = $this->requestPtr->getProperty('$vid');
$kennzeichen = $this->requestPtr->getProperty('kennzeichen');

echo '<div class="inner_container">
';
include $_SERVER['STS_ROOT'] . '/pages/menu/aftersales.menu.php';


if (is_array($msgs)) { ?>
    <div class="row ">
        <div class="columns six">
            <?php echo implode('<br>', $msgs); ?>
        </div>
    </div>

    <?php
}
if (isset($this->action) && $this->action == 'produzierte') {
    ?>
    <div class="row ">
        <div class="columns twelve">
            <?php
            $headings[0]['headingone'] = array('VIN', 'Datum');
            $table = new DisplayTable(array_merge($headings, $this->producedVehicles), array('id' => 'sort_filter_table'));
            echo $table->getContent();
            ?>
        </div>
    </div>
    <?php
} else if (isset($this->action) && $this->action == 'ausgelieferte') {
    ?>
    <div class="row ">
        <div class="columns twelve">
            <?php
            $headings[0]['headingone'] = array('Datenbank ID', 'VIN', 'AKZ', 'Anlieferungsdatum');
            $table = new DisplayTable(array_merge($headings, $this->deliveredVehicles), array('id' => 'sort_filter_table'));
            echo $table->getContent();
            ?>
        </div>
    </div>
    <?php
} else if (isset($this->action) && $this->action == 'verlaufsdaten') {
    include $_SERVER['STS_ROOT'] . "/actions/html/Form@Engg_Verlaufsdaten.php";
} else if (isset($this->action) && $this->action == 'tagesstatistik') {
    include $_SERVER['STS_ROOT'] . "/actions/html/Form@Engg_Tagesstatistik.php";
} else if (isset($this->action) && $this->action == 'fertigmelden') {
    echo '
    <div class="row ">
      <div class="columns twelve">
        <h1>Fahrzeug Fertig Status setzen</h1>
      </div>
    </div>
    <div class="row ">
      <div class="columns twelve">
';

    if (!empty($this->poolVehicles)) {
        $this->qform_vehicles = new QuickformHelper($this->displayHeader, 'vehicle_fertig_status');
        $this->qform_vehicles->getQSFertigForm($this->poolVehicles, null, 'saveQS');
        echo $this->qform_vehicles->getContent();

    } else echo 'Keine Fahrzeuge!';

    echo '
      </div>
    </div>
';
} else if (isset($this->action) && $this->action == 'werkstaettenlogin') {
    $infosymbol = '"/images/symbols/information2.png"';
    $ttip_open = "<div class=\"ttip\"><img src=$infosymbol><span class=\"ttiptext\">";
    $ttip_close = "</span></div>";

    echo <<<HEREDOC
            <div class="wksArea">
        	<h2>Werkstatt Logins verwalten</h2>
HEREDOC;

    if (count($this->newLogins)) { ?>
        <h3>Neu angelegte Logins</h3>
        <div class="wksFrame">
        <table class="wksTable wksHead">
            <thead>
            <tr>
                <td>PLZ</td>
                <td>Ort</td>
                <td>Firma</td>
                <td>Zugeordnete Zspls (Id)</td>
                <td>Login</td>
                <td>Password</td>
                <td>&nbsp;</td>
            </tr>
            </thead>
        </table>
        <div class="wksTableArea">
            <table class="wksTable wksBody">
                <tbody><?php


                foreach ($this->newLogins as $workshop_id => $set) {
                    $wks = $this->all_workshops[$workshop_id];
                    unset ($this->all_workshops[$workshop_id]);

                    $zspls = substr($wks['zspls'], 1, -1);

                    if (empty ($wks))
                        continue;

                    echo <<<HEREDOC
    				    <tr>
    				      <td>{$wks['zip_code']}</td>
    				      <td>{$wks['location']}</td>
    				      <td>{$wks['name']}</td>
                          <td>$zspls</td>
    				      <td><input class="wksPasswd" type="text" maxsize="16" value="{$set['L']}" readonly></td>
                          <td><input class="wksPasswd" type="text" maxsize="16" value="{$set['P']}" readonly></td>
    				    </tr>
HEREDOC;
                }
                ?>
                </tbody>
            </table>
        </div>
        </div><?php
    }


    if (count($this->all_logins)) {
        ?>
        <h3>Existierende Werkstatt Zugänge</h3>
        <div class="wksFrame">
        <table class="wksTable wksHead">
            <thead>
            <tr>
                <td>PLZ</td>
                <td>Ort</td>
                <td>Firma</td>
                <td>Zugeordnete Zspls (Id)</td>
                <td>Login</td>
                <td>Password</td>
                <td>&nbsp;</td>
            </tr>
            </thead>
        </table>
        <div class="wksTableArea">
            <table class="wksTable wksBody">
                <tbody><?php


                foreach ($this->all_logins as $workshop_id => $login) {
                    $wks = $this->all_workshops[$workshop_id];
                    unset ($this->all_workshops[$workshop_id]);

                    if (empty ($wks))
                        continue;


                    $zspls = str_replace(',', ' ', substr($wks['zspls'], 1, -1));

                    if (isset ($this->changedPwds[$workshop_id])) {
                        $td_passwd = '<input class="wksPasswd" type="text" maxsize="16" value="' . $this->changedPwds[$workshop_id] . '" readonly>';
                    } else {
                        $td_passwd = "<a href=\"{$_SERVER['PHP_SELF']}?action={$this->action}&passwd_reset=$workshop_id\">neues Passwort erzeugen</a>";
                    }

                    echo <<<HEREDOC
    				    <tr>
    				      <td>{$wks['zip_code']}</td>
    				      <td>{$wks['location']}</td>
    				      <td>{$wks['name']}</td>
                          <td>$zspls</td>
    				      <td>{$login['username']}</td>
                          <td>$td_passwd</td>
    				    </tr>
HEREDOC;
                }
                ?>
                </tbody>
            </table>
        </div>
        </div><?php
    }


    if (count($this->all_workshops)) {
        ?>
        <h3>Werkstätte ohne Zugang</h3>
        <div class="wksFrame">
        <table class="wksTable wksHead">
            <thead>
            <tr>
                <td>PLZ</td>
                <td>Ort</td>
                <td>Firma</td>
                <td>Zugeordnete Zspls (Id)</td>
                <td>Login</td>
                <td>Password</td>
                <td>&nbsp;</td>
            </tr>
            </thead>
        </table>
        <div class="wksTableArea">
            <table class="wksTable wksBody">
                <tbody><?php


                foreach ($this->all_workshops as $workshop_id => &$wks) {
                    $login = sprintf("WKS%05d.%d", $wks['zip_code'], $wks['workshop_id']);
                    $zspls = str_replace(',', ', ', substr($wks['zspls'], 1, -1));

                    echo <<<HEREDOC
    				    <tr>
    				      <td>{$wks['zip_code']}</td>
    				      <td>{$wks['location']}</td>
    				      <td>{$wks['name']}</td>
                          <td>$zspls</td>
    				      <td><input class="wksLogin" type="text" size="24" name="login[$workshop_id]" id="id_login_$workshop_id" value="$login"></td>
                          <td><a href="javascript:return 0;" OnClick="createAccount($workshop_id)">Login anlegen</a></td>
    				    </tr>
HEREDOC;
                }

                ?>
                </tbody>
            </table>
        </div>
        </div><?php
    }

    echo "
			</div>
";
} else {
    if (!empty($this->overview)) {
        ?>
        <div class="row ">
            <div class="columns eight">
                <?php $this->overview->printContent(); ?>
            </div>
        </div>
    <?php } ?>

    <?php
}

echo "
        </div>";

?>
<div id="dialog-form"></div>
