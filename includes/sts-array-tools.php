<?php
define('INSERT_AT_INDEX', 0);
define('INSERT_BEFORE_VALUE', 1);
define('INSERT_AFTER_VALUE', 2);

/**
 * array_set_pos Moves or inserts elements to a given position
 * @param array & $array Array to modify
 * @param mixed $to_insert Element(s) to insert
 * @param integer|string $pos Index or existing element of $array defining new position of $to_insert
 * @param integer INSERT_AT_INDEX|INSERT_BEFORE_VALUE|INSERT_AFTER_VALUE $mode Specifies $pos is an index of a value.
 * @return void
 */

function array_set_pos(&$array, $to_insert, $pos, $mode = INSERT_AT_INDEX) {
    foreach ((array)$to_insert as $value) {
        $pos_del = array_search($value, $array);
        if ($pos_del !== false)
            unset ($array[$pos_del]);
    }

    switch ($mode) {
        case INSERT_BEFORE_VALUE:
        case INSERT_AFTER_VALUE:
            $pos_insert = array_search($pos, $array);
            if ($pos_insert === false)
                return;
            if ($mode == INSERT_AFTER_VALUE)
                $pos_insert++;

            array_splice($array, $pos_insert, 0, $to_insert);
            break;

        case INSERT_AT_INDEX:
            array_splice($array, $pos, 0, $to_insert);
            break;
    }
}

//#########################################################################

/**
 * reduce_assoc Creates a new 1-dimensional array from a 2-dimensional array
 * using same keys with one selected column
 * @param array $array 2-dimensiona înput array
 *
 * @return 1-dim reduced array
 */

function reduce_assoc($array, $column) {
    return array_combine(array_keys($array), array_column($array, $column));
}


/**
 * Explodes a postgresql subquery string like '{a,b,c}' into a php-array [a,b,c];
 * @param string $string
 * @return array|array
 */

function pg_explode($string) {
    if (is_string($string) && ($string[0] == '{')) {
        $csv = trim(substr($string, 1, -1));
        if (strlen($csv))
            return explode(',', $csv);
    }
    return [];
}

function safe_merge($a1, $a2) {
    if (is_array($a1) && is_array($a2))
        return array_merge($a1, $a2);

    if (is_array($a1))
        return $a1;

    return $a2;
}

function array_rename_key(&$array, $search, $replace) {
    $result = [];
    foreach ($array as $key => &$set) {
        if ($key == $search)
            $result[$replace] = &$set;
        else
            $result[$key] = &$set;
    }
    return $result;
}

function array_remove_empty($array) {
    $result = [];
    foreach ($array as $key => $val)
        if (!empty ($val))
            $result[$key] = $val;
    return $result;
}

//##########################################################################
/**
 * array_remove_value Returns an array without a given value
 * @param array $array 1-dimension input array
 * @param mixed $value value to be removed from the array
 * @return 1-dim reduced array
 */
function array_remove_value($array, $value) {
    if (($key = array_search($value, $array)) !== false) {
        unset ($array[$key]);
    }
    return $array;
}

//#########################################################################
function format_array_values($array, $php_format) {
    $php_format = str_replace('"', '\"', $php_format);

    $_R = [];
    foreach ($array as $_K => $_S) {
        extract($_S, EXTR_OVERWRITE);
        eval ("\$_R[\$_K]=\"$php_format\";");
    }
    return $_R;
}

//#########################################################################
/**
 * array_select_columns returns all columns of an 2-dimensional array, which are selected in column_numbers, in a new array
 * @param array $array
 * @param array $column_numbers
 * returns 2-dim array
 */
function array_select_columns($array, $column_numbers) {
    $result = array();
    $rowcount = 0;
    foreach ($array as $row) {
        foreach ($column_numbers as $number) {

            $result[$rowcount][] = $row[$number];

        }
        $rowcount++;
    }
    return $result;
}

//##########################################################################

function make_map($array, $index_col, $data_cols) {
    if (!is_array($array))
        return null;

    if (is_array($data_cols)) {
        $result = [];
        foreach ($array as &$set) {
            $ind = $set[$index_col];
            $result[$ind] = [];
            foreach ($data_cols as $col)
                $result[$ind][$col] = $set[$col];
        }
        return $result;
    }
    return array_column($array, $data_cols, $index_col);
}


//function array_key_first($array)
//{
//  reset($array);
//  return key($array);
//}
//
//function array_key_last($array)
//{
//  end($array);
//  return key($array);
//}

?>