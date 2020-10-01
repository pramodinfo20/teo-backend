<?php
/**
 * CommonFunctions_ShowOZSelect_FreeStations.class.php
 *
 * @author Pradeep Mohan
 */

/**
 * Class to handle common functions
 *
 */
class CommonFunctions_ShowOZSelect_FreeStations extends CommonFunctions_ShowOZSelect {

    //@todo maybe also show only divisions with vehicles in them

    /**
     *  returns only those depots whose ZSPLs are assigned to the user role.
     */

    function getZspls() {
        if ($this->user->getUserRole() == 'fpv')
            return;

        if ($this->div)
            $zspls = $this->ladeLeitWartePtr->zsplPtr->getZsplsWithFreeStations(array('division_id' => $this->div));
        else if ($this->user->getUserRole() == 'zentrale')
            $zspls = $this->ladeLeitWartePtr->zsplPtr->getZsplsWithFreeStations();
        //continue here for other roles else if()

        //if the zspl actually doesnt belong here (in case of a different division being selected)
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

    }


    function getDepots() {
        if ($this->zspl)
            $this->depots = $this->ladeLeitWartePtr->depotsPtr->getDepotsWithFreeStations(array('zspl_id' => $this->zspl));
        else if ($this->div)
            $this->depots = $this->ladeLeitWartePtr->depotsPtr->getDepotsWithFreeStations(array('division_id' => $this->div));


        if (!in_array($this->zsp, array_column($this->depots, 'depot_id')))
            $this->zsp = null;

        $processedDepots = array('' => ' ');

        //array_combine not used since we have to combine both the name and dp_depot_id
        foreach ($this->depots as $singleDepot) {
            $processedDepots[$singleDepot['depot_id']] = $singleDepot['name'] . ' : ' . $singleDepot['dp_depot_id'];
        }

        $oder = ($this->user->getUserRole() == 'fpv') ? "" : "oder direkt ";
        $this->qform_depot->genSelect("depot", array("id" => "depotSelect"), $processedDepots, $oder . 'ZSP wählen/eintippen', $this->zsp, array('div' => $this->div, 'zspl' => $this->zspl));

    }

}
