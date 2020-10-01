<?php
/**
 * depots.class.php
 * Klasse für alle database
 * @author Pradeep Mohan
 */

/**
 * Class to handle depots
 */

require_once $_SERVER['STS_ROOT'].'/includes/sts-defines.php';

class Depots extends LadeLeitWarte {

	function __construct(DataSrc $dataSrcPtr,$tableName) {
		$this->dataSrcPtr=$dataSrcPtr;
		$this->tableName=$tableName;
	}

	/***
	 * getAllInUnitWithFilters
	 * Used primarily in the CommonFunctions_ShowOZSelect_Vehicles.class.php (called in FPS Controller)
	 * @param array $unitParams array('zspl_id'=>$this->zspl) single WHERE statement to get depots from either the division or the ZSPL
	 * @param array $filterParams array('vehicles','stations') one or more tablenames for JOIN statements to remove the rows where depot_id is NULL in either vehicles or stations
	 * @return array of depot_id, name and dp_depot_id for all matching depots
	 */
	 function getAllInUnitWithFilters($unitParams,$filterParams)
	{
		$result=$this->newQuery();

			if(isset($unitParams))
			{
				foreach($unitParams as $key=>$val)
					$result->where($key,'=',$val);
			}

	// 		$result->where('depots.dp_depot_id','IS','NOT NULL'); @todo should this be changed? if this is set then unbekannt will not be shown even if vehicles are present
			if(isset($filterParams))
			{
				foreach($filterParams as $val)
					$result->join($val,$val.'.depot_id=depots.depot_id','INNER JOIN'); // INNER JOIN ensures that rows with NULL values are removed from the result
			}

		 	$result->orderBy('dp_depot_id','ASC');

			return $result->get('DISTINCT(depots.depot_id),depots.name,depots.dp_depot_id');

	}

	/***
	 * getDepotsWithAssignedVehicles
	 * Used primarily in the CommonFunctions_ShowOZSelect_Vehicles.class.php (called in FPS Controller)
	 * @param array $unitParams array('zspl_id'=>$this->zspl) single WHERE statement to get depots from either the division or the ZSPL
	 * @return array of depot_id, name and dp_depot_id for all matching depots
	 */

	function getDepotsWithAssignedVehicles($unitParams)
	{
		$result=$this->newQuery();

			if(isset($unitParams))
			{
				foreach($unitParams as $key=>$val)
					$result->where($key,'=',$val);
			}

			$result->where('depots.dp_depot_id','IS','NOT NULL'); // @todo need this or not?

			return $result->join('vehicles','vehicles.depot_id=depots.depot_id','INNER JOIN')
					->join('stations','stations.station_id=vehicles.station_id','INNER JOIN')
					->orderBy('dp_depot_id','ASC')
					->get('DISTINCT(depots.depot_id),depots.name,depots.dp_depot_id');

	}
	/***
	 * getDepotsWithFreeStations
	 * Used in CommonFunctions_VehicleManagement.class.php and CommonFunctions_ShowOZSelect_FreeStations.class.php
	 * @param array $unitParams array('zspl_id'=>$this->zspl) single WHERE statement to get depots from either the division or the ZSPL
	 * @return array of depot_id, name and dp_depot_id for all matching depots
	 */
	function getDepotsWithFreeStations($unitParams, $max_vehicles_at_station=1)
	{
	    $WHERE = "";

		$qry   =$this->newQuery();
        /*
			if(isset($unitParams))
			{
				foreach($unitParams as $key=>$val)
						$result->where($key,'=',$val);
			}

			$result->where('vehicles.station_id','IS','NULL'); //vehicles.stationid will be NULL when performing a FULL OUTER JOIN  vehicles on station_id

			return $result->join('stations','stations.depot_id=depots.depot_id','INNER JOIN')
						->join('vehicles','vehicles.station_id=stations.station_id','FULL OUTER JOIN')
						->orderBy('dp_depot_id','ASC')
						->get('DISTINCT(depots.depot_id),depots.name,depots.dp_depot_id');
		*/


		if(isset($unitParams))
		{
			foreach($unitParams as $key=>$val)
			{
			    $slashed = addslashes ($val);
			    if (empty ($WHERE))
			        $WHERE = " WHERE $key='$slashed'";
                else
                    $WHERE .= " AND $key='$slashed'";
			}
		}


		$SQL = "
            SELECT DISTINCT(depot_id), name, dp_depot_id, num_vehicles
            FROM
            (SELECT depots.name, depots.dp_depot_id, depot_id,
               (select count(*)  FROM vehicles WHERE vehicles.station_id=stations.station_id) as num_vehicles
            FROM depots
            INNER JOIN stations using (depot_id) $WHERE
            ) as sub

            where num_vehicles < $max_vehicles_at_station
            ORDER BY dp_depot_id ASC;";

		if ($qry->query ($SQL))
            return $qry->fetchAll();

		return false;

	}

	/***
	* Used in CommonFunctions_VehicleManagement.class.php for ZSP with only free Stations
	*/
	function getDepotsForOnlyFreeStations($unitParams, $max_vehicles_at_station=1)
		{
		    $WHERE = "";

			$qry   =$this->newQuery();

			if(isset($unitParams))
			{
				foreach($unitParams as $key=>$val)
				{
				    $slashed = addslashes ($val);
				    if (empty ($WHERE))
				        $WHERE = " WHERE $key='$slashed'";
	                else
	                    $WHERE .= " AND $key='$slashed'";
				}
			}

			$sql_replacement_vehicles = "SELECT DISTINCT(depot_id), depots.name
									FROM
										divisions
									INNER JOIN
										depots USING (division_id)
									INNER JOIN
										stations USING (depot_id)
									LEFT OUTER JOIN
										vehicles USING (station_id, depot_id) $WHERE
									GROUP BY
										depot_id, station_id
									HAVING
										BOOL_AND(COALESCE(replacement_vehicles, True))";

			if ($qry->query ($sql_replacement_vehicles))
	            return $qry->fetchAll();

			return false;
	}

	/***
	 * getAllinZspl
	 * Used in CommonFunctions_ShowOZSelect.class.php, FpvController.class.php, InterservController.class.php, ZentraleController.class.php
	 * @param integer $zspl
	 * @return array of depots in this zspl
	 */
	function getAllInZspl($zspl) {

		return $this->newQuery()->where('zspl_id','=',$zspl)
		->orderBy('dp_depot_id','ASC')
		->get('*');

	}

	/***
	 * getDivision
	 * returns an integer, the division_id of this depot. Used in CommonFunctions_VehicleManagement.class.php
	 * @param integer $zsp
	 * @return integer division_id
	 */
	function getDivision($zsp)
	{
		return $this->newQuery()->where('depot_id','=',$zsp)->getVal('division_id');
	}

	/***
	 * getZspl
	 * returns an integer, the zspl_id of this depot.
	 * Used in CommonFunctions_ShowOZSelect_AssignedVehicles.class.php, CommonFunctions_ShowOZSelect_FreeStations.class.php, Zspl.class.php,
	 * CommonFunctions_ShowOZSelect_Vehicles.class.php, CommonFunctions_ShowOZSelect.class.php, ChrginfraController.class.php
	 * @param integer $zsp
	 * @return integer zspl_id
	 */
	function getZspl($zsp)
	{
		return $this->newQuery()->where('depot_id','=',$zsp)->getVal('zspl_id');

	}

	/***
	 * getStsPoolDepot
	 * used in CommonFunctions_VehicleManagement.class.php SalesController.class.php
	 * @return array
	 */
	function getStsPoolDepot()
	{
			return $this->newQuery()->where('name','LIKE','Sts_Pool')->getOne('depots.depot_id,depots.name,depots.dp_depot_id');
	}

	/***
	 * getFleetPoolDepot
	 * used in CommonFunctions_VehicleManagement.class.php
	 * @param integer $division_id
	 * @return array of one single depot
	 */
	function getFleetPoolDepot($division_id)
	{
		return $this->newQuery()->
		              where ('division_id','=',$division_id)->
		              where ('depot_type_id', '=', DEPOT_TYPE_POST_FLEET_POOL)->
		              getOne ('depots.depot_id,depots.name,depots.dp_depot_id');
	}

	/***
	 * getDepotAustehendeZuweisung
     * alias getUnbekanntDepot
	 * used in CommonFunctions_VehicleManagement.class.php vehiclemgmt.php
	 * @param integer $div
	 * @return array
	 */
	function getDepotAustehendeZuweisung($div)
	{
		return $this->newQuery()->where('division_id','=',$div)
						->where('depot_type_id','=',DEPOT_TYPE_POST_AUSSTEHENDE)
						->getOne('depots.depot_id,depots.name,depots.dp_depot_id');
	}

    function getUnbekanntDepot($div)
    {
        return $this->getDepotAustehendeZuweisung($div);
    }


    /***
    * getOtherDivisionsAusstehendeZuweisung
    * gibt eine Liste aller "NL_Austehende_Zuweisung" abgesehen der eigenen zurück
    * @param string $except_div division_id der Abzugebenden Niederlassung
    * @param string $order_by Spalte, nach der sortiert wird
    */
	function getOtherDivisionsAusstehendeZuweisung($except_div, $order_by='depots.name')
	{
		$qry = $this->newQuery()
		            ->join ('divisions', 'using(division_id)')
                    ->where('divisions.active','=','t')
                    ->where('division_id', '!=', $except_div)
                    ->where('depot_type_id', '=', DEPOT_TYPE_POST_AUSSTEHENDE)
                    ->orderBy($order_by);
		return $qry->get('*');

	}

	/***
	 * getAllInDiv
	 * used in CommonFunctions_ShowOZSelect.class.php FuhrparksteuerController.class.php ZentraleController.class.php Zspl.class.php
	 * @param integer $div
	 */
	function getAllInDiv($div)
	{
		return $this->newQuery()->where('division_id','=',$div)
						->orderBy('dp_depot_id','ASC')
						->get('*');
	}

	/***
	 * getAllValidDepots
	 * used in ZentraleController.class.php
	 * @return array of valid depots
	 */
	function getAllValidDepots() {
		return $this->newQuery()->where('dp_depot_id','>',0)
						->orderBy('dp_depot_id','ASC')
						->get('*');
	}

	/***
	 * getDepotName
	 * used in CommonFunctions_ShowOZSelect.class.php Depots.class.php
	 * @param integer $depot_id
	 * @return string depotname
	 */
	function getDepotName($depot_id)
	{
		return $this->newQuery()->where('depot_id','=',$depot_id)
						->getVal('name');

	}

	/***
	 * getIdFromOZ
	 * used in Chrginfra_ebgController.class.php
	 * @param integer $id
	 * @return integer
	 */
	function getIdFromOZ($oz_number) {
		return $this->newQuery()->where('dp_depot_id','=',$oz_number)
						->getVal('depot_id');
	}

	/***
	 * getDepotsForChrgInfra
	 * use in ChrginfraController.class.php
	 * @param integer $stationprovider
	 * @param integer $div
	 * @param integer $zspl
	 * @return array of depots
	 */
	function getDepotsForChrgInfra($stationprovider,$div,$zspl)
	{
		$result=$this->newQuery()->where('stationprovider','=',$stationprovider);

		if($zspl) $result->where('zspl_id','=',$zspl);
		if($div) $result->where('division_id','=',$div);

		return $result->get('depot_id,name,dp_depot_id');

	}

	function getEmails($depot_id)
	{
		$result=$this->newQuery()->where('depot_id','=',$depot_id)
		->getVal('emails');
		if(!empty($result))
			return unserialize($result);
		else
			return null;

	}

	function getThirdPartyDepot($depotname,$street,$housenr,$postcode,$place)
	{
		$division=$this->newQuery('divisions')->where('name','LIKE','Drittkunden')->getVal('division_id');

		$depot=$this->newQuery()->where('name','LIKE',$depotname.' Dummy ZSP')
								->where('division_id','=',$division)
								->get('depot_id');
		if(empty($depot))
			return false;
		else if(sizeof($depot)==1)
				return $depot[0]['depot_id'];
		else
		{
			$depot=$this->newQuery()->where('name','LIKE',$depotname.' Dummy ZSP')
								->where('division_id','=',$division)
								->where('street','=',$street)
								->where('housenr','=',$housenr)
								->where('postcode','=',$postcode)
								->where('place','=',$place)
								->get('depot_id');
			if(sizeof($depot)==1)
				return $depot[0]['depot_id'];
			else
				return false;
		}
	}

	function insertThirdPartyDepot($depot)
	{
		$division=$this->newQuery('divisions')->where('name','LIKE','Drittkunden')->getVal('division_id');
		$zspl=$this->newQuery('zspl')->insert(array('division_id'=>$division,'name'=>'Drittkunden '.$depot['name']));
		$depot['name']=$depot['name'].' Dummy ZSP';
		$depot['depot_restriction_id']=NULL;
		$depot['division_id']=$division;
		$depot['zspl_id']=$zspl;
		$depot_id=$this->newQuery()->insert($depot);
		return $depot_id;
	}

}
