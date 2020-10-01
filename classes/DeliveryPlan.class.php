<?php
/**
 * DeliveryPlan.class.php
 * Klasse für delivery_plan Tabelle
 * @author Pradeep Mohan
 */

/**
 * Class to handle production plan table
 *
 * deliveryplan ist eigentlich Mobilitätsplanung
 */
class DeliveryPlan extends LadeLeitWarte {
    protected $dataSrcPtr;
    protected $tableName;

    function __construct(DataSrc $dataSrcPtr, $tableName = null) {
        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = "delivery_plan"; //@todo does tablename have to be declared here or in LadeLeitWarte

    }


    /***
     * Returns all the delivery_plan for this variantvalue
     * Used in ZentraleController
     * @param integer $variantvalue
     * @return array
     */
    function getAllForVariant($variantvalue, $yearmonth) {
        return $this->newQuery()->where('variant', '=', $variantvalue)
            ->where('yearmonth', '>=', $yearmonth)
            ->join('divisions', 'divisions.division_id=delivery_plan.division_id', 'JOIN')
            ->groupBy('divisions.dp_division_id,divisions.name')
            ->orderBy('divisions.dp_division_id')
            ->get('divisions.name,divisions.dp_division_id,json_object_agg(delivery_plan.yearmonth,delivery_plan.quantity) as quantities');
    }
    /****
     * unused function in SalesController, no need to set process_status since all deliveryplans can be reprocessed now
     * @param integer $division_id
     * @param string $yearmonth
     * @param integer $variant_value
     *
     * function setProcessedTrue($division_id,$yearmonth,$variant_value)
     * {
     * $this->newQuery()->where('division_id','=',$division_id)
     * ->where('yearmonth','=',$yearmonth)
     * ->where('variant','=',$variant_value)->update(array('processed_status'),array('TRUE'));
     *
     * }
     */

    /**
     * Used in ZentraleController when updating the Mobilitätsplanung
     * @param integer $division_id
     * @param string $yearmonth
     * @param integer $variant vehicle_variant_value
     * @return array of results
     */
    function getForMonthAndVariant($division_id, $yearmonth, $variant) {
        return $this->newQuery()->where('yearmonth', '=', $yearmonth)
            ->where('variant', '=', $variant)
            ->where('division_id', '=', $division_id)
            ->orderBy('division_id', 'ASC')
            ->getOne('delivery_id,division_id,yearmonth,variant,quantity');

    }

    /**
     * used in CronController.class.php automatic vehicle station assing and SalesController.class.php for the manuell auslieferung function
     * @param unknown $division_id
     * @param unknown $month
     * @param unknown $variant
     */
    function getOnePendingDeliveryPlansVariant($division_id, $variant) {
        return $this->newQuery()
            ->where('division_id', '=', $division_id)
            ->where('variant', '=', $variant)
            ->where('quantity-requirement_met', '!=', 0)
            ->where('yearmonth', '>=', '2017-06-01')
            ->orderBy('yearmonth')
            ->getOne('delivery_id,quantity,requirement_met');
    }

    /**
     * used when showing delivery plan for divisions in SalesController.class.php
     * @param string $monthyear
     * @param integer $variant external variant value of selected model (StreetScooter WORK )
     * @param integer $selected_internal_value the internal variant value of selected variant (StreetScooter WORK B16)
     * @param array $other_internal_values .. internal_values of tje $variant external values, without including the selected internal value (StreetScooter WORK B14)
     * @param array $exclude_divisions exclude these division ids for which the delivery_to_divisions entries have already been saved
     * @return array
     */
    function getUnprocessedDeliveryPlansMonthVariant($monthyear, $variant, $selected_internal_value, $other_internal_values, $exclude_divisions, $weeksfromnow, $delivery_year) {
        $result = $this->newQuery()->join('divisions', 'divisions.division_id=delivery_plan.division_id', 'JOIN')
            ->where('variant', '=', $variant)
            //->where('delivery_plan.quantity','!=',0)  continue here this removes Berlin 2 from the list even though it has pending from October!
            ->orderBy('quantity', 'DESC');

        if (is_array($monthyear)) $result->where('yearmonth', 'IN', $monthyear);
        else  $result->where('yearmonth', '=', $monthyear);

        if (!empty($exclude_divisions))
            $result->where('delivery_plan.division_id', 'NOT IN', $exclude_divisions);

        $unprocessed_divisions = $result->groupBy('divisions.division_id,divisions.name,divisions.dp_division_id')
            ->get('divisions.division_id,divisions.name,divisions.dp_division_id,
												sum(delivery_plan.quantity) as quantity,sum(delivery_plan.quantity) as actual_delivery_plan_quantity');

        $weeks = $this->getWeeksFromYearMonth($monthyear, true);

        $sum = 0;

        foreach ($unprocessed_divisions as $key => &$division) {
            //get pending numbers from the delivery_plan (Mobilitäsplanung) for the StreetScooter WORK (includes StreetScooter WORK B16 + StreetScooter WORK B14)
            if (is_array($monthyear)) $first_month = $monthyear[0];
            else  $first_month = $monthyear;
            $firstweek = str_replace('kw', '', $weeksfromnow[0]);
            $pending = $this->newQuery('delivery_to_divisions')
                ->where('substring(delivery_week from 3 for 2)::int', '<', $firstweek)
                ->where('substring(delivery_week from 3 for 2)::int', '>', 23)//to account for ignoring all vom vormonat quantities before June 2017
                ->where('division_id', '=', $division['division_id'])
                ->where('variant_value', '=', $selected_internal_value)
                ->groupBy('division_id,variant_value')
                ->get('sum(delivery_quantity-vehicles_delivered_quantity) AS pendingqty');


            $pending_from_vehicleid = $this->newQuery('delivery_to_divisions')
                ->where('substring(delivery_week from 3 for 2)::int', '<', $firstweek)
                ->where('substring(delivery_week from 3 for 2)::int', '>', 23)//to account for ignoring all vom vormonat quantities before June 2017
                ->where('division_id', '=', $division['division_id'])
                ->where('variant_value', '=', $selected_internal_value)
                ->get('delivery_id,vehicles_delivered,delivery_quantity');
            $cnt = 0;
            $todeliver = 0;
            if (!empty($pending_from_vehicleid) || $pending[0]['pendingqty'] != 0) {
                foreach ($pending_from_vehicleid as $deliveryt) {

                    if (unserialize($deliveryt['vehicles_delivered']))
                        $cnt += sizeof(unserialize($deliveryt['vehicles_delivered'])) . '+';
                    $todeliver += $deliveryt['delivery_quantity'];

                }
                if ($pending[0]['pendingqty'] != ($todeliver - $cnt) || $pending[0]['pendingqty'] < 0)
                    echo '<br>' . $division['name'] . ' ' . $pending[0]['pendingqty'] . ' from vehicles' . ($todeliver - $cnt) . '<br> ';
            }


            /*
            $pending=$this->newQuery()
                    ->where('variant','=',$variant)
                    ->where('yearmonth','<',$first_month)
                    ->where('yearmonth','>=','2017-06-01')
                    ->where('division_id','=',$division['division_id'])
                    ->where('delivery_plan.quantity-delivery_plan.requirement_met','!=',0)
                    ->get('delivery_plan.yearmonth,(delivery_plan.quantity-delivery_plan.requirement_met) as pendingqty');
            */
            //if we are saving the Auslieferungsplan for StreetScooter WORK B16, then we need to deduct the quantity already satisfied with  StreetScooter WORK B14
            foreach ($other_internal_values as $internal_value) {
                $existing = $this->newQuery('delivery_to_divisions')->where('delivery_week', 'IN', $weeks)
                    ->where('division_id', '=', $division['division_id'])
                    ->where('variant_value', '=', $internal_value)
                    ->groupBy('division_id,variant_value')
                    ->getOne('sum(delivery_quantity) as sumdq,sum(vehicles_delivered_quantity) as sumvdq');
                $division['quantity'] -= ($existing['sumdq'] - $existing['sumvdq']);

            }

            $division['pendingqty'] = 0;

            if (!empty($pending)) {
                //loop through all the pending quantites from the previous months and add them to the current month's Vormonat count
                foreach ($pending as $singlepending) {
                    $division['pendingqty'] += $singlepending['pendingqty'];
                }

                //add Mobilitätsplanung quantity for this month & Vom Vormonat quantity from all the previous months
                $division['quantity'] += $division['pendingqty'];
            }
            $sum += $division['quantity'];

            //if a division gets 0 vehicles, then do not save the Ausliferungsplan for this division!
            if ($division['quantity'] == 0) {
                unset($unprocessed_divisions[$key]);
            }
        }

        return array('data' => $unprocessed_divisions, 'sum' => $sum);


    }

    function getDeliveryPlanResetVehicleToProduction($delivery_week, $delivery_year, $division_id, $external_post_variant_value) {
        return $this->newQuery()
            ->where('division_id', '=', $division_id)
            ->where('variant', '=', $external_post_variant_value)
            ->where('requirement_met', '>', 0)
            ->orderBy('yearmonth', 'DESC')
            ->getOne('*');
    }

    function getDeliveryPlanForWeekYearDivisionVariant($delivery_week, $delivery_year, $division_id, $external_post_variant_value) {
        //we have the delivery week, but we do not now which month this week falls in.
        //The mobilitätsplanung (delivery_plan) has entries only according to 2016-08-01.. year month.. so we need to find
        //the month to update the requirement_met in the delivery_plan table

        $week = (int)str_replace('kw', '', $delivery_week);
        $getmonth = '';
        $month = date('m') - 1; // go back one month from this month.. so that we get the first month to which a calendar week belongs
        $cnt = 1;
        while (empty($getmonth)) {
            $month = str_pad($month, 2, '0', STR_PAD_LEFT);
            $weeks = $this->getWeeksFromYearMonth(date($delivery_year . '-' . $month . '-01'), true);

            if (in_array($delivery_week, $weeks)) {
                $getmonth = $month;

                $deliveryPlanZentrale = $this->newQuery()
                    ->where('division_id', '=', $division_id)
                    ->where('variant', '=', $external_post_variant_value)
                    ->where('yearmonth', '=', date('Y-' . $month . '-01'))
                    ->getOne('*');
                print_r($deliveryPlanZentrale);
                if (!empty($deliveryPlanZentrale)) return $deliveryPlanZentrale;

                //in case of a calendar week which overlaps two months, then we check to see if there's still any pending requirements for
                //the first month.. if not, then we need to move to the next month, so set $getmonth to empty again

                if (empty($deliveryPlanZentrale)) {
                    $getmonth = '';
                    $month++;
                }

            } else {
                $month++;

            }
            $cnt++;

            if ($month > 12 || $month < 1 || $cnt > 5) {
                echo 'cannot retreive month or check delivery_plan table';
                return false;
            }

        } //end of while

    }

}
