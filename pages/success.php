<?php

/**
 * Loads class files automatically by including corresponding files.
 *
 * If class PageController is called, file classes/PageController.class.php is called.
 *
 * * @author pramod jayaramaiah
 * @param $class_name Class Name passed automatically
 */

// $_SERVER['STS_ROOT'] = $_SERVER['DOCUMENT_ROOT'];

// include $_SERVER['STS_ROOT'] . "/includes/sts-tools.php";


 // enable Anti CSRF Tokens here
function csrf_startup()
{
    
    csrf_conf('rewrite-js', '/csrf-magic/csrf-magic.js');
    if (isset($_POST['ajax']))
        csrf_conf('rewrite', false);
        
}

spl_autoload_register("myAutoload");

date_default_timezone_set('Europe/Berlin');

if (! function_exists('utf8_encode')) {
    require_once $_SERVER['STS_ROOT'] . "/includes/utf8_encocde.php"; // enable compatibility with PHP versions less than 5.5.0
}

new LocalConfig('webinterface');

$GLOBALS['debug']['debugout'] = 1;
$GLOBALS['VERBOSE'] = true;
ini_set("error_log", "/var/www/stslogs/php-error.log");

// feedback sent to the below email 

$to = array('pramod.jayaramaiah@streetscooter.com', 'ismail.sbika@streetscooter.com', 'Ralf.Schapdick@streetscooter.com');
// post data from feedback.php
$subject = $_POST['subject'];
$name = $_POST['name'];
$from = $_POST['mail'];
$role = $_POST['title'];
//$page = $_POST['page'];
$_current_action = $_SESSION['action'];
$url = parse_url($_current_action);
parse_str($url['query'], $params);
$page = $params['action'];
// echo "curren_action: " .  $_current_action . '<br>';
$comment = <<<HEREDOC
<html>
<head>
</head>
<body>
<div> Hallo,</div><br>
<div><b>{$subject}</b> </div> <br>
<div><b>Message:</b> {$_POST['comment']} </div><br>
<div><a href="{$_current_action}">{$_current_action}</a> </div>
<br><br>
<div> <b>Name:</b> {$name}</div>
<div> <b>From:</b> <a href="mailto:{$from}">{$from} </a></div>
</body>
</html>
HEREDOC;
$title = "Feedback: $role / $page";

  // echo $to. $from. $title. $comment;

try {
     $mailer = new MailerSmimeSwift($to, $from, $title, $comment, null, true, null);
    echo "Feedback wird gesendet. \n danke!";
} catch (Exception $E) {
    echo "Exception: {$E->message}";
}

?>
