<?php

class AClass_CsvTool_Sales extends AClass_CsvTool {
    function __construct($postName, $forUpdate, $wc_variants = null, $penta_variants = null) {
        parent::__construct($postName, $forUpdate);


        $this->wc_variants = $wc_variants;
        $this->penta_variants = $penta_variants;
    }

    function DefaultHeadlines() {
        return [
            'VIN' => 'vin',
            'FIN' => 'vin',
            'Penta Kennwort' => 'penta_kennwort',
            'IKZ' => 'ikz',
            'code' => 'akz',
            'akz' => 'akz',
            'Kennzeichen' => 'akz',
            'ts' => 'tsnumber',
            'ts num' => 'tsnumber',
            'ts number' => 'tsnumber',
            'TS Nummer' => 'tsnumber',
            'Penta #=TS Nummer' => 'penta+TS',
            'Wc Nummer' => 'windchill',
            'Windchill nummer' => 'windchill',
            'Windchill' => 'windchill',
            'Windchill Variante' => 'windchill',
            'Penta Nummer' => 'penta_number',
            'Penta Variante' => 'penta_number',
            'Vorhaben' => 'vorhaben',
            'zsp' => 'depot',
            'Zugeordnete ZSP' => 'depot',
            'Datum Auslieferung' => 'delivery_date',
            'Auslieferungsdatum' => 'delivery_date',
            'woche auslieferung' => 'delivery_week',
            'ausl.woche ' => 'delivery_week',
            'Auslieferungswoche' => 'delivery_week',
            'coc Nr.' => 'coc',
            'Coc. Nummer' => 'coc',
        ];
    }

    function DefaultDataRegex() {
        include $_SERVER['STS_ROOT'] . "/includes/sts-defines.php";
        return $GLOBALS['CvsDataRegex'];
    }

    // ==============================================================================================


    function CheckForIdentCol($colMap) {
        if (isset ($colMap['vin']))
            return 'vin';

        if (isset ($colMap['penta_kennwort']))
            return penta_kennwort;

        if (isset ($colMap['ikz']))
            return ikz;

        if (isset ($colMap['code']))
            return code;
    }
    // ==============================================================================================
}

?>
