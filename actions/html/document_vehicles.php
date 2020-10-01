<?php

$sts_userid = $_SESSION['sts_userid'];

// Ergebnis aus booking_users Tabelle
$CostCentreQuery = " SELECT *
			FROM booking_users
			WHERE booking_users.user_id = $sts_userid LIMIT 100 ";

$CostCentreResult = $this->ladeLeitWartePtr->vehiclesPtr->getSpecialSql($CostCentreQuery);

if (!empty($CostCentreResult)) {
    foreach ($CostCentreResult as $Cost) {
        $Cost_user = $Cost['cost_centre'];
    }
}

// Alle Testfahrzeuge, die in booking_vehicles Tabelle sind (mit division_id 50 aus divisions und depots Tabelle)
$orderBy = ($Cost_user) ? " ORDER BY booking_vehicles.cost_centre=$Cost_user DESC" : "";
$query = " SELECT
				booking_vehicles.vehicleid, booking_vehicles.cost_centre,
				vehicles.vehicle_id, vehicles.vin, vehicles.code, vehicles.ikz  
				FROM booking_vehicles
				LEFT JOIN vehicles ON vehicles.vehicle_id = booking_vehicles.vehicleid
				$ordefBy
				LIMIT 200 ";

$VehiclesResults = $this->ladeLeitWartePtr->vehiclesPtr->getSpecialSql($query);

// document upload
if (!empty($_POST['name']) || !empty($_POST['vehicleid']) || $_FILES['userfile']) {
    $file_name = $_FILES['userfile']['name'];
    $tmp = $_FILES['userfile']['tmp_name'];
    $name = $_POST['name'];
    $id = $_POST['vinid'];
    $mime_type = mime_content_type($tmp);


    // get uploaded file's extension
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $file = fopen($tmp, 'r') or die('<div class="columns twelve"><div class="alert alert-danger" role="alert">kann Datei nicht lesen</div></div>');
    $data = fread($file, filesize($tmp));
    $filesize = filesize($tmp);
    $created_on = date('Y-m-d H:i');

    if ($filesize > 500000) {

        $msg = '<div class="alert alert-danger" role="alert"> Dokument ist zu Groß! Maximal Größe ist 500KB</div>';

    } else {
        $es_data = pg_escape_bytea($data);
        fclose($file);

        $sqlinsert = "INSERT INTO document_vehicles (vehicleid, mime_type, doc_name, file_name, created_on, user_id, filesize, doc_type) Values($id, '$mime_type', '$name', '$file_name', '$created_on', '$sts_userid', '$filesize', '$es_data')";

        $insertData = $this->ladeLeitWartePtr->newQuery()->query($sqlinsert);
        $_POST['success'] = 'send';
        /*ob_clean();
    header("Location: {$_SERVER['PHP_SELF']}" . $_SERVER['REDIRECT_URI'] . '?' . http_build_query($_GET) . $submit);
      exit;*/

    }
}
if (isset($_POST['deleteDoc'])) {
    $deleterow = $_POST['docid'];
    $sqlDelete = " DELETE FROM document_vehicles WHERE doc_id = $deleterow ";

    $deleteData = $this->ladeLeitWartePtr->newQuery()->query($sqlDelete);
}
?>
<h1>Test Fahrzeuge Dokumente</h1>
<div class="row ">
    <div class="columns twelve">
        <?php
        if (empty($insertData))
            echo $msg;

        if ($_POST['success'] == "send")
            echo '<div class="divsuccess alert alert-success" role="success">Dokument <strong>' . $name . '</strong> "' . $file_name . '" Grösse ' . $filesize . ' KB ist erfolgreich gespeichert!</div>';

        if (isset($_POST['deleteDoc']))
            echo '<div class="divsuccess alert alert-success" role="success">Dokument <strong>' . $_POST['docname'] . '</strong> (' . $_POST['filename'] . ') ist erfolgreich gelöscht!</div>';
        ?>
    </div>
</div>
<div class="row ">
    <div class="columns twelve">
        <div id="select_vin" class="select_vin">

            <form enctype="multipart/form-data" action="?action=documentvehicles&initPage" method="get">
                <p>Bitte wählen Sie ein Fahrzeug nach VIN/Kennzeichen. <br>
                    Sie können auch direkt Teile der VIN/Kennzeichen Nummer in das Feld zum Suchen eingeben.</p>
                <p><b>Bitte beachten Sie:</b><br>
                    Es werden nur Testfahrzeuge angezeigt, die einer <b>Kostenstelle</b> zugewiesen sind.<br></p>

                <input type="hidden" name="action" value="<?php echo $this->action ?>">
                <select class="doselect fleet_search" name="vehicleid" onchange="submit()">
                    <option value="null"></option><?php
                    foreach ($VehiclesResults as $vehicle) {

                        $select_vehicle = $vehicle["vin"];

                        if (!empty($vehicle["code"]))
                            $select_vehicle .= " ({$vehicle["code"]})";

                        if (!empty($vehicle["cost_centre"]))
                            $select_vehicle .= " (Kst: {$vehicle["cost_centre"]})";

                        if ($vehicle['cost_centre'] == $Cost_user) {
                            $isbookable = " ( bearbeitbar )";
                        }
                        if ($vehicle['cost_centre'] != $Cost_user) {
                            $isbookable = ' ( nur lesen )';
                        }

                        echo '<option style="color:#ccc" value="' . $vehicle['vehicle_id'] . '">' . $select_vehicle . ' ' . $isbookable . '</option>';
                    }
                    ?>
                </select><br><br>
            </form>
            <h4><span class="costcentre"><?php echo 'Meine Kostenstelle: ' . $Cost_user; ?><span></h4>
            <?php if (isset($_GET['vehicleid'])) {
                $getVehicle = $_GET['vehicleid'];

                $queryBooking = " SELECT
				 				booking_vehicles.vehicleid, booking_vehicles.cost_centre,
				 				vehicles.vehicle_id, vehicles.vin,  
				 				booking_users.user_id, booking_users.cost_centre 
				 				FROM booking_vehicles
				 				LEFT JOIN vehicles ON vehicles.vehicle_id = booking_vehicles.vehicleid
				 				LEFT JOIN booking_users ON booking_users.cost_centre = booking_vehicles.cost_centre
				 				WHERE booking_vehicles.vehicleid = $getVehicle AND booking_users.user_id = $sts_userid ";

                $resultBooking = $this->ladeLeitWartePtr->vehiclesPtr->getSpecialSql($queryBooking);

                foreach ($resultBooking as $booking) {
                    $user_vehicle = $booking['vin'];
                }
                if (!empty($resultBooking)) {
                    echo 'Bitte wählen Sie ein Dokument für den Fahrzeug <strong>' . $user_vehicle . '</strong> und geben Sie die Dokumentname ein. <br><br>

						<form enctype="multipart/form-data" action="?action=documentvehicles&initPage" method="POST">
							<input type="hidden" name="vinid" value="' . $_GET['vehicleid'] . '">
							<strong>Dokumentname:</strong> <input style="padding-left: 4px" type="text" name="name" size="25" length="25" value="" placeholder="Dokumentname eingeben">
							<strong>Dokument:</strong> <input name="userfile" type="file" size="25"/>
							<input type="submit" value="Upload" />
						</form>';
                }

            }
            ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="columns eight">

        <?php
        $querydocname = " SELECT
		 				document_vehicles.doc_id, document_vehicles.doc_name, document_vehicles.mime_type, document_vehicles.created_on, document_vehicles.filesize, 
		 				vehicles.vehicle_id, vehicles.vin, vehicles.code 
		 				FROM document_vehicles			 				
		 				LEFT JOIN vehicles ON vehicles.vehicle_id = document_vehicles.vehicleid 
		 				LEFT JOIN users ON users.id = document_vehicles.user_id 
		 				/*WHERE document_vehicles.user_id = $sts_userid*/ LIMIT 50 ";

        $resultdocname = $this->ladeLeitWartePtr->vehiclesPtr->getSpecialSql($querydocname);

        if (!empty($resultdocname)) { ?>

            <div class="clear-top clear-bottom">
                <h2>Fahrzeugdokumente suchen</h2>
                <form action="" id="doc_search_form" method='post'>
                    Sie können nach Dokumentname, vin, Fahrzeug Kenzeichen, File name oder Dokumenttyp suchen.<br>
                    Sie können auch Teile der Suchanfrage in das Feld zum Suchen eingeben.<br><br>
                    <input type="hidden" name="action" value="<?php echo $this->action ?>">
                    <?php /*
					<select class="vin_search" name="searchdoc" onchange="submit()">
					  <option value="null"></option><?php
					      foreach($resultdocname as $docname) {
					      		$documentid 	= $docname["doc_id"];
					      		$documentname 	= $docname["doc_name"] . " ";
					      		$documentname 	.= " ( ".$docname["vin"]." ) ";

					            if (!empty($docname["code"]))
					                $documentname .= " ({$docname["code"]})";

					            if (!empty($docname["ikz"]))
					                $documentname .= " IKZ {$docname["ikz"]}";

						    	echo '<option value="'.$documentid.'">'.$documentname.'</option>';
					      }
					?>
					</select>
					*/ ?>

                    <input type="text" name="sq" class="search_sq"
                           placeholder="<?php echo $search = (isset($_POST['sq'])) ? $_POST['sq'] : "Suchanfrage eingeben"; ?>">
                    <input class="btn-sq" type="submit" value="suchen">
                </form>
            </div>
            <?php
            if (isset($_POST['sq'])) {

                /*$document_id = $_POST['searchdoc'];*/
                $sq = trim($_POST['sq']);
                $queryDoc = " SELECT
		 				document_vehicles.doc_id, document_vehicles.doc_name, document_vehicles.file_name, document_vehicles.mime_type, document_vehicles.created_on, document_vehicles.filesize, document_vehicles.user_id,
		 				vehicles.vehicle_id, vehicles.vin, vehicles.code 
		 				FROM document_vehicles			 				
		 				LEFT JOIN vehicles ON vehicles.vehicle_id = document_vehicles.vehicleid 
		 				LEFT JOIN users ON users.id = document_vehicles.user_id 
				 		WHERE /*document_vehicles.doc_id = $document_id*/ 
				 		document_vehicles.doc_name like '%$sq%' 
				 		OR document_vehicles.mime_type like '%$sq%' 
				 		OR document_vehicles.file_name like '%$sq%' 
				 		OR vehicles.vin like '%$sq%' 
				 		OR vehicles.code like '%$sq%' ";

                $resultDocs = $this->ladeLeitWartePtr->vehiclesPtr->getSpecialSql($queryDoc);

                if (!empty($resultDocs)) {
                    echo '<div class="clear-bottom"><h2>Dokumentenliste</h2><table><tbody><tr>';
                    echo "<th>Dokumentname</th>
						 	  <th>VIN</th>
						 	  <th>Kennzeichen</th>
							  <th>File name</th>						 	  
						 	  <th>Größe KB</th>
							  <th>Dokumenttyp</th>						 	  
						 	  <th>Ertellungsdatum</th>
						 	  <th>Ansehen</th>
						 	  <th>Dokument löschen</th>	";
                    echo '</tr>';

                    foreach ($resultDocs as $resultDoc) {
                        if ($resultDoc['user_id'] == $sts_userid) {
                            $deleteButton = '<button class="del" type="submit" name="deleteDoc">löschen</button>';
                        } else
                            $deleteButton = 'Nur lesen';
                        // $details = '/html/uploadfile.php?id='.$resultDoc['doc_id'];
                        $details = '?action=documentvehicles&initPage&id=' . $resultDoc['doc_id'];
                        echo '<tr>
					 					<td>' . $resultDoc['doc_name'] . '</td>
					 					<td>' . $resultDoc['vin'] . '</td>
					 			    	<td>' . $resultDoc['code'] . '</td>
					 					<td>' . $resultDoc['file_name'] . '</td>				 			    
					 			    	<td>' . $resultDoc['filesize'] . '</td>
					 			    	<td>' . $resultDoc['mime_type'] . '</td>
					 			    	<td>' . $resultDoc['created_on'] . '</td>
					 			    	<td><span><a class="botton" target="_blank" href="' . $details . '">Details</a></span></td>
					 			    	<td>
					 			    		<form action="" id="form_delete" name="form_delete" method="post">
					 			    			<input type="hidden" name="docid" value="' . $resultDoc['doc_id'] . '">
					 			    			<input type="hidden" name="docname" value="' . $resultDoc['doc_name'] . '">
					 			    			<input type="hidden" name="filename" value="' . $resultDoc['file_name'] . '">'
                            . $deleteButton . '
					 			    		</form>
					 			    	</td>
					 				 </tr>';
                    }
                    echo '</tbody></table></div>';
                }

            }

        } else
            echo '<h4>Kein Dokument gefunden!</h4>';
        ?>

    </div>
</div>
<?php
if (isset($_GET['id'])) {
    // $sts_userid = $_SESSION['sts_userid'];
    $document_id = $_GET['id'];
    $queryDoc = " SELECT
			document_vehicles.doc_id, document_vehicles.doc_name, document_vehicles.mime_type, encode(doc_type, 'base64') AS data 
			FROM document_vehicles			 				
			LEFT JOIN vehicles ON vehicles.vehicle_id = document_vehicles.vehicleid 
			LEFT JOIN users ON users.id = document_vehicles.user_id 
			WHERE document_vehicles.doc_id = $document_id ";

    $resultDocs = $this->ladeLeitWartePtr->vehiclesPtr->getSpecialSql($queryDoc);

    if (!empty($resultDocs)) {

        foreach ($resultDocs as $resultDoc) {

            // The output buffer is not empty. It contains only a UTF-8 BOM and/or whitespace, let's clean it
            ob_clean();

            $mime_type = $resultDoc['mime_type'];
            header("Content-type: $mime_type");
            echo base64_decode($resultDoc['data']);
            exit;

        }
    }
}
?>
<script type="text/javascript">
    // autocomplete
    $(".vin_search").chosen();

    // hide div after the form is submitted
    $(document).ready(function () {
        $('.divsuccess').delay(1000).fadeIn(250).delay(3000).fadeOut(850);
    });

    // block the pop up asking for form resubmission on refresh once the form is submitted.
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    // Delete Document Cinfirm
    $(document).ready(function () {
        $(".del").click(function () {
            if (!confirm("Möchten Sie diesen Dokument löschen?")) {
                return false;
            }
        });
    });
</script> 