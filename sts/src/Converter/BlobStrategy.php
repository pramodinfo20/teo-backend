<?php

namespace App\Converter;

class BlobStrategy implements Convertible
{
    use EndiannessTrait;

    /**
     * @var string
     */
    private $strategy = 'blob';

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
     * BLOB ignore endianness - Always Big Endian
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
        return "0x" . $this->calculateEndian($value, true);
    }


    /**
     * BLOB ignore endianness - Always Big Endian
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
        return $this->calculateEndian($hex, true);
    }
}