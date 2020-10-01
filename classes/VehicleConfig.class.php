<?php
/**
 * VehicleConfig.class.php
 * Klasse für Vehicle ConfigurationController
 * @author Pradeep Mohan
 */

/**
 * Class to handle vehicle configuration table
 *
 */
class VehicleConfig extends LadeLeitWarte {
    protected $dataSrcPtr;
    protected $tableName;

    function __construct(DataSrc $dataSrcPtr, $tableName = null) {
        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = "vehicle_configuration";

    }

    function getVehicleConfiguration($vid, $showconfig_timestamp) {

        return $this->newQuery()
            ->where('vehicle_configuration.timestamp', '<', $showconfig_timestamp)
            ->where('vehicle_configuration.vehicle_id', '=', $vid)
            ->join('vehicles', 'vehicle_configuration.vehicle_id=vehicles.vehicle_id', 'JOIN')
            ->join('vehicle_attributes', 'vehicle_configuration.attribute_id=vehicle_attributes.attribute_id', 'JOIN')
            ->join('vehicle_attribute_values', 'vehicle_configuration.attribute_id=vehicle_attributes.attribute_id AND vehicle_configuration.value_id=vehicle_attribute_values.value_id', 'JOIN')
            ->join('depots', 'vehicles.depot_id=depots.depot_id', 'JOIN')
            ->orderBy('vehicle_configuration.vehicle_id,vehicle_configuration.attribute_id')
            ->orderBy('vehicle_configuration.timestamp', 'DESC')
            ->get('DISTINCT ON (vehicle_configuration.vehicle_id,vehicle_configuration.attribute_id) vehicle_configuration.vehicle_id,
					vehicle_configuration.timestamp,
					vehicle_configuration.value_id,
					vehicle_configuration.attribute_id,
					vehicle_configuration.description,
					vehicle_configuration.user,
					depots.name AS dname,
					vehicle_attributes.name as aname,
					vehicle_attributes.editable as editable,
					vehicle_attribute_values.partnumber as partnumber,
					vehicle_attribute_values.value,
					vehicles.vin,
					vehicles.code');
    }

}
