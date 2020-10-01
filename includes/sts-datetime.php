<?php
define('ONE_DAY', 86400);

function to_iso8601_date($localDate, $default = "") {
    if (preg_match('/([0-3][0-9]).([0-1][0-9]).(20[0-9][0-9])/', $localDate, $match)) {
        return "{$match[3]}-{$match[2]}-{$match[1]}";
    }
    return $default;
}

function to_iso8601_datetime($localDate, $default = "") {
    if (preg_match('/([0-3][0-9]).([0-1][0-9]).(20[0-9][0-9])(.*)/', $localDate, $match)) {
        return "{$match[3]}-{$match[2]}-{$match[1]}{$match[4]}";
    }
    return $default;
}

function to_locale_date($iso8601, $default = "") {
    if (preg_match('/(20[0-9][0-9])-([0-1][0-9])-([0-3][0-9])/', $iso8601, $match)) {
        return "{$match[3]}.{$match[2]}.{$match[1]}";
    }
    return $default;
}

function to_locale_datetime($iso8601, $include_millisecods = false, $default = "") {
    if (preg_match('/(20[0-9][0-9])-([0-1][0-9])-([0-3][0-9]) ([0-2][0-9]:[0-5][0-9]:[0-5][0-9])(.*)/', $iso8601, $match)) {
        if (!$include_millisecods)
            $match[5] = '';
        return "{$match[3]}.{$match[2]}.{$match[1]} {$match[4]}{$match[5]}";
    }
    return $default;
}

?>