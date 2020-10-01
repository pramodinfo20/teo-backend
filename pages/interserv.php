<?php
/**
 * interserv.php
 * Template für die Benutzer Rolle Interserv
 * @author Pradeep Mohan
 */
?>

<div class="inner_container">
    <div class="row ">
        <div class="columns twelve">
            <ul class="submenu_ul">
                <li>
                    <a href="?action=zspn" data-target="zspn"
                       class="sts_submenu <?php if ($this->action == "zspn") echo 'selected'; ?>">ZSPn mit weniger
                        Ladepunkten als Fahrzeuge</a>
                </li>
                <li>
                    <a href="?action=assign" data-target="assign"
                       class="sts_submenu <?php if ($this->action == "assign") echo 'selected'; ?>">Fahrzeuge an
                        Ladepunkte zuordnen</a>
                </li>
                <li>
                    <a href="?action=overview" data-target="overview"
                       class="sts_submenu <?php if ($this->action == "overview") echo 'selected'; ?>">Ladeinfrastruktur
                        Übersicht</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="row ">
        <div class="columns twelve">
		<span class="error_msg">
		<?php if (is_array($this->msgs)) echo implode('<br>', $this->msgs); ?>
		</span>
        </div>
    </div>
    <?php if ($this->action == "assign"): ?>
        <div id="assign" class="submenu_target_child current">
            <div class="row ">
                <div class="columns twelve	">
                    <h1>Fahrzeuge an Ladepunkte zuordnen</h1>
                </div>
            </div>
            <div class="row ">
                <div class="columns eight">
                    <h2>ZSP wählen</h2>
                    <?php echo $this->qform_zsp->getContent(); ?>
                </div>
            </div>
            <div class="row ">
                <div class="columns eight">
                    <form id="assigned_vehicles_stations_div" method="post">
                        <?php if (isset($this->listVS)) echo $this->listVS; ?>
                    </form>
                </div>
            </div>
        </div>
    <?php elseif ($this->action == "zspn"): ?>
        <div id="zspn" class="submenu_target_child current">
            <div class="row ">
                <div class="columns eight">
                    <h1><?php echo $this->listObjectsHeading; ?></h1>

                    <?php


                    $processed_listObjects[] = array('headingone' => $this->listObjectsTableHeadings);

                    if (!empty($this->listObjects)) {
                        foreach ($this->listObjects as $listObject) {
                            // 					<a href="?editdepot=y&amp;depot=484#depot_edit" class="parent_hidden_text"><span class="genericon genericon-edit"> </span><span class="">Bearbeiten</span></a>

                            $listObjectLink = '<a href="?action=assign&depot=' . $listObject["depot_id"] . '" >
										<span class="genericon genericon-edit"></span><span class="">' . $listObject['name'] . '(' . $listObject['dp_depot_id'] . ')' . '</span></a>';

                            $processed_listObjects[] = array($listObjectLink, $listObject["vehicleCnt"]);


                        }

                        $displaytable = new DisplayTable ($processed_listObjects);
                        echo $displaytable->getContent();
                    } else
                        echo "Keine ZSPn gefunden!";
                    ?>

                </div>
            </div>
        </div>
    <?php elseif ($this->action == "overview"): ?>
        <div class="row ">
            <div class="columns twelve">
                <h1><?php echo $this->listObjectsHeading; ?></h1>
            </div>
        </div>

        <div class="row">
            <div class="columns eight">

                <?php
                $processed_listObjects[] = array('headingone' => $this->listObjectsTableHeadings);
                $sumTotalV = $sumTotalS = $sumTotalAV = $ratioone = $ratiotwo = $ratiothree = 0;
                if ($this->listObjects) {
// 					$before = microtime(true); to test performance
                    foreach ($this->listObjects as $listObject) {
                        $vehicleCnt = $this->ladeLeitWartePtr->vehiclesPtr->getVehiclesCnt($this->objectLabel, $listObject["{$this->objectLabel}_id"]);
                        if (!$vehicleCnt) $vehicleCnt = '';

                        $stationCnt = $this->ladeLeitWartePtr->stationsPtr->getStationsCnt($this->objectLabel, $listObject["{$this->objectLabel}_id"]);
                        if (!$stationCnt) $stationCnt = '';

                        $assignedVehiclesCnt = $this->ladeLeitWartePtr->vehiclesPtr->getVehiclesCnt($this->objectLabel, $listObject["{$this->objectLabel}_id"], true);
                        if (!$assignedVehiclesCnt) $assignedVehiclesCnt = '';

                        if ($stationCnt != 0)
                            $ratioone = ceil($vehicleCnt / $stationCnt * 100) . ' %';
                        else $ratioone = '';


                        if ($vehicleCnt != 0 && $assignedVehiclesCnt != 0)
                            $assignedVehiclesData = $assignedVehiclesCnt . ' (' . ceil($assignedVehiclesCnt / $vehicleCnt * 100) . ' %) ';
                        else
                            $assignedVehiclesData = $assignedVehiclesCnt;
                        if ($this->objectLabel == "depot") {
// 							$listObjectLink='<a href="?action=assign&'.$this->objectLabel.'='.$listObject["{$this->objectLabel}_id"].'">'.$listObject["name"].'</a>';
                            $listObjectLink = '<a href="?action=assign&depot=' . $listObject["depot_id"] . '" style="white-space: nowrap;" >
										<span class="genericon genericon-edit"></span><span class="">' . $listObject["name"] . '</span></a>';

                            $processed_listObjects[] = array($listObject["dp_{$this->objectLabel}_id"], $listObjectLink, $vehicleCnt, $stationCnt, $ratioone, $assignedVehiclesData, $listObject["lon"], $listObject["lat"]);
                        } else {
                            $listObjectLink = '<a href="?action=overview&' . $this->objectLabel . '=' . $listObject["{$this->objectLabel}_id"] . '"  style="white-space: nowrap;">' . $listObject["name"] . '</a>';
                            $processed_listObjects[] = array($listObject["dp_{$this->objectLabel}_id"], $listObjectLink, $vehicleCnt, $stationCnt, $ratioone, $assignedVehiclesData);

                        }


                        $sumTotalV += $vehicleCnt;
                        $sumTotalS += $stationCnt;
                        $sumTotalAV += $assignedVehiclesCnt;
                    }
// 					$after = microtime(true); to test performance
// 					echo ($after-$before)/100000 . " sec/serialize\n";


                    $displaytable = new DisplayTable ($processed_listObjects, array('id' => 'zentralelist'));
                    echo $displaytable->getContent();
                }

                ?>

            </div>
        </div>
        <div class="row ">
            <div class="columns six">
                <?php

                $contentnew = '<h2>Summe der ausgelieferten Sts-Fahrzeuge : ' . $sumTotalV . '</h2>';
                $contentnew .= '<h2>Summe der eingetragenen Ladepunkten : ' . $sumTotalS . '</h2>';
                if ($sumTotalS != 0)
                    $ratiotwo = ceil($sumTotalV / $sumTotalS * 100) . ' % ';
                else
                    $ratiotwo = ' ';

                if ($sumTotalAV != 0)
                    $ratiothree = $sumTotalAV . ' (' . ceil($sumTotalAV / $sumTotalV * 100) . ' %)';
                else $ratiothree = '';

                $contentnew .= '<h2>#ausgelieferter Fahrzeuge / #eingetragener Ladepunkten : ' . $ratiotwo . '</h2>';
                $contentnew .= '<h2>Ladepunkte zugewiesene Fahrzeuge (Anteil) : ' . $ratiothree . ' </h2>';

                echo $contentnew;
                ?>
            </div>
        </div>
    <?php endif; ?>
    <div class="row ">
        <div class="columns six">
            <a href="#" onClick="window.history.back();"> Zurück</a>
        </div>
    </div>

</div>		