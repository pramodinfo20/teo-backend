<?php

class ACtion_Uebergabeprotokoll extends AClass_Base {
    function __construct(PageController $pageController) {
        parent::__construct($pageController);
        $this->vehiclesPtr = $this->controller->GetObject("vehicles");

        $this->fn_template = $_SERVER['STS_ROOT'] . '/doctemplate/uebergabeprotokoll.fods';

        if (!file_exists('/tmp/gen_pdf'))
            mkdir('/tmp/gen_pdf');
    }

    function Execute() {
        global $CvsDataRegex;

        $qry = $this->vehiclesPtr->newQuery();

        $this->mode = $_REQUEST['mode'];
        if (preg_match('/^[0-9][0-9,]*$/', $_REQUEST['vlist'])) {
            $vehicle_ids = explode(',', $_REQUEST['vlist']);
            $qry = $qry->where('vehicle_id', 'in', $vehicle_ids);
        } else
            if (preg_match($CvsDataRegex['vin'], $_REQUEST['vin_from']) && preg_match($CvsDataRegex['vin'], $_REQUEST['vin_to'])) {
                $qry = $qry->where('vin', '>=', $_REQUEST['vin_from'])->where('vin', '<=', $_REQUEST['vin_to']);
            } else
                return;


        $this->vehicles = $qry
            ->join('vehicles_sales', 'using(vehicle_id)')
            ->join('penta_numbers', 'using(penta_number_id)')
            ->join('colors', 'vehicles.color_id = colors.color_id')
            ->get('vehicle_id,vehicles.vin, penta_number, colors.name,
                            vehicles_sales.production_date, vehicles_sales.delivery_date, vehicles_sales.shipping_date
                            vehicles_sales.qs_user', 'vehicle_id');
    }

    function WriteHtmlPage($displayheader, $displayfooter) {
        if (!$this->vehicles || !count($this->vehicles))
            return;

        $pdf_files = [];

        foreach ($this->vehicles as $vehicle_id => $vehicle) {
            $fin = fopen($this->fn_template, 'r');
            $fcontent = fread($fin, filesize($this->fn_template));
            $tmpName = "/tmp/gen_pdf/UEB{$vehicle['vehicle_id']}";
            $fout = fopen("$tmpName.fods", 'w+');


            $one_day = 86400;
            $abholdatum = to_locale_date($vehicle['shipping_date'], date('d.m.Y', time() + $one_day));
            $lieferdatum = to_locale_date($vehicle['delivery_date'], date('d.m.Y', time() + 2 * $one_day));

            $replacevals = array(
                'VIN_HERE_VIN' => $vehicle['vin'],
                'DA_HERE_DA' => $abholdatum,
                'PENTA_ARTICLE' => $vehicle['penta_number'],
            );


            $fcontent = str_replace(array_keys($replacevals), array_values($replacevals), $fcontent);

            fwrite($fout, $fcontent);
            fclose($fin);
            fclose($fout);

            exec("libreoffice --convert-to pdf:writer_pdf_Export $tmpName.fods --outdir /tmp/gen_pdf --headless");
            unlink("$tmpName.fods");
            $pdf_files[] = "$tmpName.pdf";
        }

        if (count($this->vehicles) > 1) {
            $datestr = date('Y-m-j_H_i');
            $pdf_filename = "/tmp/gen_pdf/protokoll_$datestr.pdf";
            $usr_filename = "uebergabeprotokoll-$datestr.pdf" .
                $pdf_list = implode(' ', $pdf_files);
            exec("pdftk $pdf_list cat output $pdf_filename");

            foreach ($pdf_files as $file)
                unlink($file);
        } else {
            $pdf_filename = trim($pdf_files[0]);
            $usr_filename = "uebergabeprotokoll-{$this->vehicles[0]['vin']}.pdf";
        }


        $pdf_size = filesize($pdf_filename);
        if ($pdf_size) {
            header('Pragma: public');
            if ($_REQUEST['mode'] == 'view') {
                header('Content-Disposition: inline; filename="' . $usr_filename . '"');
            } else {
                header('Content-Description: File Transfer');
                header('Content-Disposition: attachment; filename="' . $usr_filename . '"');
            }
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Content-Length: ' . $pdf_size);
            header('Content-Type: application/pdf');

            readfile($pdf_filename);
            unlink($pdf_filename);
            exit;
        }
    }
}

?>