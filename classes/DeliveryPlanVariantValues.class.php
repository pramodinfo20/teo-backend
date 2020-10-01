<?php
/**
 * DeliveryPlanVariantValues.class.php
 * Class to handle the delivery_plan_variant_values table
 * @author Pradeep Mohan
 */

/**
 * Class to handle delivery_plan_variant_values table
 * Problem : Deutsche Post's DeliveryPlan does not distinguish between the variants B14 and B16. They're the same StreetScooter WORK
 * However, in our database, we used different variant values for B14 and B16.
 * Solution : Create a table of external_variant_value vs internal_variant_value for these models. Thus Deutsche Post handles only StreetScooter WORK
 * variant, however, we can distinguish between these two models when planning the Auslieferung
 *
 */
class DeliveryPlanVariantValues extends LadeLeitWarte {
    protected $dataSrcPtr;
    protected $tableName;

    function __construct(DataSrc $dataSrcPtr, $tableName = null) {
        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = $tableName;

    }

    /***
     * gets all the available External Variant Values
     * Used in ZentraleController.class.php
     * @return array
     */
    public function getAllExternal() {
        return $this->newQuery()->groupBy('external_variant_value,variant_external_name')->get('external_variant_value,variant_external_name');

    }

    /**
     * get all the internal values for this external value
     * @param integer $external_value
     * @return json of just the internal values
     */
    public function getInternalValues($external_value) {
        return $this->newQuery()->where('external_variant_value', '=', $external_value)->groupBy('external_variant_value')->getOne('external_variant_value,json_agg(internal_variant_value) as internal_variant_values');
    }

    /**
     * get the external value for this internal value
     * @param integer $internal_value
     * @return one single value which is the external value for this internal value
     */
    public function getExternalValue($internal_value) {
        return $this->newQuery()->where('internal_variant_value', '=', $internal_value)->getVal('external_variant_value');
    }

}
