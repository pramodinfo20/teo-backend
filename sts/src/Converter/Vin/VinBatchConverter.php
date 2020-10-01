<?php

namespace App\Converter\Vin;

class VinBatchConverter
{
    public static function convertSeriesToVinBatch($series) : string
    {
        $iBatch = intval($series);
        $vinBatch = chr(64 + ($iBatch >= 9 ? $iBatch + 1 : $iBatch));

        return $vinBatch;
    }
}