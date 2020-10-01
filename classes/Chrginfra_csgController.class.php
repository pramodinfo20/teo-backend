<?php

/**
 * Chrginfra_aixController.class.php
 * Controller for User Role chrginfra_aix
 * @author Pradeep Mohan
 */
class Chrginfra_csgController extends ChrginfraController
{

    protected $autoGenStations;


    function saveStationUpload()
    {

        if (isset($_FILES["csvfile"])) {
            $filename = $_FILES['csvfile']['tmp_name'];
        }

        if (($handle = fopen($filename, "r")) !== FALSE) {

            while (($data = fgetcsv($handle, null, ",")) !== FALSE) {

                if (! preg_match('/^\d+$/', $data[4])) {
                    continue;
                }
                $newStation['name'] = $data[0];

                if (! preg_match('/^([0-9]){7}([rRlL]){1}$/', $newStation['name'])) 
                {
                    $stn_msgs[] = 'Fehler mit Ladepunkte Name. Korrigieren Sie bitte die CSV Datei';
                    break;
                }
                $newStation['restriction_id'] = array_search($data[1], $restrictions_names);
                $newStation['restriction_id2'] = array_search($data[2], $restrictions_names);
                $newStation['restriction_id3'] = array_search($data[3], $restrictions_names);
                $newStation['station_power'] = (float) $data[4] * 215.0;

                if ($newStation['restriction_id'] == 'null' || empty($newStation['restriction_id'])) {
                    $newStation['restriction_id'] = NULL;
                    $stn_msgs[] = 'Fehler mit CSV Datei. Korrigieren Sie bitte die CSV Datei';
                    break;
                } else {
                    if ($newStation['restriction_id2'] == 'null' || empty($newStation['restriction_id2']))
                        $newStation['restriction_id2'] = NULL;
                    if ($newStation['restriction_id3'] == 'null' || empty($newStation['restriction_id3']))
                        $newStation['restriction_id3'] = NULL;
                    $newStation['depot_id'] = $depot;
                    $newStationId = $this->ladeLeitWartePtr->stations->add($newStation, $depot);
                }
            }
        }

    }


    function getAutoGenCtrl()
    {

        $qform_autogen = new QuickformHelper($this->display_header, "auto_gen_stations");
        $qform_autogen->autoGenStations();
        $qform_autogen->addElement('hidden', 'depot', array(
            'value' => $this->depot
        ));
        $qform_autogen->addElement('hidden', 'zspl', array(
            'value' => $this->zspl
        ));
        $qform_autogen->addElement('hidden', 'div', array(
            'value' => $this->div
        ));
        $this->autoGenStations = $qform_autogen->getContent();

    }


    function saveAutoGen()
    {

        $autogen_cnt = $this->requestPtr->getProperty('autogencnt');
        $phases = $this->ladeLeitWartePtr->restrictionsPtr->getAllPhases($this->depot);
        $laststation = $this->ladeLeitWartePtr->stationsPtr->getLastStationForDepot($this->depot);

        $skip_next_li_station = false;
        if (empty($laststation))
            $i = 1;
        else {
            $lastcnt = (int) substr($laststation['name'], 3, 2);

            if (substr($laststation['name'], 5, 2) == 're')
                $i = $lastcnt + 1;
            else {
                $i = $lastcnt;
                $skip_next_li_station = true;
            }
        }

        for ($stncnt = 1; $stncnt <= $autogen_cnt; $i ++) {
            $phasenum = $i % 3;

            /**
             * $i $phasenum
             * 1 1
             * 2 2
             * 3 3
             * 4 1
             * 5 2
             * 6 3
             */

            if ($phasenum == 0)
                $phasenum = 3;
            $newStation['station_power'] = (float) 16 * 215.0;

            $newStation['name'] = 'DLS' . str_pad($i, 2, '0', STR_PAD_LEFT) . 'li';
            $newStation['depot_id'] = $this->depot;

            $newStation['restriction_id2'] = NULL;
            $newStation['restriction_id3'] = NULL;

            $currentphase = $phases[$phasenum - 1];

            $newStation['restriction_id'] = $currentphase['restriction_id'];
            if ($skip_next_li_station) {
                $skip_next_li_station = false;
            } else {
                $newStationId = $this->ladeLeitWartePtr->stationsPtr->add($newStation, $this->depot);
                $stncnt ++;
            }

            if ($stncnt <= $autogen_cnt) {
                $newStation['name'] = 'DLS' . str_pad($i, 2, '0', STR_PAD_LEFT) . 're';
                $newStationId = $this->ladeLeitWartePtr->stationsPtr->add($newStation, $this->depot);
                $stncnt ++;
            }
        }

    }

}
