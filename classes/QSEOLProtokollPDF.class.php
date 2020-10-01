<?php
/**
 * QSEOLProtokollPDF.class.php
 * Helper Klasse für QSEOLProtokollPDF.class.php
 * @author Pradeep Mohan
 */

/**
 * QSEOLProtokollPDF
 * Generiert EOL Protokoll pdfLink zurück
 * @author Pradeep Mohan
 *
 */
class QSEOLProtokollPDF {
    /***
     * Constructor for the QSEOLProtokollPDF class
     * @param array of $vehicles with vin,akz etc..
     * @return string pdfLink
     */
    protected $pdfmerged;

    public function __construct($vehicle) {
        $pdf = new FPDFFooterJS();
        $pdf->AddPage('P', 'A4');
        $pdf->SetFont('Arial', 'B', 24);
        $pdf->Cell(0, 20, 'EOL Protokoll', 0, 0, 'C');

        $pdf->Ln();
        $pdf->SetFont('Arial', '', 18);
        $pdf->Cell(0, 8, 'Fahrgestellnummer: ');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->Cell(0, 10, $vehicle['vin']);
        $pdf->Ln(14);
        $pdf->SetFont('Arial', '', 18);
        $pdf->Cell(0, 8, 'WC-Konfiguration: ');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->Cell(0, 10, $vehicle['windchill_variant_name']);
        $pdf->Ln(14);
        $pdf->SetFont('Arial', '', 18);
        $pdf->Cell(0, 8, 'Penta Artikel: ');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->Cell(0, 10, $vehicle['penta_number']);
        $pdf->Ln(14);
        $pdf->SetFont('Arial', '', 18);
        $pdf->Cell(0, 8, 'Penta Kennwort: ');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->Cell(0, 10, $vehicle['penta_kennwort']);
        $pdf->Ln(14);
        $pdf->SetFont('Arial', '', 18);
        $pdf->Cell(0, 8, 'Kennzeichen: ');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->Cell(0, 10, $vehicle['code']);
        $pdf->Ln(14);
        $pdf->SetFont('Arial', '', 18);
        $pdf->Cell(0, 8, 'IKZ: ', 0);
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->Cell(0, 10, $vehicle['ikz']);
        $pdf->Ln(14);
        $pdf->SetFont('Arial', '', 18);
        $pdf->Cell(0, 8, 'Serien C2C: ', 0);
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->Cell(0, 10, $vehicle['c2cbox']);
        $pdf->Ln(14);
        $pdf->SetFont('Arial', '', 18);
        $pdf->Cell(0, 8, 'Serien Body: ', 0);
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->Cell(0, 10, $vehicle['body_serial']);
        $pdf->Ln(14);
        $pdf->SetFont('Arial', '', 18);
        $pdf->Cell(0, 8, 'Produktionsbeginn: ', 0);
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->Cell(0, 10, $vehicle['body_date']);
        $pdf->Ln(14);

        $colSize1 = 100;
        $colSize2 = 60;

        $pdf->SetFont('Arial', '', 18);
        $pdf->Cell(0, 8, 'Status: ', 0);
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->Cell($colSize1, 10, $vehicle['processed_diagnose_status']);
        if (toBool($vehicle['qmlocked'])) {
            $pdf->SetTextColor(192, 0, 0);
            $pdf->Cell($colSize, 10, 'QM GESPERRT');
            $pdf->SetTextColor(0, 0, 0);
        }
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->Ln();
        $pdf->Cell($colSize1, 10, date('Y-m-d H:i', strtotime($vehicle['diagnose_status_time'])));

        if (toBool($vehicle['qmlocked'])) {
            $pdf->SetFont('Arial', '', 18);
            $pdf->Cell($colSize, 10, '(Kommentare auf Blatt 2)');
        }

        $pdf->Ln(14);
        $pdf->SetDrawColor($pdf->hex2dec('#CCCCCC'));

        if (!empty($vehicle['tables'])) {
            $pdf->AddPage();
            foreach ($vehicle['tables'] as $tablename => $dtable) {
                if (!empty($dtable)) {
                    if ($dtable['header']) {
                        $pdf->SetFont('Arial', 'B', 20);
                        $pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', strip_tags($dtable['header'])));
                        $pdf->Ln();

                    }
                    $pdf->SetFont('Arial', 'B', 12);
                    $row = $dtable['headings'][0]['headingone'];
                    $pdf->SetWidths($dtable['colWidths']);
                    $pdf->Row($this->processColsForPdf($row));
                    $pdf->SetFont('Arial', '', 12);
                    foreach ($dtable['content'] as $row) {
                        $cnt = 0;
                        $pdf->SetWidths($dtable['colWidths']);
                        $pdf->Row($this->processColsForPdf($row));
                    }
                }
                $pdf->Ln();
            }
            $script = 'print(true);';
            $pdf->setFooterParams('1.0', 'Pramod Jayaramaiah', 'Ralf Frohn');
            $pdf->IncludeJS($script);
            $pdf->Output();
        }
    }

    public function processColsForPdf($row) {
        $row = array_values($row);
        array_walk($row, array('self', 'convert'));
        return $row;
    }

    public function convert(&$value, $key) {
        $value = iconv('UTF-8', 'windows-1252', $value);
        $value = str_replace("<br>", "\n", $value);
    }

    public function getPdfName() {
        return $this->pdfmerged;
    }
}