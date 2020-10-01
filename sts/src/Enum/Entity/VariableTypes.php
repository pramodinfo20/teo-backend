<?php

namespace App\Enum\Entity;

class VariableTypes
{
    const VARIABLE_TYPE_ASCII = 1;
    const VARIABLE_TYPE_STRING = 2;
    const VARIABLE_TYPE_BLOB = 3;
    const VARIABLE_TYPE_INTEGER = 4;
    const VARIABLE_TYPE_UNSIGNED = 5;
    const VARIABLE_TYPE_DOUBLE = 6;
    const VARIABLE_TYPE_BOOLEAN = 7;
    const VARIABLE_TYPE_BIGINTEGER = 8;
    const VARIABLE_TYPE_DATE = 9;
    const VARIABLE_TYPE_SIGNED = 10;

    private static $availableVariableTypes = [
        self::VARIABLE_TYPE_ASCII => 'ASCII',
        self::VARIABLE_TYPE_STRING => 'string',
        self::VARIABLE_TYPE_BLOB => 'blob',
        self::VARIABLE_TYPE_INTEGER => 'integer',
        self::VARIABLE_TYPE_UNSIGNED => 'unsigned',
        self::VARIABLE_TYPE_DOUBLE => 'double',
        self::VARIABLE_TYPE_BOOLEAN => 'bool',
        self::VARIABLE_TYPE_BIGINTEGER => 'biginteger',
        self::VARIABLE_TYPE_DATE => 'date',
        self::VARIABLE_TYPE_SIGNED => 'signed'
    ];

    public static function getVariableTypeByName(string $variableType): ?int
    {
        $flippedArray = array_flip(self::$availableVariableTypes);

        return array_key_exists($variableType, $flippedArray)
            ? $flippedArray[$variableType]
            : null;
    }
}