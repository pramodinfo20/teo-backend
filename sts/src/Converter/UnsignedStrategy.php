<?php

namespace App\Converter;

class UnsignedStrategy implements Convertible
{
    use EndiannessTrait;

    /**
     * @var string
     */
    private $strategy = 'unsigned';

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
        $hex = dechex((int)$value);
        return "0x" . $this->calculateEndian(str_pad($hex, 2 * $bytes, "0", STR_PAD_LEFT),
            $parameterBigEndian);
    }

    public function convertFromHex(
        string $hex,
        int $bytes,
        bool $parameterBigEndian
    ): string
    {
        return $this->calculateEndian(hexdec($hex), $parameterBigEndian);
    }
}