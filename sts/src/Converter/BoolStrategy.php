<?php

namespace App\Converter;

class BoolStrategy implements Convertible
{
    use EndiannessTrait;

    /**
     * @var string
     */
    private $strategy = 'bool';

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
        return "0x" . $this->calculateEndian((filter_var($value, FILTER_VALIDATE_BOOLEAN)) ? "01" : "00",
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