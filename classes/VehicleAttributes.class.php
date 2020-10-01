<?php
/**a
 * VehicleAttributes.class.php
 * Klasse für alle vehicles
 * @author Pradeep Mohan
 */

/**
 * Class to handle vehicles
 *
 */
class VehicleAttributes extends LadeLeitWarte {
    protected $dataSrcPtr;
    protected $tableName;
    //variants which need to be in the database but do not need to be shown anywhere in the webinterface since we do not produce them anymore
    protected $obsoleteVariants;

    function __construct(DataSrc $dataSrcPtr, $tableName = null) {
        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = "vehicle_attributes";
        $this->obsoleteVariants = array("vor 50er", "A12", "10er", "30er", "50er");


    }

    function getFromName($attributename) {
        return $this->newQuery()->where('name', 'LIKE', $attributename)->getOne('*');
    }

    function getAttributeValuesFor($attribute_name, $extraoptions = array('showObsoleteVariants' => false)) {
        $attribute = $this->getFromName($attribute_name);

        if ($attribute) {
            $result = $this->newQuery()->where('vehicle_attributes.attribute_id', '=', $attribute['attribute_id'])
                ->join('vehicle_attribute_values', 'vehicle_attributes.attribute_id=vehicle_attribute_values.attribute_id', 'INNER JOIN');

            if ($extraoptions['showObsoleteVariants'] === false)
                $result->where('vehicle_attribute_values.value', 'NOT IN', $this->obsoleteVariants);

            return $result->get('vehicle_attributes.name,vehicle_attribute_values.value,vehicle_attributes.attribute_id,vehicle_attribute_values.value_id');

        }
    }


}
