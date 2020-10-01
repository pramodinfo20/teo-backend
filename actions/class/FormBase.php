<?php
/**
 * actions/class/CAction_class_FormBase.php
 *
 * Basisklasse fuer die neue Methode der Action-Paches aus dem Verzeichnis {http-root}/actions
 * @author Lothar Jürgens
 */

// ==============================================================================================

class AClass_FormBase extends AClass_Base {
    // ==============================================================================================

    CONST ERROR_MAPPED_METHOD_NOT_FOUND = 1;

    protected $ERRORS = array();
    protected $ERRORS_LOGGED = array();
    protected $loadJQuery = false;

    /**
     * Konstruktor
     *
     * @param PageController $pageController
     */

    function __construct() {
        parent::__construct();
    }


    // ==============================================================================================

    function GetHtmlElement_Default($dataRow, $content, $idCol, $id, $attr) {
        return "$content";
    }

    // ==============================================================================================

    function GetHtmlElement_Edit($dataRow, $content, $idCol, $id, $attr) {
        return "<span id=\"ID$idCol-$id\" class=\"editable\" $attr>$content</span>";
    }

    // ==============================================================================================

    function GetHtmlElement_Number($dataRow, $content, $idCol, $id, $attr) {
        return "<span id=\"ID$idCol-$id\" class=\"editable\" $attr>$content</span>";
    }

    // ==============================================================================================
    function GetHtmlElement_Checkbox($dataRow, $content, $idCol, $id, $attr) {
        $name = "cb_$idCol" . "[$id]";
        $checked = (($content != "") ? " checked" : "");

        return "<input type=\"checkbox\" id=\"id_$idCol-$id\" name=\"$name\" $checked $attr>";
    }

    // ==============================================================================================

    function GetHtmlElement_Deleted($dataRow, $content, $idCol, $id, $attr) {
        return "<del $attr>$content</del>";
    }

    // ==============================================================================================

    function GetHtmlElement_ChangingTo($dataRow, $content, $idCol, $id, $attr) {
        if ($attr == "")
            $attr = 'class="ChangingTo"';

        if (isset($dataRow['new']) && isset($dataRow['new'][$idCol])) {
            $text_new = $dataRow['new'][$idCol];
            if (isset($dataRow['new']['.lookup'][$idCol]))
                $text_new = $dataRow['new']['.lookup'][$idCol];
            else
                if (isset($this->colConfig[$idCol]['.lookup'][$text_new]))
                    $text_new = $this->colConfig[$idCol]['.lookup'][$text_new];

            if ($text_new == $content)
                return $content;
        } else
            return $content;

        return "<div $attr><del>$content</del><br>&rArr;&nbsp;<span>$text_new</span></div>";

    }

    // ==============================================================================================

    function GetHtmlElement_ChangeIt($dataRow, $content, $idCol, $id, $attr) {
        if (is_array($attr)) {
            $attr_del = safe_val($params, 'attr_old', "");
            $attr_input = safe_val($params, 'attr_input', "");
            $name = safe_val($params, 'name', "input_$idCol");
        } else {
            $attr_input = $attr;
        }

        $element = ($content == "") ? "" : "<del $attr_del>$content</del><br>";
        $element .= '<input type="text" name="' . $name . '[' . $id . ']" ' . $attr_input . ">\n";
        return $element;
    }

    // ==============================================================================================

    function GetHtmlElement_ShowAsDate($dataRow, $content, $idCol, $id, $attr) {
        if ($content != "") {
            // sscanf ($content, "%d-%d-%d %d:%d:%d.%d%c%d", $year, $month, $day, $hour, $minute, $second, $millisec, $vz, $timeshift);
            sscanf($content, "%d-%d-%d %s", $year, $month, $day, $rest);

            $content = sprintf("%02d.%02d.%04d", $day, $month, $year);
        }
        return "<span $attr>$content</span>";
    }

    // ==============================================================================================

    function SetupHeaderFiles($displayheader) {
        parent::SetupHeaderFiles($displayheader);

        if (!$this->loadJQuery) {
            $displayheader->removeStylesheet("/css-jQuery.*/i");
            $displayheader->removeJS();
        }

        $displayheader->removeScriptTags();
        $displayheader->enqueueJs('sts-tools', 'js/sts-tools.js');
        $displayheader->enqueueJs('formtools', 'js/formtools.js');
    }

    // ==============================================================================================
}

?>
