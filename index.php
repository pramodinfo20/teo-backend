<?php
// Test

//error_reporting(E_ALL & -E_NOTICE);
//ini_set('display_errors', 'On');
//phpinfo();exit;
// before merge
/**
 * Loads class files automatically by including corresponding files.
 *
 * If class PageController is called, file classes/PageController.class.php is called.
 *
 * @param $class_name Class Name passed automatically
 * @author Pradeep Mohan
 */

$_SERVER['STS_ROOT'] = $_SERVER['DOCUMENT_ROOT'];

include $_SERVER['STS_ROOT'] . "/includes/sts-tools.php";

$GLOBALS['config'] = "/var/www/config/FEV_Pl.config.php";

// enable Anti CSRF Tokens here

//function csrf_startup()
//{
//  csrf_conf('rewrite-js', '/csrf-magic/csrf-magic.js');
//  if (isset($_POST['ajax'])) csrf_conf('rewrite', false);
//}
//
//include_once dirname(__FILE__) . '/csrf-magic/csrf-magic.php';

ini_set('session.cookie_httponly', 1);

// **PREVENTING SESSION FIXATION**
// Session ID cannot be passed through URLs
//ini_set('session.use_only_cookies', 1);

//WARNING!!!!!!!!!!! COMMENT TO DISABLE CSRF!!!!!!!!!!!!!!
// Uses a secure connection (HTTPS) if possible
//ini_set('session.cookie_secure', 1);
session_start(['cookie_lifetime' => 86400,]);

spl_autoload_register("myAutoload");

date_default_timezone_set('Europe/Berlin');

if (!function_exists('utf8_encode')) {
    require_once $_SERVER['STS_ROOT'] . "/includes/utf8_encocde.php"; //enable compatibility with PHP versions less than 5.5.0
}

include $_SERVER['STS_ROOT'] . '/classes/HTML_QuickForm2_Element_InputTime.class.php';
include $_SERVER['STS_ROOT'] . '/classes/HTML_QuickForm2_Element_InputNumber.class.php';
include $_SERVER['STS_ROOT'] . '/classes/HTML_QuickForm2_Element_StaticNoLabel.class.php';
include $_SERVER['STS_ROOT'] . '/fpdf/fpdf.php';
require $_SERVER['STS_ROOT'] . '/vendor/autoload.php';

new LocalConfig('FEV_Pl');

$pageController = new PageController();

$pageController->Run();

?>