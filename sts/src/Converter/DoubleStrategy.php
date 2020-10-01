<?php

namespace App\Converter;

class DoubleStrategy implements Convertible
{
    use EndiannessTrait;

    /**
     * @var string
     */
    private $strategy = 'double';

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
        // TODO: Implement convertToHex() method.
    }

    public function convertFromHex(
        string $hex,
        int $bytes,
        bool $parameterBigEndian
    ): string
    {
        // TODO: Implement convertFromHex() method.
    }
}