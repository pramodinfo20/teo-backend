<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>StreetScooter Cloud Systems : Engineering</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="/css/skeleton.css?5316">
    <link rel="stylesheet" type="text/css" href="/css/style.css">
    <link rel="stylesheet" type="text/css" href="css/genericons/genericons.css?28266">
    <link rel="stylesheet" type="text/css" href="js/newjs/jquery-ui.css?34730">
    <link rel="stylesheet" type="text/css" href="js/newjs/jquery-ui.structure.css?18028">
    <link rel="stylesheet" type="text/css" href="js/newjs/jquery-ui.theme.css?16924">
    <link rel="stylesheet" type="text/css" href="css/theme.default.css?6665">
    <script type="text/javascript" src="js/sts-tools.js?filever=1344"></script>
    <script type="text/javascript" src="js/jquery-2.2.0.min.js?filever=85589"></script>
    <script type="text/javascript" src="js/newjs/jquery-ui.min.js?filever=212568"></script>
    <script type="text/javascript" src="js/sts-tools.js?filever=3185"></script>
    <script type="text/javascript" src="js/sts-custom.js?filever=3185"></script>
    <style>

        ul.strichliste {
            list-style-type: none;
        }

        ul.strichliste li::before {
            content: "- ";
        }

        ol.alfabetisch {
            list-style-type: upper-alpha;
        }

        ol.ebene1 {
            counter-reset: listenpunkt_ebene1;
            list-style-type: none;
        }

        ol.ebene1 li:before {
            content: counter(listenpunkt_ebene1) ") ";
            counter-increment: listenpunkt_ebene1;
        }

        ol.ebene2 {
            counter-reset: listenpunkt_ebene2;
            list-style-type: none;
        }

        ol.ebene2 li:before {
            content: counter(listenpunkt_ebene1) "." counter(listenpunkt_ebene2) ") ";
            counter-increment: listenpunkt_ebene2;
        }

        ol.ebene3 {
            counter-reset: listenpunkt_ebene3;
            list-style-type: none;
        }

        ol.ebene3 li:before {
            content: counter(listenpunkt_ebene1) "." counter(listenpunkt_ebene2) "." counter(listenpunkt_ebene3) ") ";
            counter-increment: listenpunkt_ebene3;
        }

        p {
            margin-top: 2px;
        }

        h1 {
            margin-top: 3em;
        }

        ]
        h2 {
            margin-top: 2em;
            margin-bottom: 1em;
        }

        h3 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 0px;
        }

        span.underline {
            text-decoration: underline;
        }

    </style>
</head>
<body class="logged-in">
<div class="pagewrap">
    <div class="container">
        <div class="row">
            <div class="six columns div4logo">
                <a href="https://streetscooter-cloud-system.eu/">
                    <img src="/images/Logo_StreetScooter_Long.svg" class="sts_logo" alt="StreetScooter">
                </a>
            </div>
            <div class="six columns div4logo">
                <img src="/images/dplogo.svg" class="dp_logo" alt="StreetScooter">
            </div>
        </div>

        <div class="row user_login_info">
            <div class="six columns" style="text-align: left; padding:6px 0; white-space: nowrap;">&nbsp;</div>
            <div class="six columns" style="text-align: right; padding:6px 0">&nbsp;</div>
        </div>

        <div class="container">
            <div class="row">
                <div class="seitenteiler" style="width:10%;">&nbsp;</div>
                <div class="seitenteiler" style="width:70%;">

                    <?php

                    if (!isset($_SESSION))
                        session_start(['cookie_lifetime' => 86400,]);

                    if (!empty ($_SESSION["sts_username"])) {
                        ?>

                        <h1>Datenschutzerklärung für Anwender</h1>
                        <?php
                        if ($reAcceptPrivacy) echo <<<HEREDOC
        <h2 style="color:#c20">ACHTUNG: Unsere Datenschutzerklärung wurde am $datumPrivacy geändert. Dadurch ist eine erneute Zustimmung Ihrerseits erforderlich.</h2>
HEREDOC;
                        else if (empty ($_SESSION['sts_privacy_accepted']))
                            echo <<<HEREDOC
<h4>Zustimmung erforderlich! (weiter unten)</h4>
HEREDOC;
                        ?>

                        <h2>Inhalt</h2>
                        <p>
                        <ol class="ebene1">
                            <li><a href="#sect-1">Kontaktdaten</a>
                                <ol class="ebene2">
                                    <li><a href="#sect-1.1">Name / Kontakt des Verantwortlichen</a></li>
                                    <li><a href="#sect-1.2">Name / Kontakt des Datenschutzbeauftragten</a></li>
                                    <li><a href="#sect-1.3">Aufsichtsbehörde</a>
                                        <ol class="ebene3">
                                            <li><a href="#sect-1.3.1">Beschwerdehinweis bei Aufsichtsbehörde</a></li>
                                            <li><a href="#sect-1.3.2">Kontakt Aufsichtsbehörde</a></li>
                                        </ol>
                                    </li>
                                </ol>
                            </li>
                            <li>Art der Datenerhebung
                                <ol class="ebene2">
                                    <li>Daten</li>
                                    <li>Zweck</li>
                                    <li>Speicherdauer</li>
                                </ol>
                            </li>
                            <li>Auskunftsrecht</li>
                            <li>Hinweis Recht auf Einschränkung der Verarbeitung</li>
                            <li>Hinweis Recht auf Berichtigung</li>
                            <li>Hinweis Recht auf Löschung</li>
                            <li>Hinweis Recht auf Datenübertragung</li>
                            <li>Technisch / organisatorische Maßnahmen</li>
                        </ol>
                        </p>


                        <h2 id="sect-1">1) Kontaktdaten</h2>
                        <h3 id="sect-1.1">1.1) Name / Kontakt des Verantwortlichen</h3>
                        <a href="/html/impressum.html">Impressum</a>

                        <h3 id="sect-1.2">1.2) Name / Kontakt des Datenschutzbeauftragten</h3>
                        <p>Wenn Sie Fragen hinsichtlich der Verarbeitung Ihrer persönlichen Daten haben, können Sie sich
                            an die Datenschutzbeauftragte der Deutschen Post AG wenden.
                            Auch im Falle von Auskunftsersuchen, Anregungen oder Beschwerden stehen wir Ihnen zur
                            Verfügung.
                        </p>

                        <p>
                            <strong>Datenschutzbeauftragte</strong><br>
                            <b>Imatec GmbH</b><br>
                            Hans-Jürgen Fellgiebel<br>
                            Bickerather Str. 3<br>
                            52152 Simmerath</p>
                        <p>
                            <span class="LabelX W080">Telefon:  </span>0049 (0)2473 928 7925<br>
                            <span class="LabelX W080">Mobil:    </span>0049 (0)175 584 0627<br>
                            <span class="LabelX W080">E-Mail:   </span><a href="mailto:fellgiebel@imatec.de">mailto:fellgiebel@imatec.de</a>
                        </p>

                        <p>
                            Kontaktieren Sie für Ihre Wünsche auf Auskunft, Einschränkung der Verarbeitung,
                            Berichtigung,
                            Löschung oder Datenübertragung (Punkte 2 bis 6)
                            bitte direkt uns bei StreetScooter <a href="mailto:ticket@streetscooter-cloud-system.eu">mailto:ticket@streetscooter-cloud-system.eu</a>
                            oder den Datenschutzbeauftragten.
                        </p>
                        <p>
                            Bitte verstehen Sie das wir in Ihren eigenem Interesse bei jeglichen Wünschen von Ihnen,
                            sicher stellen müssen, dass die Anfrage wirklich von Ihnen kommt, um zu verhindern, dass
                            Ihre
                            Daten an unberechtigte Dritte gelangen oder jemand unberechtigt Ihre Daten verändert. Wir
                            werden - falls unten keine andere Frist angegeben ist - Ihren Wünschen unverzüglich, dies
                            bedeutet innerhalb eines Monats - nachkommen.
                            Des weiteren werden wir Ihnen verschiedenen Wege der sicheren Datenübertragung anbieten wie
                            der Übermittlung per verschlüsselter Mail (PGP- oder S/MIME – Verfahren, Hilfe erhalten Sie
                            z.B. unter
                            <a href="https://www.bsi-fuer-buerger.de/BSIFB/DE/Empfehlungen/Verschluesselung/Verschluesseltkommunizieren/Einsatzbereiche/einsatzbereiche.html"
                               target="_blank">
                                https://www.bsi-fuer-buerger.de/BSIFB/DE/Empfehlungen/Verschluesselung/Verschluesseltkommunizieren/Einsatzbereiche/einsatzbereiche.htm</a>,
                            per Post oder via geschützten Downloads (Https – erkennbar am Schlüsselsymbol in der
                            Adresszeile Ihres Browsers) wobei wir Ihnen die Zugangsdaten
                            per PGP- oder S/MIME-verschlüsselter Mail zustellen.
                        </p>
                        <p>
                            Bitte beachten Sie: Fall Sie uns kontaktieren und uns Merkmale nennen, zu denen wir keine
                            Namen gespeichert haben, kann dies dazu führen, dass aus anonymen Daten bei uns
                            personenbezogenen Daten werden.
                            Speichern wir Daten zum Beispiel nur anhand eines Benutzernamens worin Ihr wirklicher Name
                            nicht enthalten ist, Sie uns nun unter Nennung Ihres Namens einen Auskunftsversuch oder
                            Löschgesuch zu den Daten,
                            die unter diesem Benutzernamen gespeichert sind, schicken, erhalten wir die Person zu diesem
                            Benutzernamen. <br>
                            Des weiteren müssen Sie uns auch immer nachweisen können, dass die Daten unter einem
                            bestimmten Merkmal auch zu Ihrer Person gehören,
                            d.h. das Sie dieses Merkmal / diese Nummer etc. besitzen.
                        </p>
                        <p>&nbsp;</p>

                        <h3 id="sect-1.3">1.3) Aufsichtsbehörde</h3>
                        <h4 id="sect-1.3.1">1.3.1) Beschwerdehinweis bei Aufsichtsbehörde</h4>
                        <p>Wir möchten Sie hier darauf hinweisen, dass Sie jederzeit die Möglichkeit haben, gegen unser
                            Vorgehen Widerspruch bei der
                            Datenschutzaufsichtsbehörde - Kontaktdaten siehe unten – einzureichen.
                        </p>


                        <h4 id="sect-1.3.1">1.3.2) Kontakt Aufsichtsbehörde</h4>
                        <p><b>Land NRW<br>
                                Landesbeauftragte für Datenschutz und Informationsfreiheit Nordrhein-Westfalen</b><br>
                            Helga Block<br>
                            Postfach 20 04 44<br>
                            Kavalleriestraße 2-4<br>
                            40213 Düsseldorf</p>
                        <p>
                            <span class="LabelX W080">Telefon:	</span>02 11/384 24-0<br>
                            <span class="LabelX W080">Telefax: 	</span>02 11/384 24-10<br>
                            <span class="LabelX W080">E-Mail: 	</span><a href="mailto:poststelle@ldi.nrw.de">poststelle@ldi.nrw.de</a><br>
                            <span class="LabelX W080">(PGP Key) </span><a
                                    href="https://www.ldi.nrw.de/metanavi_Kontakt/index.php" target="_blank">https://www.ldi.nrw.de/metanavi_Kontakt/index.php</a><br>
                            <span class="LabelX W080">Homepage: </span><a href="https://www.ldi.nrw.de" target="_blank">https://www.ldi.nrw.de</a>
                        </p>

                        <h2 id="sect-2">2) Art der Datenerhebung</h2>
                        <h3 id="sect-2.2">2.1) Daten</h3>
                        <p>
                            A) Vor der Anmeldung d.h. durch das reine Aufrufen unserer Seite:<br>
                            - IP-Adresse [interner Link Hinweis Speicherung]<br>
                            B) Mit der Anmeldung (auch bei fehlgeschlagener Anmeldung)<br>
                            B1) Login(Versuch)zeit: Wir speichern wann Sie versucht haben sich anzumelden und ob, die
                            Anmeldung erfolgreich war.<br>
                            B2) Bei Der Account-Erstellung durch Andere wie zum Beispiel Ihre Vorgesetzten wurden von
                            diesen folgende Daten an uns geliefert:<br>
                            - Benutzername<br>
                            - Gegenfalls Zuordnung zu einer Organisationseinheit in Ihrem Unternehmen<br>
                            - Email-Adresse<br>
                            C) Nach der erfolgreichen Anmeldung erhalten Sie weitere Information über Daten, die nach
                            Ihrer Zustimmung dann erhoben werden.<br>
                            <br>
                            Weder bei A) noch B) werden sogenannte Cookis oder Tracking-Verfahren, die über den Umfang
                            in
                            B1 beschrieben hinausgehen, eingesetzt.<br>
                            Tracking-Verfahren [https://de.wikipedia.org/wiki/Web_Analytics] sind Verfahren bei denen
                            Ihre
                            Aktivitäten (z.B. Anklicken von Verknüpfungen, Öffnen von Fenster, Eingabe von Text)
                            inklusive
                            des Zeitpunktes) aufgezeichnet werden. Daraus lässt sich Ihre persönliches Nutzerverhalten
                            ermitteln.
                        </p>

                        <h3 id="sect-2.2">2.2) Zweck</h3>
                        <p>
                            Mit der Anmeldung (auch bei fehlgeschlagener Anmeldung)
                            A) Vor der Anmeldung d.h. durch das reine Aufrufen unserer Seite:
                            - Link Hinweis Speicherung IPs
                            B1) Zum Schutz vor Ausprobieren von Passwörtern beschränken wir die Anzahl der
                            Login-Versuche
                            pro Zeit und Account und müssen Sie deshalb kurzfristig speichern.
                            Um einen Missbrauch Ihres Account feststellen zu können, müssen wir auch Daten über einen
                            längeren Zeitraum auswerten und deshalb länger speichern.
                            B2) Um einen Account Ihnen zuordnen zu können, müssen die Anleger/innen Ihres Accounts einen
                            eindeutigen Benutzernamen wählen.
                            Dieser Benutzername braucht nicht Ihren persönlichen Namen zu enthalten, worauf bei der
                            Account-Erstellung hingewiesen wird. Für die Zustellung (eines Teiles) Ihrer Login-daten
                            wird
                            eine E-Mail-Adresse von Ihnen benötigt.
                            Eine anderweitige Form der Benachrichtigung an Sie ist leider wegen des nicht zumutbaren
                            Aufwandes nicht möglich.
                        </p>

                        <h3 id="sect-2.2">2.3) Speicherdauer</h3>
                        <p>

                            B1) Wir speichern einzelne personenbezogene Daten 1 Monat lang. Einige Daten werden für
                            statistische Zwecke <a
                                    href="http://eur-lex.europa.eu/legal-content/DE/TXT/HTML/?uri=CELEX:32016R0679&from=DEP#d1e6503-1-1"
                                    target="_blank">(gemäß Artikel 89 Absatz 2 der DSGVO)</a> bis zu 45 Tagen
                            gespeichert.<br>
                            Die Daten auf Backup (Kopien Ihrer Daten, die nicht an der aktiven Nutzung teilnehmen)
                            werden
                            1 Jahre lang gespeichert.<br>
                            B2) Diese Daten bleiben so lange gespeichert, bis Ihr Account durch Ihren Wunsch oder durch
                            jemand anderes gelöscht wird.<br>
                            Die Löschung erfolgt bei einer Löschung durch Dritte sofort, bei einen Antrag von Ihnen
                            innerhalb eines Monates. Daten auf Backup (Kopien Ihrer Daten,
                            die nicht an der aktiven Nutzung teilnehmen) werden wegen des sonst nicht zumutbaren
                            Aufwandes
                            erst nach 3 Monaten gelöscht,
                            wobei eine Nutzung dieser Daten jedoch von uns verhindert wird. D.h. bei einer
                            Wiederherstellung von Daten werden die zur Löschung beantragten Datensätze nicht
                            wiederhergestellt. Alternativ behalten wir uns vor, ihre Daten verschlüsselt zu speichern
                            und
                            bei einer Löschung nur den für Ihre Daten individuellen Schlüssel zu löschen,
                            sodass eine Nutzung der Daten auf dem Backup unmöglich ist.
                        </p>

                        <h2 id="sect-3">3) Auskunftsrecht</h2>
                        <p>
                            Nach Artikel 15 <a
                                    href="http://eur-lex.europa.eu/legal-content/DE/TXT/HTML/?uri=CELEX:32016R0679&from=DEP#d1e2528-1-1"
                                    target="_blank">(http://eur-lex.europa.eu/legal-content/DE/TXT/HTML/?uri=CELEX:32016R0679&amp;from=DEP#d1e2528-1-1)</a>
                            der Datenschutzgrundverordnung haben Sie jederzeit ein Auskunftsrecht, welche Daten wir von
                            Ihnen gespeichert haben.
                            Im Konkreten haben Sie ein Recht auf folgende Informationen:
                            a) Verarbeitungszwecke (siehe interner Link)
                            b) Kategorien personenbezogener Daten, die verarbeitet werden
                            c) Die Empfänger oder Kategorien von Empfängern, gegenüber denen die personenbezogenen Daten
                            offengelegt worden sind oder noch offengelegt werden,
                            insbesondere bei Empfängern in Drittländern oder bei internationalen Organisationen
                            d) Falls möglich die geplante Dauer, für die die personenbezogenen Daten gespeichert werden,
                            oder,
                            falls dies nicht möglich ist, die Kriterien für die Festlegung dieser Dauer
                            e) Das Bestehen eines Rechts auf Berichtigung oder Löschung der sie betreffenden
                            personenbezogenen Daten oder auf
                            Einschränkung der Verarbeitung durch den Verantwortlichen oder eines Widerspruchsrechts
                            gegen
                            diese Verarbeitung;
                            f) Das Bestehen eines Beschwerderechts bei einer Aufsichtsbehörde
                            g) Wenn die personenbezogenen Daten nicht bei der betroffenen Person erhoben werden, alle
                            verfügbaren Informationen über die Herkunft der Daten
                            h) Das Bestehen einer automatisierten Entscheidungsfindung einschließlich Profiling gemäß
                            Artikel 22 Absätze 1 und 4
                            und — zumindest in diesen Fällen — aussagekräftige Informationen über die involvierte Logik
                            sowie die Tragweite
                            und die angestrebten Auswirkungen einer derartigen Verarbeitung für die betroffene Person.
                        </p>
                        <p>
                            Zusätzliche Hinweis bei Anfragen bei uns:
                            Bitte bedenken Sie, das bei allen Anfragen / Aufforderungen zu anhand einer zu einer
                            Fahrzeugidentifikationsnummer (FIN, Engl. VIN – finden Sie z.B. in der
                            Zulassungsbescheinigung
                            Teil1 Ihres Fahrzeuges) wird aufgrund Ihrer Anfrage eine Zuordnung zwischen FIN und Ihrem
                            Namen herstellen können, was vorher nicht möglich war.
                            Außerdem ist in diesem Fall ein Nachweis von Ihnen notwendig, dass
                            1) Sie über das entsprechende Fahrzeug verfügen. Dies kann z.B. über eine Kopie der
                            Zulassungsbescheinigung Teil und Ihres Personalausweises erfolgen bzw. einer Bescheinigung
                            des/der Fahrzeughalters/in.
                            2) Es um Daten des Zeitraumes geht, in dem Sie das Fahrzeug selber genutzt haben.
                        </p>

                        <h2 id="sect-4">4) Hinweis Recht auf Einschränkung der Verarbeitung</h2>
                        Nach Artikel 18 <a
                                href="http://eur-lex.europa.eu/legal-content/DE/TXT/HTML/?uri=CELEX:32016R0679&from=DEP#d1e2715-1-1"
                                target="_blank">(http://eur-lex.europa.eu/legal-content/DE/TXT/HTML/?uri=CELEX:32016R0679&amp;from=DEP#d1e2715-1-1)</a> der Datenschutzgrundverordnung haben Sie das Recht, eine Einschränkung der Verarbeitung Ihrer Daten bei uns zu fordern.
                                                                                                                                                        Dies können Sie immer dann tun, wenn Sie meinen, dass einer der folgenden Fälle vorliegt:
                        <ol class="alfabetisch">
                            <li> Ihre Daten sind nicht korrekt. Beispiel, weise wurde eine falsche Email-Adresse
                                gespeichert oder Ihr Name falsch geschrieben oder
                                eine falsche Uhrzeit Ihres Seitenzugriffes gespeichert.
                            </li>
                            <li> Sie meinen, dass wir Ihre Daten unrechtmäßig verarbeiten Sie aber keine Löschung (siehe
                                interner Link) fordern. Beispiel: Sie halten die Speicherzeit von Login-Versuchen für zu
                                lange,
                                wollen aber nicht deren Löschen, aber die Auswertung dieser Daten verhindern.
                            </li>
                            <li> Wir Daten nicht länger für die genannten Zwecke benötigen, Ihre Daten deshalb löschen
                                möchten, Sie dies aber für die Geltungsmachung / Ausübung oder
                                Verteidigung von Rechtsansprüchen benötigen: Beispiel: Wir möchten Ihre Login-Versuche
                                nach 3 Monaten löschen. Sie möchten dies jedoch nicht,
                                um sich gegen einen potenziellen Vorwurf einer absichtlichen Lastaussetzung unseres
                                Servers dadurch wehren zu können.
                            </li>
                            <li> Sie Widerspruch gemäß Artikel 21 der Datenschutzgrundverordnung (Widerspruch gegen
                                Verarbeitung Ihrer Daten) eingelegt haben, darüber aber noch keine Entscheidung
                                vorliegt.
                                Beispiel: Sie halten Ihre Gründe für die Speicherdauer Ihrer IP-Adresse für berechtigter
                                als unser Interesse aus Sicherheitsgründe und haben deshalb bei der Aufsichtsbehörde
                                eine
                                kürzere Speicherdauer gefordert. Die Aufsichtsbehörde hat darüber aber noch noch
                                entschieden und deshalb verlangen Sie, dass Ihre IP-Adresse bis zur Entscheidung
                                nicht mehr in Auswertungen mitein bezogen wird.
                            </li>
                        </ol>

                        <h2 id="sect-5">5) Hinweis Recht auf Berichtigung</h2>
                        Nach Artikel 16 <a
                                href="http://eur-lex.europa.eu/legal-content/DE/TXT/HTML/?uri=CELEX:32016R0679&from=DEP#d1e2614-1-1"
                                target="_blank">(http://eur-lex.europa.eu/legal-content/DE/TXT/HTML/?uri=CELEX:32016R0679&amp;from=DEP#d1e2614-1-1)</a> der Datenschutzgrundverordnung haben Sie das Recht auf eine Berichtigung
                                                                                                                                                        oder/und Vervollständigung Ihrer bei uns gespeicherten Daten. Beispiele:
                                                                                                                                                        Sie möchten Ihre E-Mail Adresse korrigieren.
                                                                                                                                                        Sie möchten neben Ihrem Login-Namen Ihren vollen Namen angegeben haben.

                        <h2 id="sect-6">6) Hinweis Recht auf Löschung</h2>
                        Nach Artikel 17 <a
                                href="http://eur-lex.europa.eu/legal-content/DE/TXT/HTML/?uri=CELEX:32016R0679&from=DEP#d1e2621-1-1"
                                target="_blank">(http://eur-lex.europa.eu/legal-content/DE/TXT/HTML/?uri=CELEX:32016R0679&amp;from=DEP#d1e2621-1-1)</a> der Datenschutzgrundverordnung haben Sie das Recht auf
                                                                                                                                                        eine Löschung Ihrer Daten , wenn
                        <ol class="alfabetisch">
                            <li> Die Zwecke für die Speicherung nicht mehr gegeben sind. Beispiel: Unser Server wurde
                                abgeschaltet -> Login-Überwachung ist nicht mehr zum Schutz des Server notwendig.
                            </li>
                            <li> Sie Ihre Einwilligung widerrufen haben. (Wir arbeiten ohne Einwilligung, deshalb ist
                                dies
                                hier nicht anwendbar.)
                            </li>
                            <li> Sie Widerspruch gegen die Verarbeitung Ihrer Daten eingelegt haben und unsere Gründe
                                für
                                die Verarbeitung der Daten nicht vorrangig sind.
                                (Da wir Ihre Daten nicht für Werbezwecke einsetzen entfällt der 2. Art des Widerspruchs
                                hier.)
                            </li>
                            <li> Wir Ihre Daten unrechtmäßig verarbeitet haben. Beispiel: Ihr Account wurde von jemanden
                                angelegt, der dies nicht durfte und wir haben dies durch unzureichende Prüfung
                                zugelassen.
                            </li>
                            <li> Ein Gesetzt dies verlangt.</li>
                            <li> Informationsdienste an Kindern angeboten wurden - trifft bei uns nicht zu.</li>
                        </ol>
                        Eine Veröffentlichung Ihrer Daten findet bei uns in keinem Fall statt.
                        Die von Ihnen gespeicherten Daten dienen weder der freien Meinungsäußerung noch rechtlichen Verpflichtungen, noch dem öffentlichem Interesse, noch öffentlichem Archivzwecken, noch der Verteidigung von Rechtsansprüchen,
                        sodass Ihr Löschgesuch nicht von uns abgelehnt werden kann.

                        <h2 id="sect-7">7) Hinweis Recht auf Datenübertragung</h2>
                        Nach Artikel 20 <a
                                href="http://eur-lex.europa.eu/legal-content/DE/TXT/HTML/?uri=CELEX:32016R0679&from=DEP#d1e2768-1-1"
                                target="_blank">(http://eur-lex.europa.eu/legal-content/DE/TXT/HTML/?uri=CELEX:32016R0679&amp;from=DEP#d1e2768-1-1)</a> haben Sie das Recht, die von Ihnen selbst bereitgestellten Daten in
                                                                                                                                                        einem strukturierten, gängigen und maschinenlesbaren Format zu erhalten oder an jemanden dritten übertragen zu zulassen.
                                                                                                                                                        Die von Ihnen stammendes Daten können Ihr Benutzername und Ihre Email-Adresse sein. Aus Sicherheitsgründen wird Ihr Passwort nicht im Klartext gespeichert, sodass wir Ihnen dies nicht liefern können.
                                                                                                                                                        Als Datenformat nutzen wir CSV [https://de.wikipedia.org/wiki/CSV] nach RFC 4180.

                        <h2 id="sect-8">8) Technisch / organisatorische Maßnahmen</h2>
                        Zum Schutz Ihrer Daten setzen wir folgende technischen und organisatorischen Maßnahmen ein:
                        <ul class="strichliste">
                            <li> Speicherung Ihrer Daten ausschließlich auf dem Gebiet der Bundesrepublik Deutschland
                                durch Firmen (inkl. Mutterkonzern) der Bundesrepublik Deutschland
                            </li>
                            <li> Speicherung durch Firmen zertifiziert nach ISO27001 und ISO9001. Dies weißt ein
                                geprüftes
                                Datensicherheitskonzept und Maßnahmen nach sowie ein
                            </li>
                            <li> Qualitätssicherungskonzept nach.</li>
                            <li> Rechenzentrum zertifiziert nach EN50600 (Physikalische Sicherheit)</li>
                            <li> Regelmäßige Test der Sicherheit (Pen-test) durch unabhängige Experten.</li>
                            <li> Einsatz von ausschließlich quell-offener Software (keine Versteckten
                                Spionage-Funktionen
                                durch Software von außerhalb der EU).
                            </li>
                            <li> Internes sicherheit-geschultes und zertifiziertes Personal.</li>
                        </ul>
                        <p>&nbsp;</p>
                        <?php if (empty ($_SESSION['sts_privacy_accepted']) || $reAcceptPrivacy) { ?>
                            <p class="privacyButtonsIntern">
                                <span class="privacyButton" id="id_accept_privacy2" data-msg="Privacy2-accept">ich stimme zu</span>
                                <span class="LabelX W060">&nbsp;</span>
                                <span class="privacyButton" data-msg="Privacy2-deny">ich stimme <span class="underline">nicht</span> zu</span>
                            </p>
                        <?php } ?>
                        <p>&nbsp;</p>


                        <?php
                    } else {
                        ?>
                        <h2>Diese Inhalte sind für nicht registrierte Anwender gesperrt!</h2>
                        <a href="/">&lt;&lt; zurück</a>
                        <?php
                    }
                    ?>
                </div>
            </div><!--row-->
        </div><!--container-->
    </div><!--container-->
</div><!--pagewrap-->
</body>
</html>
