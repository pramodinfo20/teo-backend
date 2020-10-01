<?php
/**
 * CommonajaxController.class.php
 * Controller for Common ajax functions
 * @author Pradeep Mohan
 */


class CommonajaxController extends PageController {
    /**
     * Konstruktor
     */
    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->user = $user;
        $this->displayHeader = $this->container->getDisplayHeader();
        $this->displayHeader->setTitle("StreetScooter Cloud Systems : " . $this->user->getUserRoleLabel());


        $this->action = $this->requestPtr->getProperty('action');

        if (isset($this->action))
            call_user_func(array($this, $this->action));

        $this->displayHeader->printContent();

        $this->printContent();

    }

    function getStationsListForDepot() {
        $zsp = $this->requestPtr->getProperty('zsp');
        $stations = $this->ladeLeitWartePtr->stationsPtr->getStationsForDepot($zsp);
        $result = array();
        //cannot use array_column to get value/label pair as we have to json_encode this in format {val: 1, label:'Ladepunkte1'}
        foreach ($stations as $station) {
            if (!empty($station['restriction_id2']) && !empty($station['restriction_id3']))
                $stationame = $station['name'] . ' (3-phasig)';
            else
                $stationame = $station['name'] . ' (1-phasig)';
            if ($station['deactivate'] == 'f')
                $result[] = array('val' => $station['station_id'], 'label' => $stationame);
        }
        if (empty($result))
            echo 'false';
        else
            echo json_encode($result);
        exit(0);

    }

    function getFreeStationsListForDepot() {
        $zsp = $this->requestPtr->getProperty('zsp');
        $stations = $this->ladeLeitWartePtr->stationsPtr->getFreeStationsForDepot($zsp);
        $result = array();

        //cannot use array_column to get value/label pair as we have to json_encode this in format {val: 1, label:'Ladepunkte1'}
        foreach ($stations as $station) {
            if (!empty($station['restriction_id2']) && !empty($station['restriction_id3']))
                $stationame = $station['name'] . ' (3-phasig)';
            else
                $stationame = $station['name'] . ' (1-phasig)';
            if ($station['deactivate'] == 'f')
                $result[] = array('val' => $station['station_id'], 'label' => $stationame);
        }

        if (empty($result))
            echo 'false';
        else
            echo json_encode($result);
        exit(0);

    }

}

