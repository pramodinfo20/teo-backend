<?php
function utf8_encode($string) {
    return iconv("ISO-8859-1", "UTF-8", $string);
}

function utf8_decode($string) {
    return iconv("UTF-8", "ISO-8859-1//TRANSLIT", $string);
}

?>