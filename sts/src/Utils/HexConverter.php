<?php

namespace App\Utils;

use App\Converter\Convertible;

class HexConverter
{
    /**
     * @var array
     */
    private $strategies;

    /**
     * @param Convertible $strategy
     */
    public function addStrategy(Convertible $strategy): void
    {
        $this->strategies[] = $strategy;
    }

    /**
     * @param string $variableType
     * @param string $value
     * @param int    $bytes
     * @param bool    $parameterBigEndian
     *
     * @return string
     */
    public function convertToHex(
        string $variableType,
        string $value,
        int $bytes,
        bool $parameterBigEndian):
    string
    {
        /** @var Convertible $strategy */
        foreach ($this->strategies as $strategy) {
            if ($strategy->isConvertible($variableType)) {
                return $strategy->convertToHex($value, $bytes, $parameterBigEndian);
            }
        }
    }

    /**
     * @param string $variableType
     * @param string $hex
     * @param int    $bytes
     * @param bool   $parameterBigEndian
     *
     * @return string
     */
    public function convertFromHex(
        string $variableType,
        string $hex,
        int $bytes,
        bool $parameterBigEndian
    ): string
    {
        /** @var Convertible $strategy */
        foreach ($this->strategies as $strategy) {
            if ($strategy->isConvertible($variableType)) {
                return $strategy->convertFromHex($hex, $bytes, $parameterBigEndian);
            }
        }
    }
}