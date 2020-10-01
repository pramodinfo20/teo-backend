<?php
/* sts-defines.php */

define('REGEX_VALID_CONFIG', '^([BDE][123][0-9])');

$GLOBALS['CvsDataRegex'] = [
    'penta_kennwort' => '[BDE][123][0-9][0-9](POST)\s[0-9]+',
    'penta_nummer' => '[BDE][123][0-9][01][0-9][A-Z]POS[A-Z]_[A-Z][A-Z]_0[1-9]',
    'vin' => 'WS5[BDE][123][0-9][A-Z]{2}[A-Z0-9][A-Z]{2}[1-9][0-9]{5}',
    'akz' => 'BN-(P[A-Z]?)[ -]?([0-9]{1,4})(E)?',
    'ikz' => '9[0-9]{6}',
    'delivery_week' => '(?:i|kw)\s*[0-9][0-9]?',
    'date' => '(?:|20)[1-9][0-9]-[01][0-9]-[0-3][0-9]',
];


$GLOBALS['person_designation'] = [
    'pfranz' => ['person' => 'P.Franz', 'designation' => 'L. Gesamtfzg.'],
    'akampker' => ['person' => 'A.Kampker', 'designation' => 'CEO'],
    'fschmitt' => ['person' => 'F.Schmitt', 'designation' => 'CTO'],
    'smueller' => ['person' => 'S.Müller', 'designation' => 'Leiter E/E Gesamtsystem'],
    'rfrohn' => ['person' => 'R.Frohn', 'designation' => 'Leiter Qualität'],
    'treil' => ['person' => 'T.Reil', 'designation' => 'Leiter Produktion'],
];


define('MSGTYPE_DEFAULT', 0);
define('MSGTYPE_MESSAGE', 1);
define('MSGTYPE_WARNING', 2);
define('MSGTYPE_ERROR', 3);
define('MSGTYPE_FATAL', 4);


define('MSG_TEXT', 0);
define('MSG_FLAGS', 1);
define('MSG_HEAD', 2);
define('MSG_DELIM', 3);

define('MSGFLAG_DEFAULT', 0x00000000);
define('MSGFLAG_NOT_HANDLED', 0x00000000);
define('MSGFLAG_FORCE_SINGLE_MESSAGE', 0x00000100);
define('MSGFLAG_NO_GUI', 0x00000200);
define('MSGFLAG_ALLOW_HTML_TAGS', 0x00000400);
define('MSGFLAG_PAGE_ONLY_FIRSTS', 0x00001000);
define('MSGFLAG_SESSION_ONLY_FIRSTS', 0x00002000);
define('MSGFLAG_ONLY_ON_CHANGE', 0x00004000);
define('MSGFLAG_HIDE_DEPENCES_ON_DATA', 0x00008000);

define('MSGFLAG_SHOW_ICON_INFORMATION', 0x00010000);
define('MSGFLAG_SHOW_ICON_WARNING', 0x00020000);
define('MSGFLAG_SHOW_ICON_ERROR', 0x00030000);
define('MSGFLAG_SHOW_ICON_FATAL', 0x00040000);
define('MSGFLAG_SHOW_ICON_DEFAULT', 0x00070000);


define('MSG_BASE_MESSAGES', 0x00000000);
define('MSG_BASE_WARNING', 0x10000000);
define('MSG_BASE_ERROR', 0x20000000);
define('MSG_BASE_FATAL', 0x30000000);
define('MSG_CLASS_MESSAGES', 0x00008000);
define('MSG_CLASS_WARNING', 0x10008000);
define('MSG_CLASS_ERROR', 0x20008000);
define('MSG_CLASS_FATAL', 0x30008000);


define('STS_NO_ERROR', MSG_BASE_MESSAGES + 0);
define('STS_MESSAGE_SUCCEED', MSG_BASE_MESSAGES + 1);

define('STS_UNREGISTERED_ERROR', MSG_BASE_ERROR + 0x0000);
define('STS_ERROR_PHP_EXCEPTION', MSG_BASE_ERROR + 0x0001);
define('STS_ERROR_PHP_ASSERTION', MSG_BASE_ERROR + 0x0002);
define('STS_ERROR_NOT_IMPLEMENTED', MSG_BASE_ERROR + 0x0040);
define('STS_ERROR_METHOD_NOT_FOUND', MSG_BASE_ERROR + 0x0041);
define('STS_ERROR_INPUT_FILTER_MATCH', MSG_BASE_ERROR + 0x0042);
define('STS_ERROR_WRONG_USER', MSG_BASE_ERROR + 0x0043);
define('STS_ERROR_NO_PRIVILEGS', MSG_BASE_ERROR + 0x0044);

define('STS_ERROR_FILE_IO', MSG_BASE_ERROR + 0x0100);
define('STS_ERROR_CANNOT_OPEN_FILE', MSG_BASE_ERROR + 0x0101);
define('STS_ERROR_UNKNOWN_FILETYPE', MSG_BASE_ERROR + 0x0102);
define('STS_ERROR_NO_DATA', MSG_BASE_ERROR + 0x0103);
define('STS_ERROR_INVALID_STATE', MSG_BASE_ERROR + 0x0104);
define('STS_INVALID_FILE', MSG_BASE_ERROR + 0x0105);
define('STS_NOT_AN_IMAGE_FILE', MSG_BASE_ERROR + 0x0106);

define('STS_ERROR_DB_GENERAL_ERROR', MSG_BASE_ERROR + 0x0200);
define('STS_ERROR_DB_SELECT', MSG_BASE_ERROR + 0x0201);
define('STS_ERROR_DB_UPDATE', MSG_BASE_ERROR + 0x0202);
define('STS_ERROR_DB_INSERT', MSG_BASE_ERROR + 0x0203);
define('STS_ERROR_DB_DELETE', MSG_BASE_ERROR + 0x0204);
define('STS_ERROR_DATA_LOCKED', MSG_BASE_ERROR + 0x0205);

define('STS_PARTGROUP_SEAT2', 1);
define('STS_PARTGROUP_BATTERY', 2);
define('STS_PARTGROUP_RADIO', 3);
define('DIVISION_TESTFAHRZEUGE', 50);

define('DEPOT_TYPE_PRODUKTION', 1);
define('DEPOT_TYPE_NACHARBEIT', 2);
define('DEPOT_TYPE_DRITTKUNDEN', 5);
define('DEPOT_TYPE_POST_AUSLIEFEUNG', 10);
define('DEPOT_TYPE_POST_AUSSTEHENDE', 11);
define('DEPOT_TYPE_POST_FLEET_POOL', 12);
define('DEPOT_TYPE_POST_STANDORT', 13);
define('DEPOT_TYPE_OZ_VERBUND', 20);
define('DEPOT_TYPE_OZ_DHL', 21);
define('DEPOT_TYPE_OZ_DELIVERY', 22);


?>