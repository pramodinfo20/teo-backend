<?php require_once "{$_SERVER['STS_ROOT']}/includes/sts-datetime.php"; ?>
<?php

$sts_userid = $_SESSION['sts_userid'];

// insert Booking Vehicle into database
if (isset($_POST['sendbooking'])) {
    $startDate = strtotime($_POST['start_timestamp_selector']);
    $endDate = strtotime($_POST['end_timestamp_selector']);
    $dateNow = strtotime(date("Y-m-d H:i"));

    // Check entry time
    if ($startDate === false || $startDate < 0 || $endDate === false || $endDate < 0 || $startDate >= $endDate || $endDate <= $dateNow) {
        echo '<div class="alert alert-danger danger">falscher Datum eingabe! Bitte korrigieren Sie Ihre Buchungszeit</div>
        		  <div class="btn-return"><a class="btn btn btn-warning" href="?action=vehiclebooking&initPage" role="button">Zurück zum buchen</a></div>';
        return false;
    }

    // Check booked Vehicles
    $booking_id = $_GET['select_vehicle'];
    $new_booking_date = to_iso8601_datetime($_POST['booking_date']);
    $new_start_timestamp = to_iso8601_datetime($_POST['start_timestamp_selector']);
    $new_end_timestamp = to_iso8601_datetime($_POST['end_timestamp_selector']);


    $selectBookedVehicle = " SELECT *
	 				FROM booking_sys
	 				WHERE booking_sys.booking_vehicleid = $booking_id AND
				   ( (booking_sys.booking_start < '$new_start_timestamp' AND booking_sys.booking_end > '$new_start_timestamp') OR
				   (booking_sys.booking_start < '$new_end_timestamp' AND booking_sys.booking_end > '$new_end_timestamp') OR
				   (booking_sys.booking_start > '$new_start_timestamp' AND booking_sys.booking_end < '$new_end_timestamp') )";

    $BookedVehicleResults = $this->ladeLeitWartePtr->vehiclesPtr->getSpecialSql($selectBookedVehicle);
    if (!empty($BookedVehicleResults)) {
        echo '<div class="alert alert-danger danger">Buchung in diesem Zeitraum nicht möglich!</div>';
    } // Insert Booking Vehicle into Database
    else {
        $sqlinsert = "INSERT INTO booking_sys (booking_date, booking_start, booking_end, booking_status, booking_id, userid, booking_vehicleid)" .
            "values ('now()', '$new_start_timestamp', '$new_end_timestamp', '$_POST[vehicle_online_status]', '$_POST[booking_id]', '$_POST[userid]', '$_POST[vehicleid]')";

        $insertData = $this->ladeLeitWartePtr->newQuery()->query($sqlinsert);
        $submit = '&submit=ok';

        // The output buffer is not empty. It contains only a UTF-8 BOM and/or whitespace, let's clean it
        ob_clean();

        header("Location: {$_SERVER['PHP_SELF']}" . $_SERVER['REDIRECT_URI'] . '?' . http_build_query($_GET) . $submit);
        exit;
    }
}

?>
<h1>Test Fahrzeuge Buchung</h1>

<div class="row ">
    <div class="columns twelve">
        <?php if ($_GET['submit'] == 'ok') { ?>
            <div id="divsuccess" class="alert alert-success">Fahrzeug ist erfolgreich gebucht.</div>
        <?php } else echo $message; ?>
    </div>
</div>

<div class="row">
    <div class="columns eight">
        <div>
            <?php
            if (isset($_GET['select_vehicle'])) {
                $booking_id = $_GET['select_vehicle'];

                $calquery = " SELECT
			 				booking_sys.id, booking_sys.booking_date, booking_sys.booking_start, booking_sys.booking_end, booking_sys.booking_status, booking_sys.booking_id, booking_sys.booking_vehicleid,
			 				vehicles.vehicle_id, vehicles.vin, vehicles.code, vehicles.ikz
			 				FROM booking_sys
			 				LEFT JOIN booking_vehicles ON booking_vehicles.booking_id = booking_sys.booking_id
			 				LEFT JOIN vehicles ON vehicles.vehicle_id = booking_sys.booking_vehicleid
			 				WHERE booking_sys.booking_vehicleid = $booking_id ORDER BY booking_sys.id DESC LIMIT 50 ";

                $calendarResults = $this->ladeLeitWartePtr->vehiclesPtr->getSpecialSql($calquery);
                if (!empty($calendarResults)) {
                    $bookedResult = '<div class="clear-bottom"><h2>Buchungsverlauf</h2><table><tbody><tr>';
                    $bookedResult .= '<th>VIN</th>';
                    $bookedResult .= '<th>AKZ</th>';
                    $bookedResult .= '<th>Buchungsdatum</th>';
                    $bookedResult .= '<th>Start Zeitpunkt</th>';
                    $bookedResult .= '<th>End Zeitpunkt</th>';

                    $_GET['count'] = count($calendarResults);
                    $i = 1;
                    foreach ($calendarResults as $calendarResult) {
                        $_GET['status' . $i] = $calendarResult['booking_status'];
                        $_GET['start' . $i] = strtotime(date('Y-m-d', strtotime($calendarResult['booking_start'])));
                        $_GET['end' . $i] = strtotime($calendarResult['booking_end']);

                        $bookedResult .= "<tr>";
                        $bookedResult .= '<td>' . $calendarResult['vin'] . '</td>';
                        $bookedResult .= '<td>' . $calendarResult['code'] . '</td>';
                        $bookedResult .= '<td>' . to_locale_datetime($calendarResult['booking_date']) . '</td>';
                        $bookedResult .= '<td>' . to_locale_datetime($calendarResult['booking_start']) . '</td>';
                        $bookedResult .= '<td>' . to_locale_datetime($calendarResult['booking_end']) . '</td>';
                        $bookedResult .= "</tr>";
                        $i++;
                    }
                    $bookedResult .= "</body></table></div>";
                }
            }
            ?>
        </div>

        <?php
        $CostCentreQuery = " SELECT *
		 				FROM booking_users
		 				WHERE booking_users.user_id = $sts_userid LIMIT 100 ";

        $CostCentreResult = $this->ladeLeitWartePtr->vehiclesPtr->getSpecialSql($CostCentreQuery);

        if (!empty($CostCentreResult)) {
            foreach ($CostCentreResult as $Cost) {
                $Cost_user = $Cost['cost_centre'];
            }
        }

        $orderby = ($Cost_user) ? " ORDER BY booking_vehicles.cost_centre = $Cost_user DESC" : "";
        $query = " SELECT
		 				booking_vehicles.vehicleid, booking_vehicles.cost_centre,
		 				vehicles.vehicle_id, vehicles.vin, vehicles.code, vehicles.ikz 
		 				FROM booking_vehicles
		 				LEFT JOIN vehicles ON vehicles.vehicle_id = booking_vehicles.vehicleid 
		 				$orderBy
		 				LIMIT 200 ";

        $result = $this->ladeLeitWartePtr->vehiclesPtr->getSpecialSql($query);

        if (!empty($result)) {
            ?>
            <div class="clear-bottom">
                <form action='?action=vehiclebooking&initPage' id="vehicle_search_form" method='get'>
                    <p>Bitte wählen Sie ein Fahrzeug nach VIN/Kennzeichen. <br>
                        Sie können auch direkt Teile der VIN/Kennzeichen Nummer in das Feld zum Suchen eingeben.</p>
                    <p><b>Bitte beachten Sie:</b><br>
                        Es werden nur Testfahrzeuge angezeigt, die einer <b>Kostenstelle</b> zugewiesen sind.<br></p>

                    <input type="hidden" name="action" value="<?php echo $this->action ?>">
                    <select class="fleet_search" name="select_vehicle">
                        <option value="null"></option><?php
                        foreach ($result as $vehicle) {

                            $booking_vehicle = $vehicle["vin"];

                            if (!empty($vehicle["code"]))
                                $booking_vehicle .= " ({$vehicle["code"]})";

                            if (!empty($vehicle["cost_centre"]))
                                $booking_vehicle .= " (Kst: {$vehicle["cost_centre"]})";

                            /*if (!empty($vehicle["ikz"]))
                      $booking_vehicle .= " IKZ {$vehicle["ikz"]}";*/

                            if ($vehicle['cost_centre'] == $Cost_user) {
                                $isbookable = " (buchbar)";
                            }
                            if ($vehicle['cost_centre'] != $Cost_user) {
                                $isbookable = ' (nicht buchbar)';
                            }

                            echo '<option value="' . $vehicle['vehicle_id'] . '">' . $booking_vehicle . ' ' . $isbookable . '</option>';
                        }
                        ?>
                    </select>
                    <input type="hidden" name="actionbooking" value="booking">
                </form>
            </div>
            <h4><span class="costcentre"><?php echo 'Meine Kostenstelle: ' . $Cost_user; ?><span></h4>

            <?php
            if (isset($_GET['select_vehicle'])) {

                $getVehicle = $_GET['select_vehicle'];
                $queryBooking = " SELECT
				 				booking_vehicles.booking_id, booking_vehicles.vehicleid, booking_vehicles.cost_centre,
				 				vehicles.vehicle_id, vehicles.vin, vehicles.code, vehicles.ikz 
				 				FROM booking_vehicles
				 				LEFT JOIN vehicles ON vehicles.vehicle_id = booking_vehicles.vehicleid 
				 				WHERE booking_vehicles.vehicleid = $getVehicle ";

                $resultBooking = $this->ladeLeitWartePtr->vehiclesPtr->getSpecialSql($queryBooking);

                if (!empty($resultBooking)) {
                    echo "<div><table><tbody><tr>";
                    echo "<th>Kostenstelle</th>
						 	  <th>VIN</th>
						 	  <th>AKZ</th>
						 	  <th>IKZ</th>
						 	  <th>Fahrzeug Variante</th>";
                    echo '</tr>';

                    foreach ($resultBooking as $booking) {

                        $vehicle_variant = "unknown";
                        if (stripos($booking['vin'], 'B14')) $vehicle_variant = 'WORK B14 1-Sitzer Verbund';
                        if (stripos($booking['vin'], 'B16')) $vehicle_variant = 'WORK B16 1-Sitzer Verbund';
                        if (stripos($booking['vin'], 'D16')) $vehicle_variant = 'WORK L D16 1-Sitzer Verbund';
                        if (stripos($booking['vin'], 'D17')) $vehicle_variant = 'WORK L D17 1-Sitzer Verbund';

                        echo "<tr>";
                        echo '<td>' . $booking['cost_centre'] . '</td>';
                        echo '<td>' . $booking['vin'] . '</td>';
                        echo '<td>' . $booking['code'] . '</td>';
                        echo '<td>' . $booking['ikz'] . '</td>';
                        echo '<td>' . $vehicle_variant . '</td>';
                        echo "</tr>";

                        $form = '<form action="" method="post">
							 				<input type="hidden" name="action" value="' . $this->action . '">
											<input type="hidden" name="booking_date" value="' . date("Y-m-d H:i:s") . '">
											<input type="hidden" name="booking_id" value="' . $booking['booking_id'] . '">
											<input type="hidden" name="vehicleid" value="' . $booking['vehicleid'] . '">
											<input type="hidden" name="vin" value="' . $booking['vin'] . '">
											<input type="hidden" name="userid" value="' . $sts_userid . '">
											<input type="text" style="padding: 0 4px" name="vin" value="' . $booking['vin'] . '" readonly disabled>
											<label style="display: inline-block; margin-left:10px; font-weight: 700">Start Zeitpunkt</label>
											<input type="text" id="start_timestamp_selector" class="timestamp_selector" name="start_timestamp_selector" style="display: inline-block; margin-left:20px;padding: 0 4px">
											<label style="display: inline-block; margin-left:10px; font-weight: 700">End Zeitpunkt</label>
											<input type="text" id="end_timestamp_selector" class="timestamp_selector" name="end_timestamp_selector" style="display: inline-block; margin-left:20px; padding: 0 4px">
											<input type="hidden" id="vehicle_online_status" name="vehicle_online_status" value="t">
											<input type="hidden" id="booking_sendform" name="booking_sendform" value="block">
											<input type="submit" name="sendbooking" value="Buchen" style="display: inline-block; margin-left:20px;padding: 1px 14px;">
										</form>';

                    }
                    echo "</tbody></table></div>";

                    // booking only for login user
                    /*if( $booking['user_id'] == $sts_userid )*/
                    if ($booking['cost_centre'] == $Cost_user) {
                        echo '<div class="clear-bottom">
							 			<h2>Jetzt buchen</h2>'
                            . $form .
                            '</div>';
                    }

                }

            }

        } else
            echo 'Kein Fahrzeug gefunden!';
        ?>

        <div class="clear-top clear-bottom">
            <?php include $_SERVER['STS_ROOT'] . "/classes/calendar.class.php"; ?>

            <?php
            // Calendar
            $calendar = new Calendar();
            echo $calendar->show();

            // booking History
            if (!empty($bookedResult))
                echo $bookedResult;
            ?>
        </div>
    </div>

    <div class="columns four">

    </div>
</div>

<script type="text/javascript">
    // hide div after the form is submitted
    $(document).ready(function () {
        $('#divsuccess').delay(1000).fadeIn(250).delay(3000).fadeOut(850);
    });

    // block the pop up asking for form resubmission on refresh once the form is submitted.
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script> 
