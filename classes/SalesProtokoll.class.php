<?php
/**
 * SalesProtokoll.class.php
 * Helper Klasse für SalesController.class.php
 * @author Pradeep Mohan
 */

/**
 * SalesProtokoll
 * Generiert Fahrzeug Belgleitschein und gibt pdfLink zurück
 * @author Pradeep Mohan
 *
 */
class SalesProtokoll {
    /***
     * Constructor for the SalesProtokoll class
     * @param array of $vehicles with vin,akz etc..
     * @return string pdfLink string
     */
    protected $pdfmerged;
    protected $ladeLeitWartePtr;

    public function __construct($vehicles, $ladeLeitWartePtr) {
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;

        $lib_path = getenv('LD_LIBRARY_PATH');
        putenv("LD_LIBRARY_PATH=/var/www/zint/:$lib_path");

        $pdf_merge_files = [];
        require_once('phpqrcode/qrlib.php');
        require_once('fpdf/fpdf.php');
        

        if (!file_exists('/tmp/vinbarcodes'))
            mkdir('/tmp/vinbarcodes');
        if (!file_exists('/tmp/gen_pdf'))
            mkdir('/tmp/gen_pdf');


        foreach ($vehicles as $vehicle) {
            $vehicle_id = $vehicle['vehicle_id'];

            $execute = "/var/www/zint/zint -o /tmp/vinbarcodes/{$vehicle['vin']}.png -d '{$vehicle['vin']}'";
            //shell_exec("/var/www/zint/zint -o /tmp/vinbarcodes/{$vehicle['vin']}.png -d '{$vehicle['vin']}'");
            shell_exec($execute);

            $configuration_type = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                ->join('sub_vehicle_configurations', 'vehicles.sub_vehicle_configuration_id=sub_vehicle_configurations.sub_vehicle_configuration_id', 'LEFT JOIN')
                ->join('vehicle_configurations', 'vehicle_configurations.vehicle_configuration_id=sub_vehicle_configurations.vehicle_configuration_id', 'LEFT JOIN')
                ->where('vehicles.vehicle_id', '=', $vehicle_id)
                ->getOne('vehicle_id,vin,vehicle_configurations.vehicle_configuration_id,default_production_location_id,vehicles.sub_vehicle_configuration_id,sub_vehicle_configurations.sub_vehicle_configuration_id,vehicle_type_name,vehicle_type_year,vehicle_series');

                $fz_location = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                ->join('vehicles_sales', 'vehicles_sales.vehicle_id=vehicles.vehicle_id', 'LEFT JOIN')
                ->where('vehicles.vehicle_id', '=', $vehicle_id)
                ->getOne('vin,production_location');

            $vehicle_info = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                ->join('vehicle_variants', 'vehicles.vehicle_variant=vehicle_variants.vehicle_variant_id', 'INNER JOIN')
                ->where('vehicles.vehicle_id', '=', $vehicle_id)
                ->getOne('vehicle_id,color_id,penta_kennwort,penta_number_id,vehicle_variant_id,windchill_variant_name,type,vmax,max_power,max_power_30min,fahrzeugvariante,zielstaat,luftdruck_vorne,luftdruck_hinten,is_dp,battery');

            $vehicle_params = $this->ladeLeitWartePtr->vehicleVariantsPtr->Decode_WC_Variant_Name($vehicle_info['windchill_variant_name']);

            if ($vehicle_params) {
                $this->ladeLeitWartePtr->vehiclesPtr->getPartlist($vehicle_info, true, false);
            } else {
                $this->ladeLeitWartePtr->vehiclesPtr->getPartlist($vehicle_info, false, true);
            }

            $pdf = new FPDFCustomFooter();
            $pdf->AddPage('P', 'A4');
            $pdf->SetFont('Arial', 'B', 30);
            $pdf->Cell(0, 20, 'Fahrzeugbegleitschein', 0, 0, 'C');
            $pdf->Ln();
            $pdf->SetFont('Arial', '', 18);
            $pdf->Cell(120, 8, 'Fahrgestellnummer: ');
            $pdf->Ln();
            $pdf->SetFont('Arial', 'B', 30);
            $pdf->Cell(120, 10, $vehicle['vin']);
            $pdf->Ln(15);

            $pdf->SetFont('Arial', '', 18);
            $pdf->Cell(120, 8, 'Konfiguration: ');
            $pdf->Cell(120, 8, 'Penta Kennwort: ');
            $pdf->Ln();

            if (empty ($vehicle_info['penta_kennwort'])) {
                $kennwort = '---';
                $pos = 140;
            } else {
                $kennwort = $vehicle_info['penta_kennwort'];
                $pos = 120;
            }

            $pdf->SetFont('Arial', 'B', 20);
            $pdf->Cell($pos, 8, $vehicle_info['windchill_variant_name']);
            $pdf->Cell(120, 8, $kennwort);
            $pdf->Ln();

//          space before barcode image
            $pdf->Ln(16);
            $ypos = $pdf->GetY();

            $url = (empty($_SERVER['HTTPS'])) ? 'http://' : 'https://';
            $url .= $_SERVER['HTTP_HOST'];           
            $qr_code = $url . '/qr_generator.php?code=' . $vehicle['vin'];

            // QR Code
            $fz_type = $configuration_type['vehicle_type_name'].$configuration_type['vehicle_type_year'];
            $fz_series = $configuration_type['vehicle_series'];
            $fz_production_location_id = $configuration_type['default_production_location_id'];
            $vis_production_location = substr($configuration_type['vin'], 10, 1);
            $production_location = $fz_location['production_location'];
            // var_dump($fz_production_location_id);
            // var_dump($production_location);
            
            $layouts = array('EPOS', 'BPOS', 'EBOX', 'BBOX', 'BOXA', 'POST', 'PICK', 'PURE', 'PVS-', 'YAPT', 'PVSZ', 'PVSA', 'P Ba');
            $layout = (string)substr($vehicle_info['windchill_variant_name'], 5, 4);
            // if ($fz_type == 'D17' && $fz_series > '01')
            if (($fz_type == 'D17' && $fz_series > '01') || ($fz_type > 'B14' && !in_array($layout, $layouts)))
                $pdf->Image($qr_code, 164, 26, 42, 42, "png");

            if (file_exists('/tmp/vinbarcodes/' . $vehicle['vin'] . '.png'))
                $pdf->Image('/tmp/vinbarcodes/' . $vehicle['vin'] . '.png', 10, $ypos, 100);

//          space after barcode image
            $pdf->Ln(31);

            $pdf->SetFont('Arial', 'B', 20);
            $pdf->Cell(0, 10, 'Fahrzeugangaben:');
            $pdf->Ln();

            $ln_spacing = 5;

            /***
             * start analysis if this a thirdparty delivery or not and then proceed
             *
             *
             */
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(50, $ln_spacing, 'Penta Artikelnummer:');
            $penta_variante = $this->ladeLeitWartePtr->newQuery('penta_numbers')
                ->where('penta_number_id', '=', $vehicle_info['penta_number_id'])
                ->getVal('penta_number');
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(50, $ln_spacing, $penta_variante, 0, 1);

//          Fahrzeugtyp:                B14 / B16 / D16 / D17
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(50, $ln_spacing, 'Fahrzeugtyp:');
            $pdf->SetFont('Arial', 'B', 11);

            $type = $vehicle_info['type'];
            if ($type == 'D16')
                $type = 'D16A';

            $pdf->Cell(50, $ln_spacing, $type, 0, 1);


//          Fahrzeugfarbe:          Weiß / Gelb / Orange
            $vehicle_color = "";
            if (isset($vehicle['colorname']) && !empty($vehicle['colorname']))
                $vehicle_color = $vehicle['colorname'];

            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(50, $ln_spacing, 'Fahrzeugfarbe:');
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(50, $ln_spacing, iconv('UTF-8', 'windows-1252', $vehicle_color), 0, 1);


//          Fahrzeugvariante:           Koffer / Pure / Pritsche
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(50, $ln_spacing, iconv('UTF-8', 'windows-1252', 'Ausführung:'));
            $pdf->SetFont('Arial', 'B', 11);

            $ausfuehrung = ($vehicle_params) ? $vehicle_params['layout'] : $vehicle_info['fahrzeugvariante'];
            $pdf->Cell(50, $ln_spacing, iconv('UTF-8', 'windows-1252', $ausfuehrung), 0, 1);


            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(50, $ln_spacing, 'Merkmal:');
            $pdf->SetFont('Arial', 'B', 11);

            $merkmal = ($vehicle_params) ? $vehicle_params['feature'] : implode(',', array_column($vehicle_info['features'], 'begleitscheinname'));
            $pdf->Cell(50, $ln_spacing, iconv('UTF-8', 'windows-1252', $merkmal), 0, 1);

            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(50, $ln_spacing, 'Batterie:');
            $pdf->SetFont('Arial', 'B', 11);
            $battery = ($vehicle_params) ? $vehicle_params['battery'] : $vehicle_info['battery'];
            $pdf->Cell(50, $ln_spacing, iconv('UTF-8', 'windows-1252', $battery), 0, 1);

            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(50, $ln_spacing, 'Sondermerkmal:');
            $pdf->SetFont('Arial', 'B', 11);


            if (count($vehicle_info['parts'])) {
                $text = "";
                foreach ($vehicle_info['parts'] as $key => $part)
                    $text .= (($key > 0) ? ", " : "") . iconv('UTF-8', 'windows-1252', $part['begleitscheinname']);

                $pdf->Cell(150, $ln_spacing, $text, 0, 1);
            } else {
                $pdf->Cell(50, $ln_spacing, '-', 0, 1);
            }
            $pdf->Ln();


//          Zielstaat:           DE / NL / AU / PL / GB / CZ
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(50, $ln_spacing, 'Zielstaat:');
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(50, $ln_spacing, $vehicle_info['zielstaat'], 0, 1);

            /*
            $pdf->SetFont('Arial','',11);
            $pdf->Cell(50,  $ln_spacing,iconv('UTF-8', 'windows-1252', 'Höchstgeschwindigkeit:'));
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell( 8,$ln_spacing, $vehicle_info['vmax'], 0);
            $pdf->SetFont('Arial','',10 );
            $pdf->Cell(35, $ln_spacing, "km/h", 0, 1);
                  */

            $air_front_kpa = $vehicle_info['luftdruck_vorne'];
            $air_rear_kpa = $vehicle_info['luftdruck_hinten'];
            $air_front_bar = empty ($air_front_kpa) ? "" : sprintf("%.1f", $air_front_kpa / 100);
            $air_rear_bar = empty ($air_rear_kpa) ? "" : sprintf("%.1f", $air_rear_kpa / 100);

//          Luftdruck vorne:            2.7 bar / 2.7 bar
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(50, $ln_spacing, 'Luftdruck vorne:');
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(7, $ln_spacing, $air_front_kpa, 0);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(15, $ln_spacing, ' kPa ', 0);
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->Cell(6, $ln_spacing, '(' . $air_front_bar, 0);
            $pdf->SetFont('Arial', 'I', 7);
            $pdf->Cell(4, $ln_spacing, ' bar', 0);
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->Cell(16, $ln_spacing, ')', 0, 1);

//          Luftdruck hinten:           2.9 bar / 4.0 bar
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(50, $ln_spacing, 'Luftdruck hinten:');
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(7, $ln_spacing, $air_rear_kpa, 0);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(15, $ln_spacing, ' kPa ', 0);
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->Cell(6, $ln_spacing, '(' . $air_rear_bar, 0);
            $pdf->SetFont('Arial', 'I', 7);
            $pdf->Cell(4, $ln_spacing, ' bar', 0);
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->Cell(16, $ln_spacing, ')', 0, 1);

            $pdf->Ln(-15);
            $y = $pdf->GetY();
            $pdf->SetXY(110, $y);
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(50, $ln_spacing, iconv('UTF-8', 'windows-1252', 'Höchstgeschwindigkeit:'));
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(8, $ln_spacing, $vehicle_info['vmax'], 0);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(35, $ln_spacing, "km/h", 0, 1);


            $y = $pdf->GetY();
            $pdf->SetXY(110, $y);
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(50, $ln_spacing, iconv('UTF-8', 'windows-1252', 'höchste Nennleistung:'));
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(8, $ln_spacing, $vehicle_info['max_power'], 0);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(20, $ln_spacing, "kW", 0, 1);

            $y = $pdf->GetY();
            $pdf->SetXY(110, $y);
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(50, $ln_spacing, '30 min Dauerleistung:');
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(8, $ln_spacing, $vehicle_info['max_power_30min'], 0);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(10, $ln_spacing, "kW", 0, 1);


            $pdf->Ln(10);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->MultiCell(0, 4, iconv('UTF-8', 'windows-1252', 'Das Fahrzeug mit der o.g. Fahrgestellnummer wurde von mir geprüft; die spezifikationskonforme Montage wurde festgestellt.'));
            $pdf->Ln(5);


            $pdf->SetFont('Arial', '', 9);
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->SetXY($x + 100, $y + 3);

            $pdf->MultiCell(60, 5, iconv('UTF-8', 'windows-1252', "Nacharbeit erforderlich \nsiehe Dokumentation Fahrzeugmängel"), 0, 'L');
            $pdf->SetXY($x, $y);
            $pdf->Ln();

            $pdf->Cell(6, 6, '', 1);
            $pdf->Cell(2, 6, '');
            $pdf->Cell(40, 5, 'Freigabe');
            $pdf->Cell(40, 4, '');
            $pdf->Cell(6, 6, '', 1);
            $pdf->Cell(2, 6, '', 0, 1);
            $pdf->Ln(15);


            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->Line($x, $y, $x + 40, $y);
            $pdf->Line($x + 50, $y, $x + 90, $y);
            $pdf->Line($x + 100, $y, $x + 170, $y);
            $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Datum'));
            $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Unterschrift '));
            $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Name in Druckbuchstaben'));

            $pdf->Ln(15);

            $pdf->Cell(6, 6, '', 1);
            $pdf->Cell(2, 6, '');
            $pdf->Cell(50, 5, iconv('UTF-8', 'windows-1252', 'Freigabe Fahrzeugmängel beseitigt'));
            $pdf->Ln(20);

            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->Line($x, $y, $x + 40, $y);
            $pdf->Line($x + 50, $y, $x + 90, $y);
            $pdf->Line($x + 100, $y, $x + 170, $y);
            $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Datum'));
            $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Unterschrift '));
            $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Name in Druckbuchstaben'));

            $result = $this->ladeLeitWartePtr->newQuery('production_protocol_params')->get('*');

            if (!empty($result)) $footer_params = array_combine(array_column($result, 'param_key'), array_column($result, 'param_value'));

            if (!empty($footer_params)) $pdf->setFooterParams($vehicle['ikz'], $vehicle['vin'], $footer_params['version'], $footer_params['creator'], $footer_params['approval']);
            
            // EAN Barcode
            // if($fz_type <= 'D1701')
                $pdf->Output('F', '/var/www/barcode_' . $vehicle['vehicle_id'] . '.pdf', true);
            
            // New Onpager Start
            $query = <<<SQLDOC
            SELECT
                vc.vehicle_type_name,
                vc.vehicle_type_year,
                vc.vehicle_series,
                svc.vehicle_configuration_id,
                svc.sub_vehicle_configuration_id,
                svc.sub_vehicle_configuration_name,
                svc.short_production_description,
                vcp.vehicle_configuration_property_name,
                vcps.german_description as description,
                als.symbol
            FROM
                vehicles v
            INNER JOIN
                sub_vehicle_configurations svc USING (sub_vehicle_configuration_id)
            INNER JOIN
                vehicle_configurations vc USING (vehicle_configuration_id)            
            INNER JOIN
                vehicle_configuration_properties_mapping vcpm USING
            (vehicle_configuration_id)
            INNER JOIN
                vehicle_configuration_properties vcp USING (vc_property_id)
            INNER JOIN
                vehicle_configuration_properties_symbols vcps USING (vc_property_id,
            allowed_symbols_id)
            INNER JOIN
                allowed_symbols als USING (allowed_symbols_id)
            WHERE
                vehicle_id = {$vehicle_id}
SQLDOC;

            $qry =  $this->ladeLeitWartePtr->vehiclesPtr->newQuery();
            $qry->query($query);
            $onepager_info = $qry->fetchAll();

            $sub_vehicle_configuration_id = $onepager_info[0]['sub_vehicle_configuration_id'];
            $special_vehicle_pressure_front = $this->ladeLeitWartePtr->newQuery('special_vehicle_property_values')
                ->join('special_vehicle_properties_mapping', 'special_vehicle_properties_mapping.special_vehicle_property_value_id=special_vehicle_property_values.svpv_id', 'INNER JOIN')
                ->join('sub_vehicle_configurations', 'sub_vehicle_configurations.sub_vehicle_configuration_id=special_vehicle_properties_mapping.sub_vehicle_configuration_id', 'INNER JOIN')
                ->multipleAndWhere('sub_vehicle_configurations.sub_vehicle_configuration_id', '=', $sub_vehicle_configuration_id, 'AND', 'special_vehicle_properties_mapping.special_vehicle_property_id', '=', 9)
                ->getOne('value_integer');

            $special_vehicle_pressure_rear = $this->ladeLeitWartePtr->newQuery('special_vehicle_property_values')
                ->join('special_vehicle_properties_mapping', 'special_vehicle_properties_mapping.special_vehicle_property_value_id=special_vehicle_property_values.svpv_id', 'INNER JOIN')
                ->join('sub_vehicle_configurations', 'sub_vehicle_configurations.sub_vehicle_configuration_id=special_vehicle_properties_mapping.sub_vehicle_configuration_id', 'INNER JOIN')
                ->multipleAndWhere('sub_vehicle_configurations.sub_vehicle_configuration_id', '=', $sub_vehicle_configuration_id, 'AND', 'special_vehicle_properties_mapping.special_vehicle_property_id', '=', 10)
                ->getOne('value_integer');

            $air_pressure_front_kpa = $special_vehicle_pressure_front['value_integer'];
            $air_pressure_rear_kpa = $special_vehicle_pressure_rear['value_integer'];

            $air_pressure_front_bar = empty ($air_pressure_front_kpa) ? "" : sprintf("%.1f", $air_pressure_front_kpa / 100);
            $air_pressure_rear_bar = empty ($air_pressure_rear_kpa) ? "" : sprintf("%.1f", $air_pressure_rear_kpa / 100);

            // $pdf->AddPage('P','A4');
            $pdf = new PdfFormat();
            $pdf->AddPage('P', 'A4');

            // Vehicle document
            $pdf->SetFont('Arial', '', 32);
            $pdf->Cell(0, 20, 'Fahrzeugbegleitschein', 0, 0, 'C');
            $pdf->Ln();

            // VIN
            $pdf->SetFont('Arial', '', 18);
            $pdf->Cell(120, 8, 'Fahrgestellnummer (VIN): ');

            //add QR code
            $pdf->Image($qr_code, 164, 26, 42, 42, "png");

            $pdf->Ln();

            $pdf->SetFont('Arial', 'B', 20);
            $pdf->Cell(120, 10, $vehicle['vin']);
            $pdf->Ln(15);

            // Short production description
            $pdf->SetFont('Arial', '', 15);
            $pdf->Cell(120, 8, 'Produktionskurzbezeichnung:');
            $pdf->Ln();

            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(158.8    ,7,'',0,0);
            $pdf->Cell(10, 7, $vehicle['vin']);
            $pdf->Ln(3);

            $pdf->SetFont('Arial', 'B', 15);
            $pdf->Cell(120, 2, $onepager_info[0]['short_production_description']);
            $pdf->Ln(10);

            // Configuration Key and pentakennwort
            $pdf->SetFont('Arial', '', 15);
            $pdf->Cell(140, 8, 'Konfigurationsbezeichnung:');
            $pdf->Cell(120, 8, 'Penta-Kennwort:');
            $pdf->Ln();

            $pdf->SetFont('Arial', 'B', 15);
            $pdf->Cell(140, 8, $onepager_info[0]['sub_vehicle_configuration_name']);
            $pdf->Cell(120, 8, $vehicle_info['penta_kennwort']);
            $pdf->Ln();

            // Line
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->Line($x, $y, $x + 190, $y);

            $pdf->SetFont('Arial', 'B', 20);
            $pdf->Cell(120, 12, 'Fahrzeugangaben:');
            $pdf->Ln();

            // Define column size
            $pdf->SetWidths(array(44, 50, 2, 44, 50));
            $pdf->SetFont('Arial', '', 9);

            // Row 1
            $pdf->PropertiesRows('Karosserie', $onepager_info[0]['description'], 'Aufbauten', $onepager_info[14]['description']);
            // Row 2
            $pdf->PropertiesRows('Anzahl/Antriebs-/Lenkachsen', $onepager_info[1]['description'], 'Energieversorgung Aufbau', $onepager_info[15]['description']);
            // Row 3
            $pdf->PropertiesRows('Dauer- / Spitzenleistung', $onepager_info[2]['description'], 'Lenkung',
                $onepager_info[16]['description']);
            // Row 4
            $pdf->PropertiesRows('Länger der Karosserie - Radstand der Fertigstellung', $onepager_info[4]['description'], 'Verglasung + Spiegel', $onepager_info[17]['description']);
            // Row 5
            $pdf->PropertiesRows('Vorderachse / Luftdruck vorne', $onepager_info[5]['description'] . ' / ' . $air_pressure_front_bar . ' bar', 'Klimaanlage', $onepager_info[18]['description']);
            // Row 6
            $pdf->PropertiesRows('Hinterachse / Luftdruck hinten', $onepager_info[6]['description'] . ' / ' . $air_pressure_rear_bar . ' bar', 'Beifahrerairbag', $onepager_info[19]['description']);
            // Row 7
            $pdf->PropertiesRows('ZGG (zulässiges Gesamtgewicht)', $onepager_info[7]['description'], 'Schlüsselloser Zugang', $onepager_info[20]['description']);
            // Row 8
            $pdf->PropertiesRows('Art des Kraftstoffs', $onepager_info[8]['description'], 'Spezieller Anwendungsbereich', $onepager_info[21]['description']);
            // Row 9
            $pdf->PropertiesRows('Traktionsbatterie', $onepager_info[9]['description'], 'Radio', $onepager_info[22]['description']);
            // Row 10
            $pdf->PropertiesRows('Ladesystem', $onepager_info[10]['description'], 'Klangerzeuger', $onepager_info[23]['description']);
            // Row 11
            $pdf->PropertiesRows('V-max: vorw. / rückw.', $onepager_info[11]['description'], 'Länderkennung', $onepager_info[24]['description']);
            // Row 12
            $pdf->PropertiesRows('Sitze', $onepager_info[12]['description'], 'Farbe', $onepager_info[25]['description']);
            // Row 13
            $pdf->PropertiesRows('Anhängerkupplung', $onepager_info[13]['description'], 'Reifen', $onepager_info[26]['description']);

            $pdf->Ln(5);
            $pdf->SetFont('Arial', '', 10);
            $pdf->MultiCell(0, 4, iconv('UTF-8', 'windows-1252', 'Das Fahrzeug mit der o.g. Fahrgestellnummer wurde von mir geprüft; die spezifikationskonforme Montage wurde festgestellt.'));

            $pdf->SetFont('Arial', '', 9);
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->SetXY($x + 100, $y + 3);

            $pdf->MultiCell(60, 5, iconv('UTF-8', 'windows-1252', "Nacharbeit erforderlich \nsiehe Dokumentation Fahrzeugmängel"), 0, 'L');
            $pdf->SetXY($x, $y);
            $pdf->Ln(4);

            $pdf->Cell(6, 6, '', 1);
            $pdf->Cell(2, 6, '');
            $pdf->Cell(40, 5, 'Freigabe');
            $pdf->Cell(40, 4, '');
            $pdf->Cell(6, 6, '', 1);
            $pdf->Cell(2, 6, '', 0, 1);
            $pdf->Ln(12);

            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->Line($x, $y, $x + 40, $y);
            $pdf->Line($x + 50, $y, $x + 90, $y);
            $pdf->Line($x + 100, $y, $x + 170, $y);
            $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Datum'));
            $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Unterschrift '));
            $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Name in Druckbuchstaben'));

            $pdf->Ln(10);

            $pdf->Cell(6, 6, '', 1);
            $pdf->Cell(2, 6, '');
            $pdf->Cell(50, 5, iconv('UTF-8', 'windows-1252', 'Freigabe Fahrzeugmängel beseitigt'));
            $pdf->Ln(14);

            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->Line($x, $y, $x + 40, $y);
            $pdf->Line($x + 50, $y, $x + 90, $y);
            $pdf->Line($x + 100, $y, $x + 170, $y);
            $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Datum'));
            $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Unterschrift '));
            $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Name in Druckbuchstaben'));

            // Footer
            $pdf->SetY(-26);
            $pdf->Line(10, $pdf->getY(), 199, $pdf->getY());

            $pdf->Cell(0.01, 4, 'Dok.: ' . 'FB_0010/Rev. 1.0');
            $pdf->Cell(0, 4, 'FB_Erstellt: ' . ' A.Celebi/Zentrale/19.11.2019', 0, 0, 'C');
            $pdf->Cell(0, 4, 'Freigegeben: ' . ' P.Ley/QM/25.11.2019', 0, 0, 'R');    
            // NEW Onpager End

            // if ($fz_type == 'D17' && $fz_series > '01')
            if (($fz_type == 'D17' && $fz_series > '01') || ($fz_type > 'B14' && !in_array($layout, $layouts)))
                $pdf->Output('F', '/var/www/barcode_' . $vehicle['vehicle_id'] . '.pdf', true);

            // $pdf = new FPDFTwoCol();
            $pdf = new PdfFormat();

            $pdf->AddPage('L', 'A4');
            $pdf->SetFont('Arial', '', 18);
            $pdf->Cell(0, 10, 'Penta Kennwort: ', 0, 1);
            $pdf->SetFont('Arial', 'B', 60);
            if (!empty($vehicle_info['penta_kennwort'])) $pdf->Cell(0, 30, $vehicle_info['penta_kennwort'], 0, 1);
            else $pdf->Cell(0, 30, '---', 0, 1);
            $pdf->SetFont('Arial', '', 18);
            $pdf->Cell(120, 10, 'Fahrgestellnummer: ', 0, 1);
            $pdf->SetFont('Arial', 'B', 60);
            $pdf->Cell(120, 30, $vehicle['vin'], 0, 1);

            $pdf->SetFont('Arial', '', 18);

            $pdf->Cell(0, 8, 'Konfiguration: ');
            $pdf->Ln();
            $pdf->SetFont('Arial', 'B', 30);
            $pdf->Cell(0, 10, $vehicle_info['windchill_variant_name']);

            // leere Seite
            $pdf->AddPage('L', 'A4');

            $splitted = "{$vehicle_params['type']} {$vehicle_params['series']} {$vehicle_params['layout-key']} {$vehicle_params['feature-key']} {$vehicle_params['battery-key']}";

            if ($type == 'E17' || $type == 'E18') {
                $pdf->AddPage('L', 'A4');
                $pdf->SetFont('Arial', 'B', 80);
                // $pdf->SetXY(30, 80);
                $pdf->SetXY(50, 80);
                $pdf->Cell(120, 30, $vehicle_info['windchill_variant_name'], 0, 1);
                // $pdf->Cell(120,30,$splitted, 0, 1);
                $pdf->SetXY(-70, -40);
                $pdf->SetFont('Arial', '', 12);
                $pdf->Cell(30, 10, $vehicle['vin'], 0, 1);

                // leere Seite
                $pdf->AddPage('L', 'A4');
            }

            switch ($vehicle['vin'][10]) {
                case 'A':
                    $standort = 'ema';
                    break;
                case 'D':
                    $standort = 'emd';
                    break;
                case 'K':
                    $standort = 'emk';
                    break;
            }
            if (preg_match('/WS(.)*/', $vehicle['vin']) && $detparts = $this->GetDeterminingParts($vehicle_info['vehicle_variant_id'], $standort)) {

                $Cellsize = array(
                    'artikelnummer' => 26,
                    'pentanummer' => 26,
                    'bezeichnung' => 70,
                    'menge' => 10,
                    'ema' => 13,
                    'emd' => 13,
                    'emk' => 13,
                    'arbeitsgang' => 19,
                    'farbcode_ema' => 22,
                    'farbcode_emd' => 22,
                    'farbcode_emk' => 22

                );
                $head = array(
                    'artikelnummer' => 'Artikelnummer',
                    'pentanummer' => 'Pentanummer',
                    'bezeichnung' => 'Bezeichnung',
                    'menge' => 'Menge',
                    'ema' => 'EMA',
                    'emd' => 'EMD',
                    'emk' => 'EMK',
                    'arbeitsgang' => 'Arbeitsgang',
                    'farbcode_ema' => 'Farbcode_EMA',
                    'farbcode_emd' => 'Farbcode_EMD',
                    'farbcode_emk' => 'Farbcode_EMK'
                );
                $location = $vehicle['vin'][10];
                $pdf->AddPage('P', 'A4');

                $count = 0;

                $grey = true;
                $vorher = 'default';
                foreach ($detparts as $part) {
                    if ($count == 0) {
                        foreach ($detparts[0] as $k => $v) {
                            $pdf->SetFillColor(151, 255, 255);
                            $pdf->SetFont('Arial', 'B', 7);
                            if ($k == 'ema' && $location != 'A' || $k == 'emd' && $location != 'D' || $k == 'emk' && $location != 'K') {
                                continue;
                            }
                            if ($k == 'farbcode_ema' && $location != 'A' || $k == 'farbcode_emd' && $location != 'D' || $k == 'farbcode_emk' && $location != 'K') {
                                continue;
                            }
                            $pdf->Cell($Cellsize[$k], 8, $head[$k], 1, 0, 'L', 'true');
                        }
                    }
                    if ($part[$standort] != $vorher) {
                        $grey = !$grey;
                        $vorher = $part[$standort];
                    }
                    if ($grey) {
                        $pdf->SetFillColor(211, 211, 211);
                    } else {
                        $pdf->SetFillColor(255, 255, 255);
                    }
                    foreach ($part as $k => $v) {

                        $pdf->SetFont('Arial', '', 6);
                        if ($k == 'ema' && $location != 'A' || $k == 'emd' && $location != 'D' || $k == 'emk' && $location != 'K') {
                            continue;
                        }
                        if ($k == 'farbcode_ema' && $location != 'A' || $k == 'farbcode_emd' && $location != 'D' || $k == 'farbcode_emk' && $location != 'K') {
                            continue;
                        }
                        if ($k == 'artikelnummer') {
                            $pdf->Ln();

                        }

                        $pdf->Cell($Cellsize[$k], 8, iconv('', 'UTF-8', $v), 1, 0, 'L', 'true');

                    }
                    $count++;
                    if ($count > 30) {
                        $count = 0;
                        $pdf->AddPage('P', 'A4');
                    }
                }
            }

            $pdf->Output('F', '/var/www/extra_' . $vehicle['vehicle_id'] . '.pdf', true);

            if (file_exists('/tmp/vinbarcodes/' . $vehicle['vin'] . '.png'))
                unlink('/tmp/vinbarcodes/' . $vehicle['vin'] . '.png');

            if (file_exists('/tmp/vinbarcodes/' . $vehicle['code'] . '.png'))
                unlink('/tmp/vinbarcodes/' . $vehicle['code'] . '.png');

            if (file_exists('/var/www/teo-backend/out.png'))
                unlink('/var/www/teo-backend/out.png');

            $pdf_merge_files[] = '/var/www/extra_' . $vehicle['vehicle_id'] . '.pdf';
            $pdf_merge_files[] = '/var/www/barcode_' . $vehicle['vehicle_id'] . '.pdf';

        // Production location Aachen
        if ($production_location == 0) {
            if ($fz_type == 'D17' && $fz_series > '01') {
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('protokoll_template', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Vorlage_Fahrzeugmaengel_D1702', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Klebprotokoll_Body_D1702', 'fods', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('empty_page_LF', 'fodt', $vehicle);                              
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Durchlaufbestaetigung-Q-Gates-Aachen', 'fods', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('empty_page_LF', 'fodt', $vehicle);
                if($configuration_type['vehicle_configuration_id'] != 243) {
                    $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Inbetriebnahmeprotokoll_D1702', 'fodt', $vehicle);
                    $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('empty_page', 'fodt', $vehicle);
                }
                if($configuration_type['vehicle_configuration_id'] == 243) {
                    $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Pre_Delivery_Inspection_Yamato', 'fods', $vehicle);
                }
            } else if ($fz_type == 'D17' && $fz_series == '01') {
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('protokoll_template', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Vorlage_Fahrzeugmaengel', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Inbetriebnahmeprotokoll_Batterie_E17', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('empty_page', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Klebprotokoll_E17', 'fods', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('empty_page_LF', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Schraubprotokoll_E17', 'fods', $vehicle);
            } 
        }
        
        // Production location Düren
        else if ($production_location == 3348) {
            if ($fz_type == 'D17' && $fz_series > '01') {
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('protokoll_template', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Vorlage_Fahrzeugmaengel_D1702', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Klebprotokoll_Body_D1702', 'fods', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('empty_page_LF', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Druchlaufbestaetigung-Q-Gates', 'fods', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('empty_page_LF', 'fodt', $vehicle);
            } else if ($fz_type == 'D17' && $fz_series == '01') {
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('protokoll_template', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Vorlage_Fahrzeugmaengel', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Inbetriebnahmeprotokoll_Batterie_E17', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('empty_page', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Klebprotokoll_E17', 'fods', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('empty_page_LF', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Schraubprotokoll_E17', 'fods', $vehicle);
            }
        }

        // Production location Köln
        else if ($production_location == 3368) {
            if ($fz_type == 'E17') {
                // empty_page Leere Seite Hochformat
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('protokoll_template', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Vorlage_Fahrzeugmaengel_new', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Inbetriebnahmeprotokoll_Batterie_E17', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('empty_page', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Klebprotokoll_E17', 'fods', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('empty_page_LF', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Schraubprotokoll_E17', 'fods', $vehicle);
            } else if ($fz_type == 'E18' && $fz_series == '03') {
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('empty_page', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Vorlage_Fahrzeugmaengel_new', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Inbetriebnahmeprotokoll_HV_System_E1803', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('empty_page', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Klebprotokoll_E1803', 'fods', $vehicle);
                // Leere Seite Querformat
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('empty_page_LF', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Drehmomentkarte_E1803', 'fods', $vehicle);
            } else if ($fz_type == 'E18' && $fz_series == '02') {
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Vorlage_Fahrzeugmaengel_new', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Inbetriebnahmeprotokoll_HV_System_E1802', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Klebprotokoll_E1802', 'fods', $vehicle);
                // Leere Seite Querformat
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('empty_page_LF', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Drehmomentkarte_E1802', 'fods', $vehicle);
            } else if ($fz_type == 'E18') {
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Vorlage_Fahrzeugmaengel_new', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Inbetriebnahmeprotokoll_HV_System_E18', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('empty_page', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Klebprotokoll_E18', 'fods', $vehicle);
                // Leere Seite Querformat
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('empty_page_LF', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Drehmomentkarte_E18', 'fods', $vehicle);
            } else {
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('protokoll_template', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Vorlage_Fahrzeugmaengel', 'fodt', $vehicle);
            }
        } else {
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('protokoll_template', 'fodt', $vehicle);
                $pdf_merge_files[] = $this->MakeVehicleModifiedDocument('Vorlage_Fahrzeugmaengel', 'fodt', $vehicle);
        }
    }

        if (count($pdf_merge_files)) {
            $pdf_merge_str = implode(' ', $pdf_merge_files);
            $this->pdfmerged = 'protokoll_' . date('Y-m-j_H_i');
            $commandline = "pdftk $pdf_merge_str cat output /tmp/gen_pdf/{$this->pdfmerged}.pdf";
            exec($commandline);
        }

        foreach ($pdf_merge_files as $filename) {
            if (file_exists($filename))
                unlink($filename);
        }
    }

    protected function MakeVehicleModifiedDocument($filename, $ext, $vehicle) {
        $templateFilename = $_SERVER['STS_ROOT'] . "/doctemplate/$filename.$ext";
        $vehicleFilename = "/tmp/gen_pdf/$filename-{$vehicle['vehicle_id']}";          

        $fhandle = fopen($templateFilename, 'r');
        $fcontent = fread($fhandle, filesize($templateFilename));
        $fhandle_new = fopen("$vehicleFilename.$ext", 'w');

        $replacevals = array(
            'VIN_HERE_VIN' => $vehicle['vin'],

        );
        $fcontent = str_replace(array_keys($replacevals), array_values($replacevals), $fcontent);

        fwrite($fhandle_new, $fcontent);
        fclose($fhandle);
        fclose($fhandle_new);

        exec("libreoffice --headless --convert-to pdf $vehicleFilename.$ext --outdir /tmp/gen_pdf/");

        if (file_exists("$vehicleFilename.$ext"))
            unlink("$vehicleFilename.$ext");

        return "$vehicleFilename.pdf ";
    }

    /**
     * @param $pdf
     * @param $pdfData
     */
    protected function generateFooter(&$pdf, $pdfData)
    {
        $pdf->SetY(-15);
        $pdf->Line(10, $pdf->getY(), 199, $pdf->getY());

        $pdf->SetY(-12);
        $pdf->SetFont($this->font, '', 7);

        //here use $pdfData
        $pdf->Cell(0.01, 4, 'Dok.: ' . 'FB_0010/Rev. 1.0');
        $pdf->Cell(0, 4, 'FB_Erstellt: ' . ' A.Celebi/Zentrale/19.11.19', 0, 0, 'C');
        $pdf->Cell(0, 4, 'Freigegeben: ' . ' P.Ley/QM/25.11.2019', 0, 0, 'R');
    }

    function GetDeterminingParts($variant_id, $standort) {

        return $this->ladeLeitWartePtr->newQuery('mbom_variant_components')
            ->join('components', 'mbom_variant_components.part_number=components.part_number', 'INNER JOIN')
            ->join('penta_artikel', 'components.part_number=penta_artikel.windchill_part_number', 'INNER JOIN')
            ->join('determining_components', 'penta_artikel.penta_number=determining_components.penta_number', 'INNER JOIN')
            ->where('mbom_variant_components.vehicle_variant_id', '=', $variant_id)
            ->orderBy($standort)
            ->get('components.part_number as Artikelnummer, penta_artikel.penta_number as Pentanummer, part_name as Bezeichnung, workstation_aachen as EMA, workstation_dueren as EMD, workstation_koeln as EMK, determining_components.workstep as Arbeitsgang, amount as Menge, colorcode_aachen as Farbcode_EMA, colorcode_dueren as Farbcode_EMD, colorcode_koeln as Farbcode_EMK');

    }

    public function getPdfName() {
        return $this->pdfmerged;
    }

    public function getXmlName() {
        return $this->xmlmerge;
    } 
}
