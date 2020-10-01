<?php

namespace App\Converter;

interface Convertible
{
    public const SERVICE_TAG = 'converters';

    public function isConvertible(string $variableType): bool;

    /**
     * @param string $value
     * @param int    $bytes
     * @param bool   $parameterBigEndian
     *
     * @return mixed
     */
    public function convertToHex(
        string $value,
        int $bytes,
        bool $parameterBigEndian
    ): string;

    /**
     * @param string $hex
     * @param int    $bytes
     * @param bool   $parameterBigEndian
     *
     * @return mixed
     */
    public function convertFromHex(
        string $hex,
        int $bytes,
        bool $parameterBigEndian
    ): string;

    /**
     * @param string $hex
     * @param bool   $parameterBigEndian
     *
     * @return mixed
     */
    public function calculateEndian(
        string $hex,
        bool $parameterBigEndian
    ): string;
}
