<?php
$vehicleids = explode(',', $_POST['vehicle_ids']);
$vehicles = $_POST;
require('fpdf/fpdf.php');
$pdf = new FPDF();

putenv('LD_LIBRARY_PATH=/home/sts_rz5q/zint-2.4.3/build/backend/:$LD_LIBRARY_PATH');

foreach ($vehicleids as $vehicleid) {
    shell_exec('/home/sts_rz5q/Zint_test/built/CINT_CIN ' . '*' . $vehicles['v' . $vehicleid . '_vin'] . '*');
    shell_exec('mv /var/www/WebinterfaceNew/out.png /var/www/WebinterfaceNew/vinbarcodes/' . $vehicles['v' . $vehicleid . '_vin'] . '.png');
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 44);
    $pdf->Cell(0, 10, $vehicles['v' . $vehicleid . '_vin']);
    $pdf->Ln(10);
    $pdf->Cell(0, 70, $vehicles['v' . $vehicleid . '_code']);
    $pdf->Ln(10);
    $pdf->Cell(0, 140, $vehicles['v' . $vehicleid . '_ikz']);
    $pdf->Ln(10);
    if (file_exists('/var/www/WebinterfaceNew/vinbarcodes/' . $vehicles['v' . $vehicleid . '_vin'] . '.png'))
        $pdf->Image('/var/www/WebinterfaceNew/vinbarcodes/' . $vehicles['v' . $vehicleid . '_vin'] . '.png', 10, 150, 100);

}
$pdf->Output();
unlink('/var/www/WebinterfaceNew/vinbarcodes/' . $vehicles['v' . $vehicleid . '_vin'] . '.png');
?>
