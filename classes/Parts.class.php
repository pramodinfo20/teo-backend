<?php
/**
 * parts.class.php
 * Klasse für alle database
 * @author Pradeep Mohan
 */

/**
 * Class to handle depots
 */
class Parts extends LadeLeitWarte {
    protected $dataSrcPtr;

    function __construct(DataSrc $dataSrcPtr) {
        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = 'parts';
        $this->batteryGroup = 0;
    }

    /***
     * getForVehicle
     * CommonFunctions_VehicleManagement.class.php, FuhrparksteuerController.class.php, SalesController.class.php
     * @param integer $vehicle_id
     * @return array
     */

    function getPartsGrouped($visible_sales = null, $visible_engg = null) {
        $query = $this->newQuery()
            ->join('part_groups', 'parts.group_id=part_groups.group_id')
            ->orderBy('parts.group_id')
            ->orderBy('parts.name');

        if (isset ($visible_sales))
            $query = $query->where('visible_sales', '=', $visible_sales ? 't' : 'f');
        if (isset ($visible_engg))
            $query = $query->where('visible_engg', '=', $visible_engg ? 't' : 'f');

        $qlResult = $query->get('*');
        $part_groups = [];

        foreach ($qlResult as $part) {
            $group_id = $part['group_id'];
            $group_name = $part['group_name'];
            $part_id = $part['part_id'];

            if (!$this->batteryGroup && preg_match('/^[a-z0-1_]*batter[a-z0-1_]*$/i', $group_name))
                $this->batteryGroup = $group_id;


            if (!isset($part_groups[$group_id]))
                $part_groups[$group_id] = [
                    'group' => [
                        'group_id' => $part['group_id'],
                        'group_name' => $part['group_name'],
                        'allow_none' => $part['allow_none'],
                        'allow_multi' => $part['allow_multi'],
                        'visible_sales' => false,
                        'visible_engg' => false,
                    ],
                    'parts' => []
                ];

            $base = &$part_groups[$group_id];

            $base['parts'][$part_id] = $part;
            if (toBool($part['visible_sales']))
                $base['group']['visible_sales'] = true;
            if (toBool($part['visible_engg']))
                $base['group']['visible_engg'] = true;

        }
        return $part_groups;
    }


    function getHtmlEditControls() {

    }
}