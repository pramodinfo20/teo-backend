<?php

define('SQL_LOG_OFF', 0);
define('SQL_LOG_ERROR', 1);
define('SQL_LOG_ALL', 2);

define('COL_DEFAULT', 0);
define('COL_NOT_USED', 1);
define('COL_INVISIBLE', 2);
define('COL_VISIBLE', 3);

define('_cma_', ',');
define('_dot_', '.');
define('nl', "\n");
define('lf', "\n");
define('crlf', "\r\n");
define('brlf', "<br>\n");

define('_X_', 0);
define('_LFT_', 0);
define('_Y_', 1);
define('_TOP_', 1);
define('_Z_', 2);
define('_lon_', 0);
define('_lat_', 1);
define('_loc_', 2);

function r($string) {
    echo '<pre>';
    print_r($string);
    echo '</pre>';

//  debug_print_backtrace();
}

function myAutoload($class_name) {
    global $_SERVER;

    if (strpos($class_name, 'Swift_') !== false) {
        return;
    } else if (substr_compare($class_name, "AClass_", 0, 7) == 0) {
        $cls = substr($class_name, 7);
        $file = sprintf("%s/actions/class/%s.php", $_SERVER['STS_ROOT'], $cls);

    } else if (substr_compare($class_name, "ACtion_", 0, 7) == 0) {
        // $parts = explode ('_', $class_name, 3);
        $action = substr($class_name, 7);
        $file = sprintf("%s/actions/%s/%s.php", $_SERVER['STS_ROOT'], $GLOBALS['role'], $action);

        // if (! file_exists($file))
        //     $file = sprintf ("%s/actions/%s/%s/Execute.php", $_SERVER['STS_ROOT'], $GLOBALS['role'], $action);

    } else {
        $file = $_SERVER['STS_ROOT'] . '/classes/' . $class_name . '.class.php';
    }

    if (!file_exists($file))
        return;
    // throw new Exception ("Include file not found: $file");

    include $file;
}

// ==============================================================================================
function &InitSessionVar(&$expression, $default, $force_init = false) {
    if (!isset ($expression) || $force_init) {
        $expression = $default;
    }
    return $expression;
}

// ==============================================================================================
function SafeUpdateFromRequest(&$variable, $requestname) {
    global $_REQUEST;

    if (isset($_REQUEST[$requestname]) && ($_REQUEST[$requestname] != ''))
        $variable = $_REQUEST[$requestname];
}

// ==============================================================================================
function safe_val(&$array, $key, $default = "") {
    return (isset($array[$key]) ? $array[$key] : $default);
}

// ==============================================================================================
/**
 * safe_array ensures a result to be an array.
 *
 * @param mixed $mixed : anv value
 * @return array
 */
function safe_array(&$mixed) {
    if (!is_array($mixed))
        return empty($mixed) ? [] : [$mixed];
    return $mixed;
}

// ==============================================================================================
function safe_tag_value(&$array, $tag, $default = "", $value_name = 'tag_value') {
    return (isset($array[$tag][$value_name]) ? $array[$tag][$value_name] : $default);
}

// ==============================================================================================
function tag_exists(&$array, $tag, $value_name = 'tag_value') {
    return isset($array[$tag][$value_name]);
}

// ==============================================================================================
function make_node(array &$base) {
    $num = func_num_args() - 1;
    $ref = &$base;

    if ($num <= 1) {
        return false;
    }

    for ($i = 1; $i < $num; $i++) {
        $key = func_get_arg($i);
        if (!isset($ref[$key]))
            $ref[$key] = [];
        else
            if (!is_array($ref[$key]))
                return false;

        $ref = &$ref[$key];
    }
    $key = func_get_arg($i);
    $ref = $key;
    return true;
}

// ==============================================================================================

/**
 * safe_id returns a php/html syntax conform identifier
 *
 * @param string any identifier, such as a SQL column name
 */
function safe_id($colname) {
    return preg_replace('[^a-zA-Z0-9_]', '_', $colname);
}

// ==============================================================================================
/**
 * safe_lower_id sql as safe_id, but turns the string into lower case
 *
 * @param string any identifier, such as a SQL column name
 */
function safe_lower_id($colname) {
    return preg_replace('[^a-z0-9_]', '_', strtolower($colname));
}

// ==============================================================================================
function extractFromStyleString($param, &$style, $default, $delete = false) {
    $value = preg_replace("/^.*;$param:([-a-z0-9 _]+)[;].*/i", '$1', ";$style;", 1, $count);
    if ($count == 1) {
        if ($delete) {
            $style = preg_replace("/^;?(.*);$param:[-a-z0-9 _]+(;.*$)/i", '$1$2', ";$style;");
            if (substr($style, 0, 1) == ';') $style = substr($style, 1);
        }
        return $value;
    }
    return $default;
}

// ==============================================================================================
function makeVarlist(&$varlist, $varname, $value) {
    if (is_array($value)) {
        foreach ($value as $key => $subval) {
            makeVarlist($varlist, $varname . "[" . $key . "]", $subval);
        }
    } else {
        $varlist[] = ['name' => $varname, 'value' => $value];
    }
}

// ---------------------------------------------------------------------
// ==============================================================================================
function forwardPostVarsAsHiddenInput($excludeVars = null, $varset = false) {
    $result = "";
    $varlist = [];
    $excludeVars = (array)$excludeVars;
    if (!$varset) $varset = &$_REQUEST;


    foreach ($varset as $varname => $value) {
        if (array_search($varname, $excludeVars) === false)
            makeVarlist($varlist, $varname, $value);
    }

    foreach ($varlist as $varset) {
        $result .= '<input type="hidden" name="' . $varset['name'] . '" value="' . $varset['value'] . "\">\n";
    }
    return $result;
}

// ==============================================================================================
function DebugOut($text) {
    echo "<script>DebugOut($text);</script>";
}

// ==============================================================================================
function DebugLog($text) {
    if ($GLOBALS['IS_DEBUGGING'] && isset($GLOBALS['debug']['debuglog'])) {
        $fout = fopen($GLOBALS['debug']['debugout'], 'at+');
        if ($fout) {
            fprintf($fout, "%s: %s\n", date("d.M.Y - G:i:s:"), $text);
            fclose($fout);
        }
    }
}

// ==============================================================================================
function mkBool(&$value) {
    $value = ($value == 't') ? true : false;
}

// ==============================================================================================
function toBool($value) {
    if (isset($value)) {
        if (($value === false) || ($value === true)) return $value;

        $v = strtolower($value);

        if (($v === 'f') || ($v === '0') || ($v === '') || ($v == 'nein'))
            return false;
        return true;
    }
    return false;
}

// ==============================================================================================
function zero2dash($value) {
    $value = intval($value);
    if ($value == 0)
        return '-';
    return $value;
}

// ==============================================================================================
function nohexprefix($hex) {
    if (!strncasecmp($hex, "0x", 2))
        return substr($hex, 2);
    return $hex;
}

// ==============================================================================================
function striphexprefix(&$hex) {
    if (!strncasecmp($hex, "0x", 2))
        $hex = substr($hex, 2);
}

// ==============================================================================================
function normhex($val, $size) {
    if (empty(trim($val)))
        return "";

    if (!strncasecmp($val, "0x", 2))
        return $val;
    $strsize = 2 * $size;
    if (strlen($val) < $strsize)
        $val = str_pad($val, $strsize, '0', STR_PAD_LEFT);
    return "0x$val";
}

// ==============================================================================================
function add_delimiter($delim, $string) {
    if (empty ($string))
        return "";
    return $delim . $string;
}

// ==============================================================================================
function safe_db_value_bool($value) {
    return toBool($value) ? "'t'" : "'f'";
}

// ==============================================================================================
function safe_db_value_booln($value) {
    if (!isset($value))
        return 'null';

    return toBool($value) ? "'t'" : "'f'";
}

// ==============================================================================================
function safe_db_value_int($value) {
    $value = trim($value);

    if (!isset($value) || ($value == ''))
        return 'null';

    if (preg_match('/^[+-]?[0-9]+$/', $value))
        return $value;

    return 'null';
}

// ==============================================================================================
function safe_db_value_float($value) {
    $value = trim(str_replace('.', ',', $value));

    if (!isset($value) || ($value == ''))
        return 'null';

    if (preg_match('/^[+-]?([0-9]*)[.]?([0-9]*)$/', $value, $match)) {
        if (empty ($match[1]) && empty ($match[2]))
            return '0';
        return $value;
    }

    return 'null';
}

// ==============================================================================================
function safe_db_value_string($value) {
    if (!isset ($value))
        return 'null';

    return "E'" . addslashes($value) . "'";
}

// ==============================================================================================
function get_safe_update(&$update, &$db_data, &$post_data, $column) {
    $new_value = addslashes($post_data[$column]);
    $old_value = safe_val($db_data, $column, '');

    if ($new_value != $old_value) {
        $update[$column] = $new_value;
    }
}

function strcrop(&$string, $delim) {
    $pos = strpos($string, $delim);
    if ($pos === false)
        return null;
    $result = substr($string, 0, $pos);
    $string = substr($string, $pos + 1);
    return $result;
}

?>