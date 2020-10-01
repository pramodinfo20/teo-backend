<?php

namespace App\Converter;

class IntegerStrategy implements Convertible
{
    use EndiannessTrait;

    /**
     * @var string
     */
    private $strategy = 'integer';

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
    ) : string
    {
        $intValue = (int)$value;
        if ($intValue >= 0) {
            $hex = dechex(bindec(str_pad(decbin($intValue), 8 * $bytes, "0", STR_PAD_LEFT)));
            return "0x" . $this->calculateEndian(str_pad($hex, 2 * $bytes, "0", STR_PAD_LEFT),
                $parameterBigEndian);
        } else {
            $bin = decbin(abs($intValue));
            $binBytes = str_pad($bin, 8 * $bytes, "0", STR_PAD_LEFT);
            $negateBin = array_map(function ($bit)
            {
                return ($bit == "0") ? "1" : "0";
            }, str_split($binBytes));

            $x = implode("", $negateBin);
            $y = "1";
            $result = "";
            $sum = 0;

            $i = strlen($x) - 1;
            $j = strlen($y) - 1;
            while ($i >= 0 || $j >= 0 || $sum == 1) {
                $sum += (($i >= 0) ? ord($x[$i]) -
                    ord('0') : 0);
                $sum += (($j >= 0) ? ord($y[$j]) -
                    ord('0') : 0);

                $result = chr($sum % 2 + ord('0')) . $result;

                $sum = (int)($sum / 2);

                $i--;
                $j--;
            }

            $hex = dechex(bindec($result));

            return "0x" . $this->calculateEndian(str_pad($hex, 2 * $bytes, "0", STR_PAD_LEFT),
                    $parameterBigEndian);
        }
    }

    public function convertFromHex(
        string $hex,
        int $bytes,
        bool $parameterBigEndian
    ): string
    {
        $bin = str_pad(decbin(hexdec($hex)), 8 * $bytes, "0", STR_PAD_LEFT);

        $binArray = str_split($bin);
        $value = ($binArray[0] == 1) ? -pow(2, count($binArray) - 1) : 0;

        for ($i = 1; $i < count($binArray); ++$i) {
            $value += ($binArray[$i]) ? pow(2, count($binArray) - $i - 1) : 0;
        }

        return $this->calculateEndian($value, $parameterBigEndian);
    }
}