<?php

class ACtion_Auslieferungsliste extends AClass_Base
{


    function __construct(PageController $pageController)
    {

        parent::__construct($pageController);
        $this->vehiclesPtr = $this->controller->GetObject("vehicles");

        $this->fn_template = $_SERVER['STS_ROOT'] . '/doctemplate/uebergabeprotokoll.fods';

        if (! file_exists('/tmp/gen_pdf'))
            mkdir('/tmp/gen_pdf');

    }


    function Execute()
    {

        $this->mode = $_REQUEST['mode'];
        $vehicle_ids = explode(',', $_REQUEST['vlist']);

        $this->vehicles = $this->vehiclesPtr->newQuery()
            ->where('vehicle_id', 'in', $vehicle_ids)
            ->join('vehicles_sales', 'using(vehicle_id)')
            ->join('penta_numbers', 'using(penta_number_id)')
            ->join('colors', 'vehicles.color_id = colors.color_id')
            ->get('vehicle_id,vehicles.vin, penta_number, colors.name,
                            vehicles_sales.production_date,vehicles_sales.qs_user', 'vehicle_id');

    }


    function WriteHtmlPage($displayheader, $displayfooter)
    {

        if (! $this->vehicles || ! count($this->vehicles))
            return;

        $pickup = '';

        // table headings
        $header = array(
            'Pos.',
            'VIN'
        );
        // Column widths
        $w = array(
            10,
            30,
            55,
            45,
            40
        );

        $pickup_pdf = new FPDF();

        $fill = false;
        $pickup_pdf->SetFillColor(241, 241, 241);
        $cnt = 1;

        $cnt = 1;
        $pickup_pdf->AddPage();

        $pickup_pdf_break = false;
        $pickup_pdf_header = true;

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
            exit();
        }

    }

}
?>