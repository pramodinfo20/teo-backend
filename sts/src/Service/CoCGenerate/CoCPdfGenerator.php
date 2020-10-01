<?php

namespace App\Service\CoCGenerate;

class CoCPdfGenerator extends \FPDF
{
    var $angle = 0;
    var $leftRightMargin = 20;
    var $topBottomMargin = 10;

    function _endpage()
    {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }

    /**
     * @param $configs
     */
    function makeBlankPage($configs)
    {
        $this->AddPage('P', 'A4');
        $this->SetAutoPageBreak(false, 0);
        $this->setMargins($this->leftRightMargin, $this->topBottomMargin, $this->leftRightMargin);

        if ($configs['is_test_print'] == true)
            $this->makeWatermark($configs['watermark'], $configs['fontB']);

        $this->makeHeaderWithLogo($configs);
        $this->makeFooter($configs);
    }

    /**
     * @param $text
     * @param $font
     */
    function makeWatermark($text, $font)
    {
        $this->SetFont($font, 'B', 85);
        //Try calculate the start position for text (on the middle page)
        $textWidth = $this->GetStringWidth($text);
        $pageHeight = $this->GetPageHeight();
        $pageWidth = $this->GetPageWidth();
        $textStartX = $pageWidth / 2 - $textWidth / 3.2;
        $textStartY = $pageHeight / 2 + $textWidth / 2.7;
        //Print watermark
        $this->SetTextColor(170);
        $this->RotatedText($textStartX + 2, $textStartY + 3, iconv('UTF-8', 'windows-1252', $text), 45);
        $this->SetTextColor(210);
        $this->RotatedText($textStartX, $textStartY, iconv('UTF-8', 'windows-1252', $text), 45);
    }

    /**
     * @param $x
     * @param $y
     * @param $txt
     * @param $angle
     */
    function RotatedText($x, $y, $txt, $angle)
    {
        //Text rotated around its origin
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }

    /**
     * @param $angle
     * @param $x
     * @param $y
     */
    function Rotate($angle, $x = -1, $y = -1)
    {
        if ($x == -1)
            $x = $this->x;
        if ($y == -1)
            $y = $this->y;
        if ($this->angle != 0)
            $this->_out('Q');
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    /**
     * @param $configs
     */
    function makeHeaderWithLogo($configs)
    {
        $logoWidth = 35;
        $this->SetY($this->topBottomMargin + 10);
        $this->SetTextColor(0);
        $this->SetFont($configs['font'], '', 9);
        $this->Cell(26, 5, iconv('UTF-8', 'windows-1252', $configs['year_label'] . ' '));
        $this->Cell(20, 5, iconv('UTF-8', 'windows-1252', $configs['year']));
        $this->Ln(6);
        $this->Cell(26, 5, iconv('UTF-8', 'windows-1252', $configs['seq_number_label'] . ' '));
        $this->Cell(20, 5, iconv('UTF-8', 'windows-1252', $configs['seq_number']));
        //Place logo StreetScooter
        $this->Image($configs['SS_logo_path'], $this->GetPageWidth() - $this->leftRightMargin - $logoWidth - 1, $this->topBottomMargin,
            $logoWidth);
        //Make a header centered text
        $this->SetY($this->topBottomMargin);
        $this->SetFont($configs['fontB'], 'B', 10);
        $this->SetY($this->topBottomMargin + 5);
        $this->Cell(180, 5, iconv('UTF-8', 'windows-1252', $configs['eccofc']), 0, 0, 'C');
        $this->Ln();
        $this->SetFont($configs['fontB'], 'B', 9);

        if ($configs['is_vehicle_complete'] == true) {
            $this->Cell(180, 5, iconv('UTF-8', 'windows-1252', $configs['complete_vehicle']), 0, 0, 'C');
        } else {
            $this->Cell(180, 5, iconv('UTF-8', 'windows-1252', $configs['incomplete_vehicle']), 0, 0, 'C');
        }

        $this->drawLine(0, 31, $this->GetPageWidth() - $this->leftRightMargin);
    }

    /**
     * @param $positionX
     * @param $positionY
     * @param $width
     */
    function drawLine($positionX, $positionY, $width)
    {
        $this->SetY($positionY);
        $x = $this->GetX();
        $this->Line($x + $positionX, $this->GetY(), $width, $this->GetY());
    }

    /**
     * @param $configs
     */
    function makeFooter($configs)
    {
        $this->drawLine(0, -31, $this->GetPageWidth() - $this->leftRightMargin);
        $this->SetFont($configs['font'], '', 9);
        $this->Cell('', '10', iconv('UTF-8', 'windows-1252', $configs['legal_paper_label']), 0, 0, 'C');
        $this->Ln(5);
        $this->Cell('', '10', iconv('UTF-8', 'windows-1252', $configs['page_text'] . ' ' . $configs['page_number']),
            0, 0,
            'R');
    }

    /**
     * @param $font
     * @param $pdfLabels
     * @param $pdfData
     */
    function makeFirstPageTable($font, $pdfLabels, $pdfData)
    {
        $this->setContentSettings($font);

        $this->makeRow([
            'columns' => 2,
            'width' => ['83', '70'],
            'content' => [$pdfLabels['undersigned'], $pdfData['undersigned'] ?? ''],
            'fill' => [false, true],
        ]);
        $this->makeRow([
            'columns' => 1,
            'width' => ['83'],
            'content' => [$pdfLabels['herebyCertifies']],
            'fill' => [false],
        ]);
        $this->makeRow([
            'columns' => 3,
            'width' => ['13', '70', '70'],
            'content' => ['0.1.', $pdfLabels['trade'], $pdfData['trade_mark'] ?? ''],
            'fill' => [false, false, true],
        ]);
        $this->makeRow([
            'columns' => 3,
            'width' => ['13', '70', '70'],
            'content' => ['0.2.', $pdfLabels['type'], $pdfData['type'] ?? ''],
            'fill' => [false, false, true],
        ]);
        $this->makeRow([
            'columns' => 3,
            'width' => ['13', '70', '70'],
            'content' => ['', $pdfLabels['variant'], $pdfData['variant'] ?? ''],
            'fill' => [false, false, true],
        ]);
        $this->makeRow([
            'columns' => 3,
            'width' => ['13', '70', '70'],
            'content' => ['', $pdfLabels['version'], $pdfData['version'] ?? ''],
            'fill' => [false, false, true],
        ]);
        $this->makeRow([
            'columns' => 3,
            'width' => ['13', '70', '70'],
            'content' => ['0.2.1.', $pdfLabels['commercialName'], $pdfData['trade_name'] ?? '-'],
            'fill' => [false, false, true],
        ]);
        $this->makeRow([
            'columns' => 3,
            'width' => ['13', '70', '70'],
            'content' => ['0.4.', $pdfLabels['vehicleCategory'], $pdfData['vehicle_category'] ?? ''],
            'fill' => [false, false, true],
        ]);
        $this->makeRowLastMultiLine([
            'columns' => 3,
            'width' => ['13', '70', '70'],
            'content' => ['0.5.', $pdfLabels['companyName'], $pdfData['manufacturer_adress'] ?? ''],
            'fill' => [false, false, true],
        ]);
        $this->makeRow([
            'columns' => 3,
            'width' => ['13', '70', '70'],
            'content' => ['0.6.', $pdfLabels['locStatutoryPlate'], $pdfData['factory_nameplate_location'] ?? ''],
            'fill' => [false, false, true],
        ]);
        $this->makeRow([
            'columns' => 3,
            'width' => ['13', '70', '70'],
            'content' => ['0.6.', $pdfLabels['locVehicleId'], $pdfData['vin_location'] ?? '-'],
            'fill' => [false, false, true],
        ]);
        $this->makeRow([
            'columns' => 3,
            'width' => [13, 70, 70],
            'content' => ['0.10.', $pdfLabels['vehicleId'], $pdfData['vin'] ?? ''],
            'fill' => [false, false, true],
        ]);
    }

    /**
     * @param $font
     */
    function setContentSettings($font)
    {
        $this->setY(35);
        $this->SetFillColor(230);
        $this->SetFont($font, '', 8);
    }

    /**
     * @param $row
     */
    function makeRow($row)
    {
        for ($i = 0; $i < $row['columns']; $i++) {
            $this->Cell($row['width'][$i], '3.7', iconv('UTF-8', 'windows-1252', $row['content'][$i]), 0, 0, 'L',
                $row['fill'][$i]);
        }
        $this->Ln(3.95);
    }

    /**
     * @param $row
     */
    function makeRowLastMultiLine($row)
    {
        for ($i = 0; $i < $row['columns'] - 1; $i++) {
            $this->Cell($row['width'][$i], '4', iconv('UTF-8', 'windows-1252', $row['content'][$i]), 0, 0, 'L',
                $row['fill'][$i]);
        }
        $this->MultiCell($row['width'][$i], '4', iconv('UTF-8', 'windows-1252', $row['content'][$i]), 0, 'L',
            $row['fill'][$i]);
    }

    /**
     * @param $pdfLabels
     * @param $pdfData
     */
    function makeFirstPageLongText($pdfLabels, $pdfData)
    {
        $approvalCode = $pdfData['approval_code'] ?? '--';
        if(!empty($pdfData['approval_date']))
            $approvalDate = $pdfData['approval_date']->format('d.m.Y');
        else
            $approvalDate = '--';

        $this->Ln(1);
        if ($pdfData['is_vehicle_complete'] == true) {
            if ($pdfData['language'] == 'de') {
                $this->makeRowLastMultiLine([
                    'columns' => 1,
                    'width' => [$this->GetPageWidth() - (2 * $this->leftRightMargin)],
                    'content' => [$pdfLabels['firstDocLongOne'] . ' ' . $approvalCode . ' ' . $pdfLabels['firstDocLongTwo'] . ' ' . $approvalDate . ' ' . $pdfLabels['firstDocLongThree']],
                    'fill' => [false],
                ]);
        } else {
                $this->makeRowLastMultiLine([
                    'columns' => 1,
                    'width' => [$this->GetPageWidth() - (2 * $this->leftRightMargin)],
                    'content' => [$pdfLabels['firstDocLongOne'] . ' ' . $approvalCode . ' ' . $pdfLabels['firstDocLongTwo'] . ' ' . $approvalDate . ' ' . $pdfLabels['firstDocLongThree'] . ' ' . $pdfData['driver_side'] . ' ' . $pdfLabels['firstDocLongFour'] . ' ' . $pdfData['units'] . ' ' . $pdfLabels['firstDocLongFive'] . ' ' . $pdfData['units'] . ' ' . $pdfLabels['firstDocLongSix']],
                    'fill' => [false],
                ]);
            }

        } else {
            $this->makeRowLastMultiLine([
                'columns' => 1,
                'width' => [$this->GetPageWidth() - (2 * $this->leftRightMargin)],
                'content' => [$pdfLabels['secondDocLongOne'] . ' ' . $approvalCode . ' ' . $pdfLabels['secondDocLongTwo'] . ' ' . $approvalDate . ' ' . $pdfLabels['secondDocLongThree']],
                'fill' => [false],
            ]);
        }
    }

    /**
     * @param $pdfLabels
     * @param $pdfData
     */
    function makeSignaturePlaces($pdfLabels, $pdfData)
    {
        $linesWidth = 65;
        $distanceBetweenLines = 25;
        $firstLineX = 15;
        $firstLineY = -140;
        $firstLineWidth = $firstLineX + $linesWidth;
        $secondLineX = $firstLineWidth + $distanceBetweenLines;
        $secondLineY = $firstLineY + 30;
        $secondLineWidth = $secondLineX + $linesWidth;

        $this->SetY($firstLineY - 5);
        $this->makeRow([
            'columns' => 4,
            'width' => [$firstLineX, $linesWidth, $distanceBetweenLines, $linesWidth],
            'content' => ['', 'Aachen', '', $pdfData['coc_date']],
            'fill' => [false, false, false, false],
        ]);
        $this->drawLine($firstLineX, $firstLineY, $firstLineWidth);
        $this->drawLine($secondLineX, $firstLineY, $secondLineWidth);
        $this->makeRow([
            'columns' => 4,
            'width' => [$firstLineX, $linesWidth, $distanceBetweenLines, $linesWidth],
            'content' => ['', $pdfLabels['place'], '', $pdfLabels['date']],
            'fill' => [false, false, false, false],
        ]);


        $this->SetY($secondLineY - 5);
        $this->makeRow([
            'columns' => 4,
            'width' => [$firstLineX, $linesWidth, $distanceBetweenLines, $linesWidth],
            'content' => ['', '', '', $pdfData['official_position']],
            'fill' => [false, false, false, false],
        ]);

        $this->SetY($firstLineY - 5);
        $this->drawLine($firstLineX, $secondLineY, $firstLineWidth);
        $this->drawLine($secondLineX, $secondLineY, $secondLineWidth);
        $this->makeRow([
            'columns' => 4,
            'width' => [$firstLineX, $linesWidth, $distanceBetweenLines, $linesWidth],
            'content' => ['', $pdfLabels['signature'], '', $pdfLabels['officialPosition']],
            'fill' => [false, false, false, false],
        ]);
    }

    /**
     * @param $font
     * @param $pdfLabels
     * @param $pdfData
     */
    function makeSecondPageUniversal($font, $pdfLabels, $pdfData)
    {
        $this->setContentSettings($font);

        $this->makeRow([
            'columns' => 6,
            'width' => [15, 50, 20, 25, 35, 20],
            'content' => ['1.', $pdfLabels['numOfAxles'], $pdfData['num_axles'] ?? '', '', $pdfLabels['numOfWheels'], $pdfData['num_wheels'] ?? ''],
            'fill' => [false, false, true, false, false, true],
        ]);
        $this->makeRow([
            'columns' => 6,
            'width' => [15, 50, 20, 25, 35, 20],
            'content' => ['3.', $pdfLabels['poweredAxles'], $pdfData['num_driven_axles'] ?? '', '', $pdfLabels['position'], $pdfData['driven_axles_location'] ?? ''],
            'fill' => [false, false, true, false, false, true],
        ]);
        $this->makeRow([
            'columns' => 3,
            'width' => [15, 50, 20],
            'content' => ['', $pdfLabels['interconn'], $pdfData['driven_axles_connection'] ?? ''],
            'fill' => [false, false, true],
        ]);
        $this->makeRow([
            'columns' => 4,
            'width' => [15, 50, 20, 7],
            'content' => ['4.', $pdfLabels['whellbase'], $pdfData['wheelbase'] ?? '', 'mm'],
            'fill' => [false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 9,
            'width' => [15, 43, 7, 20, 7, 46, 7, 20, 7],
            'content' => ['4.1.', $pdfLabels['axleSpacing'], '1-2', $pdfData['axle_distance_1_2'] ?? '', 'mm', '', '2-3', $pdfData['axle_distance_2_3'] ?? '', 'mm'],
            'fill' => [false, false, false, true, false, false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 4,
            'width' => [58, 7, 20, 7],
            'content' => ['', '3-4', $pdfData['axle_distance_3_4'] ?? '', 'mm'],
            'fill' => [false, false, true, false],
        ]);

        if ($pdfData['is_vehicle_complete'] == true)
            $this->makeSecondPageCompleteOne($pdfLabels, $pdfData);
        else
            $this->makeSecondPageIncompleteOne($pdfLabels, $pdfData);

        $this->makeRow([
            'columns' => 4,
            'width' => [15, 77, 20, 15],
            'content' => ['16.1.', $pdfLabels['techPermMaxLoadMass'], $pdfData['max_laden_mass'] ?? '', 'kg'],
            'fill' => [false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 8,
            'width' => [15, 65, 12, 20, 18, 15, 20, 7],
            'content' => ['16.2.', $pdfLabels['techPermEachAxle'], $pdfLabels['axleOne'], $pdfData['max_laden_mass_axle_1'] ?? '', 'kg', $pdfLabels['axleTwo'], $pdfData['max_laden_mass_axle_2'] ?? '', 'kg'],
            'fill' => [false, false, false, true, false, false, true, false],
        ]);

        if ($pdfData['is_vehicle_complete'] == true)
            $this->makeSecondPageCompleteTwo($pdfLabels, $pdfData);
        else
            $this->makeSecondPageIncompleteTwo($pdfLabels, $pdfData);


        $this->makeRow([
            'columns' => 2,
            'width' => [15, 130],
            'content' => ['18.', $pdfLabels['techPermMaxTowableCaseOf']],
            'fill' => [false, false],
        ]);
        $this->makeRow([
            'columns' => 4,
            'width' => [15, 50, 20, 7],
            'content' => ['18.4.', $pdfLabels['unbrackedTrailer'], $pdfData['max_towable_mass'] ?? '', 'kg'],
            'fill' => [false, false, true, false],
        ]);

        $this->makeRow([
            'columns' => 4,
            'width' => [15, 130, 20, 7],
            'content' => ['19.', $pdfLabels['techPermMaxStatMass'], $pdfData['max_coupling_mass'] ?? '', 'kg'],
            'fill' => [false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 3,
            'width' => [15, 50, 47],
            'content' => ['20.', $pdfLabels['manufacturerEngine'], $pdfData['powertrain_manufacturer'] ?? ''],
            'fill' => [false, false, true],
        ]);
        $this->makeRow([
            'columns' => 3,
            'width' => [15, 50, 47],
            'content' => ['21.', $pdfLabels['engineCode'], $pdfData['powertrain_type_examination'] ?? ''],
            'fill' => [false, false, true],
        ]);
        $this->makeRow([
            'columns' => 3,
            'width' => [15, 50, 47],
            'content' => ['22.', $pdfLabels['workPrinciple'], $pdfData['powertrain_type'] ?? ''],
            'fill' => [false, false, true],
        ]);
        $this->makeRow([
            'columns' => 8,
            'width' => [15, 50, 14, 13, 14, 44, 15, 20, 7],
            'content' => ['23.', $pdfLabels['pureElectric'], $pdfData['pure_electric_drive'] ?? '', '', '23.1.', $pdfLabels['hybricVehicle'], $pdfData['hybrid_electric_drive'] ?? '', ''],
            'fill' => [false, false, true, false, false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 3,
            'width' => [15, 50, 14],
            'content' => ['26.', $pdfLabels['fuel'], $pdfData['fuel'] ?? ''],
            'fill' => [false, false, true],
        ]);
        $this->makeRow([
            'columns' => 8,
            'width' => [15, 50, 14, 13, 14, 44, 15, 20, 7],
            'content' => ['27.2.', $pdfLabels['maxHourlyOutput'], $pdfData['max_power_hour'] ?? '', 'kW', '27.3.', $pdfLabels['maxNetPower'], $pdfData['max_power'] ?? '', 'kW'],
            'fill' => [false, false, true, false, false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 4,
            'width' => [15, 50, 14, 13],
            'content' => ['27.4.', $pdfLabels['maxHalfHour'], $pdfData['max_power_30min'] ?? '', 'kW'],
            'fill' => [false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 3,
            'width' => [15, 50, 47],
            'content' => ['28.', $pdfLabels['gearboxType'], $pdfData['gearbox'] ?? ''],
            'fill' => [false, false, true],
        ]);
        $this->makeRow([
            'columns' => 4,
            'width' => [15, 50, 14, 13],
            'content' => ['29.', $pdfLabels['maxSpeed'], $pdfData['vmax'] ?? '', 'km/h'],
            'fill' => [false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 8,
            'width' => [15, 36, 14, 14, 14, 14, 14, 7],
            'content' => ['30.', $pdfLabels['axleTrack'], $pdfLabels['axleOne'], $pdfData['track_width_1'] ?? '', 'mm', $pdfLabels['axleTwo'], $pdfData['track_width_2'] ?? '', 'mm'],
            'fill' => [false, false, false, true, false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 6,
            'width' => [15, 63, 16, 27, 17, 27],
            'content' => ['35.', $pdfLabels['fittedTyreComb'], $pdfLabels['axleOne'], $pdfData['tyre_dimensions_axle1'] ?? '', '', $pdfData['rim_type_axle_1'] ?? '', ''],
            'fill' => [false, false, false, true, false, true],
        ]);
        $this->makeRow([
            'columns' => 5,
            'width' => [78, 16, 27, 17, 27],
            'content' => ['', $pdfLabels['axleTwo'], $pdfData['tyre_dimensions_axle2'] ?? '', '', $pdfData['rim_type_axle_2'] ?? ''],
            'fill' => [false, false, true, false, true],
        ]);

        if ($pdfData['is_vehicle_complete'] == true)
            $this->makeSecondPageCompleteThree($pdfLabels, $pdfData);

        $this->makeRow([
            'columns' => 3,
            'width' => [15, 106, 44],
            'content' => ['44.', $pdfLabels['approvalNumOfCouplingDevice'], $pdfData['hitch_approval_code'] ?? ''],
            'fill' => [false, false, true],
        ]);

        if ($pdfData['is_vehicle_complete'] == false)
            $this->makeSecondPageIncompleteThree($pdfLabels, $pdfData);

        $this->makeRow([
            'columns' => 14,
            'width' => [15, 43, 7, 14, 8, 7, 14, 8, 7, 14, 7, 7, 14, 5],
            'content' => ['45.1.', $pdfLabels['characteristicVal'], 'D:', $pdfData['hitch_property_d'] ?? '', 'kN', '/ V:', $pdfData['hitch_property_v'] ?? '',
                'kN', '/ S:', $pdfData['hitch_property_s'] ?? '', 'kg', '/ U:', $pdfData['hitch_property_u'] ?? '', 't'],
            'fill' => [false, false, false, true, false, false, true, false, false, true, false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 2,
            'width' => [15, 50],
            'content' => ['46.', $pdfLabels['soundLevel']],
            'fill' => [false, false],
        ]);
        $this->makeRow([
            'columns' => 4,
            'width' => [15, 50, 27, 7],
            'content' => ['', $pdfLabels['stationary'], $pdfData['stationary_noise'] ?? '', 'db(A)'],
            'fill' => [false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 4,
            'width' => [15, 50, 27, 7],
            'content' => ['', $pdfLabels['driveBy'], $pdfData['pass_by_noise'] ?? '', 'db(A)'],
            'fill' => [false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 3,
            'width' => [15, 50, 47],
            'content' => ['48.', $pdfLabels['exhaustEmissions'], $pdfData['emission_characteristics'] ?? ''],
            'fill' => [false, false, true],
        ]);
        $this->makeRow([
            'columns' => 2,
            'width' => [15, 50],
            'content' => ['49.', $pdfLabels['elEnergyConsuption']],
            'fill' => [false, false],
        ]);
        $this->makeRow([
            'columns' => 4,
            'width' => [15, 79, 27, 7],
            'content' => ['', $pdfLabels['elEnergyConsuptionCombined'], $pdfData['combined_energy_consumption'] ?? '', 'Wh/km'],
            'fill' => [false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 4,
            'width' => [15, 50, 27, 7],
            'content' => ['', $pdfLabels['elRange'], $pdfData['range'] ?? '', 'km'],
            'fill' => [false, false, true, false],
        ]);
        $this->makeRowLastMultiLine([
            'columns' => 3,
            'width' => [15, 50, 100],
            'content' => ['52.', $pdfLabels['remarks'], $pdfData['additional_annotations'] ?? ''],
            'fill' => [false, false, true],
        ]);

        $this->Ln(4);
    }

    /**
     * @param $pdfLabels
     * @param $pdfData
     */
    function makeSecondPageCompleteOne($pdfLabels, $pdfData)
    {
        $this->makeRow([
            'columns' => 4,
            'width' => [15, 50, 20, 7],
            'content' => ['5.', $pdfLabels['length'], $pdfData['length'] ?? '', 'mm'],
            'fill' => [false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 8,
            'width' => [15, 50, 20, 27, 15, 18, 20, 7],
            'content' => ['6.', $pdfLabels['width'], $pdfData['width'] ?? '', 'mm', '7.', $pdfLabels['height'], $pdfData['height'] ?? '', 'mm'],
            'fill' => [false, false, true, false, false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 4,
            'width' => [15, 130, 20, 7],
            'content' => ['9.', $pdfLabels['distanceFronEndCentre'], $pdfData['distance_to_coupling'] ?? '', 'mm'],
            'fill' => [false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 4,
            'width' => [15, 50, 20, 7],
            'content' => ['11.', $pdfLabels['lengthOfLoadArea'], $pdfData['length_cargo_area'] ?? '', 'mm'],
            'fill' => [false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 4,
            'width' => [15, 50, 20, 7],
            'content' => ['13.', $pdfLabels['massRunOrder'], $pdfData['mass_ready_to_start'] ?? '', 'kg'],
            'fill' => [false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 8,
            'width' => [15, 65, 12, 20, 18, 15, 20, 7],
            'content' => ['13.1.', $pdfLabels['distribThisMass'], $pdfLabels['axleOne'], $pdfData['kerb_weight_axle_1'] ?? '', 'kg', $pdfLabels['axleTwo'], $pdfData['kerb_weight_axle_2'] ?? '', 'kg'],
            'fill' => [false, false, false, true, false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 4,
            'width' => [15, 50, 20, 7],
            'content' => ['13.2.', $pdfLabels['actMassVehicle'], $pdfData['actual_weight'] ?? '', 'kg'],
            'fill' => [false, false, true, false],
        ]);
    }

    /**
     * @param $pdfLabels
     * @param $pdfData
     */
    function makeSecondPageIncompleteOne($pdfLabels, $pdfData)
    {
        $this->makeRow([
            'columns' => 4,
            'width' => [15, 50, 20, 7],
            'content' => ['5.1.', $pdfLabels['maxPermissibleLength'], $pdfData['max_length'] ?? '', 'mm'],
            'fill' => [false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 4,
            'width' => [15, 50, 20, 7],
            'content' => ['6.1.', $pdfLabels['maxPermWidth'], $pdfData['max_width'] ?? '', 'mm'],
            'fill' => [false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 4,
            'width' => [15, 50, 20, 7],
            'content' => ['7.1.', $pdfLabels['maxPermHeight'], $pdfData['max_height'] ?? '', 'mm'],
            'fill' => [false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 4,
            'width' => [15, 77, 20, 7],
            'content' => ['12.1.', $pdfLabels['maxPermRearOverhang'], $pdfData['max_overhang'] ?? '', 'mm'],
            'fill' => [false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 4,
            'width' => [15, 77, 20, 7],
            'content' => ['14.1', $pdfLabels['massRunIncopleteVeh'], $pdfData['mass_incomplete_vehicle'] ?? '', 'kg'],
            'fill' => [false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 8,
            'width' => [15, 65, 12, 20, 18, 15, 20, 7],
            'content' => ['14.1.', $pdfLabels['distribThisMass'], $pdfLabels['axleOne'], $pdfData['mass_incomplete_vehicle_axle_1'] ?? '', 'kg', $pdfLabels['axleTwo'], $pdfData['mass_incomplete_vehicle_axle_2'] ?? '', 'kg'],
            'fill' => [false, false, false, true, false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 4,
            'width' => [15, 77, 20, 7],
            'content' => ['15.', $pdfLabels['minMassWhenCompleted'], $pdfData['min_weight_completed_vehicle'] ?? '', 'kg'],
            'fill' => [false, false, true, false],
        ]);
        $this->makeRow([
            'columns' => 8,
            'width' => [15, 65, 12, 20, 18, 15, 20, 7],
            'content' => ['15.1.', $pdfLabels['distribThisMass'], $pdfLabels['axleOne'], $pdfData['min_weight_completed_vehicle_axle_1'] ?? '', 'kg', $pdfLabels['axleTwo'], $pdfData['min_weight_completed_vehicle_axle_2'] ?? '', 'kg'],
            'fill' => [false, false, false, true, false, false, true, false],
        ]);
    }

    /**
     * @param $pdfLabels
     * @param $pdfData
     */
    function makeSecondPageCompleteTwo($pdfLabels, $pdfData)
    {
        $this->makeRow([
            'columns' => 4,
            'width' => [15, 77, 20, 18],
            'content' => ['16.4.', $pdfLabels['techPermOfCombination'], $pdfData['max_laden_mass_combined'] ?? '', 'kg'],
            'fill' => [false, false, true, false],
        ]);
    }

    /**
     * @param $pdfLabels
     * @param $pdfData
     */
    function makeSecondPageIncompleteTwo($pdfLabels, $pdfData)
    {
        $this->makeRow([
            'columns' => 4,
            'width' => [15, 130, 20, 7],
            'content' => ['16.4.', $pdfLabels['techPermMaxCombMass'], $pdfData['max_laden_mass_combined'] ?? '', 'kg'],
            'fill' => [false, false, true, false],
        ]);

    }

    /**
     * @param $pdfLabels
     * @param $pdfData
     */
    function makeSecondPageCompleteThree($pdfLabels, $pdfData)
    {
        $this->makeRow([
            'columns' => 3,
            'width' => [15, 50, 29],
            'content' => ['38.', $pdfLabels['codeForBodywork'], $pdfData['compartment_kind'] ?? ''],
            'fill' => [false, false, true],
        ]);
        $this->makeRow([
            'columns' => 3,
            'width' => [15, 50, 29],
            'content' => ['40.', $pdfLabels['colorOfVehicle'], $pdfData['colour'] ?? ''],
            'fill' => [false, false, true],
        ]);
        $this->makeRow([
            'columns' => 3,
            'width' => [15, 79, 27],
            'content' => ['41.', $pdfLabels['numConfDoors'], $pdfData['num_doors'] ?? ''],
            'fill' => [false, false, true],
        ]);
        $this->makeRow([
            'columns' => 3,
            'width' => [15, 79, 27],
            'content' => ['42.', $pdfLabels['numSeatsAndPosition'], $pdfData['number_of_seats'] ?? ''],
            'fill' => [false, false, true],
        ]);
    }

    /**
     * @param $pdfLabels
     * @param $pdfData
     */
    function makeSecondPageIncompleteThree($pdfLabels, $pdfData)
    {
        $this->makeRow([
            'columns' => 2,
            'width' => [15, 100],
            'content' => ['45.', $pdfLabels['typesClassesCouplingDevices'], 'Test'],
            'fill' => [false, false, true],
        ]);
        $this->makeRow([
            'columns' => 2,
            'width' => [15, 64],
            'content' => ['', $pdfData['kinds_of_hitches'] ?? ''],
            'fill' => [false, true],
        ]);

    }

    function makeAdditionalApprovalData($pdfData)
    {
        $this->SetFont($pdfData['fontB'], 'B', 8);
        $this->makeRow([
            'columns' => 1,
            'width' => ['83'],
            'content' => ['Zusätzliche Zulassungsdaten'],
            'fill' => [false],
        ]);
        $this->SetFont($pdfData['font'], '', 8);

        $this->makeRow([
            'columns' => 9,
            'width' => [15, 40, 15, 20, 15, 5, 12, 25, 18],
            'content' => ['', 'Schlüssel', '2.1.', 'HSN:', $pdfData['hsn'] ?? '', '', '2.2.', 'TSN:', $pdfData['tsn'] ?? ''],
            'fill' => [false, false, false, false, true, false, false, false, true],
        ]);

        $this->makeRow([
            'columns' => 4,
            'width' => [55, 15, 20, 15],
            'content' => ['', '2.2.', 'VVS/PZ:', $pdfData['vv_pz'] ?? ''],
            'fill' => [false, false, false, true],
        ]);

        $this->makeRow([
            'columns' => 7,
            'width' => [15, 40, 50, 5, 12, 25, 18],
            'content' => ['2.', 'Hersteller', $pdfData['manufacturer'] ?? '', '', 'D.2.', 'Amtl. Text-Typ:', $pdfData['type'] ?? ''],
            'fill' => [false, false, true, false, false, false, true],
        ]);

        $this->makeRow([
            'columns' => 3,
            'width' => [15, 40, 15],
            'content' => ['4.', 'Amtl. Aufbau:', $pdfData['official_compartment_kind'] ?? ''],
            'fill' => [false, false, true],
        ]);

        $this->makeRow([
            'columns' => 3,
            'width' => [15, 40, 50],
            'content' => ['5.', 'Fahrzeugklasse:', $pdfData['vehicle_class'] ?? ''],
            'fill' => [false, false, true],
        ]);

        $this->makeRow([
            'columns' => 3,
            'width' => [15, 40, 50],
            'content' => ['5.', 'Amtl. Text Aufbau:', $pdfData['official_compartment_text'] ?? ''],
            'fill' => [false, false, true],
        ]);

        $this->makeRow([
            'columns' => 7,
            'width' => [15, 40, 50, 5, 12, 25, 18],
            'content' => ['14.', 'Nat. Emiklasse:', $pdfData['national_emission_class'] ?? '', '', '14.1.', 'Code zu V9 od. 14:', $pdfData['national_emission_class_code'] ?? ''],
            'fill' => [false, false, true, false, false, false, true],
        ]);

        $this->makeRow([
            'columns' => 3,
            'width' => [15, 40, 15],
            'content' => ['P.3.', 'Text Kraftstoff kurz:', $pdfData['fuel_text_short'] ?? ''],
            'fill' => [false, false, true],
        ]);

        $this->makeRow([
            'columns' => 7,
            'width' => [15, 40, 15, 40, 12, 25, 18],
            'content' => ['U.1.', 'Standgeräusch:', $pdfData['stationary_noise'] ?? '', '', 'U.1.', 'Fahrgeräusch:', $pdfData['pass_by_noise'] ?? ''],
            'fill' => [false, false, true, false, false, false, true],
        ]);

        $this->makeRow([
            'columns' => 3,
            'width' => [15, 40, 15],
            'content' => ['V.7.', 'CO2 Kombi:', $pdfData['co2_combi'] ?? ''],
            'fill' => [false, false, true],
        ]);

        $this->makeRow([
            'columns' => 3,
            'width' => [15, 40, 110],
            'content' => ['22.', 'Bem. Ausnahmen:', $pdfData['remark_exceptions'] ?? ''],
            'fill' => [false, false, true],
        ]);
    }

}