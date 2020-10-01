<?php

/**
 * Loads class files automatically by including corresponding files.
 *
 * If class PageController is called, file classes/PageController.class.php is called.
 *
 * @param $class_name Class Name passed automatically
 * @author Pradeep Mohan
 */

$_SERVER['STS_ROOT'] = dirname($_SERVER['DOCUMENT_ROOT']);
include $_SERVER['STS_ROOT'] . "/includes/sts-tools.php";

spl_autoload_register("myAutoload");

date_default_timezone_set('Europe/Berlin');

/**
 * If PHP version PHP 5 <= 5.5.0 then array_column does not exist by default.
 */
if (!function_exists('array_column')) {
    require_once $_SERVER['STS_ROOT'] . "/scr/array_column.php"; //enable compatibility with PHP versions less than 5.5.0
}

include $_SERVER['STS_ROOT'] . '/classes/HTML_QuickForm2_Element_InputTime.class.php';
include $_SERVER['STS_ROOT'] . '/classes/HTML_QuickForm2_Element_InputNumber.class.php';
include $_SERVER['STS_ROOT'] . '/classes/HTML_QuickForm2_Element_StaticNoLabel.class.php';
include $_SERVER['STS_ROOT'] . '/fpdf/fpdf.php';

$pageController = new PageController();
?>
