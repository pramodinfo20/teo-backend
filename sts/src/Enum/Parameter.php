<?php

namespace App\Enum;

class Parameter
{
    const LINKING_TYPE_DEFAULT = 1;
    const LINKING_TYPE_CONSTANT = 2;
    const LINKING_TYPE_GLOBAL_PARAMETER = 3;
    const LINKING_TYPE_DYNAMIC_VALUE = 4;
//    const LINKING_TYPE_COC_PARAMETER = 5;

    const LINKING_TYPE_DEFAULT_NAME = 'Default';
    const LINKING_TYPE_CONSTANT_NAME = 'Constant';
    const LINKING_TYPE_GLOBAL_PARAMETER_NAME = 'Global Parameter';
    const LINKING_TYPE_DYNAMIC_VALUE_NAME = 'Dynamic';
//    const LINKING_TYPE_COC_PARAMETER_NAME = 'CoC';

    /**
     * Map string to int linking type
     *
     * @param string $linkingType
     *
     * @return int
     */
    public static function getLinkingTypeByName(string $linkingType): int
    {
        switch ($linkingType) {
            case self::LINKING_TYPE_DEFAULT_NAME:
            default:
                return self::LINKING_TYPE_DEFAULT;
            case self::LINKING_TYPE_CONSTANT_NAME:
                return self::LINKING_TYPE_CONSTANT;
            case self::LINKING_TYPE_GLOBAL_PARAMETER_NAME:
                return self::LINKING_TYPE_GLOBAL_PARAMETER;
            case self::LINKING_TYPE_DYNAMIC_VALUE_NAME:
                return self::LINKING_TYPE_DYNAMIC_VALUE;
/*            case self::LINKING_TYPE_COC_PARAMETER_NAME:
                return self::LINKING_TYPE_COC_PARAMETER;*/
        }
    }

    /**
     * Map int to string linking type
     *
     * @param int $linkingType
     *
     * @return string
     */
    public static function getLinkingTypeById(int $linkingType): string
    {
        switch ($linkingType) {
            case self::LINKING_TYPE_DEFAULT:
            default:
                return self::LINKING_TYPE_DEFAULT_NAME;
            case self::LINKING_TYPE_CONSTANT:
                return self::LINKING_TYPE_CONSTANT_NAME;
            case self::LINKING_TYPE_GLOBAL_PARAMETER:
                return self::LINKING_TYPE_GLOBAL_PARAMETER_NAME;
            case self::LINKING_TYPE_DYNAMIC_VALUE:
                return self::LINKING_TYPE_DYNAMIC_VALUE_NAME;
/*            case self::LINKING_TYPE_COC_PARAMETER:
                return self::LINKING_TYPE_COC_PARAMETER_NAME;*/
        }
    }
}