<?php
/**
 * CommonFunctions_ShowOZSelect_Vehicles.class.php
 *
 * @author Pradeep Mohan
 */

/**
 * Class to handle common functions
 *
 */
class CommonFunctions_ShowOZSelect_AssignedVehicles extends CommonFunctions_ShowOZSelect {

    //@todo maybe also show only divisions with vehicles in them

    /**
     *  returns only those depots whose ZSPLs are assigned to the user role.
     */

    function getZspls() {
        if ($this->div)
            $zspls = $this->ladeLeitWartePtr->zsplPtr->getZsplsWithAssignedVehicles($this->div);
        else if ($this->user->getUserRole() == 'zentrale')
            $zspls = $this->ladeLeitWartePtr->zsplPtr->getZsplsWithAssignedVehicles(); // @todo 20160816 check if this works
        //continue here for other roles else if()
        if (!in_array($this->zspl, array_column($zspls, 'zspl_id')))
            $this->zspl = null;

        //in case a zsp is passed, but no zspl, then set the zspl automatically
        if (!isset($this->zspl) && isset($this->zsp))
            $this->zspl = $this->ladeLeitWartePtr->depotsPtr->getZspl($this->zsp);

        $processedZspl = array('' => ' ');

        foreach ($zspls as $singlezspl) {
            $processedZspl[$singlezspl['zspl_id']] = $singlezspl['name'] . ' : ' . $singlezspl['dp_zspl_id'];
        }

        $this->qform_zspl->genSelect("zspl", array("id" => "zsplSelect"), $processedZspl, 'ZSPL wählen/eintippen', $this->zspl, array('div' => $this->div));

        if ($this->getaction == 'abfahrtszeit') {
            $static = $this->qform_depot->addElement('static', null, array('href' => '?action=set_departures_oz', 'class' => 'set_departures_zspl', 'style' => 'font-size:1em;position: relative;left: 250px;top: -45px;float: left;'));
            $static->setContent('Die gleiche Abfahrtszeiten für gesamten ZSPL setzen')->setTagName('a');
        }

    }


    function getDepots() {
        if ($this->zspl)
            $this->depots = $this->ladeLeitWartePtr->depotsPtr->getDepotsWithAssignedVehicles(array('zspl_id' => $this->zspl));
        else if ($this->div)
            $this->depots = $this->ladeLeitWartePtr->depotsPtr->getDepotsWithAssignedVehicles(array('division_id' => $this->div));
        else
            $this->depots = $this->ladeLeitWartePtr->depotsPtr->getDepotsWithAssignedVehicles(null);


        if (!in_array($this->zsp, array_column($this->depots, 'depot_id')))
            $this->zsp = null;

        $processedDepots = array('' => ' ');

        //array_combine not used since we have to combine both the name and dp_depot_id
        foreach ($this->depots as $singleDepot) {
            $processedDepots[$singleDepot['depot_id']] = $singleDepot['name'] . ' : ' . $singleDepot['dp_depot_id'];
        }

        $this->qform_depot->genSelect("depot", array("id" => "depotSelect"), $processedDepots, 'oder direkt ZSP wählen/eintippen', $this->zsp, array('div' => $this->div, 'zspl' => $this->zspl));

        if ($this->getaction == 'abfahrtszeit') {
            $static = $this->qform_depot->addElement('static', null, array('href' => '?action=set_departures_oz', 'class' => 'set_departures_depot', 'style' => 'font-size:1em;position: relative;left: 250px;top: -45px;float: left;'));

            $static->setContent('Die gleiche Abfahrstzeiten für gesamten ZSP setzen')->setTagName('a');

        }

    }

}
