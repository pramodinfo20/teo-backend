<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<title>StreetScooter</title>
<meta name="description" content="">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<style type="text/css">
body
{
}
table, th, td {border-collapse: collapse; border: 1px solid #CCCCCC;}

th,td { padding: 5px; text-align: left}

table.secondtable
{
max-width: 500px;
}
</style>

<body>
<?php
$db_handle=pg_connect('dbname=LeitwartenDB host=10.12.54.173 port=5432 user=webinterface password=kv2bktj7Xn2IpNv5R82p') or die("Couln't Connect ".pg_last_error());
// $query = "SELECT * FROM vehicles ORDER BY update_timestamp";
// $query_result = pg_query ( $db_handle, $query ) or die ( "Couln't Connect " . pg_last_error () );
// $theobjects=array();

function objtotable($theobjects,$tableclass)
{
	$contentHTML = "";
	$cnt=0;
	$length = sizeof ( $theobjects [0] );
	$headings = array_shift ( $theobjects );
	$contentHTML = "<table class=\"$tableclass\" width=\"100%\" style=\"border-collapse: collapse; \"><tr>";

	foreach ( $headings ["headingone"] as $value ) {
		$contentHTML .= "<th>".$value."</th>";
	}
	$contentHTML .= "</tr>";
	if (! empty ( $headings ["headingtwo"] )) {
		$contentHTML .= "<tr>";
		foreach ( $headings ["headingtwo"] as $value )
		{
			$contentHTML .= "<th>" . $value . "</th>";
		}
		$contentHTML .= "</tr>";
	}
	foreach ( $theobjects as $theobject ) {
		$contentHTML .= "<tr>";
		$rowdata = $theobject;
		if(empty($rowdata)){$contentHTML .= "</tr>"; continue;}
		if($rowdata["checked"]==1){
			if($cnt%2)
				$bgcolor="#CCFFCC";
			else
				$bgcolor="#C1F1C1";
		}
		elseif($rowdata["checked"]==-1){
			if($cnt%2)
				$bgcolor="#FFCCCC";
			else
				$bgcolor="#F1C1C1";
		}
		else{
			if($cnt%2)
				 $bgcolor="#FFFFCC";
			else
				 $bgcolor="#F1F1C1";
		}
		foreach ( $rowdata as $key => $value )
		{
			if($key=="update_timestamp")
//			$contentHTML .= "<td style=\"background-color: ".$bgcolor."\" >" . date("F j, Y, g:i a",$value). "</td>";
			$contentHTML .= "<td style=\"background-color: ".$bgcolor."\" >" . date(DATE_ISO8601,$value). "</td>";
			else
			$contentHTML .= "<td style=\"background-color: ".$bgcolor."\" >" . $value . "</td>";
		}
		$contentHTML .= "</tr>";
		$cnt++;
	}
	$contentHTML .= "</table>";

	echo $contentHTML;
}


//echo "<h1>Fahrzeuge mit Tippfehler in VIN</h1>";
//$query = "select vin, c2cbox, timestamp, -1 as checked FROM vehicles_failed_vins ORDER BY substring(vin,1,3) DESC, substring(vin,13) DESC;";
//$query_result = pg_query ( $db_handle, $query ) or die ( "Couln't Connect " . pg_last_error () );
//$theobjects=array();
//$theobjects[]["headingone"]=array(
//		"VIN","C2CBox ID","Update Timestamp","Gepr&uuml;ft");
//while($theobjects[]= pg_fetch_assoc ( $query_result ));
//objtotable($theobjects,"secondtable");
//echo "<br><br><br>";


echo "<h1>Fahrzeuge mit C2C-Box</h1>";
//$query = "SELECT vin,c2cbox,update_timestamp FROM vehicles ORDER BY update_timestamp DESC LIMIT 15";
//$query = "select vin,c2cbox,update_timestamp FROM vehicles WHERE substring(vin,1,3) != 'WST' ORDER BY substring(vin,1,3) DESC, substring(vin,13) DESC;";
$query = "select vin,v.c2cbox,update_timestamp,CASE WHEN sd_card = 'ok' AND reverse_tunnel = 'ok' AND vin_ok = 'ok' AND can_ok = 'ok' THEN 1 WHEN sd_card IS NULL AND reverse_tunnel IS NULL AND vin_ok IS NULL AND can_ok IS NULL THEN 0 ELSE -1 END as checked from vehicles as v LEFT JOIN c2c_configuration as c on v.c2cbox = c.c2cbox WHERE v.c2cbox != '' and (substring(vin,1,5) = 'WS5D1' or substring(vin,1,5) = 'WS5E1' or substring(vin,1,5) = 'WS5B1') AND substring(vin,12,5)!='MIRCO' AND v.c2cbox IS NOT NULL ORDER BY substring(vin,1,6) DESC, substring(vin,13) DESC LIMIT 20000;";
//$query = "select vin,v.c2cbox,update_timestamp,CASE WHEN sd_card = 'ok' AND reverse_tunnel = 'ok' AND vin_ok = 'ok' AND can_ok = 'ok' THEN 1 WHEN sd_card IS NULL AND reverse_tunnel IS NULL AND vin_ok IS NULL AND can_ok IS NULL THEN 0 ELSE -1 END as checked from vehicles as v LEFT JOIN c2c_configuration as c on v.c2cbox = c.c2cbox WHERE substring(vin,1,3) != 'WST' AND substring(vin,1,3) != 'WT5' AND substring(vin,12,6) != '134349' AND v.c2cbox IS NOT NULL ORDER BY substring(vin,1,3) DESC, substring(vin,13) DESC LIMIT 500;";
$query_result = pg_query ( $db_handle, $query ) or die ( "Couln't Connect " . pg_last_error () );
$theobjects=array();
$theobjects[]["headingone"]=array(
		"VIN","C2CBox ID","Update Timestamp","Gepr&uuml;ft");
while($theobjects[]= pg_fetch_assoc ( $query_result ));

objtotable($theobjects,"secondtable");
echo "<br><br><br>";
?>
</body>
</html>
