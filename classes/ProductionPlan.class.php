<?php
/**
 * ProductionPlan.class.php
 * Klasse für production_plan Tabelle
 * @author Pradeep Mohan
 */

/**
 * Class to handle production plan table
 *
 */
class ProductionPlan extends LadeLeitWarte {
    protected $dataSrcPtr;
    protected $tableName;

    function __construct(DataSrc $dataSrcPtr, $tableName = null) {
        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = $tableName;

    }

    /**
     * used in SalesController.class.php to fetch production plan data for all variant_values
     *
     *
     * @param string $yearmonth
     */
    function getForYearMonth($yearmonth) {
        $weeks = $this->getWeeksFromYearMonth($yearmonth, true);

        return $this->newQuery()->where('production_week', 'IN', $weeks)
            ->where('production_year', '=', date('Y', strtotime($yearmonth)))
            ->groupBy('production_week')
            ->get('production_week,json_object_agg(variant_value,production_quantity) as variant_quantities');

    }

    /**
     * used in SalesController.Class.php->saveDivisionsDeliveryPlan()
     * @param string $yearmonth
     * @param integer $variant_value
     */

    function getGroupedForYearMonthVariant($yearmonth, $variant_value, $productionyear) {
        $weeks = $this->getWeeksFromYearMonthStartingNow($yearmonth, true);
        return $this->newQuery()->where('production_week', 'IN', $weeks)
            ->where('variant_value', '=', $variant_value)
            ->where('production_year', '=', $productionyear)
            ->groupBy('variant_value')
            ->getVal('json_object_agg(production_week,production_quantity-production_to_pool_qty)');

    }

    /**
     * used in SalesController.Class.php->showDivisionsDeliveryPlan()
     * @param string $yearmonth
     * @param integer $variant_value
     */


    function getSumYearMonthVariant($yearmonth, $variant_value, $productionyear) {
        $weeks = $this->getWeeksFromYearMonthStartingNow($yearmonth, true);

        return $this->newQuery()->where('production_week', 'IN', $weeks)
            ->groupBy('variant_value')
            ->where('production_year', '=', $productionyear)
            ->where('variant_value', '=', $variant_value)
            ->getVal('sum(production_quantity-production_to_pool_qty) as sqty');
    }


    /**
     * Used in SalesController.Class.php->savedepotassign()
     * @param string $year
     * @param string $kweek for e.g. kw36
     * @param integer $variant_value
     */
    function getQtyForYearVariantWeek($production_year, $kweek, $variant_value) {
        return $this->newQuery()
            ->where('production_week', '=', $kweek)
            ->where('production_year', '=', $production_year)
            ->where('variant_value', '=', $variant_value)
            ->getVal('production_quantity-production_to_pool_qty');
    }


    /**
     * Used in SalesController.Class.php->saveProPlan()
     * @param string $yearmonth
     * @param string $kweek for e.g. kw36
     * @param integer $variant_value
     */
    function getForYearMonthVariantWeek($yearmonth, $kweek, $variant_value) {
        return $this->newQuery()
            ->where('production_week', '=', $kweek)
            ->where('production_year', '=', date('Y', strtotime($yearmonth)))
            ->where('variant_value', '=', $variant_value)
            ->getOne('*');
    }

    function adjustProductionToFleetQty($kw_week, $variant_value, $production_year) {
        $quantity = $this->newQuery()->where('variant_value', '=', $variant_value)
            ->where('production_week', '=', $kw_week)
            ->where('production_year', '=', $production_year)
            ->getVal('production_to_pool_qty');

        return $this->newQuery()->where('variant_value', '=', $variant_value)
            ->where('production_week', '=', $kw_week)
            ->where('production_year', '=', $production_year)
            ->update(array('production_to_pool_qty'), array(++$quantity));
    }


}
