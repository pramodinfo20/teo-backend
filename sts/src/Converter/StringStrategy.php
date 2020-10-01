<?php

namespace App\Converter;

use ForceUTF8\Encoding;

class StringStrategy implements Convertible
{
    use EndiannessTrait;

    /**
     * @var string
     */
    private $strategy = 'string';

    /**
     * @param string $variableType
     *
     * @return bool
     */
    public function isConvertible(string $variableType): bool
    {
        return $variableType === $this->strategy;
    }

    public function convertToHex(
        string $value,
        int $bytes,
        bool $parameterBigEndian
    ): string
    {
        $ut8Split = function ($str, $len = 1)
        {
            $arr = array();
            $strLen = mb_strlen($str, 'UTF-8');
            for ($i = 0; $i < $strLen; $i++)
            {
                $arr[] = mb_substr($str, $i, $len, 'UTF-8');
            }
            return $arr;
        };

        return "0x" . $this->calculateEndian(str_pad(implode("", array_map(function ($utf8Value) use ($bytes)
            {
                $hex = unpack("H*", $utf8Value);
                return $hex[1];
            }, $ut8Split(Encoding::toUTF8($value)))),  2 * $bytes, "0", STR_PAD_LEFT), $parameterBigEndian);
    }

    public function convertFromHex(
        string $hex,
        int $bytes,
        bool $parameterBigEndian
    ): string
    {
        return $this->calculateEndian(implode("", array_map(function ($hexValue)
        {
            return pack("H*", $hexValue);
        }, str_split($hex, $bytes * 2))), $parameterBigEndian);
    }
}