<?php
/**
 * AClass_Base    (actions/class/Base.php)
 *
 * Basisklasse fuer die neue Methode der Action-Paches aus dem Verzeichnis {http-root}/actions
 * @author Lothar Jürgens
 */
require_once($_SERVER['STS_ROOT'] . '/includes/sts-defines.php');
require_once($_SERVER['STS_ROOT'] . "/includes/sts-array-tools.php");
require_once __DIR__."/../../translations/LegacyTranslations.php";


// ==============================================================================================

class AClass_Base {
    // ==============================================================================================


    public $error = null;
    protected $msgList = [[], [], [], [], [], []];
    protected $msgMaxType = 0;
    protected $displayheader = null;
    protected $displayfooter = null;

    private $firstMsgType = "";
    private $firstMsgTypeName = "";
    private $firstMsgHead = "";
    private $firstMsgText = "";
    protected $msgDefines = [];
    protected $mailDefaultSender = "mopra <mopra@streetscooter.eu>";
    private $S_pageid;

    protected $useCommandMapping = false;
    private $autoForwards = [];
    private $autopostVar = [];

    protected $current_lang = 'de';
    protected $resource_text = [];
    protected $command_result = null;
    protected $translate;


    /**
     * Konstruktor
     *
     * @param PageController $pageController
     */

    function __construct() {

        global $pageController;

        $this->controller = $pageController;
        $this->userRole = $pageController->userRole;
        $this->action = $pageController->action;
        $this->UAction = $pageController->UAction;
        $this->displayheader = $pageController->GetObject('displayheader');
        $this->displayfooter = $pageController->GetObject('displayfooter');
        $this->print = safe_val($_REQUEST, 'print', null);
        $this->nameprefix = "";
        $this->force_init = isset ($_REQUEST['initPage']);
        $this->no_session = isset ($_REQUEST['nosession']);

        if ($this->no_session) {
            $this->S_roleVar = [];
            $this->S_actionVar = [];
        } else {
            $this->S_roleVar = &InitSessionVar($_SESSION[$this->userRole], array());
            $this->S_actionVar = &InitSessionVar($_SESSION[$this->userRole][$this->UAction], array(), $this->force_init);
        }

        //var_dump($_SESSION);
        $this->S_globals = &InitSessionVar($_SESSION['globals'], array());
        $this->S_MsgsSeen = &InitSessionVar($_SESSION['msgs_seen'], [[], [], [], [], []]);
        $this->S_data = &InitSessionVar($this->S_actionVar['data'], []);
//var_dump($this->S_data);
        $this->S_pageid = &InitSessionVar($this->S_globals['pageid'], 1);
        $this->translate = (new LegacyTranslations())->getTranslationsForDomain();

    }
    // ==============================================================================================
    // ==============================================================================================
    function Init() {
        $this->LoadTextResource($this->current_lang);
        $this->LoadMessageFiles();
    }

    // ==============================================================================================
    function CleanUp() {
        unset ($_SESSION[$this->userRole][$this->UAction]);
    }

    // ==============================================================================================
    function Debug_Log_Session($logfile, &$data = null, $dataname = "") {
        if (!($f = fopen($logfile, "at+"))) return;
        fprintf($f, "\n%s  ====================================================", date("d.M.Y - G:i:s:"));
        if ($data && ($dataname = ""))
            $dataname = "DATA";

        if (!$data || substr($dataname, 0, 1) == '+') {
            fprintf($f, "\n%s  ====================================================", date("d.M.Y - G:i:s:"));
            fprintf($f, "\n\nRequest:\n----------------------------------------------------------------------------\n");
            fprintf($f, var_export($_REQUEST, true));
            fprintf($f, "\n\nSession:\n\n----------------------------------------------------------------------------\n");
            fprintf($f, var_export($_SESSION, true));
        }
        if ($data) {
            fprintf($f, "\n\n$dataname:\n----------------------------------------------------------------------------\n");
            fprintf($f, var_export($data, true));

        }
        fprintf($f, "\n\n----------------------------------------------------------------------------\n");
        fclose($f);
    }

    // ==============================================================================================
    function SetAutoForward($varname, $enable = true) {
        if (isset ($this->autoForwards[$varname]))
            $this->autoForwards[$varname] = $enable;
        else if ($enable)
            $this->autoForwards[$varname] = true;
    }

    // ==============================================================================================
    function SetAutoPostVar($varname, $value = null) {
        if (isset ($value))
            $this->autopostVar[$varname] = $value;
        else
            unset ($this->autopostVar[$varname]);
    }

    // ==============================================================================================
    private function GetHtml_HiddenForward($requestval, $varname, $indent) {
        if (!isset ($requestval))
            return "";

        if (is_array($requestval)) {
            $result = "";
            foreach ($requestval as $name => $value)
                $result .= $this->GetHtml_HiddenForward($requestval[$name], "{$varname}[{$name}]", $indent);
            return $result;
        }

        return "$indent  <input type=\"hidden\" name=\"$varname\" value=\"$requestval\">\n";
    }

    // ==============================================================================================
    function GetHtml_FormHeader($id_prefix = '', $indent = 0, $name = 'myForm', $method = 'POST', $xtra_set = null, $xtra_forwards = null, $enctype = '') {
        $ind1 = str_pad('', $indent, ' ');

        $formstr = <<<HEREDOC
$ind1<form method="$method"$enctype name="$name" id="{$id_prefix}form" action="{$_SERVER['PHP_SELF']}">
$ind1  <input type="hidden" name="action" value="{$this->action}">
$ind1  <input type="hidden" name="pageid" value="{$this->S_pageid}">
$ind1  <input type="hidden" name="command" id="{$id_prefix}command" value="">

HEREDOC;

        foreach ($this->autoForwards as $varname => $enable) {
            if ($enable && (!isset ($xtra_set[$varname])) && (!isset ($this->autopostVar[$varname])) && isset($_REQUEST[$varname]))
                $formstr .= $this->GetHtml_HiddenForward($_REQUEST[$varname], $varname, $ind1);
        }

        if ($xtra_forwards)
            foreach ($xtra_forwards as $varname => $enable) {
                if ($enable && (!isset ($xtra_set[$varname])) && (!isset ($this->autopostVar[$varname])) && isset ($_REQUEST[$varname]))
                    $formstr .= $this->GetHtml_HiddenForward($_REQUEST[$varname], $varname, $ind1);
            }

        foreach ($this->autopostVar as $varname => $value)
            $formstr .= "$ind1  <input type=\"hidden\" name=\"$varname\" value=\"$value\">\n";

        if ($xtra_set)
            foreach ($xtra_set as $varname => $value)
                $formstr .= "$ind1  <input type=\"hidden\" name=\"$varname\" value=\"$value\">\n";

        return $formstr;
    }

    // ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    function GetHtml_MultipartFormHeader($id_prefix = '', $indent = 0, $name = 'myForm', $method = 'POST', $xtra_set = null, $xtra_forwards = null, $enctype = '') {
        return $this->GetHtml_FormHeader($id_prefix, $indent, $name, $method, $xtra_set, $xtra_forwards, ' enctype="multipart/form-data"');
    }

    // ==============================================================================================
    private function GetHtml_UrlForward(&$paramList, $requestval, $varname) {
        if (!isset ($requestval))
            return;

        if (is_array($requestval)) {
//             $paramList["!$varname"] = serialize ($requestval);
//             $tmp = http_build_query ($requestval);
//             return $tmp;
            foreach ($requestval as $name => $value)
                $this->GetHtml_UrlForward($paramList, $requestval[$name], "{$varname}[{$name}]");
        } else {
            $paramList[$varname] = $requestval;
        }
    }

    // ==============================================================================================
    function GetHtml_UrlParams($xtra_set = null, $xtra_forwards = null, $filter_vars = null) {
        $paramList = ['action' => $this->action, 'pageid' => $this->S_pageid];
        $encocded = [];

        foreach ($this->autoForwards as $varname => $enable) {
            if ($enable && (!isset ($xtra_set[$varname])) && (!isset ($this->autopostVar[$varname])) && isset ($_REQUEST[$varname]))
                if (!isset($filter_vars) || in_array($varname, $filter_vars))
                    $this->GetHtml_UrlForward($paramList, $_REQUEST[$varname], $varname);
        }

        if ($xtra_forwards)
            foreach ($var_forwards as $varname => $enable) {
                if ($enable && (!isset ($xtra_set[$varname])) && (!isset ($this->autopostVar[$varname])) && isset ($_REQUEST[$varname]))
                    $this->GetHtml_UrlForward($paramList, $_REQUEST[$varname], $varname);
            }

        foreach ($this->autopostVar as $varname => $value)
            if (!isset($filter_vars) || in_array($varname, $filter_vars))
                $paramList[$varname] = $value;

        if ($xtra_set)
            foreach ($xtra_set as $varname => $value)
                $paramList[$varname] = $value;

        foreach ($paramList as $varname => $value)
            $encocded[] = "$varname=" . urlencode($value);

        return implode('&', $encocded);
    }

    // ==============================================================================================
    function GetHtml_Url($xtra_set = null, $xtra_forwards = null) {
        return $_SERVER['PHP_SELF'] . '?' . $this->GetHtml_UrlParams($xtra_set, $xtra_forwards);
    }

    // ==============================================================================================
    function SendHtmlMail($empfaenger, $betreff, $nachrichtHtml, $absender = "") {
        $empfaenger = implode(', ', $empfaenger);
        if (empty($absender))
            $absender = $this->mailDefaultSender;

        // für HTML-E-Mails muss der 'Content-type'-Header gesetzt werden
        $header = 'MIME-Version: 1.0' . "\r\n";
        $header .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $header .= "From: $absender\r\n";

        return mail($empfaenger, $betreff, $nachrichtHtml, $header);
    }

    // ==============================================================================================
    protected function GetParentClassList() {
        $classname = get_class($this);
        $parents_list = [$classname];

        $classname = get_parent_class($classname);
        while (!empty ($classname)) {
            array_unshift($parents_list, $classname);
            $classname = get_parent_class($classname);
        }
        return $parents_list;
    }

    // ==============================================================================================
    private function LoadTextResourceFile($fn) {
        $TXT = &$this->resource_text;
        include($fn);
    }

    // ==============================================================================================
    protected function LoadTextResource($lang) {
        $parents_list = $this->GetParentClassList();
        $resource_root = $_SERVER['STS_ROOT'] . "/actions/lang/$lang";

        foreach ($parents_list as $classname) {
            $cl = strtolower(substr($classname, 7));
            $fn = "$resource_root/$cl.res.php";
            if (file_exists($fn))
                $this->LoadTextResourceFile($fn);
        }
    }

    // ==============================================================================================
    function GetText($text_id) {
        if (isset ($this->resource_text[$text_id]))
            return $this->resource_text[$text_id];
        return "#$text_id#";
    }

    // ==============================================================================================
    function EchoText($text_id) {
        echo $this->GetText($text_id);
    }

    // ==============================================================================================
    function LoadMessageFiles() {
        $this->LoadMessageFile("{$this->UAction}/{$this->UAction}.msg.php");
    }

    // ==============================================================================================
    function MergeMessages($type, &$contents, &$headers, &$delims, &$flags, $flagsDefault) {
        $defHead = $this->GetMessageTypeName($type);

        foreach ($contents as $si => $text) {
            $id = abs($si);

            $flg = $type | (isset($flags[$si]) ? $flags[$si] : $flagsDefault);

            if (!isset ($this->msgDefines[$id]))
                $this->msgDefines[$id] = [];


            $this->msgDefines[$id][MSG_TEXT] = $text;
            if (!isset ($this->msgDefines[$id][MSG_FLAGS]))
                $this->msgDefines[$id][MSG_FLAGS] = $flg;

            if (isset ($headers[$si]))
                $this->msgDefines[$id][MSG_HEAD] = $headers[$si];

            if (isset ($delims[$si]))
                $this->msgDefines[$id][MSG_DELIM] = $delims[$si];
        }
    }

    // ==============================================================================================
    function LoadMessageFile($filename, $dir = "") {
        if (empty ($dir))
            $dir = $_SERVER['STS_ROOT'] . "/actions/{$this->userRole}/";
        else
            if (substr($dir, -1) != '/')
                $dir .= '/';

        $fn = $dir . $filename;
        if (file_exists($fn)) {
            $default = 0;
            $flags = [];
            $header = [];
            $delim = [];
            $message = [];
            $warning = [];
            $error = [];
            $fatal = [];

            include $fn;

            $this->MergeMessages(MSGTYPE_MESSAGE, $message, $header, $delim, $flags, $default);
            $this->MergeMessages(MSGTYPE_WARNING, $warning, $header, $delim, $flags, $default);
            $this->MergeMessages(MSGTYPE_ERROR, $error, $header, $delim, $flags, $default);
            $this->MergeMessages(MSGTYPE_FATAL, $fatal, $header, $delim, $flags, $default);
        }
    }

    // ==============================================================================================
    function Ajaxecute($command) {

    }

    // ==============================================================================================
    function Execute() {
        global $IS_DEBUGGING, $DEBUG_LOG_SESSION;

        if ($IS_DEBUGGING && $DEBUG_LOG_SESSION) {
            $this->Debug_Log_Session($DEBUG_LOG_SESSION);
        }

        if (isset ($_REQUEST['pageid'])) {

        }

        if (isset ($_REQUEST['ajaxcmd'])) {
            $this->Ajaxecute($_REQUEST['ajaxcmd']);
            echo 'ready:ok';
            exit;
        }

        if ($this->useCommandMapping && isset ($_REQUEST['command'])) {
            $method = $_REQUEST['command'];
            if (!empty ($method)) {
                if (is_array($method))
                    $method = array_keys($method)[0];

                if (method_exists($this, $method)) {
                    if (isset($_REQUEST['param'])) {
                        $parameter = $_REQUEST['param'];
                    }

                    $this->command_result = call_user_func([$this, $method], $parameter);
                }
            }
        }
    }

    // ==============================================================================================
    function SetupHeaderFiles($displayheader) {
    }

    // ==============================================================================================
    function GetHtml_SelectOptions($values, $default = false, $indent = -1, $disabled = []) {
        $result = "";
        $lf = (($indent >= 0) ? lf : "");
        $indent++;
        foreach ($values as $value => $caption)
            $result .= sprintf("%$indent" . 'soption value="%s"%s%s>%s</option>%s', "<",
                $value,
                ($value == $default ? " selected" : ""),
                (in_array($value, $disabled) ? ' disabled' : ''),
                $caption, $lf);
        return $result;
    }

    // ==============================================================================================
    function WriteHtmlMenu() {
        $this->translate = (new LegacyTranslations())->getTranslationsForDomain();
        $role = strtolower($GLOBALS['role']);
        include $_SERVER['STS_ROOT'] . "/pages/menu/$role.menu.php";
    }

    // ==============================================================================================
    function WriteHtmlContent($options = "") {
        if (strpos($options, 'nomenu') === false)
            $this->WriteHtmlMenu();
    }

    // ==============================================================================================
    function WriteHtmlPage($displayheader, $displayfooter) {
        $options = $this->print ? "nomenu,nodebug,nolinks" : "";
        $this->displayheader = $displayheader;
        $this->displayfooter = $displayfooter;

        $this->SetupHeaderFiles($displayheader);

        $displayheader->printContent($options);
        $this->WriteHtmlContent($options);
        if ($this->error)
            $this->DisplayError($error);

        $this->DisplayMessages();

        $displayfooter->enqueueFinallyVars('_urlparams', $this->GetHtml_UrlParams());
        $displayfooter->printContent($options);

    }

    // ==============================================================================================
    function DisplayError($error) {
        echo $this->GetHtml_ErrorBox($error);
    }

    // ==============================================================================================
    function LogErrorFirstOnly($errId, $msg) {
        if (isset ($this->ERRORS_LOGGED[$errId]))
            return;
        error_log($msg);
        $this->ERRORS_LOGGED [$errId] = 1;
    }

    // ==============================================================================================
    function LogError($errId, $msg) {
        error_log($msg);
        $this->ERRORS_LOGGED[$errId] = 1;
    }

    //==============================================================================================
    function GetErrorText($errorno = false) {
        if ($errorno === false)
            $errorno = $this->CsvResult;

        switch ($errorno) {
            case STS_NO_ERROR:
                return "";
            case STS_ERROR_CANNOT_OPEN_FILE:
                return "Fehler beim öffnen der Datei: $this->S_UploadedFile";
            case STS_ERROR_UNKNOWN_FILETYPE:
                return "Unbekannnter oder nicht unterstützter Datei-Typ: $this->S_UploadedFile";
            case STS_ERROR_NO_DATA:
                return "Keine CSV Daten gefunden. Datei: $this->S_UploadedFile";
            case STS_ERROR_NOT_IMPLEMENTED:
                return "Diese Funktion ist leider noch nicht implementiert!";
        }
        return "Fehler Nummer $errorno";
    }

    //==============================================================================================
    function GetHtml_ErrorBox($error = null) {
        $exception = null;
        $errorNum = "";
        $headln = "Fehler ";

        if (is_null($error))
            $error = $this->error;

        if (!$error)
            return;

        if (is_a($error, 'Exception')) {
            $headln = "Exception";
            $exception = $error;
            $errorText = $exception->getMessage();
        } else
            if (is_numeric($error)) {
                $headln += "Fehler Nr: $error";
                $errorText = $this->GetErrorText($errorNum);
            } else {
                $trenn = strpos($error, '|');
                if ($trenn) {
                    $errorText = substr($error, $trenn + 1);
                    $headln = substr($error, 0, $trenn);
                } else {
                    $errorText = $error;
                }
            }

        return <<<EOT
        <div class="errorbox" id="id_errorbox">
            <h2 style="color:#DD4400;">$headln</h2>
            <div class="big">$errorText</div>
            <input type="button" value="Ok" onClick="this.parentNode.style.visibility='hidden';">
        </div>
EOT;

    }

    //==============================================================================================
    function SetMessage($msgId) {
        try {
            return $this->SetMsg(MSGTYPE_MESSAGE, $msgId, $this->ProcessMsgData(func_get_args()));
        } catch (Exception $E) {
        }
    }

    //==============================================================================================
    function SetWarning($msgId) {
        try {
            return $this->SetMsg(MSGTYPE_WARNING, $msgId, $this->ProcessMsgData(func_get_args()));
        } catch (Exception $E) {
        }
    }

    //==============================================================================================
    function SetError($msgId) {
        try {
            return $this->SetMsg(MSGTYPE_ERROR, $msgId, $this->ProcessMsgData(func_get_args()));
        } catch (Exception $E) {
        }
    }

    //==============================================================================================
    function SetFatal($msgId) {
        try {
            return $this->SetMsg(MSGTYPE_FATAL, $msgId, $this->ProcessMsgData(func_get_args()));
        } catch (Exception $E) {
        }
    }

    //==============================================================================================
    function SetMsgDefault($msgId) {
        try {
            return $this->SetMsg(MSGTYPE_DEFAULT, $msgId, $this->ProcessMsgData(func_get_args()));
        } catch (Exception $E) {
        }
    }

    //==============================================================================================
    protected function ProcessMsgData($msgData) {
        $result = [];
        $msgId = array_shift($msgData);

        foreach ($msgData as &$item) {
            if (is_object($item)) {
                if (is_subclass_of($item, 'Exception')) {
                    $result[] = $item->getMessage();
                    $result[] = $item->getFile();
                    $result[] = $item->getLine();
                    if ($GLOBALS['VERSBOSE'])
                        $result[] = $item->getTrace();
                }
            } else if (is_array($item)) {
                array_push($result, ...$item);
            } else {
                $result[] = &$item;
            }
        }
        return $result;
    }

    //==============================================================================================
    protected function SetMsg($msgType, $msgId, $msgData) {
        $empty_array = [];
        $msgTypeDefault = MSGTYPE_DEFAULT;
        $ret_id = $msgId;

        if ($msgId <= 0)
            return $msgId;

        if ($msgId >= MSG_BASE_FATAL) {
            $msgTypeDefault = MSGTYPE_FATAL;
        } else
            if ($msgId >= MSG_BASE_ERROR) {
                $msgTypeDefault = MSGTYPE_ERROR;
            } else
                if ($msgId >= MSG_BASE_WARNING) {
                    $ret_id = -$msgId;
                    $msgTypeDefault = MSGTYPE_WARNING;
                } else {
                    $ret_id = 0;
                    $msgTypeDefault = MSGTYPE_MESSAGE;
                }


        if ($msgId)

            $flags = $this->GetMessageProperties($msgId, $msgTypeDefault);
        if ($msgType == MSGTYPE_DEFAULT)
            $msgType = $msgTypeDefault;

        $ret_id = ($msgType >= MSGTYPE_ERROR) ? $msgId : -$msgId;


        if (!is_array($msgData))
            $msgData = [$msgData];


        $msgStruct = ['flags' => $flags, 'cmp' => 0, 'data' => $msgData];

        if ($flags & MSGFLAG_ONLY_ON_CHANGE)
            $msgStruct['cmp'] = array_shift($msgStruct['data']);


        if ($flags & MSGFLAG_SESSION_ONLY_FIRSTS) $hold_list = &$this->S_MsgsSeen[$msgType];
        else if ($flags & MSGFLAG_PAGE_ONLY_FIRSTS) $hold_list = &$this->msgList[$msgType];


        if (isset($hold_list)) {
            if ($flags & MSGFLAG_ONLY_ON_CHANGE) {
                if (isset ($hold_list[$msgId]) && ($hold_list[$msgId]['cmp'] == $msgStruct['cmp']))
                    return $ret_id;

                $hold_list[$msgId] = $msgStruct;
            } else
                if ($flags & MSGFLAG_HIDE_DEPENCES_ON_DATA) {
                    if (isset ($hold_list[$msgId])) {
                        $seen_data = &$hold_list[$msgId]['cmp'];

                        foreach ($msgStruct['data'] as $key => $data) {
                            if (array_search($data, $seen_data, true)) {
                                unset ($msgStruct['data'][$key]);
                            } else {
                                $seen_data[] = $data;
                            }
                            if (count($msgStruct['data']) == 0)
                                return $ret_id;
                        }
                    } else {
                        $msgStruct['cmp'] = $msgStruct['data'];
                        $hold_list[$msgId] = $msgStruct;
                    }
                } else {
                    if (isset($hold_list[$msgId]))
                        return $ret_id;

                    $hold_list[$msgId] = $msgStruct;
                }
        }

        make_node($this->msgList, $msgType, $msgId, $msgStruct);


        /*
        $hold_list = &$this->msgList[$msgType][$msgId];
        foreach ($msgData as $key=>$data)
        {
            if (! array_search($data, $hold_list, true))
                $hold_list[] = $data;
        }
        */

        $this->msgMaxType = max($this->msgMaxType, $msgType);

        return $ret_id;
    }

    // ==============================================================================================
    function GetMessageTypeName($msgType) {
        switch ($msgType) {
            case MSGTYPE_MESSAGE:
                return "Hinweis";
            case MSGTYPE_WARNING:
                return "Warnung";
            case MSGTYPE_ERROR:
                return "Fehler";
            case MSGTYPE_FATAL:
                return "kritischer Fehler";
        }
        return "Hinweis";
    }

    //==============================================================================================
    function GetMessageProperties($msgId, $msgTypeDefault) {
        if (isset ($this->msgDefines[$msgId])) {
            return $this->msgDefines[$msgId][MSG_FLAGS];
        }
        return 0;
    }

    //==============================================================================================
    function FormatMessage(&$msgHead, &$msgFormat, &$flags, &$delim, $msgType, $msgId, $msgData, $numData) {
        switch ($msgId) {
            case STS_NO_ERROR:
                return true;

            case STS_MESSAGE_SUCCEED:
                $flags |= MSGFLAG_ALLOW_HTML_TAGS;
                $msgHead = "Meldung";
                $msgFormat = "Aktion erfolgreich ausgeführt:<br><br>\n";
                break;


            case STS_ERROR_PHP_EXCEPTION:
                $msgHead = "PHP Exception";
                $msgFormat = "Kritischer Ausnahmefehler: \n";
                break;

            case STS_ERROR_PHP_ASSERTION:
                $msgHead = "Assertion Error";
                $msgFormat = "Unerwarteter Zustand aufgetreten: \n";
                break;


            case STS_ERROR_CANNOT_OPEN_FILE:
                $msgHead = "Dateifehler";
                $msgFormat = "Fehler beim öffnen der Datei: " . $this->S_UploadedFile;
                return true;

            case STS_ERROR_UNKNOWN_FILETYPE:
                $msgHead = "Dateifehler";
                $msgFormat = "Unbekannnter oder nicht unterstützter Datei-Typ: " . $this->S_UploadedFile;
                return true;

            case STS_ERROR_NO_DATA:
                $msgHead = "CSV Import!";
                $msgFormat = "Keine CSV Daten gefunden. Datei: " . $this->S_UploadedFile;
                return true;

            case STS_NOT_AN_IMAGE_FILE:
                $msgHead = "Dateifehler";
                $msgFormat = "Angegebene Datei (%s) ist keine Bilddatei.";
                return true;

            case STS_ERROR_NOT_IMPLEMENTED:
                $msgHead = "Interner Fehler";
                if ($numData = 1)
                    $msgFormat = 'Die Funktion "%s" ist leider noch nicht implementiert.';
                else
                    $msgFormat = 'Die Funktionen "%s" sind leider noch nicht implementiert.';
                return true;

            case STS_ERROR_METHOD_NOT_FOUND:
                $msgHead = "Interner Fehler";
                $c = ($numData > 1) ? "n" : "";
                $msgFormat = "Objektmethode$c %s nicht vorhanden.";
                return true;

            default:
                if (isset ($this->msgDefines[$msgId])) {
                    $msgFormat = $this->msgDefines[$msgId][MSG_TEXT];
                    if (isset ($this->msgDefines[$msgId][MSG_HEAD]))
                        $msgHead = $this->msgDefines[$msgId][MSG_HEAD];
                    if (isset ($this->msgDefines[$msgId][MSG_DELIM]))
                        $delim = $this->msgDefines[$msgId][MSG_DELIM];
                } else {
                    switch ($msgType) {
                        case MSGTYPE_MESSAGE:
                            $msgFormat = "Dieser Hinweistext";
                            break;
                        case MSGTYPE_WARNING:
                            $msgFormat = "Dieser Warnhinweis";
                            break;
                        case MSGTYPE_ERROR:
                            $msgFormat = "Diese Fehlermeldung";
                            break;
                        case MSGTYPE_FATAL:
                            $msgFormat = "Dieser kritische Fehler";
                            break;
                    }

                    $msgFormat .= "ist bisher noch nicht beschrieben oder übersetzt worden.\nSollte diese Meldung wiederholt auftreten, ruf einfach bei der DKS an.";

                    if ($numData)
                        $msgFormat .= "zugehörige Information:\n";
                }
                return false;
        }

    }

    //==============================================================================================
    function DisplayMessages($displayType = "box", $minType = 0, $maxType = 0) {
        if ($minType == 0) $minType = MSGTYPE_MESSAGE;
        if ($maxType == 0) $maxType = MSGTYPE_FATAL;

        if ($this->msgMaxType < $minType)
            return;

        $this->numMessages = 0;
        $this->nMsg = 0;
        $this->withIcons = false;

        for ($msgType = $maxType; $msgType >= $minType; $msgType--) {
            foreach ($this->msgList[$msgType] as $msgId => $msgStruct) {
                $flags = $msgStruct['flags'];
                if ($flags & MSGFLAG_NO_GUI)
                    continue;

                if ($flags & MSGFLAG_FORCE_SINGLE_MESSAGE)
                    $this->numMessages += (count($msgData) - 1);
                else
                    $this->numMessages++;

                if ($flags & MSGFLAG_SHOW_ICON_DEFAULT)
                    $this->withIcons = true;
            }
        }


        if ($this->numMessages == 0)
            return;

        $this->Display_FormatedMessage("$displayType:header");

        for ($msgType = $maxType; $msgType >= $minType; $msgType--) {
            foreach ($this->msgList[$msgType] as $msgId => $msgStruct) {
                $this->DisplayMessage($displayType, $msgType, $msgId, $msgStruct);
            }
        }

        $this->Display_FormatedMessage("$displayType:footer");
    }

    // ==============================================================================================
    function DisplayMessage($displayType, $msgType, $msgId, $msgStruct) {
        $msgText = "";
        $delim = ', ';
        $fitem = "";

        $typeName = $this->GetMessageTypeName($msgType);
        $msgHead = sprintf('%s Nr.: %04x', $typeName, $msgId & 0xffff);
        $flags = $msgStruct['flags'];
        $msgData = $msgStruct['data'];

        $this->FormatMessage($msgHead, $msgFormat, $flags, $delim, $msgType, $msgId, $msgData, $numData);

        $numData = count($msgData);
        $do_format = (stripos($msgFormat, "%s") !== false);

        if (!($flags & MSGFLAG_FORCE_SINGLE_MESSAGE)) {
            $msgData = implode($delim, $msgData);
            $msgData = [$msgData];
        }

        foreach ($msgData as $data) {
            $msgText = ($do_format) ? sprintf($msgFormat, $data) : "$msgFormat $data";

            if (!($flags & MSGFLAG_ALLOW_HTML_TAGS))
                $msgText = nl2br(htmlspecialchars($msgText));

            if ($this->nMsg)
                $this->Display_FormatedMessage("$displayType:between");

            $this->nMsg++;
            $this->Display_FormatedMessage("$displayType:message", $this->nMsg, $msgType, $msgHead, $msgText);
        }
    }

    //==============================================================================================
    function Display_FormatedMessage($what, $msgNum = 0, $msgType = null, $msgHead = null, $msgText = null) {
        if ($msgNum && isset($msgType)) {
            $msgTypeName = $this->GetMessageTypeName($msgType);

            if ($msgNum == 1) {
                $this->firstMsgType = $msgType;
                $this->firstMsgTypeName = $msgTypeName;
                $this->firstMsgHead = $msgHead;
                $this->firstMsgText = $msgText;
            }
        }

        switch ($what) {
            case 'list:header':
                echo '
        <div class="locator" id="id_locator">
          <h2>Mopra Meldungen</h2>
          <table class="messages">
            <thead>
              <tr><th>#</th><th>Typ</th><th>Meldung</th><th>weitere Informationen</th></tr>
            </thead>
            <tbody>';
                break;


            case 'list:message':
                echo "
            <tr id=\"TRMSG{$msgNum}\">
              <td>{$msgNum}</td>
              <td>{$msgTypeName}</td>
              <td>{$msgHead}</td>
              <td>{$msgText}</td>
            </tr>";
                break;


            case 'list:footer':
                echo '
            </tbody>
            <tfoot>
              <tr><td colspan="4"><input type="button" value="Ok" onClick="' . "document.getElementById('id_locator').style.visibility='hidden';" . '"></td></tr>
            </tfoot>
          </table>
        </div>
';
                break;


            case "box:header":
                echo <<<HEREDOC
        <div style="display:none;">
HEREDOC;
                break;


            case "box:message":
                echo <<<HEREDOC
          <div id="IDMSG_{$msgNum}_Type" data-type="{$msgType}">{$msgTypeName}</div>
          <div id="IDMSG_{$msgNum}_Head">{$msgHead}</div>
          <div id="IDMSG_{$msgNum}_Text">{$msgText}</div>
HEREDOC;
                break;


            case "box:footer":
                if ($this->nMsg > 1) {
                    $buttons = '
            <input type="button" id="id_msgPrev"  value="&lt;&lt;"  disabled onClick="HandleMsgFrame(this)">
            <input type="button" id="id_msgClose" value="Schließen" disabled onClick="HandleMsgFrame(this)">
            <input type="button" id="id_msgNext"  value="&gt;&gt;"  onClick="HandleMsgFrame(this)">';
                } else {
                    $buttons = '
            <input type="button" value="<" style="visibility:hidden;">
            <input type="button" id="id_msgOk" value="Ok" onClick="HandleMsgFrame(this)">
            <input type="button" value="<" style="visibility:hidden;">';
                }

                $image_class = $this->withIcons ? 'with_icon' : 'no_icon';
                $borderClass = "msgBordrColor" . $this->firstMsgType;
                $textClass = "msgTextColor" . $this->firstMsgType;
                $visibleImg = ['', '', '', '', ''];
                $visibleImg[$this->firstMsgType] = 'style="display:inline"';

                echo <<<HEREDOC
        </div>
        <div class="msgbox {$image_class} {$borderClass}" id="id_messagebox">
          <div class="msgType {$borderClass}" id="id_msgType">{$this->firstMsgTypeName}</div>
          <div class="msgHead {$textClass} {$borderClass}" id="id_msgHead">{$this->firstMsgHead}</div>
          <div class="msgBody {$borderClass}">
            <div class="msgIcon {$image_class}">
                <img id="id_msgIcon1" {$visibleImg[1]} src="/images/symbols/icon-info.png">
                <img id="id_msgIcon2" {$visibleImg[2]} src="/images/symbols/icon-warning.png">
                <img id="id_msgIcon3" {$visibleImg[3]} src="/images/symbols/icon-error.png">
                <img id="id_msgIcon4" {$visibleImg[4]} src="/images/symbols/icon-error.png">
            </div>
            <div class="msgText" id="id_msgText">{$this->firstMsgText}</div>
          </div>
          <div class="msgButtons">$buttons</div>
        </div>
        <script>
            var msgNumMessages = {$this->nMsg};
            var msgCurrentMsg  = 1;
            var msgCurrentType = {$this->firstMsgType};

            function UpdateMessages (iMsg, suffix)
            {
                var divSrc, divDst;

                divSrc  = document.getElementById('IDMSG_'+iMsg + '_' + suffix);
                divDst  = document.getElementById('id_msg' + suffix);

                if (divSrc && divDst)
                {
                    divDst.innerHTML = divSrc.innerHTML

                    if (suffix=='Type')
                    {
                        var iType, img;
                        iType = divSrc.dataset.type;
                        if (iType != msgCurrentType)
                        {
                            img = document.getElementById('id_msgIcon'+msgCurrentType);
                            if (img)
                                img.style.display = 'none';

                            img = document.getElementById('id_msgIcon'+iType);
                            if (img)
                                img.style.display = 'inline';

                            msgCurrentType = iType;
                        }
                    }
                }
            }


            function  HandleMsgFrame (button)
            {
                switch (button.id)
                {
                    case 'id_msgClose':
                    case 'id_msgOk':
                            document.getElementById('id_messagebox').style.visibility='hidden';
                            break;

                    case 'id_msgPrev':
                        if (msgCurrentMsg>1)
                            msgCurrentMsg--;
                        break;

                    case 'id_msgNext':
                        if (msgCurrentMsg<msgNumMessages)
                            msgCurrentMsg++;
                        break;

                }

                UpdateMessages (msgCurrentMsg, 'Type');
                UpdateMessages (msgCurrentMsg, 'Head');
                UpdateMessages (msgCurrentMsg, 'Text');

                button = document.getElementById('id_msgPrev');
                if (button)
                    button.disabled = (msgCurrentMsg==1);

                button = document.getElementById('id_msgNext');
                if (button)
                    button.disabled = (msgCurrentMsg==msgNumMessages);

                if (msgCurrentMsg==msgNumMessages)
                {
                    button = document.getElementById('id_msgClose');
                    if (button)
                        button.disabled = false;
                }
            }


        </script>

HEREDOC;
                break;

        }
    }


}

?>
