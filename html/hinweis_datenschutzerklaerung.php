<?php
if ($this->user_logged_in) {
    $showThis = $showPrivacy2 ? 'none' : 'block';

    ?>
    <div class="privacyMsg" style="display: <?php echo $showThis; ?>">
        <h3>Hinweis Datenschitzerklärung</h3>
        <p>
            Diese Webseite verwendet von nun an sogenannte Cookies (siehe unten). Wenn Sie der Verwendung zustimmen,
            bestätigen Sie dies bitte.
            Andernfalls ist leider kein Login möglich.
        </p>
        <p>
            Cookies werden genutzt, um mit einer Website bzw. Domain verbundene Informationen für einige Zeit lokal auf
            dem Computer zu speichern
            und dem Server auf Anfrage wieder zu übermitteln. Dadurch kann der Anwender die Website für sich
            individualisieren,
            z. B. die Sprache und Schriftgröße bzw. Design der Website allgemein wählen. Cookies können außerdem
            verwendet werden, um Besucher zu authentifizieren,
            mit ihnen wird ein Sitzungsbezeichner (engl. Session-ID) gespeichert.
            (Aus Wikiepedia <a href="http://de.wikipedia.org/wiki/Cookie">http://de.wikipedia.org/wiki/Cookie</a>
            entnommen),
            der Text steht unter der Lizenz „Creative Commons Attribution/Share Alike“ <a
                    href="https://de.wikipedia.org/wiki/Wikipedia:Lizenzbestimmungen_Commons_Attribution-ShareAlike_3.0_Unported"
                    target="_blank">https://de.wikipedia.org/wiki/Wikipedia:Lizenzbestimmungen_Commons_Attribution-ShareAlike_3.0_Unported</a>
            .)
        </p>
        <p>
            Wir verwenden Cookies ausschließlich als zusätzlichen Autorisierungsmethode zusätzlich zu Ihrem Passwort.
            Eine zusätzliche Identifikation
            zu den eh durch Ihre Login-Daten gegeben Identifikation findet nicht statt.
        </p>
        <p>
            Sie erteilen uns hiermit keinerlei Einverständnis zur Speicherung von Daten von Ihnen.
            Sollte es irgendwann erneut notwendig sein Cookies bei Ihnen zu speichern, werden Sie erneut um
            Einverständnis gefragt.
        </p>
        <p>
            <a href="/html/datenschutzerklaerung-intern.php" class="privacyBig" target="_blank">Mehr zum Thema
                Datenschutz in unserer Datenschutzerklärung für Cloud-Anwender</a>
        </p>
        <p>&nbsp;</p>
        <p class="privacyButtons">
            <span class="privacyButton" data-msg="Privacy2-accept">ich stimme zu</span>
            <span class="LabelX W060">&nbsp;</span>
            <span class="privacyButton" data-msg="Privacy2-deny">ich stimme <u>nicht</u> zu</span>
        </p>
    </div>
    <?php
}
?>

