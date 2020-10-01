<?php

class ACtion_Abholschein extends AClass_Base
{

    protected $vehicles = false;

    protected $nach_depots = [];

    protected $workshop_delivery = faLSE;


    function __construct(PageController $pageController)
    {

        parent::__construct($pageController);
        $this->vehiclesPtr = $this->controller->GetObject("vehicles");

        if (! file_exists('/tmp/gen_pdf'))
            mkdir('/tmp/gen_pdf');

    }


    function Execute()
    {

        $this->mode = $_REQUEST['mode'];
        $depot_id = intVal($_REQUEST['depot']);
        $vList = [];
        $csvVehicles = explode(',', $_REQUEST['vlist']);
        foreach ($csvVehicles as $id) {
            if (intval($id) > 0)
                $vList[] = intval($id);
        }

        if (count($vList)) {
            $this->vehicles = $this->vehiclesPtr->newQuery()
                ->join('vehicles_sales', 'using(vehicle_id)')
                ->join('penta_numbers', 'using(penta_number_id)')
                ->join('colors', 'vehicles.color_id = colors.color_id')
                ->join('depots', 'using(depot_id)')
                ->join('park_lines', 'using(park_id)', 'left join')
                ->join('gps_maps', 'using(map_id)', 'left join')
                ->where('vehicle_id', 'in', $vList)
                ->orderBy('depots.name')
                ->get_no_parse('vehicle_id,vehicles.vin, penta_number, colors.name,
                        vehicles.depot_id, dp_depot_id, depots.name as dname,
                        park_lines.ident as park_line, gps_maps.name as sub_location, park_position,
                        vehicles_sales.production_date, vehicles_sales.delivery_date,
                        vehicles_sales.qs_user', 'vehicle_id');
        }

        if ($this->vehicles)
            foreach ($this->vehicles as $vehicle_id => $set) {
                $depot_id = $set['depot_id'];
                $depot_name = $set['dname'];

                if (! isset($this->nach_depots[$depot_id]))
                    $this->nach_depots[$depot_id] = [
                        'name' => $depot_name,
                        'liste' => [],
                        'dp_depot_id' => $set['dp_depot_id']
                    ];

                $this->nach_depots[$depot_id]['liste'][] = $vehicle_id;
            }

    }


    function WriteHtmlPage($displayheader, $displayfooter)
    {

        if (! $this->vehicles || ! count($this->vehicles))
            return;
        $pickup_pdf_header = true;

        $header = array(
            'Pos.',
            'VIN',
            'Position'
        );
        // Column widths
        $w = array(
            10,
            55,
            40
        );
        $vins = '';

        $pickup_pdf = new FPDF();
        $pickup_pdf->SetFillColor(241, 241, 241);

        $pdf_files = [];
        $prodlocs = [];
        $cnt = 0;

        foreach ($this->nach_depots as $depot_id => $depot) {
            $pickup_pdf->AddPage();

            foreach ($depot['liste'] as $vehicle_id) {
                $vehicle = &$this->vehicles[$vehicle_id];
                $prev_pickup = $pickup;
                $vins .= '-' + $vehicle['vin'];

                if (isset($vehicle['production_location']) && ! empty($vehicle['production_location'])) {
                    $prod_id = $vehicle['production_location'];
                    if (! isset($prodlocs[$prod_id]))
                        $prodlocs[$prod_id] = $this->ladeLeitWartePtr->depotsPtr->newQuery()
                            ->where('depot_id', '=', $prod_id)
                            ->getVal('name');
                    $production_loc_name = $prodlocs[$prod_id];
                    $pickup = iconv('UTF-8', 'windows-1252', 'Abholort: ' . $production_loc_name);
                } else if (isset($vehicle['production_date']) && isset($vehicle['qs_user'])) {
                    if ($vehicle['qs_user'] == - 1)
                        $pickup = iconv('UTF-8', 'windows-1252', 'Abholort: Sts-Pool (Würselen)');
                    else
                        $pickup = iconv('UTF-8', 'windows-1252', 'Abholort: Produktion (Aachen Jülicher Straße)');
                } else
                    $pickup = iconv('UTF-8', 'windows-1252', 'Abholort: Sts-Pool (Würselen)');

                // break every four vehicles within the same depot
                if ((($cnt - 1) % 4 == 0 && $cnt != 1))
                    $pickup_pdf_break = true;

                // if pdf_break is required then show the Abholort, same code repeated below at the end of each depot L1514
                if ($pickup_pdf_break) {
                    $pickup_pdf->Cell(array_sum($w), 0, '', 'T');
                    $pickup_pdf->SetFont('Arial', 'B', 16);
                    $pickup_pdf->Ln();

                    if (! empty($prev_pickup) && $prev_pickup != $pickup)
                        $pickup_pdf->Cell(40, 20, $prev_pickup);
                    else
                        $pickup_pdf->Cell(40, 20, $pickup);
                    $pickup_pdf->Ln();
                    $pickup_pdf->SetFont('Arial', '', 12);
                    $pickup_pdf->Cell(40, 20, 'Abgeholt am ' . '_________________' . ' durch Spedition' . ' _________________________');
                    $pickup_pdf->Ln();
                    $pickup_pdf->Cell(40, 20, 'Unterschrift Fahrer: ');
                    $cnt = 1;
                    $pickup_pdf->AddPage();
                    $pickup_pdf_header = true;
                    $pickup_pdf_break = false;
                }

                if ($pickup_pdf_header) {
                    if ($this->workshop_delivery) {
                        $workshop_id = $this->vehiclesPtr->newQuery('workshop_delivery')
                            ->where('vehicle_id', '=', $vehicle['vehicle_id'])
                            ->getVal('workshop_id');
                        $workshop = $this->vehiclesPtr->newQuery('workshops')
                            ->where('workshop_id', '=', $workshop_id)
                            ->getOne('name,location,street,zip_code');
                        $destination_name = iconv('UTF-8', 'windows-1252', $workshop['name']);
                        $destination_addr_one = $workshop['street'];
                        $destination_addr_two = $workshop['location'] . ' ' . $workshop['zip_code'];
                    } else {
                        $destination_name = iconv('UTF-8', 'windows-1252', $depot['name']);
                        $destination_addr_one = $depot['street'] . ',' . $depot['housenr'];
                        $destination_addr_two = $depot['place'] . ' ' . $depot['postcode'];
                    }
                    $pickup_pdf->SetFont('Arial', 'B', 16);
                    $pickup_pdf->Cell(40, 20, $destination_name);
                    $pickup_pdf->Ln(16);
                    $pickup_pdf->SetFont('Arial', '', 12);
                    $pickup_pdf->Cell(40, 6, iconv('UTF-8', 'windows-1252', $destination_addr_one));
                    $pickup_pdf->Ln();
                    $pickup_pdf->Cell(40, 6, iconv('UTF-8', 'windows-1252', $destination_addr_two));
                    $pickup_pdf->Ln(10);

                    for ($i = 0; $i < count($header); $i ++)
                        $pickup_pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C');
                    $pickup_pdf->Ln();
                    $pickup_pdf_header = false;
                }

                $park_location = $vehicle['sub_location'];
                $park_position = $vehicle['park_line'] . ' ' . $vehicle['park_position'];

                $pickup_pdf->Cell($w[0], 6, ($cnt + 1), 'LR', 0, 'L', $fill);
                $pickup_pdf->Cell($w[1], 6, $vehicle['vin'], 'LR', 0, 'L', $fill);
                $pickup_pdf->Cell($w[2], 6, $vehicle['ikz'], 'LR', 0, 'L', $fill);
                if ($workshop_delivery === false) {
                    $pickup_pdf->Cell($w[4], 6, $vehicle['sname'], 'LR', 0, 'L', $fill);
                }
                $pickup_pdf->Ln();

                $cnt ++;
            }
        }
        $pickup_pdf->Cell(array_sum($w), 0, '', 'T');
        $pickup_pdf->SetFont('Arial', 'B', 16);
        $pickup_pdf->Ln();
        $pickup_pdf->Cell(40, 20, $pickup);
        $pickup_pdf->Ln();
        $pickup_pdf->SetFont('Arial', '', 12);
        $pickup_pdf->Cell(40, 20, 'Abgeholt am ' . '_________________' . ' durch Spedition' . ' _________________________');
        $pickup_pdf->Ln();
        $pickup_pdf->Cell(40, 20, 'Unterschrift Fahrer: ');

        $output = ($this->mode == 'view') ? 'I' : 'D';

        $pickup_pdf->Output($output, "Abholschein$vins.pdf", true);
        // $pickup_pdf->Output ('F', "/tmp/Abholschein$vins.pdf", true);
        exit();

    }

}
?>