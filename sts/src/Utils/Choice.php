<?php

namespace App\Utils;

use RuntimeException;

class Choice
{
    /**
     * Replace default array of objects into choice array
     *
     * @param array  $array
     * @param string $idKey
     * @param string $valueKey
     *
     * @param bool   $unique
     *
     * @return array
     */
    public static function transformToChoice(array $array, string $idKey, string $valueKey, bool $unique = false): array
    {
        $transformed = [];

        foreach ($array as $index => $item) {
            $getIdMethod = 'get' . ucfirst($idKey);
            $getValueMethod = 'get' . ucfirst($valueKey);

            if (!method_exists($item, $getIdMethod))
                throw new RuntimeException('ChoiceType Transformator'); /* TODO: Fix exception message */
            if ($unique) {
                if (!in_array($item->{$getIdMethod}(), $transformed)) {
                    $transformed[$item->{$getValueMethod}()] = $item->{$getIdMethod}();
                }
            } else {
                $transformed[$item->{$getValueMethod}()] = $item->{$getIdMethod}();
            }
        }

        return $transformed;
    }

    /**
     * Replace default array of objects into choice array
     *
     * @param array  $array
     * @param string $key
     * @param bool   $unique
     *
     * @return array
     */
    public static function transformDateTimeToChoice(
        array $array,
        string $key,
        bool $unique = false
    ): array
    {
        $transformed = [];

        foreach ($array as $index => $item) {
            $getter = 'get' . ucfirst($key);

            if (!method_exists($item, $getter))
                throw new RuntimeException('ChoiceType Transformator'); /* TODO: Fix exception message */

            $dateTimeString = $item->{$getter}()->format('Y-m-d') . " " . $item->{$getter}()->format('H:i');

            if ($unique) {
                if (!in_array($dateTimeString, $transformed)) {
                    $transformed[$dateTimeString] = $dateTimeString;
                }
            } else {
                $transformed[$dateTimeString] = $dateTimeString;
            }
        }

        return $transformed;
    }

    /**
     * Replace default array of objects into choice array
     *
     * @param array  $array
     * @param string $key
     * @param bool   $unique
     *
     * @return array
     */
    public static function transformUserObjectToChoice(
        array $array,
        string $key,
        bool $unique = false
    ): array
    {
        $transformed = [];

        foreach ($array as $index => $item) {
            $getter = 'get' . ucfirst($key);

            if (!method_exists($item, $getter))
                throw new RuntimeException('ChoiceType Transformator'); /* TODO: Fix exception message */


            if ($unique) {
                if (!in_array($item->{$getter}() . "", $transformed)) {
                    $transformed[$item->{$getter}() . ""] = $item->{$getter}()->getId();
                }
            } else {
                $transformed[$item->{$getter}() . ""] = $item->{$getter}()->getId();
            }
        }

        return $transformed;
    }

    /**
     * Replace default array into choice array
     *
     * @param array  $array
     * @param string $idKey
     * @param string $valueKey
     *
     * @return array
     */
    public static function transformArrayToChoice(array $array, string $idKey, string $valueKey): array
    {
        $transformed = [];

        foreach ($array as $index => $item) {
            if (!array_key_exists($idKey, $item))
                throw new RuntimeException('ChoiceType Transformator'); /* TODO: Fix exception message */

            $transformed[$item[$valueKey]] = $item[$idKey];
        }

        return $transformed;
    }
}