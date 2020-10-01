<?php

class CronController extends PageController {

    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {


        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->user = $user;
        $this->msgs = null;
        $this->content = '';
        $this->displayHeader = $this->container->getDisplayHeader();

        $this->displayHeader->setTitle("StreetScooter Cloud Systems : " . 'test');


        $this->action = $this->requestPtr->getProperty('action');

        $db_vehicle_variants = $this->ladeLeitWartePtr->vehicleAttributesPtr->getAttributeValuesFor('Fahrzeugvariante');

        $this->vehicle_variants = array_combine(array_column($db_vehicle_variants, 'value_id'), array_column($db_vehicle_variants, 'value'));

        $this->sopVariants = $this->pvsVariants = array();

        foreach ($this->vehicle_variants as $vehicle_variant_value => $vehicle_variant) {
            if (strpos($vehicle_variant, 'B14')) {
                $this->defaultVehicleVariant = $vehicle_variant_value;
                $this->pvsVariants[] = $vehicle_variant_value;
            } else
                $this->sopVariants[] = $vehicle_variant_value;
        }


        $this->emailtext = "
					Sehr geehrte Damen und Herren ,\r\n
						\r\n
						voraussichtlich zu unten genannten Zeitpunkten wird Ihre Niederlassung mit der folgenden
				Anzahl an StreetScootern beliefert werden: \r\n
						\r\n
						{deliveryStr}\r\n
						\r\n
						Bitte weisen Sie im Webinterface des StreetScooter Cloud-Systems unter 
						<a href='https://streetscooter-cloud-system.eu/index.php?action=auszulieferende'>https://streetscooter-cloud-system.eu/index.php?action=auszulieferende</a>
						möglichst innerhalb einer Woche diesen Fahrzeugen einen ZSP und eine
						Ladepunkte zu. \r\n
						\r\n
						Bitte nehmen Sie die Eintragung innerhalb von 5 Arbeitstagen vor, weil sonst leider andere Niederlasungen vorgezogen werden müssen.
						\r\n
						\r\n
						Die Reihenfolge Ihrer Zuweisung bestimmt die zeitliche Abfolge der Auslieferung.\r\n\r\n\r\n	
						Mit freundlichen Grüßen,\r\n
						\r\n
						Ihr StreetScooter Cloud-System Team\r\n
						\r\n
						\r\n
						\r\n
						Diese Mail wurde automatisch generiert. Eine Antwort ist nicht möglich. \r\n
						Bitte wenden Sie sich bei Fragen an support@streetscooter-cloud-system.eu \r\n";

        $this->salestext = "
					Sehr geehrte Damen und Herren ,\r\n
						\r\n
						Auslieferungsplan diesen Monat ist jetzt im Webinterface des StreetScooter Cloud-Systems verfügbar.\r\n
						\r\n
						
						<a href='https://streetscooter-cloud-system.eu/index.php?action=showDivisionsDeliveryPlan'>Auslieferungsplan</a>
						\r\n\r\n
						Mit freundlichen Grüßen,\r\n
						\r\n
						Ihr StreetScooter Cloud-System Team\r\n
						\r\n
						\r\n
						\r\n
						Diese Mail wurde automatisch generiert. Eine Antwort ist nicht möglich. \r\n
						Bitte wenden Sie sich bei Fragen an support@streetscooter-cloud-system.eu \r\n";

        if (isset($this->action))
            call_user_func(array($this, $this->action));


        $this->displayHeader->printContent();

        $this->printContent();

    }

    /**
     * genAllVehiclesReport
     * called from the CronController for sending report out everyday
     *
     */
    function genAllVehiclesReport() {
        $vehicleHeadings = array('AKZ', 'IKZ', 'VIN', 'ZSP', 'ZSP OZ');
        $vehicles = $this->ladeLeitWartePtr->vehiclesPtr->getVehiclesForReport();
        $fname = "/tmp/daily_report_01.csv";
        $fhandle = fopen($fname, "w");
        $fcontent = array();
        $fcontent[] = implode(',', $vehicleHeadings);
        if (!empty($vehicles)) {
            foreach ($vehicles as $vehicle)
                $fcontent[] = implode(",", $vehicle);
            fwrite($fhandle, implode("\r\n", $fcontent) . "\r\n");
            fclose($fhandle);
            echo '<a href="/downloadcsv.php?fname=daily_report_01">CSV Datei herunterladen</a><br>';
        }

    }

    function sendSecondEmail() {
        $divisions = array(
            'Gießen',
            'Braunschweig',
            'Köln-West',
            'Kassel',
            'Duisburg',
            'Hannover',
            'Bonn',
            'Göppingen',
            'Münster',
            'Düsseldorf',
            'Würzburg',
            'Herford',
            'Reutlingen'
        );
        $result = $this->ladeLeitWartePtr->divisionsPtr->newQuery()->where('name', 'IN', $divisions)->get('division_id');
        foreach ($result as $division)
            $this->fpsMail($division['division_id']);
    }

    function fpsMail($division_id) {

        //gets station count grouped by division
        $division = $this->ladeLeitWartePtr->stationsPtr->getFreeStationsToBeAssignedCountForDiv($division_id);
        $this->content = '';
        $delivery_quantity_total = array();

        $deliveryStr = '';
        $weeks = $this->ladeLeitWartePtr->getWeeksFromYearMonth(date('Y-m-01'), true, 'KW', 'kw');
        //$i<=($startweek+4) changed to $i<=$endweek
        foreach ($weeks as $kweek => $kweek_label) {
            $delivery_quantity_total = $this->ladeLeitWartePtr->deliveryToDivisionsPtr->getSumQtyAllVariantsForWeekAndDiv($kweek, $division_id);
            //@todo cron job here to check and send email
            if (!empty($delivery_quantity_total) && $division['scnt'] >= $delivery_quantity_total && $delivery_quantity_total != 0) {
                $deliveryStr .= '<strong>' . $kweek_label . '</strong><br>';
                $delivery_quantity_by_variant = $this->ladeLeitWartePtr->deliveryToDivisionsPtr->getQtyAllVariantsForWeekAndDiv($kweek, $division_id);
                foreach ($delivery_quantity_by_variant as $single) {
                    $variant_name = $this->vehicle_variants[$single['variant_value']];
                    $deliveryStr .= ' Fahrzeug Variante ' . $variant_name . ' : ' . $single['delivery_quantity'] . '<br>';

                }


            }
        }

        if ($deliveryStr != '') {
            $this->content .= '<h2>' . $division['name'] . '</h2>';
            $this->content .= 'Freie Ladepunkten: ' . $division['scnt'] . '<br>';

            $mailmsg = str_replace(array('{deliveryStr}'), array($deliveryStr), $this->emailtext);
            $this->content .= $mailmsg;

            $fps_list = $this->ladeLeitWartePtr->allUsersPtr->getFPSEmails($division_id);
            $fps_emails = array();
            foreach ($fps_list as $fps) {
                if (!isset($fps['fname'])) $fps['fname'] = '';
                if (!isset($fps['lname'])) $fps['lname'] = '';

                $fps_emails[$fps['email']] = $fps['fname'] . '  ' . $fps['lname'];

            }

            $extraemails = null;

            /**
             * Get the current domain to decide which database to use.
             */
            $domain = null;
            if ($domain === null && isset($_SERVER['HTTP_HOST'])) {
                $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
            }

            if ($domain == "62.75.137.43") {

                $mailer = new MailerSmimeSwift ('Jens.Frangenheim@streetscooter.eu', '', 'StreetScooter Fahrzeuge Auslieferung - Niederlassung ' . $division['name'], $mailmsg, null, true, array('Pradeep.Mohan@streetscooter.eu'));
            } else if ($domain == "streetscooter-cloud-system.eu") {
                $extraemails = array('Philipp.Schnelle@streetscooter.eu', 'Wilfried.Baltruweit@streetscooter.eu', 'Pradeep.Mohan@streetscooter.eu', 'Jens.Frangenheim@streetscooter.eu');
                $mailer = new MailerSmimeSwift ($fps_emails, '', 'StreetScooter Fahrzeuge Auslieferung - Niederlassung ' . $division['name'], $mailmsg, null, true, $extraemails);

            } else {
                $mailer = new MailerSmimeSwift ('Pradeep.Mohan@streetscooter.eu', '', 'Test Email LOKAL - StreetScooter Fahrzeuge Auslieferung - Niederlassung ' . $division['name'], $mailmsg, null, true, array('Pradeep.Mohan@streetscooter.eu'));
            }

            $this->content .= '<br><a href="' . $_SERVER['PHP_SELF'] . '?action=auszulieferende">Auszulieferende Fahrzeuge an Ladepunkte zuordnen</a><br>
								Mail an : ' . implode(',', $fps_emails) . '<br>';

        }

    }

// 	function testEvenOdd()
// 	{
// 		$resVehicles=$this->ladeLeitWartePtr->restrictionsPtr->getEvenOdd($_GET['restriction_id'],$this->sopVariants);
// 		$this->debugcontent=$resVehicles;
// 	}

// 	function processNewVehicles()
// 	{
// 		$filename="/var/www/newvehicles.csv";
// 		if (($handle = fopen($filename, "r")) !== FALSE) {
// 			while (($data = fgetcsv($handle, null, ",")) !== FALSE) {
// 				$vehicle=$vehicles_sales=array();
// 				if($data[0]=='name')
// 					continue;
// 				$vehicle['name']=$data[0];
// 				$vehicle_sales['tsnumber']=$data[1];

// 				if(is_numeric($data[2]))
// 					$vehicle_sales['vehicle_variant']=$data[2];

// 				$vehicle_sales['aib']=$data[3];
// 				$vehicle_sales['production_week']=$data[4];

// 				if(!empty($data[5]))
// 				{
// 					$ts_deliverydate=strtotime($data[5]);
// 					$delivery_date=date('Y-m-d 00:00:00O',$ts_deliverydate);
// 					$vehicle_sales['delivery_date']=$delivery_date;
// 				}

// 				$vehicle_sales['delivery_week']=$data[6];
// 				$vehicle_sales['coc']=$data[7];
// 				$vehicle['vin']=$data[8];
// 				if(!empty($data[9]))
// 				{
// 					$vehicle['code']=$data[9];
// 				}
// 				else 
// 					$vehicle['code']='kein AKZ '.$vehicle['name'];

// 				$vehicle['ikz']=$data[10];
// 				$vehicle_sales['vorhaben']=$data[11];

// 				if(is_numeric($data[13]))
// 					$vehicle['depot_id']=$data[13];
// 				else
// 				{
// 					$depot_id=$this->ladeLeitWartePtr->depotsPtr->newQuery()->where('name','LIKE',$data[13])->getVal('depot_id');
// 					if(is_numeric($depot_id))
// 						$vehicle['depot_id']=$depot_id;
// 					else if(strpos($data[13],'Dritt')!==FALSE)
// 					{
// 						$params=explode('-',$data[13]);
// 						$depot_id=$this->ladeLeitWartePtr->depotsPtr->newQuery()->where('name','LIKE',trim($params[1]).' %')->getVal('depot_id');
// 						if(is_numeric($depot_id))
// 						{
// 							$vehicle['depot_id']=$depot_id;

// 						}	
// 						else
// 						{
// 							$division_id=$this->ladeLeitWartePtr->divisionsPtr->newQuery()->where('name','LIKE','%Drittkunden%')->getVal('division_id');
// 							$zspl_id=$this->ladeLeitWartePtr->zsplPtr->newQuery()->insert(array('name'=>trim($params[1]),'division_id'=>$division_id));
// 							$depot_id=$this->ladeLeitWartePtr->depotsPtr->newQuery()->insert(array('name'=>trim($params[1]).' Dummy ZSP','division_id'=>$division_id,'zspl_id'=>$zspl_id));
// 							$vehicle['depot_id']=$depot_id;
// 						}

// 					}
// 					else 
// 							$vehicle['depot_id']='ERROR';


// 				}

// 				$comments=explode(',',$data[14]);
// 				$procomments=array();
// 				foreach($comments as $comment)
// 				{
// 					$procomments[]=array('addedon'=>time(),'content'=>$comment);
// 				}
// 				$vehicle_sales['comments']=serialize($procomments);
// 				if(strpos($data[15],'15,7')!==FALSE)
// 				{
// 					$vehicle['usable_battery_capacity']=15700;
// 				}
// 				else if(strpos($data[15],'20')!==FALSE)
// 				{
// 					$vehicle['usable_battery_capacity']=20480;
// 				}
// 				else 
// 					$vehicle['usable_battery_capacity']=0;
// 				if(empty($this->ladeLeitWartePtr->vehiclesPtr->newQuery()->where('code','LIKE',$vehicle['code'])->getVal('vehicle_id')))
// 				{
// 					$vehicle_id=$this->ladeLeitWartePtr->vehiclesPtr->newQuery()->insert($vehicle);
// 					$vehicle_sales['vehicle_id']=$vehicle_id;
// 					$this->ladeLeitWartePtr->vehiclesSalesPtr->newQuery()->insert($vehicle_sales);
// 				}

// 			}
// 		}
// 	}

    function showorder() {
        // 		$free_stations=$this->ladeLeitWartePtr->stationsPtr->getFreeStationsAlreadyVariantAssigned();

        // 		$tableit=new DisplayTable($free_stations);
        // 		echo $tableit->getContent();

        $this->manual_delivery_content = '';
        $pendingDivisions = $this->ladeLeitWartePtr->deliveryToDivisionsPtr->getPendingForYearVariant();
        $assigned_stations = array();

        $moved_quantities = array();
        foreach ($pendingDivisions as $division) {
            $division_name = $this->ladeLeitWartePtr->divisionsPtr->newQuery()->where('division_id', '=', $division['division_id'])->getVal('name');
            $pendingquantity = $division['delivery_quantity'] - $division['vehicles_delivered_quantity'];

            $stations = $this->ladeLeitWartePtr->stationsPtr->newGetFreeStationsVariantDiv($division['division_id'], $division['variant_value'], $assigned_stations);
            // 		$depots=$this->ladeLeitWartePtr->stationsPtr->getFreeStationsAlreadyVariantAssignedForDiv($division['division_id'],$division['variant_value']);

            if (!empty($stations)) {
                if (isset($moved_quantities[$division['division_id']]))
                    $moved = $moved_quantities[$division['division_id']];
                else $moved = 0;

                echo '<h2>' . $division_name . ' (' . $division['delivery_week'] . ')  Auszulieferende Fahrzeuge : ' . $pendingquantity . ' + ' . $moved . '</h2><br>';

                $totalquantity = $pendingquantity + $moved;


                $quotient = floor($totalquantity / 4);


                $moved_quantities[$division['division_id']] = $totalquantity % 4;

                $actual = $quotient * 4;

                $cnt = 0;
                $lastdepot = '';


                foreach ($stations as $station) {
                    if ($actual > $cnt) {
                        if ($lastdepot != $station['depot_id'] && $lastdepot != '') {
                            $prevdepot = $this->ladeLeitWartePtr->depotsPtr->getFromId($lastdepot);
                            $thisdepot = $this->ladeLeitWartePtr->depotsPtr->getFromId($station['depot_id']);

                            $distance = $this->getDistance($prevdepot['lat'], $prevdepot['lon'], $thisdepot['lat'], $thisdepot['lon']);

                            echo '<strong><br>Distance ' . $prevdepot['name'] . ' and ' . $thisdepot['name'] . ' : ' . ceil($distance) . ' km</strong><br><br>';

                        }
                        $assigned_stations[] = $station['station_id'];
                        $listno = $cnt + 1;
                        echo $listno . '. ' . $station['dname'] . ' (' . $station['dp_depot_id'] . ') <br>';
                        $lastdepot = $station['depot_id'];
                        $cnt++;
                        // 								if($cnt%4==0)
                        // 									echo '<hr>';

                    }
                }
                // 						echo '<hr>';
            }


        }

    }

    function getDistance($lat1, $lon1, $lat2, $lon2) {
        $x = (float)111.3 * cos(($lat1 + $lat2) / 2 * 0.01745) * ($lon1 - $lon2);
        $y = (float)111.3 * ($lat1 - $lat2);
        return sqrt($x * $x + $y * $y);
    }


// function adjustdelivery()
// {
// 	$affected_divs=array();
// 	$vehicles=$this->ladeLeitWartePtr->vehiclesSalesPtr->newQuery()->where('delivery_week','<','kw45')->where('delivery_date','>','2016-11-01')->get('*');
// 	$weeks=$this->ladeLeitWartePtr->getWeeksFromYearMonth('2016-11-01',true);
// 	foreach($vehicles as $vehicle)
// 	{
// 		$vehicle_db=$this->ladeLeitWartePtr->vehiclesPtr->newQuery()->where('vehicle_id','=',$vehicle['vehicle_id'])
// 		->join('depots','depots.depot_id=vehicles.depot_id','INNER JOIN')
// 		->join('divisions','divisions.division_id=depots.division_id','INNER JOIN')->getOne('divisions.division_id,vehicles.vin');
// 		$division_id=$vehicle_db['division_id'];
// 		$delivery_to_division=$this->ladeLeitWartePtr->deliveryToDivisionsPtr->newQuery()->where('delivery_week','=',$vehicle['delivery_week'])
// 		->where('division_id','=',$vehicle_db['division_id'])
// 		->getOne('delivery_id,delivery_week,vehicles_delivered,vehicles_delivered_quantity');
// 		$vehicles_delivered=unserialize($delivery_to_division['vehicles_delivered']);
// 		if(in_array($vehicle['vehicle_id'],$vehicles_delivered))
// 		{
// 			//find delivery_to_diviison for this month
// 			$new_dv=$this->ladeLeitWartePtr->deliveryToDivisionsPtr->newQuery()->where('delivery_week','IN',$weeks)
// 			->where('division_id','=',$division_id)
// 			->join('divisions','divisions.division_id=delivery_to_divisions.division_id','INNER JOIN')
// 			->where('delivery_year','=',2016)
// 			->orderBy('delivery_id')
// 			->where('delivery_quantity-vehicles_delivered_quantity','>',0)
// 			->getOne('delivery_id,delivery_week,delivery_quantity,vehicles_delivered_quantity,vehicles_delivered,divisions.name');
// 			if(empty($new_dv))
// 			{
// 				 //	do nothing
// 			}
// 			else
// 			{
// 				if(isset($affected_divs[$division_id]))
// 				{
// 					$affected_divs[$division_id]++;
// 				}
// 				else
// 				$affected_divs[$division_id]=1;

// 				$key=array_search($vehicle['vehicle_id'],$vehicles_delivered);
// 				$vehicles_del_qty=$delivery_to_division['vehicles_delivered_quantity'];

// 				echo serialize($vehicles_delivered).':'.$vehicles_del_qty.'<br>';

// 				$vehicles_del_qty--;
// 				unset($vehicles_delivered[$key]);
// 				$this->ladeLeitWartePtr->deliveryToDivisionsPtr->newQuery()->where('delivery_id','=',$delivery_to_division['delivery_id'])->update(array('vehicles_delivered','vehicles_delivered_quantity'),array(serialize($vehicles_delivered),$vehicles_del_qty));
// 				echo serialize($vehicles_delivered).':'.$vehicles_del_qty.'<br>';

// 				if(!empty($new_dv['vehicles_delivered']))
// 					$new_vehicles_delivered=unserialize($new_dv['vehicles_delivered']);
// 				else $new_vehicles_delivered=array();
// 				if(!empty($new_dv['vehicles_delivered_quantity']))
// 					$vehicles_del_qty=$new_dv['vehicles_delivered_quantity'];
// 				else
// 					$vehicles_del_qty=0;
// 				echo serialize($new_vehicles_delivered).':'.$vehicles_del_qty.'<br>';

// 				$new_vehicles_delivered[]=$vehicle['vehicle_id'];

// 				$vehicles_del_qty++;
// 				$new_vehicles_delivered_str=serialize($new_vehicles_delivered);
// 				$this->ladeLeitWartePtr->deliveryToDivisionsPtr->newQuery()->where('delivery_id','=',$new_dv['delivery_id'])->update(array('vehicles_delivered','vehicles_delivered_quantity'),array($new_vehicles_delivered_str,$vehicles_del_qty));
// 				echo serialize($new_vehicles_delivered).':'.$vehicles_del_qty.'<br>';
// 				echo $vehicle['vehicle_id'].' need to work'.$division_id.' '.$vehicle_db['vin'].'<br>';
// 				echo $vehicle['delivery_week'].' '.$delivery_to_division['delivery_week'].' '.$new_dv['delivery_week'].'<br>';
// // 				echo $delivery_to_division['delivery_id'].':';
// // 				echo $new_dv['delivery_id'].' '.$new_dv['delivery_quantity'].'-'.$new_dv['vehicles_delivered_quantity'].'<br>';
// 				die;

// 			}
// 		}
// 		else
// 		{
// // 		do nothing	echo 'not here';
// 		}


// 	}
// 	print_r($affected_divs);
// 	foreach($affected_divs as $division_id=>$qty)
// 	{	$new_dv=$this->ladeLeitWartePtr->deliveryToDivisionsPtr->newQuery()->where('delivery_week','IN',$weeks)
// 			->where('division_id','=',$division_id)
// 			->join('divisions','divisions.division_id=delivery_to_divisions.division_id','INNER JOIN')
// 			->where('delivery_year','=',2016)
// 			->where('delivery_quantity-vehicles_delivered_quantity','>',0)
// 			->orderBy('delivery_id')
// 			->getOne('delivery_id,delivery_to_divisions.division_id,delivery_quantity,vehicles_delivered_quantity,divisions.name');
// 		print_r($new_dv);
// 	}
// }


    function assignStations() {
        $debug = 1;
        $showdiv = 0;
        $showveh = 0;
        $showstation = 1;
        //@todo 2016-09-01 still need to order by kweek priority and the assign the vehicle.. after assigning update production_plan table
        //update the the delivery_to_divisions table with qty produced?
        //done?
        $free_stations = $this->ladeLeitWartePtr->stationsPtr->getFreeStationsAlreadyVariantAssigned();
        if ($showstation == 1)
            print_r($free_stations);

        $depots = array();
        //get vehicle available count (by variant) and compare
        foreach ($free_stations as $station) {
            if (in_array($station['vehicle_variant_value_allowed'], $this->sopVariants)) {
                //@todo oddeven passing only the first restriction .. should check for three phases
                $resVehicles = $this->ladeLeitWartePtr->restrictionsPtr->getEvenOdd($station['restriction_id'], $this->sopVariants);
            }

            //get one pending entry for the division in which this station is located
            $divisionsDeliveryPlan = $this->ladeLeitWartePtr->deliveryToDivisionsPtr->getOnePendingForDivisionYearVariant($station['division_id'], date('Y'), $station['vehicle_variant_value_allowed']);


            if (!empty($divisionsDeliveryPlan)) //if pending is true, then assign the vehicle, if false, then move to the next station
            {
                if ($showdiv == 1) {
                    echo '<br><br>';
                    print_r($divisionsDeliveryPlan);
                }

                if (in_array($station['vehicle_variant_value_allowed'], $this->sopVariants)) {
                    if ($resVehicles == 'getodd')
                        $vehicle = $this->ladeLeitWartePtr->vehiclesPtr->getFinishedEOLVehicleVariant($station['vehicle_variant_value_allowed'], 'odd');
                    else if ($resVehicles == 'geteven')
                        $vehicle = $this->ladeLeitWartePtr->vehiclesPtr->getFinishedEOLVehicleVariant($station['vehicle_variant_value_allowed'], 'even');
                    else {
                        $vehicle = $this->ladeLeitWartePtr->vehiclesPtr->getFinishedEOLVehicleVariant($station['vehicle_variant_value_allowed']);
                    }
                } else {
                    $vehicle = $this->ladeLeitWartePtr->vehiclesPtr->getFinishedEOLVehicleVariant($station['vehicle_variant_value_allowed']);
                }

                if (!empty($vehicle)) {
                    if ($showveh == 1)
                        print_r($vehicle);
                    //add only those depots where vehicle assignment has been done
                    if (!in_array($station['depot_id'], $depots))
                        $depots[] = $station['depot_id'];

                    //we have the delivery week, but we do not now which month this week falls in.
                    //The mobilitätsplanung (delivery_plan) has entries only according to 2016-08-01.. year month.. so we need to find
                    //the month to update the requirement_met in the delivery_plan table

                    $week = (int)str_replace('kw', '', $divisionsDeliveryPlan['delivery_week']);
                    $getmonth = '';
                    $month = date('m') - 2; // go back two months from this month.. so that we get the first month to which a calendar week belongs

                    $cnt = 1;
                    while (empty($getmonth)) {
                        $startweek = (int)date('W', strtotime('first day of ' . date('F Y', strtotime(date('Y-' . $month . '-01')))));
                        $endweek = (int)date('W', strtotime('last day of ' . date('F Y', strtotime(date('Y-' . $month . '-01')))));

                        if ($week >= $startweek && $week <= $endweek) {

                            $getmonth = $month;
                            $month = str_pad($month, 2, '0', STR_PAD_LEFT);
                            $external_post_variant_value = $this->ladeLeitWartePtr->deliveryPlanVariantValuesPtr->getExternalValue($station['vehicle_variant_value_allowed']);
                            $deliveryPlanZentrale = $this->ladeLeitWartePtr->deliveryPlanPtr->getOnePendingDeliveryPlansVariant($station['division_id'], $month, $external_post_variant_value);
                            //in case of a calendar week which overlaps two months, then we check to see if there's still any pending requirements for
                            //the first month.. if not, then we need to move to the next month, so set $getmonth to empty again

                            if (empty($deliveryPlanZentrale)) {
                                $getmonth = '';
                                $month++;
                            } else {
                                $deliveryPlanZentrale['requirement_met']++;
                                $this->ladeLeitWartePtr->deliveryPlanPtr->newQuery()->where('delivery_id', '=', $deliveryPlanZentrale['delivery_id'])->update(array('requirement_met'), array($deliveryPlanZentrale['requirement_met']));
                            }

                        } else {
                            if ($week < $startweek) $month--;
                            else $month++;

                        }
                        $cnt++;
                        if ($month > 12 || $month < 1 || $cnt > 5) {
                            echo 'cannot retreive month or check delivery_plan table';
                            die;
                        }

                    }
                    $vehicle_id = $vehicle['vehicle_id'];
                    $this->ladeLeitWartePtr->vehiclesPtr->assignVehicleToStation($vehicle_id, $station['station_id'], $station['depot_id'], $divisionsDeliveryPlan['delivery_week'], $station['cost_center']);

                    //reset the finished status back to FALSE after it has been assigned for delivery
                    $this->ladeLeitWartePtr->vehiclesPtr->newQuery()->where('vehicle_id', '=', $vehicle_id)->update(array('finished_status'), array('FALSE'));
                    $divisionsDeliveryPlan['vehicles_delivered'] = unserialize($divisionsDeliveryPlan['vehicles_delivered']);
                    $divisionsDeliveryPlan['vehicles_delivered'][] = $vehicle_id;
                    $divisionsDeliveryPlan['vehicles_delivered'] = serialize($divisionsDeliveryPlan['vehicles_delivered']);
                    $divisionsDeliveryPlan['vehicles_delivered_quantity']++;
                    $this->ladeLeitWartePtr->deliveryToDivisionsPtr->newQuery()->where('delivery_id', '=', $divisionsDeliveryPlan['delivery_id'])->update(array_keys($divisionsDeliveryPlan), array_values($divisionsDeliveryPlan));


                    $this->content .= $station['sname'] . '/' . $station['dname'] . '(' . $station['restriction_id'] . ') -> ' . $vehicle['vin'] . '/' . $vehicle['code'] . ' ' . $divisionsDeliveryPlan["delivery_id"] . '<br>';
                }
            }
        }

        if ($debug == 1) {
            $this->debugcontent = '';


            foreach ($depots as $depot_id) {
                $depot = $this->ladeLeitWartePtr->depotsPtr->getFromId($depot_id);
                $result = $this->ladeLeitWartePtr->restrictionsPtr->newQuery()->where('parent_restriction_id', '=', $depot['depot_restriction_id'])->get('restriction_id,name');
                $subres = array_column($result, 'restriction_id');
                $this->debugcontent .= $depot['name'];
                $this->debugcontent .= '<ul>';
                $restriction_names = array_combine(array_column($result, 'restriction_id'), array_column($result, 'name'));


                foreach ($subres as $key => $singlesubres) {
                    //Phase 1 Phase 2 and Phase 3
                    $this->debugcontent .= '<li>' . $singlesubres . ' ' . $restriction_names[$singlesubres];

                    $stations = $this->ladeLeitWartePtr->stationsPtr->newQuery()->where('stations.restriction_id', '=', $singlesubres)
                        ->join('vehicles', 'vehicles.station_id=stations.station_id', 'FULL OUTER JOIN')
                        ->orderBy('stations.name')->get('stations.station_id,stations.name,vehicles.vin');
                    if (!empty($stations)) {
                        $this->debugcontent .= '<ul>';
                        foreach ($stations as $station) {
                            $this->debugcontent .= '<li>' . $station['name'] . ' -> ' . $station['vin'] . '</li>';
                        }
                        $this->debugcontent .= '</ul>';

                    }


                    //next up further unterverteilung
                    $result = $this->ladeLeitWartePtr->restrictionsPtr->newQuery()->where('parent_restriction_id', '=', $singlesubres)->get('restriction_id');
                    if (!empty($result)) {
                        $newsubres = array_column($result, 'restriction_id');
                        $subres = array_merge($subres, $newsubres);
                        $subres = array_unique($subres, SORT_NUMERIC);

                        $this->debugcontent .= '<ul>';
                        foreach ($newsubres as $restriction) {
                            $this->debugcontent .= '<li>' . $restriction . ' ' . $restriction_names[$restriction] . '</li>';
                        }
                        $this->debugcontent .= '</ul>';

                    }


                    $this->debugcontent .= '</li>';
                    unset($subres[$key]);

                }
                $this->debugcontent .= '</ul>';
            }

        }


    }
}