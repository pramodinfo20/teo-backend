<?php

/**
 * Loads class files automatically by including corresponding files.
 *
 * If class PageController is called, file classes/PageController.class.php is called.
 *
 * @author Pradeep Mohan
 * @param $class_name Class Name passed automatically
 */
$_SERVER['STS_ROOT'] = $_SERVER['DOCUMENT_ROOT'];

include $_SERVER['STS_ROOT'] . "/includes/sts-tools.php";


// enable Anti CSRF Tokens here
function csrf_startup()
{
    
    csrf_conf('rewrite-js', '/csrf-magic/csrf-magic.js');
    if (isset($_POST['ajax']))
        csrf_conf('rewrite', false);
        
}

include_once dirname(__FILE__) . '/csrf-magic/csrf-magic.php';

ini_set('session.cookie_httponly', 1);

// **PREVENTING SESSION FIXATION**
// Session ID cannot be passed through URLs
ini_set('session.use_only_cookies', 1);

// Uses a secure connection (HTTPS) if possible
ini_set('session.cookie_secure', 1);

session_start([
    'cookie_lifetime' => 86400
]);

spl_autoload_register("myAutoload");

date_default_timezone_set('Europe/Berlin');

if (! function_exists('utf8_encode')) {
    require_once $_SERVER['STS_ROOT'] . "/includes/utf8_encocde.php"; // enable compatibility with PHP versions less than 5.5.0
}

new LocalConfig('webinterface');

$GLOBALS['debug']['debugout'] = 1;
$GLOBALS['VERBOSE'] = true;
ini_set("error_log", "/var/www/stslogs/php-error.log");

$test_to = [
    // 'dennis.greger@deutschepost.de'=>'Dennis Greger Post Test',
    'pramod.jayaramaiah@streetscooter.eu' => 'Streetscooter Test'
];

$content_html = <<<HEREDOC
<html>
<head>
<title>Testmail</title>
</head>
<body>
<div style="font-size: 16px;">Dieses ist nur ein</div>
<div style="font-size: 20px; Color: #500;">Test</div>
</body>
</html>
HEREDOC;

try {
    // echo $content_html;
    $mailer = new MailerSmimeSwift($test_to, '', 'StreetScooter Ankündigung ', $content_html, null, true, null);
    // var_dump ($mailer);
} catch (Exception $E) {
    echo "Exception: {$E->message}";
}

?>
