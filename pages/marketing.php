<?php
/**
 * marketing.php
 * Template für die Benutzer Rolle Marketing
 * @author Pradeep Mohan
 */
?>

<div class="inner_container">
    <div class="row ">
        <div class="columns twelve">
            <ul class="submenu_ul">

                <li>
                    <a href="index.php"
                       class="sts_submenu <?php if (!isset($this->action)) echo 'selected'; ?>">Home</a>
                </li>
                <li>
                    <a href="?action=listcsv" data-target="listcsv"
                       class="sts_submenu <?php if ($this->action == "listcsv") echo 'selected'; ?>">Drittkunden
                        Aufträge hochladen</a>
                </li>
                <li>
                    <a href="?action=listorders" data-target="listorders"
                       class="sts_submenu <?php if ($this->action == "listorders") echo 'selected'; ?>">Drittkunden
                        Aufträge Anzeige</a>
                </li>
            </ul>
        </div>
    </div>
    <?php
    if (is_array($this->msgs)): ?>
        <div class="row ">
            <div class="columns twelve">
		<span class="error_msg">
			<?php echo implode('<br>', $this->msgs); ?>
		</span>
            </div>
        </div>
    <?php endif; ?>
    <?php
    if (isset($this->action) && $this->action == "listcsv") :
        echo $this->qform_dritt->getContent();
        echo '<h3>Beispiel CSV </h3>'; ?>
        Auftragsnummer,Wunschtermin,Fahrzeugvariante,Fahrzeug Farbe,accountname,Straße (Rechnungsanschrift),Hausnummer,PLZ (Rechnungsanschrift),Stadt (Rechnungsanschrift),Primärer Kontakt,Kontakt Telefon
        <br>
        100318,27.02.2017,work,gelb,Vonovia GmbH,Julicher Str,1,52066,Aachen,Pradeep,176123456<br>
        900318,27.06.2017,work pure,orange,spijstaal,Mandemakerstraat ,150 - 152,3194 DG,Hoogvliet,New,182122466<br>
    <?php elseif (isset($this->action) && $this->action == "listorders") :
        echo $this->table_orders->getContent();
    endif;
    ?>
</div>		
