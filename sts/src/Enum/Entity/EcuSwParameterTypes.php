<?php

namespace App\Enum\Entity;

class EcuSwParameterTypes
{
    const ECU_PARAMETER_TYPE_HW = 1;
    const ECU_PARAMETER_TYPE_SW = 2;
    const ECU_PARAMETER_TYPE_SERIAL = 3;
    const ECU_PARAMETER_TYPE_PARAMETER = 4;

    /**
     * @var array
     */
    private static $editableTypes = [
        self::ECU_PARAMETER_TYPE_HW,
        self::ECU_PARAMETER_TYPE_SW,
        self::ECU_PARAMETER_TYPE_SERIAL
    ];

    public static function getEditableTypes(): array
    {
        return self::$editableTypes;
    }
}