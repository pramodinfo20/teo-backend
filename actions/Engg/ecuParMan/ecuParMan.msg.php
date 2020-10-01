<?php

$default = MSGFLAG_ALLOW_HTML_TAGS;
$eingabefehler = "Eingabefehler";

define('MESSAGE_DONT_USE_BROWSER_BACK', MSG_CLASS_MESSAGES + 1);
define('MESSAGE_MASTER_REV_CHANGED', MSG_CLASS_MESSAGES + 2);

define('WARNING_DIFFERENT_VARIANTS', MSG_CLASS_WARNING + 0);
define('WARNING_BAD_PENTA_PRODUCT_KEY', MSG_CLASS_WARNING + 1);
define('WARNING_NO_VARIANT_ECU_REV', MSG_CLASS_WARNING + 2);
define('WARNING_ECU_USES_XCP_ONLY', MSG_CLASS_WARNING + 3);
define('WARNING_ECU_USES_XCP', MSG_CLASS_WARNING + 4);
define('WARNING_MULTIPLE_WC_CONFIG', MSG_CLASS_WARNING + 5);
define('WARNING_ECU_SW_NOT_RELEASED', MSG_CLASS_WARNING + 6);
define('WARNING_ECU_SW_UNCOMPLETE', MSG_CLASS_WARNING + 7);


define('ERROR_MISSING_TAG_TYPE', MSG_CLASS_ERROR + 8);
define('ERROR_MISSING_TAG_SIZE', MSG_CLASS_ERROR + 9);
define('ERROR_MISSING_TAG_ACTION', MSG_CLASS_ERROR + 10);
define('ERROR_MISSING_TAG_UDS_ID', MSG_CLASS_ERROR + 11);
define('ERROR_CANNOT_SAVE_VALUE', MSG_CLASS_ERROR + 12);
define('ERROR_CANNOT_SAVE_ODX02', MSG_CLASS_ERROR + 13);
define('ERROR_CANNOT_SAVE_PARAMDEF', MSG_CLASS_ERROR + 14);
define('ERROR_CANNOT_CONVERT_TO_HEX', MSG_CLASS_ERROR + 15);
define('ERROR_NO_HEXADZIMAL_STRING', MSG_CLASS_ERROR + 16);
define('ERROR_UNKNOWN_TYPE', MSG_CLASS_ERROR + 17);
define('ERROR_INVALID_NUMBER', MSG_CLASS_ERROR + 18);
define('ERROR_INVALID_ODX2_CONFIG', MSG_CLASS_ERROR + 19);
define('ERROR_ODX_CREATOR_LOCKED', MSG_CLASS_ERROR + 20);
define('ERROR_ODX_CREATOR_GET_LOCK', MSG_CLASS_ERROR + 21);
define('ERROR_COPY_PARAMETER_SET', MSG_CLASS_ERROR + 22);
define('ERROR_EMPTY_VALUE', MSG_CLASS_ERROR + 23);
define('ERROR_INVALID_INPUT_INTEGER', MSG_CLASS_ERROR + 24);
define('ERROR_INVALID_INPUT_FLOAT', MSG_CLASS_ERROR + 25);
define('ERROR_INVALID_INPUT_HEX', MSG_CLASS_ERROR + 26);
define('ERROR_VARIABLE_ALLREADY_EXISTS', MSG_CLASS_ERROR + 27);
define('ERROR_CANNOT_DELETE_USED_REV', MSG_CLASS_ERROR + 28);
define('ERROR_INVALID_TOKEN', MSG_CLASS_ERROR + 29);


$warning[MESSAGE_DONT_USE_BROWSER_BACK] = "
            <p>Diese Seite verwendet auschließlich HTML-Eingabeformulare,
            wodurch die 'Zurück-Funktion' des Webbrowser nicht wie gewohnt funktioniert
            und sollte daher auch nicht benutzt werden.</p>
            <p>Es wird ebenfalls  auch nicht empfohlen, ein zweites Browserfenster oder Tab von den
            'Fahrzeug Parametern' zu öffnen, da dieses zu irreführendem Verhalten
            oder im schlimmsten Fall zu Datenverlust führen kann!</p>";
$header [MESSAGE_DONT_USE_BROWSER_BACK] = "Wichtiger Hinweis";
$flags  [MESSAGE_DONT_USE_BROWSER_BACK] = MSGFLAG_ALLOW_HTML_TAGS | MSGFLAG_SHOW_ICON_DEFAULT | MSGFLAG_SESSION_ONLY_FIRSTS;

$header [MESSAGE_MASTER_REV_CHANGED] = "Konfigurationsvergleich";
$warning[MESSAGE_MASTER_REV_CHANGED] = "Diese ECU-Daten sind als Kopie der Fahrzeugkonfiguration %s markiert, " .
    "jedoch wurde die SW-Version der Vorlage geändert.<br><br>" .
    "<b>Lösung(en):</b><ul>" .
    "<li> Die ursprünlichen Parameter der Quelle erneut in die ausgewählte Konfiguration kopieren</li>" .
    "<li>Den Bezug zur Quelle zu lösen!<br>  Durch Klick auf <br>" .
    '<img src="images/help/Datenquelle-unterschiedlich.png"> <br> ' .
    'wird diese Kopie in einen unabhängigen Parametersatz umgewandelt.</li></ul>';


$flags  [MESSAGE_MASTER_REV_CHANGED] = MSGFLAG_ONLY_ON_CHANGE | MSGFLAG_ALLOW_HTML_TAGS;

$warning[WARNING_DIFFERENT_VARIANTS] = "Unterschiedliche Fahrzeugvarianten";
$flags  [WARNING_DIFFERENT_VARIANTS] = MSGFLAG_SESSION_ONLY_FIRSTS | MSGFLAG_ALLOW_HTML_TAGS;
$delim  [WARNING_DIFFERENT_VARIANTS] = "<br> - ";

$warning[WARNING_BAD_PENTA_PRODUCT_KEY] = "Penta-Fahrzeugbezeichnung entspricht nicht den internen Richtlinien:<br> - ";
$flags  [WARNING_BAD_PENTA_PRODUCT_KEY] = MSGFLAG_SESSION_ONLY_FIRSTS | MSGFLAG_ALLOW_HTML_TAGS;
$delim  [WARNING_BAD_PENTA_PRODUCT_KEY] = "<br> - ";

$warning[WARNING_MULTIPLE_WC_CONFIG] = "Penta Kofiguration enthält mehrere Windchill Konfigurationen";
$flags  [WARNING_MULTIPLE_WC_CONFIG] = MSGFLAG_SESSION_ONLY_FIRSTS | MSGFLAG_ALLOW_HTML_TAGS;
$delim  [WARNING_MULTIPLE_WC_CONFIG] = "<br> - ";

$warning[WARNING_NO_VARIANT_ECU_REV] = "Dieser Fahrzeugvariante ist für die ECU %s keine Softwareversion zugeordnet.";
$header [WARNING_NO_VARIANT_ECU_REV] = "Keine SW Zuordnung!";

$header [WARNING_ECU_USES_XCP_ONLY] = "ACHTUNG XCP!";
$warning[WARNING_ECU_USES_XCP_ONLY] = "<b>Diese SW-Version verwendet ausschliesslich XCP!</b><br>Es werden zusätzliche, hier nicht sichtbare, Parameter verwendet.";
$flags  [WARNING_ECU_USES_XCP_ONLY] = MSGFLAG_SESSION_ONLY_FIRSTS | MSGFLAG_ONLY_ON_CHANGE | MSGFLAG_ALLOW_HTML_TAGS;

$header [WARNING_ECU_USES_XCP] = "ACHTUNG XCP!";
$warning[WARNING_ECU_USES_XCP] = "<b>Diese SW-Version verwendet XCP!</b><br>Die angezeigten Werte haben zum Teil andere Bedeutungen oder es werden zusätzliche, hier nicht sichtbare, Parameter verwendet.";
$flags  [WARNING_ECU_USES_XCP] = MSGFLAG_SESSION_ONLY_FIRSTS | MSGFLAG_ONLY_ON_CHANGE | MSGFLAG_ALLOW_HTML_TAGS;

$header [WARNING_ECU_SW_NOT_RELEASED] = "ECU SW Version nicht freigegeben";
$warning[WARNING_ECU_SW_NOT_RELEASED] = "<b>Verwendung auf eigene Gefahr!</b><br>Diese SW-Version ist nicht für die Produktion freigegeben!<br>Die Parameter sind eventuell noch nicht fertig definiert oder noch nicht getestet.";
$flags  [WARNING_ECU_SW_NOT_RELEASED] = MSGFLAG_ALLOW_HTML_TAGS | MSGFLAG_HIDE_DEPENCES_ON_DATA;

$header [WARNING_ECU_SW_UNCOMPLETE] = "Fehlerhafter SW!";
$warning[WARNING_ECU_SW_UNCOMPLETE] = "<b>Fahrzeugkonfigurationen mit fehlerhafter SW Version gefunden</b><br>Die folgende Liste von Fahrzeugkonfigurationen enthalten unvollständig oder fehlerhafte SW-Versionen.";
$flags  [WARNING_ECU_SW_UNCOMPLETE] = MSGFLAG_SESSION_ONLY_FIRSTS | MSGFLAG_HIDE_DEPENCES_ON_DATA | MSGFLAG_ALLOW_HTML_TAGS | MSGFLAG_HIDE_DEPENCES_ON_DATA;

$error  [ERROR_MISSING_TAG_TYPE] = "Attribut <i>Type</i> fehlt für Parameter";
$error  [ERROR_MISSING_TAG_SIZE] = "Attribut <i>n-Bytes</i> fehlt für Parameter";
$error  [ERROR_MISSING_TAG_ACTION] = "Attribut <i>Action</i> fehlt für Parameter";
$error  [ERROR_MISSING_TAG_UDS_ID] = "Attribut <i>UdsId</i> fehlt für Parameter";
$error  [ERROR_CANNOT_SAVE_VALUE] = "Fehler beim Speichern in odx.sts.01";
$error  [ERROR_CANNOT_SAVE_ODX02] = "Fehler beim Speichern in odx.sts.02";
$error  [ERROR_CANNOT_SAVE_PARAMDEF] = "Fehler beim Speichern der Parameterkonfiguration";
$error  [ERROR_CANNOT_CONVERT_TO_HEX] = "Es fehlen Angaben für Parameterbestimmung. Dadurch können Werte nicht nach binär/hexadezimal konvertieren werden: ";
$header [ERROR_CANNOT_CONVERT_TO_HEX] = &$eingabefehler;
$error  [ERROR_NO_HEXADZIMAL_STRING] = "Kein gültiger Hexadizimalwert";
$error  [ERROR_UNKNOWN_TYPE] = "Unbekannter Datentyp";
$error  [ERROR_INVALID_NUMBER] = "Keine Zahl";
$error  [ERROR_INVALID_ODX2_CONFIG] = "Ungültige odx.sts.02 Konfiguration";
$error  [ERROR_ODX_CREATOR_LOCKED] = "Zugriff auf ODX-Creator-Test gesperrt.";
$error  [ERROR_ODX_CREATOR_GET_LOCK] = "ERROR_ODX_CREATOR_GET_LOCK  ";
$error  [ERROR_COPY_PARAMETER_SET] = "";

$error  [ERROR_EMPTY_VALUE] = "Kein Wert eingetragen für Parameter ";
$header [ERROR_EMPTY_VALUE] = &$eingabefehler;

$error  [ERROR_INVALID_INPUT_INTEGER] = "Ungülige Eingabe/Zeichen für %s. Ganze Zahl erwartet.";
$header [ERROR_INVALID_INPUT_INTEGER] = &$eingabefehler;
$error  [ERROR_INVALID_INPUT_FLOAT] = "Ungülige Eingabe/Zeichen für %s. Fließkommazahl erwartet.";
$header [ERROR_INVALID_INPUT_FLOAT] = &$eingabefehler;
$error  [ERROR_INVALID_INPUT_HEX] = "Ungülige Eingabe/Zeichen für %s. Hexadezimale Ganzzahl erwartet.";
$header [ERROR_INVALID_INPUT_HEX] = &$eingabefehler;
$error  [ERROR_VARIABLE_ALLREADY_EXISTS] = "Globaler Parameter mit dem Namen %s existiert bereits";
$error  [ERROR_VARIABLE_ALLREADY_EXISTS] = &$eingabefehler;

$error  [ERROR_CANNOT_DELETE_USED_REV] = "Diese SW-Version ist noch in Verwendung";

$error  [ERROR_INVALID_TOKEN] = "Ungültiger Tokenwert: %s";


?>