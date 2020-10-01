<?php

class ACtion_C2cconnects extends AClass_Base {
    function __construct() {
        parent::__construct();

        $leitWartePtr = $this->controller->GetObject('ladeLeitWarte');
        $this->vehiclesPtr = $leitWartePtr->vehiclesPtr;

        $this->markers = [];
    }

    // ==============================================================================================


    function Execute() {
        $position_query = "
        select
            depots.name,
            vehicles.code,
            vehicles.vin,
            vehicles.c2cbox,
            vehicles.lat,
            vehicles.lon,
            measurements.timestamp_bchargeplug,
            online,
            bcm_c2c_bchargeplug,
            bcm_c2c_uacmains,
            bcm_c2c_iacmains,
            bcm_c2c_percsoc
        from
            vehicles,depots,measurements
        where
                vehicles.vehicle_id=measurements.vehicle_id
            and depots.depot_id=vehicles.depot_id
            and vehicles.lat is not null
            and vehicles.lon is not null
            and vehicles.depot_id != 0
            and vehicles.depot_id != 3348
            and c2cbox != ''
            and c2cbox is not null";


        $query = $this->vehiclesPtr->newQuery();
        $query->query($position_query);
        while ($line = $query->fetchArray()) {
            $lat = $line['lat'];
            $lng = $line['lon'];
            $zsp = $line['name'];
            $code = $line['code'];
            $vin = $line['vin'];
            $ts = $line['timestamp_bchargeplug'];
            $plug = $line['bcm_c2c_bchargeplug'];
            $uac = $line['bcm_c2c_uacmains'];
            $iac = $line['bcm_c2c_iacmains'];
            $online = $line['online'];
            $soc = $line['bcm_c2c_percsoc'];
            $box = $line['c2cbox'];
            if ($lat == 'NaN') {
                $lat = '0.1';
            }
            if ($lng == 'NaN') {
                $lng = '0.1';
            }

            $this->markers[] = array('zsp' => $zsp, 'lat' => $lat, 'lng' => $lng, 'ts' => $ts, 'plug' => $plug, 'uac' => $uac, 'iac' => $iac, 'online' => $online, 'soc' => $soc, 'box' => $box, 'code' => $code, 'vin' => $vin);
        }
    }

    // ==============================================================================================


    function SetupHeaderFiles($displayheader) {
        parent::SetupHeaderFiles($displayheader);

        $displayheader->enqueueJs("leaflet", "map/leaflet.js");
        $displayheader->enqueueJs("markercluster", "map/leaflet.markercluster.js");
        $displayheader->enqueueStylesheet("leaflet", "map/leaflet.css");
        $displayheader->enqueueStylesheet("MarkerCluster", "map/MarkerCluster.css");
        $displayheader->enqueueStylesheet("MarkerDefault", "map/MarkerCluster.Default.css");

        $displayheader->enqueueLocalJs('var markers=' . json_encode($markers) . ';');
    }

    // ==============================================================================================


    function WriteHtmlMenu() {
        include "./actions/html/Engg.Menu.php";
    }

    // ==============================================================================================


    function WriteHtmlContent($options = "") {
        parent::WriteHtmlContent($options);


        echo <<<HEREDOC
    <div id="map" style="width: 100%; height: 100%;"></div>
    <script type="text/javascript" src="map/map_with_data.js"></script>
HEREDOC;
    }
}