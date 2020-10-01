<?php

namespace App\Utils;

use RuntimeException;

class Dictionary
{
    /**
     * Replace default array into dictionary array
     *
     * @param array       $array
     * @param string      $key
     * @param string|null $otherIdentifierKey
     *
     * @return array
     */
    public static function transformToDictionary(array $array, string $key, string $otherIdentifierKey = null): array
    {
        $transformed = [];

        foreach ($array as $index => $item) {
            if ($otherIdentifierKey) {
                $key = $otherIdentifierKey;
            }

            $getIdMethod = 'get' . ucfirst($key);

            if (!method_exists($item, $getIdMethod))
                throw new RuntimeException('Dictionary Transformator'); /* TODO: Fix exception message */

            $transformed[$item->{$getIdMethod}()] = $item;
        }

        return $transformed;
    }
}