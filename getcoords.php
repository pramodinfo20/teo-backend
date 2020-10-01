<?php
require_once 'DrSlump/Protobuf.php';
\DrSlump\Protobuf::autoload();
require_once('./status_message.php');
require_once('./control_messages.php');

$gps_coords = array();
$client = new Mosquitto\Client(NULL, true);
define("MQTT_TLS_VERSION", "tlsv1");
define("MQTT_CAFILE", "/etc/mosquitto/ssl/cacerts/");
define("MQTT_CERTFILE", "/root/ladeleitwarte.crt");
define("MQTT_KEYFILE", "/root/ladeleitwarte.key");
//test
$client->onConnect('connect');
$client->onDisconnect('disconnect');
$client->onSubscribe('subscribe');
$client->onMessage('message');
$client->setTlsInsecure(true);
$client->setTlsOptions(Mosquitto\Client::SSL_VERIFY_NONE, "tlsv1");
$client->setTlsCertificates(MQTT_CAFILE, MQTT_CERTFILE, MQTT_KEYFILE);
$client->connect("localhost", 8883);
$client->onLog('logger');
$client->subscribe('S/sts-c2cbox22/#', 1);

$precon = new c2c\Preconditioning();
$precon->setDuration(3600);
$precon->setDefBRemoteHeatingCabin(20);
$preconmsg = $precon->serialize();
//echo $preconmsg;

$timer = 0;
$startime = 0.0000;
$currentime = 0.0000;
$cntSecondsElapsed = 0;
$durationRequired = 1;
while ($cntSecondsElapsed < $durationRequired) {
    if ($timer == 0) {
        $startime = $currentime = time();
        $timer = 1;
    }
    $currentime = time();
    if ($currentime - $startime < 1) {
        $client->loop();

    } else {
        $timer = 0;
        $cntSecondsElapsed++;
    }

}

function connect($r, $message) {
    //echo "I got code {$r} and message {$message}\n";
}

function subscribe($mid, $qos_count) {
    //echo "Subscribed to a topic".$mid."|".$qos_count."\n";
}

function unsubscribe($mid) {
    //echo "Unsubscribed from a topic".$mid."\n";
}

function message($message) {
    //printf("Got a message on topic %s with payload:\n%s\n", $message->topic, $message->payload);

    $messagetopics = explode('/', $message->topic);

    if ($messagetopics[2] == 'position') {
        $carpos = new c2c\PositionStatus ($message->payload);
        global $gps_coords;
        $gps_coords["lon"] = $carpos->getLon();
        $gps_coords["lat"] = $carpos->getLat();
        echo json_encode($gps_coords);
// 		echo "<strong>".$messagetopics[1]."</strong>";
// 		echo "Topic is ".$message->topic;
    }
// 	else if($messagetopics[2]=='vin')
// 	{

// 		$carvin= new c2c\VINStatus ($message->payload);
// 		echo "VIN : ".$carvin->getVin();
// 		echo "<br><br>";
// 	}
// 	else if($messagetopics[2]=='online')
// 	{
// 		$caronline= new c2c\OnlineStatus ($message->payload);
// 		if($caronline->getOnline()) echo "Fahrzeug Online"; 
// 		else echo "Fahrzeug nicht Online";
// 		echo "<br><br>";
// 	}
// 	else 
// 	{
// 		$carsignal= new c2c\SignalStatus($message->payload);
// 		echo $messagetopics[2]." : ".$carsignal->getValue();
// 		echo "<br><br>";
// 	}

// 	global $client;
// 	if($messagetopics[1]=='sts-c2cbox33')

}


function disconnect() {

    //echo "Disconnected cleanly\n";
}

function logger() {
//var_dump(func_get_args());
}

?>