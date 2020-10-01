<?php

/**
 * CommonFunctions_ShowOZSelect.class.php
 *
 * @author Pradeep Mohan
 */

/**
 * Class to handle common functions
 */
class CommonFunctions_ShowOZSelect extends CommonFunctions
{

    protected $qform_div;

    protected $qform_zspl;

    protected $qform_depot;

    protected $div;

    protected $zspl;

    protected $stations;

    protected $zsp;

    protected $penta_id;

    protected $depots;

    protected $max_vehicles_per_station = 2;


    function __construct($ladeLeitWartePtr, $displayHeader, $user, $requestPtr, $getaction)
    {

        parent::__construct($ladeLeitWartePtr, $displayHeader, $user, $requestPtr, $getaction);

        $this->qform_div = new QuickformHelper($this->displayHeader, "chrg_infra_div_form");
        $this->qform_zspl = new QuickformHelper($this->displayHeader, "chrg_infra_zspl_form");
        $this->qform_depot = new QuickformHelper($this->displayHeader, "chrg_infra_depot_form");

        $this->qform_vehicle_mgmt = null;

        $this->displayHeader->enqueueJs("sts-custom-common", "js/sts-custom-common.js");

        $role = $this->user->getUserRole();
        if ($role == 'fuhrparksteuer')
            $this->div = $this->user->getAssignedDiv();
        else if ($role == 'sales') {
            $this->div = $this->requestPtr->getProperty('div');
            // do not set to Hamburg.. allow sales user to select all ZSP
            // if(empty($this->div))
            // $this->div=$this->ladeLeitWartePtr->divisionsPtr->newQuery()->where('name','=','Hamburg')->where('active','!=','f')->getVal('division_id');
        } else
            $this->div = $this->requestPtr->getProperty('div');

        if ($role == 'fpv')
            $this->zspl = $this->user->getAssignedZspl();
        else
            $this->zspl = $this->requestPtr->getProperty('zspl');

        $this->zsp = $this->requestPtr->getProperty('zsp');

        if (! empty($this->zsp))
            $this->zspname = $this->ladeLeitWartePtr->depotsPtr->getDepotName($this->zsp);

        $this->week = $requestPtr->getProperty('week', 'current');
        $this->subaction = $this->requestPtr->getProperty('subaction');

        if (isset($this->subaction))
            call_user_func(array(
                $this,
                $this->subaction
            ));

        if ($getaction == 'flottenmonitor')
            $this->getLastOrCurrentWeek();

        $this->getDivs();
        $this->getZspls();
        $this->getDepots();

        // VIN Select for "Auslieferungsparkplatz Dortmund" (um alles zu zeigen bitte auskomentieren)
        if ($this->zsp == 3349)
            $this->getSelectVins();

    }


    /**
     * returns only that division assigned to the user role, or return all fivisions in case of Zentrale
     */
    function getDivs()
    {

        if ($this->user->getUserRole() == 'fuhrparksteuer')
            $this->qform_div->addElement('hidden', 'div')->setValue($this->div);
        else if (in_array($this->user->getUserRole(), array(
            'zentrale',
            'sales',
            'fleet'
        ))) {
            $divisions = $this->ladeLeitWartePtr->divisionsPtr->getAll();
            $processedDivisions = array(
                '' => ' '
            );

            foreach ($divisions as $singleDiv) {
                $processedDivisions[$singleDiv['division_id']] = $singleDiv['name'] . ' : ' . $singleDiv['dp_division_id'];
            }

            // in case a zsp is passed, but no zspl, then set the zspl automatically
            if (! isset($this->div) && isset($this->zsp))
                $this->div = $this->ladeLeitWartePtr->depotsPtr->getDivision($this->zsp);

            $this->qform_div->genSelect("div", array(
                "id" => "divisionSelect"
            ), $processedDivisions, 'Niederlassung wählen/eintippen', $this->div);
        }

        // continue here for other roles else if()
    }


    /**
     * returns only those depots whose ZSPLs are assigned to the user role.
     */
    function getZspls()
    {

        if ($this->div)
            $zspls = $this->ladeLeitWartePtr->zsplPtr->getAllInDivision($this->div);
        else if ($this->user->getUserRole() == 'zentrale')
            $zspls = $this->ladeLeitWartePtr->zsplPtr->getAll();
        else
            $zspls = $this->ladeLeitWartePtr->zsplPtr->getAll();

        // in case a zsp is passed, but no zspl, then set the zspl automatically
        if (! isset($this->zspl) && isset($this->zsp))
            $this->zspl = $this->ladeLeitWartePtr->depotsPtr->getZspl($this->zsp);

        // continue here for other roles else if()
        if (! in_array($this->zspl, array_column($zspls, 'zspl_id')))
            $this->zspl = null;

        $processedZspl = array(
            '' => ' '
        );

        foreach ($zspls as $singlezspl) {
            $processedZspl[$singlezspl['zspl_id']] = $singlezspl['name'] . ' : ' . $singlezspl['dp_zspl_id'];
        }

        $this->qform_zspl->genSelect("zspl", array(
            "id" => "zsplSelect"
        ), $processedZspl, 'ZSPL wählen/eintippen', $this->zspl, array(
            'div' => $this->div
        ));

    }


    function getDefaultDepot()
    {

        return $this->zsp;

    }


    function getDefaultZspl()
    {

        return $this->zspl;

    }


    function getDepots()
    {

        if ($this->zspl)
            $this->depots = $this->ladeLeitWartePtr->depotsPtr->getAllInZspl($this->zspl);
        else if ($this->div)
            $this->depots = $this->ladeLeitWartePtr->depotsPtr->getAllInDiv($this->div);
        else
            $this->depots = $this->ladeLeitWartePtr->depotsPtr->getAll(); // needs to be corrected to ensure only users with proper privileges have the depots

        if (! in_array($this->zsp, array_column($this->depots, 'depot_id')))
            $this->zsp = null;

        $processedDepots = array(
            '' => ' '
        );

        // array_combine not used since we have to combine both the name and dp_depot_id
        foreach ($this->depots as $singleDepot) {
            $processedDepots[$singleDepot['depot_id']] = $singleDepot['name'] . ' : ' . $singleDepot['dp_depot_id'];
        }

        $this->qform_depot->genSelect("depot", array(
            "id" => "depotSelect"
        ), $processedDepots, 'oder direkt ZSP wählen/eintippen', $this->zsp, array(
            'div' => $this->div,
            'zspl' => $this->zspl
        ));

    }


    /**
     * returns only vehicles for depot 'Fleet Auslieferungsparkplatz Dortmund'.
     */
    function getSelectVins()
    {

        // $this->depots=$this->ladeLeitWartePtr->vehiclesPtr->getAll('vin');
        $this->depots = $this->ladeLeitWartePtr->vehiclesPtr->getAllVinInZsp($this->zsp);

        $processedVins = array(
            '' => ' '
        );

        foreach ($this->depots as $singleDepot) {
            $processedVins[$singleDepot['penta_number_id']] = $singleDepot['penta_number_id'] . ' : ' . $singleDepot['penta_number'];
        }
        $this->qform_depot->genSelect("penta_id", array(
            "id" => "vinSelect"
        ), $processedVins, 'oder direkt Variante wählen/eintippen', $this->zsp, array(
            'div' => $this->div,
            'zspl' => $this->zspl
        ));

    }


    function getSelectedWeek()
    {

        return $this->week;

    }


    function getLastOrCurrentWeek()
    {

        $selected = [
            'last' => '',
            'current' => ''
        ];
        $selected[$this->week] = ' checked';

        $weekSelect = <<<HEREDOC
    <div>
      Welche Woche soll abgefragt werden?<br>
      <span class="LabelX W120"><input id="lastWeek" type="radio" name="week" value="last"{$selected['last']}>letzte Woche</span>
      <span class="LabelX W120"><input id="currentWeek" type="radio" name="week" value="current"{$selected['current']}>laufende Woche</span>
    </div>
HEREDOC;

        $this->qform_depot->addElement('staticNoLabel')->setContent($weekSelect);

    }


    function printContent()
    {

        include ("pages/common/showozselect.php");

    }

}
