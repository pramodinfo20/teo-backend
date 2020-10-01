<?php
/**
 * DeliveryToDivisions.class.php
 * Klasse für delivery_to_divisions Tabelle
 * @author Pradeep Mohan
 */

/**
 * Class to handle delivery_to_divisions plan table
 *
 */
class DeliveryToDivisions extends LadeLeitWarte {
    protected $dataSrcPtr;
    protected $tableName;

    function __construct(DataSrc $dataSrcPtr, $tableName = null) {
        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = $tableName;

    }

    /**
     * used only in SalesController.class.php and FuhrparksteuerController
     * @param integer $division_id
     * @param integer $deliveryyear
     * @param interger $variant_value
     * @return array of results
     */
    function getOnePendingForDivisionYearVariant($division_id, $deliveryyear, $variant_value = null) {
        $query = $this->newQuery()->where('delivery_year', '=', $deliveryyear)
            ->where('division_id', '=', $division_id)
            ->where('delivery_quantity-vehicles_delivered_quantity', '>', 0)
            ->orderBy('delivery_week');

        if ($deliveryyear == 2017)
            $query->where('substring(delivery_week from 3 for 2)::int', '>=', 26);
        if (isset($variant_value))
            $query->where('variant_value', '=', $variant_value);

        return $query->getOne('delivery_id,division_id,delivery_week,delivery_quantity,vehicles_delivered_quantity,vehicles_delivered');
    }

    /***
     * gets pending deliveries by division
     * Used in SalesController.class.php
     * @param integer $deliveryyear
     * @param integer $variant_value
     */
    function getPendingForYearVariant($deliveryyear = null, $variant_value = null, $selected_div = null) {
        if (!isset($deliveryyear))
            $deliveryyear = date('Y');

        $pending_thismonth = array();
        $i = 1;
        $month = date('m');
        //@todo do not think we need to go through this loop .. just get for this month and it should be enough

        $cnt = 1;
        while (empty($pending_thismonth) && $cnt <= 50) {
            if ($deliveryyear == 2017 && $month < 7) break;
            if ($deliveryyear == 2016) break;
            $weeks = $this->getWeeksFromYearMonth(date('Y-' . $month . '-01'), true);

            $lastweek = $weeks[sizeof($weeks) - 1];
            $lastweeknum = str_replace('kw', '', $lastweek);

            $moreweeks = $this->getWeeksForFPS($deliveryyear, $lastweeknum);
            if (!empty($moreweeks)) $weeks = array_merge($weeks, $moreweeks);

            //start quick fix for B16 delivery in March 2017
            /*$thistime=strtotime("first day of +1 months");
            $nextmonth_weeks=$this->getWeeksFromYearMonth(date('Y-m-01',$thistime),true);
            $weeks=array_merge($weeks,$nextmonth_weeks);*/
            //end quick fix for B16 delivery in March 2017
            $first_week = $weeks[0];

            $pending_thismonth = $this->newQuery()->where('delivery_year', '=', $deliveryyear)
                ->where('delivery_quantity-vehicles_delivered_quantity', '>', 0)
                ->where('delivery_week', 'IN', $weeks)
                ->orderBy('delivery_id'); //remove order by delivery_week since kw6 > kw10... string compare..
            if ($variant_value && is_array($variant_value))
                $pending_thismonth->where('variant_value', 'IN', $variant_value);
            else if ($variant_value && !is_array($variant_value))
                $pending_thismonth->where('variant_value', '=', $variant_value);

            if (!empty($selected_div)) $pending_thismonth->where('division_id', '=', $selected_div);

            $pending_thismonth = $pending_thismonth->get('delivery_id,division_id,delivery_week,delivery_year,delivery_quantity,vehicles_delivered_quantity,vehicles_delivered,variant_value');

            $thistime = strtotime("first day of - $i months"); // http://php.net/manual/en/function.strtotime.php#107331
            $month = date('m', $thistime);
            $i++;
            $cnt++;
        }

        // pending_previous_months was for the deliveries thatcould be made from previous months, but which do not have auslieferungsplan saved for this month
        //this is now obsolete since we save for all divisions in one go!
// 		$pending_prevmonths=$this->newQuery()->where('delivery_year','=',$deliveryyear)
// 			->where('delivery_quantity-vehicles_delivered_quantity','>',0)
// 			->where('delivery_week','<',$first_week)
// 			->orderBy('delivery_week,delivery_id')
// 			->get('delivery_id,division_id,delivery_week,delivery_year,delivery_quantity,vehicles_delivered_quantity,vehicles_delivered,variant_value');


// 		foreach($pending_prevmonths as $key=>&$delivery_to_division)
// 		{
// 			$thismonth_division=$this->newQuery()->where('delivery_year','=',$delivery_to_division['delivery_year'])
// 			->where('delivery_week','>=',$first_week)
// 			->where('division_id','=',$delivery_to_division['division_id'])
// 			->getOne('delivery_id');
// 			if(!empty($thismonth_division))
// 				unset($pending_prevmonths[$key]);

// 		}

// 		$result=array_merge($pending_prevmonths,$pending_thismonth);
        $result = $pending_thismonth;
        return $result;
    }

    /**
     * used in SalesController when trying to display the delivery schedule as per week if the delivery_plan is already processed.
     * @param string $yearmonth
     * @param integer $variant_value
     * @return array
     */
    function getForYearMonthVariant($yearmonth, $variant_value, $delivery_year) {

        $weeks = $this->getWeeksFromYearMonth($yearmonth, true);
        return $this->newQuery()->where('delivery_week', 'IN', $weeks)
            ->where('variant_value', '=', $variant_value)
            ->where('delivery_year', '=', $delivery_year)
            ->join('divisions', 'divisions.division_id=delivery_to_divisions.division_id', 'INNER JOIN')
            ->groupBy('delivery_to_divisions.division_id,divisions.name,delivery_to_divisions.priority,delivery_to_divisions.added_timestamp')
            ->orderBy('delivery_to_divisions.priority,delivery_to_divisions.division_id,divisions.name')
            ->get('delivery_to_divisions.division_id,divisions.name,json_object_agg(delivery_week,delivery_quantity) as delivery_quantities,json_object_agg(delivery_week,vehicles_delivered_quantity) as vehicles_delivered_quantity,added_timestamp');
    }

    /***
     * get the calendar week entries for the fpsMail function in SalesController
     * @param string/integer $delivery_year
     */
    function getDeliveriesForNotification($delivery_year) {
        $thisweek = date('W');

        $result = $this->newQuery()->where('substring(delivery_week from 3 for 2)::int', '>=', $thisweek)
            ->where('delivery_notification_email_sent', '=', 'f')
            ->where('delivery_year', '=', $delivery_year)
            ->orderBy('delivery_week')
            ->get('distinct(delivery_week)');
        if (!empty($result)) return array_column($result, 'delivery_week');
        else return array();
    }

    /***
     * Gets all the weeks in the table delivery_to_divisions starting from $startweeknum
     * @param string $delivery_year
     */
    function getWeeksForFPS($delivery_year, $startweeknum, $division_id = null, $variant_value = null) {
        $result = $this->newQuery()->where('substring(delivery_week from 3 for 2)::int', '>', $startweeknum)
            ->where('delivery_year', '=', $delivery_year);
        if ($variant_value)
            $result = $result->where('variant_value', '=', $variant_value);
        if ($division_id)
            $result = $result->where('division_id', '=', $division_id);

        $result = $result->orderBy('delivery_week')
            ->get('distinct(delivery_week)');

        if (!empty($result)) return array_column($result, 'delivery_week');
        else return array();
    }

    /**
     * used to get the sum of already reserved/assigned vehicles to be delivered to divisions
     * Used in SalesController.class.php
     * @param integer $year YYYY format
     * @param string $kweek kwXX format
     * @param integer $variant_value
     */
    function getCountForYearWeekVariant($year, $kweek, $variant_value) {
        return $this->newQuery()
            ->where('variant_value', '=', $variant_value)
            ->where('delivery_year', '=', $year)
            ->where('delivery_week', '=', $kweek)
            ->getVal('sum(delivery_quantity)');

    }

    /**
     * returns one row from delivery_plan table matching the parameters
     *
     * @param integer $division_id
     * @param integer $delivery_year YYYY format
     * @param string $delivery_week kwXX format
     * @param integer $variant_value
     * @return array returns one row from delivery_plan table matching the parameters
     */
    function getForDivisionYearWeekVariant($division_id, $delivery_year, $delivery_week, $variant_value) {
        $result = $this->newQuery()->where('division_id', '=', $division_id)
            ->where('delivery_week', '=', $delivery_week)
            ->where('delivery_year', '=', $delivery_year)
            ->where('variant_value', '=', $variant_value)
            ->getOne('*');
        return $result;
    }

    /**
     * gets the sum of delivery_quantity for this week and this division
     * Used in CronController and SalesController
     * @param string $kweek
     * @param integer $division_id
     */
    function getSumQtyAllVariantsForWeekAndDiv($delivery_week, $division_id) {
        $result = $this->newQuery()->where('division_id', '=', $division_id)
            ->where('delivery_week', '=', $delivery_week)
            ->where('delivery_year', '=', date('Y'))
// 		->groupBy('variant_value')
            ->getVal('sum(delivery_quantity) as sumAllVariants');
        return $result;
    }

    /**
     * gets all the deliveries of all variants for this week and this division
     * Used in SalesController when sending mail to the FPS in function fpsMail()
     * @param string $kweek
     * @param integer $division_id
     */
    function getQtyAllVariantsForWeekAndDiv($delivery_week, $division_id) {
        return $this->newQuery()->where('division_id', '=', $division_id)
            ->where('delivery_week', '=', $delivery_week)
            ->where('delivery_year', '=', date('Y'))
            ->get('*');
    }

    /***
     * Returns the delivery_quantity per week for display in FuhrparksteuerController
     * @param string $delivery_week
     * @param integer $division_id
     * @return array
     */
    function getForWeeksAndDiv($delivery_week, $division_id) {
        return $this->newQuery()->where('division_id', '=', $division_id)
            ->where('delivery_week', 'IN', $delivery_week)
            ->groupBy('division_id,variant_value')
            ->where('delivery_year', '=', date('Y'))
            ->get('variant_value,json_object_agg(delivery_week,delivery_quantity) as byweek,
				json_object_agg(delivery_week,vehicles_delivered_quantity) as byweek_delivered,
				sum(delivery_quantity) as delivery_quantity,
				sum(delivery_quantity-vehicles_delivered_quantity) as to_deliver_cnt');
    }

    function getForWeeksAndDivVariant($delivery_week, $division_id, $variant) {
        return $this->newQuery()->where('division_id', '=', $division_id)
            ->where('delivery_week', 'IN', $delivery_week)
            ->groupBy('division_id,variant_value')
            ->where('variant_value', '=', $variant)
            ->where('delivery_year', '=', date('Y'))
            ->getOne('variant_value,json_object_agg(delivery_week,delivery_quantity) as byweek,
				json_object_agg(delivery_week,vehicles_delivered_quantity) as byweek_delivered,
				sum(delivery_quantity) as delivery_quantity,
				sum(delivery_quantity-vehicles_delivered_quantity) as to_deliver_cnt');
    }

}
