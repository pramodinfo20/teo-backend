<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="mainForm" id="id_mainForm">
<input type="hidden" name="action" value="locationplan">
<input type="hidden" name="hidden_command" id="id_command" value="">
-!-- <input type="hidden" name="showSubPos" id="id_showSubPos" value="<?php echo $this->S_showSubPos; ?>"> -->
<input type="hidden" name="vpX" id="id_vpX" value="<?php echo $this->S_viewpoint[_X_];?>">
<input type="hidden" name="vpY" id="id_vpY" value="<?php echo $this->S_viewpoint[_Y_];?>">
<input type="hidden" name="vpZ" id="id_vpZ" value="<?php echo $this->S_viewpoint[_Z_];?>">
<div id="main">
<?php

if ($this->print)
{
    $this->locations[1]->WriteMap ();
    $this->locations[1]->DrawVehicles ();
    $this->FahrzeugTabelle_PrintVersion ();
    echo '<div id="printversion"></div>';
    echo '<div id="printbutton"><input type="button" value="drucken" onClick="window.print();"></div>';
}
else
{

    $refPrint = "";
    $disabled = count ($this->locations[1]->myVehicles) ? "" : " disabled";

    $urlPrint   = sprintf ("%s?action=%s&print=preview", $_SERVER['PHP_SELF'], $_REQUEST['action']);
    $refPrint   = "<a href=\"$urlPrint\" id=\"id_print\" onClick=\"return openPrintPrewiew('$urlPrint')\"$disabled><img src=\"images/symbols/printer-16x16.png\"></a>";

    ?>
      <div class="divTabelle" id="TabellenKopf">
        <table class="fztable" id="fztheader">
          <tr>
            <td style="text-align:center;"><?php echo $refPrint;?></td>
            <td>VIN</td>
            <td>Penta Kenn.</td>
            <td>Artikel</td>
            <td>Standort</td>
            <td>Park</td>
            <td style="position:relative">
              <a href="javascript:maximizeTable()" id="id_maximize"><img src="/images/symbols/maximize.png"></a>
              <a href="javascript:restoreTable()" id="id_restore"><img src="/images/symbols/restore.png"></a>
            </td>
          </tr>
        </table>
      </div>
      <div class="divTabelle" id="Tabelle">
        <table class="fztable" id="fztbody">

    <?php
    //===============================================

    $this->FahrzeugTabelle ();

    //===============================================
    ?>
        </table>
      </div>
      <div class="divTabelle" id="TabellenFunktionen">
        <input type="checkbox" onChange="SelectDeselectAll(this.checked)">
      <span style="margin:10px 10px 0px 5px;">
        Alle aus- / abwählen<br>
        <a href="javascript:InvertSelection();">Auswahl umkeheren</a>
      </span>
      <span style="float:right; margin:2px 16px;">
        <select name="filterByLocation" class="button" id="id_filterByLocation" onChange="filterByLocation(this)">
          <option value="all">Alle Orte</option>

    <?php
    //===============================================

    echo $this->GetLocationOptionsList ();

    //================================================
    ?>
          </select>
        <input type="submit" name="command[remove_selected]" class="button" value="[X] Auswahl entfernen" >&nbsp;
        <input type="submit" name="command[remove_all]" class="button" value="Alle entfernen">

      </span>
      </div>

<!--      <div id="FahrzeugListe" style="visibility:<?php echo $this->S_showSubPos ? 'hidden':'visible'; ?>;"> -->
      <div id="FahrzeugListe">
        <h2>Fahrzeugliste</h2>
      <textarea name="liste" id="liste" placeholder="VIN's, AKZ, IKZ oder Penta-Kennwörter

      (Komma, Semicolon oder Zeilenumbruch getrennt)"></textarea> <!--  &crarr;  -->
      <input type="submit" name="command[loadList]" id="loadList" class="button" value="Fahrzeugliste einladen">
      </div>
        <?php
/*
      <div id="SubPositionen" style="visibility:<?php echo $this->S_showSubPos ? 'visible':'hidden'; ?>;">
        <h2>Sub-Positionen</h2>
          <select name="subPosition[liste]" style="width:195px;" size="8">
            <?php
              echo $this->GetHtml_SelectOptions (reduce_assoc ($this->subPositions, 'name'), $this->S_currSubPos, 12);
            ?>
          </select><br>
          <div>Neue Sub-Position</div>
      <input type="text" name="subPosition[name]">
      <input type="submit" name="command[addSubPosition]" class="button" value="anlegen">
      </div>

  <?php
     if (false) {
  ?>
      <div id="d12">
         <div class="utab"><a href="javascript:ShowSubPositionen(false)">Fahrzeugliste</a></div> <div class="utab"><a href="javascript:ShowSubPositionen(true)">Sub-Positionen</a></div>
      </div>
    <?php
     }

    ?>

    <?php
*/
    //===============================================
    foreach ($this->locations as $i=>$loc)
    {
        $loc->WriteMap ();
    }
    //================================================


      echo <<<HEREDOC
        <div id="Umkreissuche">
        <h2>Umkreissuche</h2>
        <div id="d10">
          <div id="d1">Radius<br>
            <input type="text" name="radius" id="id_radius" size="6" value="{$radius}" {$vp_disabled}>
            <span>m</span>
          </div>
          <div id="d2">
            max. Anzahl<br>
            <input type="number" name="limit" id="id_limit" step="1" min="1" max="99" class="number"  size="3" value="{$limit}" {$vp_disabled}>
          </div><br>
          <div id="d5">
            <input type="checkbox" id="id_invert" name="invert" {$invert}{$vp_disabled}>
            <div class="info">außerhalb des Radius</div><br>
            <input type="checkbox" id="id_select_only" name=select_only {$select_only}{$so_disabled}>
            <div class="info">nur in Liste selektieren </div><br>
          </div>
        </div>
HEREDOC;


      echo <<<HEREDOC
      <div id="d01">
          nach Fahrzeugtyp filtern<br>
          <div id="d3">
            <input type="checkbox" id="id_B14" name="cb_variant[B14]"{$cbFilter_B14}{$vp_disabled}><div>B14</div><br>
            <input type="checkbox" id="id_B16" name="cb_variant[B16]"{$cbFilter_B16}{$vp_disabled}><div>B16</div>
          </div>
          <div id="d4">
            <input type="checkbox" id="id_E16" name="cb_variant[E16]"{$cbFilter_E16}{$vp_disabled}><div>E16</div><br>
            <input type="checkbox" id="id_D16" name="cb_variant[D16]"{$cbFilter_D16}{$vp_disabled}><div>D16</div>
          </div>
        </div><br>
      <div id="d11">
          <input type="submit" id="id_select" name="command[select]" class="button"{$vp_disabled}" value="Umkreissuche">
        </div>
      </div>
HEREDOC;

        $elected = ['vin'=>"",'code'=>"",'ikz'=>"",'penta_kennwort'=>""];
        $elected[$this->S_search['suchspalte']] = ' selected';

        echo <<<HEREDOC
      <div id="Einzelsuche">
        <h2>Einzelfahrzeuge suchen</h2>
        <select name="suchspalte" id="id_suchspalte">
            <option value="vin"{$elected['vin']}>VIN</option>
            <option value="code"{$elected['code']}>Kennzeichen</option>
            <option value="ikz"{$elected['ikz']}>IKZ</option>
            <option value="penta_kennwort"{$elected['penta_kennwort']}>Penta Kenwort</option>
        </select>
        <input type="text" name="suchtext" id="id_suchtext" value="{$this->S_search['suchtext']}" placeholder="Suche mit Platzhalter: '*' und '?'" onChange="Execute ('suche');">
        <input type="submit" name="command[suche]" value="suchen" id="id_suchen">
      </div>
HEREDOC;

        $append_checked = ($this->S_search['append_results'] ? " checked" : "");

        echo <<<HEREDOC
      <div id="id_option_append">
        <input type="checkbox" name="append_results" id="id_append_results"{$append_checked}>
        Ergebnisse an Suche anhängen. (nicht vorher löschen!)
      </div>
HEREDOC;

    //===============================================
    foreach ($this->locations as $i=>$loc)
    {
        $loc->DrawVehicles ();
    }

    //================================================
}

?>
</div>
</form>
