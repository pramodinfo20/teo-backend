<?php


namespace App\Converter;


use App\Enum\Endianness;

trait EndiannessTrait
{
    /**
     * @param string $hex
     * @param bool   $parameterBigEndian
     *
     * @return mixed
     */
    public function calculateEndian(
        string $hex,
        bool $parameterBigEndian
    ): string
    {
        if ($parameterBigEndian) {
            return $hex;
        } else {
            return implode('',array_reverse(str_split($hex, 2)));
        }
    }
}