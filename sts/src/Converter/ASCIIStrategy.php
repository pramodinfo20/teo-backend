<?php

namespace App\Converter;

class ASCIIStrategy implements Convertible
{
    use EndiannessTrait;

    /**
     * @var string
     */
    private $strategy = 'ascii';
    
    /**
     * @param string $variableType
     *
     * @return bool
     */
    public function isConvertible(string $variableType): bool
    {
        return $variableType === $this->strategy;
    }

    /**
     * ASCII ignore endianness - Always Big Endian
     * @param string $value
     * @param int    $bytes
     * @param bool   $parameterBigEndian
     *
     * @return string
     */
    public function convertToHex(
        string $value,
        int $bytes,
        bool $parameterBigEndian
    ): string
    {
        return "0x" . $this->calculateEndian(implode("", array_map(function ($decimalValue)
            {
                $hex = dechex(ord($decimalValue));
                return str_pad($hex, 2, "0", STR_PAD_LEFT);
            }, str_split($value))), true);
    }

    /**
     * ASCII ignore endianness - Always Big Endian
     * @param string $hex
     * @param int    $bytes
     * @param bool   $parameterBigEndian
     *
     * @return string
     */
    public function convertFromHex(
        string $hex,
        int $bytes,
        bool $parameterBigEndian
    ): string
    {
        return $this->calculateEndian(implode("", array_map(function ($hexValue)
        {
            return chr(hexdec($hexValue));
        }, str_split($hex, 2))), true);
    }
}