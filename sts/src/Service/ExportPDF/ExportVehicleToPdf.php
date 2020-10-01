<?php
namespace App\Service\ExportPDF;

use App\Entity\Vehicles;
use App\Service\Vehicles\Configuration\SubConfiguration;
use CodeItNow\BarcodeBundle\Utils\QrCode;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;
use CodeItNow\BarcodeBundle\Utils\BarcodeGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\ExportPDF\FPDFTwoCol;


/**
 * Class ExportVehicleToPdf
 * @package App\Service\ExportPDF
 */
class ExportVehicleToPdf extends AbstractController
{
    var $widths;
    var $aligns;
    protected $manager;
    protected $subConfiguration;
    protected $font;
    protected $fontB;
    protected $fontI;
    protected $exportPath;
    protected $pdfNamePrefix;

    const DEFAULT_TYPE = 0;
    const D1610_TYPE = 1;
    const D17XX_TYPE = 2;
    const AFTER_D1702_TYPE = 3;
    const E18XX_TYPE = 4;
    const E1802_TYPE = 5;
    const E1803_TYPE = 6;
    const E2001_TYPE = 7;

    /**
     * @var string
     */
    private $path;

    public function __construct(string $path, ObjectManager $manager, SubConfiguration $subConfService)
    {
        $this->path = dirname($path);
        $this->manager = $manager;
        $this->subConfiguration = $subConfService;
        $this->font = 'OpenSans-Regular';
        $this->fontB = 'OpenSans-Bold';
        $this->fontI = 'OpenSans-Italic';
        $this->exportPath = $this->path . DIRECTORY_SEPARATOR . 'pdf_temp/';
        $this->pdfNamePrefix = 'accompanying_document_';
    }

    public function generateVehiclePdf(string $dbColumn, string $startValue, string $endValue)
    {
        $vehicleIdList = $this->manager->getRepository(Vehicles::class)
            ->getVehiclesId($dbColumn, $startValue, $endValue);

        $oldKeyVehicles = [];
        $newKeyVehicles = [];

        foreach ($vehicleIdList as $vehicleData) {
            if ($this->subConfiguration->detectSubConfigurationVersionForPDF($vehicleData['subVehicleConfigurationName']) == SubConfiguration::SHORT_KEY) {
                array_push($oldKeyVehicles, $vehicleData['vehicleId']);
            } else {
                array_push($newKeyVehicles, $vehicleData['vehicleId']);
            }
        }

        if (!empty($oldKeyVehicles)) {
            $oldKeyVehiclesList = $this->manager->getRepository(Vehicles::class)->getVehiclesToPdfForOldConfig($oldKeyVehicles);

            if (!empty($oldKeyVehiclesList)) {


                foreach ($oldKeyVehiclesList as $vehicleData) {
                    $pdf = new PdfGenerator();

                    $pdf->AddFont($this->font, '', 'OpenSans-Regular.php');
                    $pdf->AddFont($this->fontB, 'B', 'OpenSans-Bold.php');
                    $pdf->AddFont($this->fontI, 'I', 'OpenSans-Italic.php');

                    $prePdfData = array();

                    $vehicleData['data'] = $this->manager->getRepository(Vehicles::class)
                        ->getVehiclesPropertiesToPdfForOldConfig($vehicleData['vehicleId']);
                    $vehicleData['tires'] = $this->manager->getRepository(Vehicles::class)
                        ->getTiresPressureToPdfForOldConfig($vehicleData['vehicleId']);
                    $associations = array();

                    $vehicleData['perfs'] = $this->manager->getRepository(Vehicles::class)
                        ->getCoCParameterToPdfForShortConfig($vehicleData['vehicleId']);

//                    var_dump($vehicleData['perfs']);


                    foreach ($vehicleData['data'] as $prop) {
                      $assocProp = str_replace(['undefined', 'Undefined'], '-', $prop['description']);
                      $associations[$prop['vcPropertyId']] = $assocProp;
                    }
                    foreach ($vehicleData['perfs'] as $prop) {
                      $assocProp = str_replace(['undefined', 'Undefined'], '-', $prop['valueInteger']);
                      $perfs['coc_parameter_id_' . $prop['cocParameterId']] = $assocProp;
                    }



                    $vehicleData['addComp'] = $this->manager->getRepository(Vehicles::class)
                      ->getAdditionalComponentsToPdfForOldConfig($vehicleData['vehicleId'], $vehicleData['subVehicleConfigurationId']);

                    $associations['additional_components'] = '';

                    foreach ($vehicleData['addComp'] as $comp) {
                      switch($comp['specialVehiclePropertyId']) {
                        case 1:
                          $associations['additional_components'] .= 'ESP with ESP SW; ';
                          break;
                        case 2:
                          $associations['additional_components'] .= 'Rotating beacon; ';
                          break;
                        case 5:
                          $associations['additional_components'] .= 'Radio; ';
                          break;
                        case 6:
                          $associations['additional_components'] .= 'Deutsche Post; ';
                          break;
                        case 8:
                          $associations['additional_components'] .= 'ESP functionality; ';
                          break;
                        case 12:
                          $associations['additional_components'] .= 'Test software version; ';
                          break;
                      }
                    }

                    $prePdfData['vin'] = $vehicleData['vin'];
                    $prePdfData['penta_kennwort'] = $vehicleData['pentaKennwort'];
                    $prePdfData['sub_vehicle_configuration_name'] = $vehicleData['subVehicleConfigurationName'];
                    $prePdfData['vehicle_configuration_key'] = $vehicleData['vehicleConfigurationKey'];
                    $prePdfData['vehicle_type_name'] = $vehicleData['vehicleTypeName'];
                    $prePdfData['vehicle_type_year'] = $vehicleData['vehicleTypeYear'];
                    $prePdfData['zielstaat'] = $vehicleData['zielstaat'];
                    $prePdfData['penta_variant_name'] = $vehicleData['pentaVariantName'];
                    $prePdfData['configuration_color_name'] = $vehicleData['configurationColorName'];
                    $prePdfData['tire_pressure_front'] = $vehicleData['tires'][0]['description'] ?? '-';
                    $prePdfData['tire_pressure_rear'] = $vehicleData['tires'][1]['description'] ?? '-';
                    $prePdfData['vmax'] = $perfs['coc_parameter_id_23'] ?? '-';
                    $prePdfData['max_power'] = $perfs['coc_parameter_id_27'] ?? '-';
                    $prePdfData['max_power_30min'] = $perfs['coc_parameter_id_28'] ?? '-';
                    $pdfData = $associations + $prePdfData;


                    $this->generatePdfForOldConfig($pdfData, $pdf);
                    $pdfExportName = $this->pdfNamePrefix . $prePdfData['vin'] . '.pdf';
                    $pdfExportPath = $this->exportPath . $pdfExportName;
                    $pdf->Output('F', $pdfExportPath, true);
                }

                $protocolName = $this->generateAdditionalPages($dbColumn, $startValue, $endValue);
                return $protocolName;
            } else {
                return false;
            }
        }

        if (!empty($newKeyVehicles)) {

                $newKeyVehiclesList = $this->manager->getRepository(Vehicles::class)
                    ->getVehiclesToPdfForNewConfig($newKeyVehicles);

            if (!empty($newKeyVehiclesList)) {

                foreach ($newKeyVehiclesList as $vehicleData) {
                    $pdf = new PdfGenerator();

                    $pdf->AddFont($this->font, '', 'OpenSans-Regular.php');
                    $pdf->AddFont($this->fontB, 'B', 'OpenSans-Bold.php');

                    $vehicleData['data'] = $this->manager->getRepository(Vehicles::class)
                        ->getVehiclesPropertiesToPdfForNewConfig($vehicleData['vehicleId']);
                    $vehicleData['tires'] = $this->manager->getRepository(Vehicles::class)
                        ->getTiresPressureToPdfForNewConfig($vehicleData['vehicleId']);
                    $vehicleData['props'] = array_merge($vehicleData['data'], $vehicleData['tires']);

                    $associations = array();
                    $propsLengh = count($vehicleData['props']);

                   for ($i = 0; $i < $propsLengh; $i++) {
                       array_push($associations,$this->makeAssociations($vehicleData['props'][$i], 'germanDescription'));
                    }

                    $prePdfData = array();
                    $prePdfData['vin'] = $vehicleData['vin'];
                    $prePdfData['penta_kennwort'] = $vehicleData['pentaKennwort'];
                    $prePdfData['vehicle_configuration_key'] = $vehicleData['vehicleConfigurationKey'];
                    $prePdfData['short_production_description'] = $vehicleData['shortProductionDescription'];
                    $prePdfData['tire_pressure_front'] = $vehicleData['tires'][0]['vehicleConfigurationPropertyName'];
                    $prePdfData['tire_pressure_rear'] = $vehicleData['tires'][1]['vehicleConfigurationPropertyName'];

                    $pdfData = $prePdfData + $associations;
                    $this->generatePdfForNewConfig($pdfData, $pdf);
                    $pdfExportName = $this->pdfNamePrefix . $prePdfData['vin'] . '.pdf';
                    $pdfExportPath = $this->exportPath . $pdfExportName;
                    $pdf->Output('F', $pdfExportPath, true);
                }

                $protocolName = $this->generateAdditionalPages($dbColumn, $startValue, $endValue);
                return $protocolName;
            } else {
                return false;
            }
        }
        return true;
    }

    private function makeAssociations($prop, $propDesc)
    {
        $assocProp = str_replace(['undefined', 'Undefined'], '-', $prop[$propDesc]);

        return $assocProp;
    }

    private function generatePdfForOldConfig($pdfData, PdfGenerator &$pdf)
    {
        $barcodeFile = $this->generateBarcode($pdfData['vin']);

        $pdf->AddPage('P', 'A4');
        $pdf->SetAutoPageBreak(false, 0);

        // Vehicle accompanying document
        $pdf->SetFont($this->fontB, 'B', 30);
        $pdf->Cell(0, 20, 'Fahrzeugbegleitschein', 0, 0, 'C');
        $pdf->Ln();

        // VIN
        $pdf->SetFont($this->font, '', 18);
        $pdf->Cell(120, 8, 'Fahrgestellnummer: ');
        $pdf->Ln();

        $pdf->SetFont($this->fontB, 'B', 30);
        $pdf->Cell(120, 10, $pdfData['vin']);
        $pdf->Ln(15);

        // Penta
        $pdf->SetFont($this->font, '', 18);
        $pdf->Cell(120, 8, 'Konfiguration: ');
        $pdf->Cell(120, 8, 'Penta Kennwort: ');
        $pdf->Ln();
        $pdf->SetFont($this->fontB, 'B', 20);
        $pdf->Cell(120, 8, $pdfData['vehicle_configuration_key']);
        $pdf->Cell(120, 8, $pdfData['penta_kennwort'] ?? '-');
        $pdf->Ln();

        // Barcode
        $pdf->Ln(16);
        $ypos = $pdf->GetY();
        if (file_exists($barcodeFile))
            $pdf->Image($barcodeFile, 10, $ypos, 100);
        $pdf->Ln(30);

        // vehicle identification
        $pdf->SetFont($this->fontB, 'B', 20);
        $pdf->Cell(0, 10, 'Fahrzeugangaben:');
        $pdf->Ln();

        $ln_spacing = 5;

        // Penta number
        $pdf->SetFont($this->font, '', 11);
        $pdf->Cell(50, $ln_spacing, 'Penta Artikelnummer:');
        $pdf->SetFont($this->fontB, 'B', 11);
        $pdf->Cell(50, $ln_spacing, $pdfData['penta_variant_name'], 0, 1);

        // Vehicle type
        $pdf->SetFont($this->font, '', 11);
        $pdf->Cell(50, $ln_spacing, 'Fahrzeugtyp:');
        $pdf->SetFont($this->fontB, 'B', 11);
        $pdf->Cell(50, $ln_spacing, $pdfData['vehicle_type_name'] . $pdfData['vehicle_type_year'], 0, 1);

        // Color
        $pdf->SetFont($this->font, '', 11);
        $pdf->Cell(50, $ln_spacing, 'Fahrzeugfarbe:');
        $pdf->SetFont($this->fontB, 'B', 11);
        $pdf->Cell(50, $ln_spacing, iconv('UTF-8', 'windows-1252', $pdfData['configuration_color_name']), 0, 1);

        // Execution
        $pdf->SetFont($this->font, '', 11);
        $pdf->Cell(50, $ln_spacing, iconv('UTF-8', 'windows-1252', 'Ausführung:'));
        $pdf->SetFont($this->fontB, 'B', 11);
        $pdf->Cell(50, $ln_spacing, iconv('UTF-8', 'windows-1252', $pdfData['28'] ?? '-'), 0, 1);

        // Feature
        $pdf->SetFont($this->font, '', 11);
        $pdf->Cell(50, $ln_spacing, 'Merkmal:');
        $pdf->SetFont($this->fontB, 'B', 11);
        $pdf->Cell(50, $ln_spacing, iconv('UTF-8', 'windows-1252', $pdfData['29'] ?? '-') , 0, 1);

        // Battery
        $pdf->SetFont($this->font, '', 11);
        $pdf->Cell(50, $ln_spacing, 'Batterie:');
        $pdf->SetFont($this->fontB, 'B', 11);
        $pdf->Cell(50, $ln_spacing, iconv('UTF-8', 'windows-1252', $pdfData['30'] ?? '-'), 0, 1);

        // special feature
        $pdf->SetFont($this->font, '', 11);
        $pdf->Cell(50, $ln_spacing, 'Sondermerkmal:');
        $pdf->SetFont($this->fontB, 'B', 11);
        $pdf->Cell(150, $ln_spacing, $pdfData['additional_components'], 0, 1);

        $pdf->Ln();

        // Target country
        $pdf->SetFont($this->font, '', 11);
        $pdf->Cell(50, $ln_spacing, 'Zielstaat:');
        $pdf->SetFont($this->fontB, 'B', 11);
        $pdf->Cell(50, $ln_spacing, $pdfData['zielstaat'], 0, 1);

        // Front pressure
        $pdf->SetFont($this->font, '', 11);
        $pdf->Cell(50, $ln_spacing, 'Luftdruck vorne:');
        $pdf->SetFont($this->fontB, 'B', 11);
        $pdf->Cell(7, $ln_spacing, $pdfData['tire_pressure_front'], 0);
        $pdf->SetFont($this->font, '', 10);
        $pdf->Cell(15, $ln_spacing, ' kPa ', 0);
        $pdf->SetFont($this->fontI, 'I', 8);
        $pdf->Cell(6, $ln_spacing, '(' . intval($pdfData['tire_pressure_front'])/100, 0);
        $pdf->SetFont($this->fontI, 'I', 7);
        $pdf->Cell(4, $ln_spacing, ' bar ', 0);
        $pdf->SetFont($this->fontI, 'I', 8);
        $pdf->Cell(16, $ln_spacing, ')', 0, 1);

        // Rear pressure
        $pdf->SetFont($this->font, '', 11);
        $pdf->Cell(50, $ln_spacing, 'Luftdruck hinten:');
        $pdf->SetFont($this->fontB, 'B', 11);
        $pdf->Cell(7, $ln_spacing, $pdfData['tire_pressure_rear'], 0);
        $pdf->SetFont($this->font, '', 10);
        $pdf->Cell(15, $ln_spacing, ' kPa ', 0);
        $pdf->SetFont($this->fontI, 'I', 8);
        $pdf->Cell(6, $ln_spacing, '(' . intval($pdfData['tire_pressure_rear'])/100, 0);
        $pdf->SetFont($this->fontI, 'I', 7);
        $pdf->Cell(4, $ln_spacing, ' bar ', 0);
        $pdf->SetFont($this->fontI, 'I', 8);
        $pdf->Cell(16, $ln_spacing, ')', 0, 1);

        // V max
        $pdf->Ln(-15);
        $y = $pdf->GetY();
        $pdf->SetXY(110, $y);
        $pdf->SetFont($this->font, '', 11);
        $pdf->Cell(50, $ln_spacing, iconv('UTF-8', 'windows-1252', 'Höchstgeschwindigkeit:'));
        $pdf->SetFont($this->fontB, 'B', 11);
        $pdf->Cell(8, $ln_spacing, $pdfData['vmax'], 0);
        $pdf->SetFont($this->font, '', 10);
        $pdf->Cell(35, $ln_spacing, "km/h", 0, 1);

        // Max power
        $y = $pdf->GetY();
        $pdf->SetXY(110, $y);
        $pdf->SetFont($this->font, '', 11);
        $pdf->Cell(50, $ln_spacing, iconv('UTF-8', 'windows-1252', 'höchste Nennleistung:'));
        $pdf->SetFont($this->fontB, 'B', 11);
        $pdf->Cell(8, $ln_spacing, $pdfData['max_power'], 0);
        $pdf->SetFont($this->font, '', 10);
        $pdf->Cell(20, $ln_spacing, "kW", 0, 1);

        // Continous power
        $y = $pdf->GetY();
        $pdf->SetXY(110, $y);
        $pdf->SetFont($this->font, '', 11);
        $pdf->Cell(50, $ln_spacing, '30 min Dauerleistung:');
        $pdf->SetFont($this->fontB, 'B', 11);
        $pdf->Cell(8, $ln_spacing, $pdfData['max_power_30min'], 0);
        $pdf->SetFont($this->font, '', 10);
        $pdf->Cell(10, $ln_spacing, "kW", 0, 1);

        // Form paragraph
        $pdf->Ln(10);
        $pdf->SetFont($this->font, '', 10);
        $pdf->MultiCell(0, 4, iconv('UTF-8', 'windows-1252', 'Das Fahrzeug mit der o.g. Fahrgestellnummer wurde von mir geprüft; die spezifikationskonforme Montage wurde festgestellt.'));
        $pdf->Ln(5);

        $pdf->SetFont($this->font, '', 9);
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
        $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Datum:'));
        $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Unterschrift: '));
        $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Name in Druckbuchstaben:'));

        $pdf->Ln(10);

        $pdf->Cell(6, 6, '', 1);
        $pdf->Cell(2, 6, '');
        $pdf->Cell(50, 5, iconv('UTF-8', 'windows-1252', 'Freigabe Fahrzeugmängel beseitigt'));
        $pdf->Ln(20);

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Line($x, $y, $x + 40, $y);
        $pdf->Line($x + 50, $y, $x + 90, $y);
        $pdf->Line($x + 100, $y, $x + 170, $y);
        $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Datum:'));
        $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Unterschrift: '));
        $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Name in Druckbuchstaben:'));

        $this->generateFooter($pdf, $pdfData);
    }

    /**
     * @param string $vehicleVin Vehicle Vin
     * @return string File location
     */
    protected function generateBarcode(string $vehicleVin): string
    {
        $barcodeFilePath = $this->exportPath . 'barcode_vehicle_id_' . $vehicleVin . '.png';

        $qrCode = new BarcodeGenerator();
        $qrCode->setText($vehicleVin);
        $qrCode->setType(BarcodeGenerator::Code128);
        $qrCode->setScale(2);
        $qrCode->setThickness(25);
        $qrCode->setFontSize(10);
        $qrCode->setFilename($barcodeFilePath);

        $qrCode->generate();

        return $barcodeFilePath;
    }

    private function generatePdfForNewConfig($pdfData, PdfGenerator &$pdf)
    {
        $qrcodeFile = $this->generateQrCode($pdfData['vin']);

        $pdf->AddPage('P', 'A4');
        $pdf->SetAutoPageBreak(false, 0);
        // Vehicle accompanying document
        $pdf->SetFont($this->fontB, 'B', 30);
        $pdf->Cell(0, 20, 'Fahrzeugbegleitschein', 0, 0, 'C');
        $pdf->Ln();

        // VIN
        $pdf->SetFont($this->font, '', 15);
        $pdf->Cell(120, 8, 'Fahrgestellnummer (VIN): ');

        // Qrcode
        $ypos = $pdf->GetY();
        if (file_exists($qrcodeFile))
            $pdf->Image($qrcodeFile, 170, $ypos, 25);

        $pdf->Ln();

        $pdf->SetFont($this->fontB, 'B', 20);
        $pdf->Cell(120, 10, $pdfData['vin']);
        $pdf->Ln(15);

        // Short production description
        $pdf->SetFont($this->font, '', 15);
        $pdf->Cell(120, 8, 'Produktionskurzbezeichnung:');
        $pdf->Ln();

        $pdf->SetFont($this->fontB, 'B', 15);
        $pdf->Cell(120, 10, $pdfData['short_production_description']);
        $pdf->Ln(15);

        // Configuration Key and pentakennwort
        $pdf->SetFont($this->font, '', 15);
        $pdf->Cell(120, 8, 'Konfigurationsbezeichnung:');
        $pdf->Cell(120, 8, 'Penta-Kennwort:');
        $pdf->Ln();

        $pdf->SetFont($this->fontB, 'B', 15);
        $pdf->Cell(120, 10, $pdfData['vehicle_configuration_key']);
        $pdf->SetFont($this->fontB, 'B', 15);
        $pdf->Cell(120, 10, $pdfData['penta_kennwort']);
        $pdf->Ln();

        // Line
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Line($x, $y, $x + 190, $y);

        $pdf->SetFont($this->fontB, 'B', 20);
        $pdf->Cell(120, 12, 'Fahrzeugangaben:');
        $pdf->Ln();

        // Rest of document
        $pdf->SetFont($this->fontB, 'B', 8);

        // Define column size
        $pdf->SetWidths(array(42, 52, 2, 42, 52));

        // Row 1
        $pdf->PropertiesRowForNewConfig('Body', $pdfData['1'], 'Superstructures', $pdfData['15']);
        // Row 2
        $pdf->PropertiesRowForNewConfig('Number/Drive/Steered Axle', $pdfData['2'], 'Energy supply superstructure',
            $pdfData['16']);
        // Row 3
        $pdf->PropertiesRowForNewConfig('Continuous - / peak power', $pdfData['3'], 'Steering',
            $pdfData['17']);
        // Next row ;)
        $pdf->PropertiesRowForNewConfig('Stage of completion', $pdfData['4'], 'Glazing + mirror', $pdfData['18']);
        // Row 4
        $pdf->PropertiesRowForNewConfig('Length body - wheelbase ("L"-"W")', $pdfData['5'], 'Air conditioning',
            $pdfData['19']);
        // Row 5
        $pdf->PropertiesRowForNewConfig('VA-Last / Tire pressure front', $pdfData['6'] . ' / ' . intval($pdfData['tire_pressure_front'])/100 .
            ' bar', 'Passenger Airbag', $pdfData['20']);
        // Row 6
        $pdf->PropertiesRowForNewConfig('HA-Last / Tire pressure rear', $pdfData['7'] . ' / ' . intval
            ($pdfData['tire_pressure_rear'])/100 . ' bar', 'Keyless entry', $pdfData['21']);
        // Row 7
        $pdf->PropertiesRowForNewConfig('ZGG', $pdfData['8'], 'Special application area', $pdfData['22']);
        // Row 8
        $pdf->PropertiesRowForNewConfig('Type of fuel', $pdfData['9'], 'Radio', $pdfData['23']);
        // Row 9
        $pdf->PropertiesRowForNewConfig('Traction battery', $pdfData['10'], 'Sound generator', $pdfData['24']);
        // Row 10
        $pdf->PropertiesRowForNewConfig('Charging-system', $pdfData['11'], 'Country code', $pdfData['25']);
        // Row 11
        $pdf->PropertiesRowForNewConfig('V-Max fwd / reverse', $pdfData['12'], 'Color', $pdfData['26']);
        // Row 12
        $pdf->PropertiesRowForNewConfig('Seats', $pdfData['13'], 'Wheelings', $pdfData['27']);
        // Row 13
        $pdf->PropertiesRowForNewConfig('Trailer hitch', $pdfData['14'], '', '');

        // Form paragraph
        $pdf->Ln(5);
        $pdf->SetFont($this->font, '', 10);
        $pdf->MultiCell(0, 4, iconv('UTF-8', 'windows-1252', 'Das Fahrzeug mit der o.g. Fahrgestellnummer wurde von mir geprüft; die spezifikationskonforme Montage wurde festgestellt.'));


        $pdf->SetFont($this->font, '', 9);
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
        $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Datum:'));
        $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Unterschrift: '));
        $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Name in Druckbuchstaben:'));

        $pdf->Ln(10);

        $pdf->Cell(6, 6, '', 1);
        $pdf->Cell(2, 6, '');
        $pdf->Cell(50, 5, iconv('UTF-8', 'windows-1252', 'Freigabe Fahrzeugmängel beseitigt'));
        $pdf->Ln(20);

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Line($x, $y, $x + 40, $y);
        $pdf->Line($x + 50, $y, $x + 90, $y);
        $pdf->Line($x + 100, $y, $x + 170, $y);
        $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Datum:'));
        $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Unterschrift: '));
        $pdf->Cell(50, 4, iconv('UTF-8', 'windows-1252', 'Name in Druckbuchstaben:'));

        $this->generateFooter($pdf, $pdfData);
    }

    /**
     * @param string $vehicleVin Vehicle Vin
     * @return string File location
     * @throws Exception
     */
    protected function generateQrCode(string $vehicleVin): string
    {
        $qrCodePath = $this->exportPath . 'qrcode_vehicle_id_' . $vehicleVin . '.png';

        $qrCode = new QrCode();
        $qrCode
            ->setText($vehicleVin)
            ->setSize(300)
            ->setPadding(10)
            ->setErrorCorrection('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setLabel($vehicleVin)
            ->setLabelFontSize(21)
            ->setImageType(QrCode::IMAGE_TYPE_PNG);

        $encodedImage = $qrCode->generate();

        file_put_contents($qrCodePath, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $encodedImage)));

        return $qrCodePath;
    }

    /**
     * @param $pdf
     * @param $pdfData
     */
    protected function generateFooter(&$pdf, $pdfData)
    {
        $pdf->SetY(-16);
        $pdf->Line(10, $pdf->getY(), 199, $pdf->getY());

        $pdf->SetY(-12);
        $pdf->SetFont($this->font, '', 7);

        //here use $pdfData
        $pdf->Cell(0.01, 4, 'Dok.: ' . 'FB_0010/Rev. 0.1');
        $pdf->Cell(0, 4, 'FB_Erstellt: ' . ' A.Celebi/Zentrale/12.08.19', 0, 0, 'C');
        $pdf->Cell(0, 4, 'Freigegeben: ' . ' P.Ley/QM/xx.08.2019', 0, 0, 'R');
    }

    /**
     * @param string $dbColumn
     * @param string $startValue
     * @param string $endValue
     *
     * @return string
     */
    public function generateAdditionalPages(string $dbColumn, string
    $startValue, string $endValue) : string
    {

        $papersTypes = [
            'Deckblatt' => [
                'pdf' => null,
                'doctemplate' => null,
                'function' => function ($vehicle) {
                    return $this->generateExtra($vehicle);
                }],
            'EP' => [
                'pdf' => null,
                'doctemplate' => ['empty_page', 'fodt'],
                'function' => null
                ],
            'VOP' => [
                 'pdf' => null,
                 'doctemplate' => ['Vorlage_Onepager', 'fods'],
                 'function' => null
                ],
            'VFM' => [
                 'pdf' => null,
                 'doctemplate' => ['Vorlage_Fahrzeugmaengel', 'fodt'],
                 'function' => null
                ],
            'IPB_E17' => [
                 'pdf' => null,
                 'doctemplate' => ['Inbetriebnahmeprotokoll_Batterie_E17', 'fodt'],
                 'function' => null
                ],
            'KP_E17' => [
                  'pdf' => null,
                  'doctemplate' => ['Klebprotokoll_E17', 'fods'],
                  'function' => null
                ],
            'EP_LF' => [
                'pdf' => null,
                'doctemplate' => ['empty_page_LF', 'fodt'],
                'function' => null
            ],
            'SP_E17' => [
                'pdf' => null,
                'doctemplate' => ['Schraubprotokoll_E17', 'fods'],
                'function' => null
            ],
            'PT' => [
                'pdf' => null,
                'doctemplate' => ['protokoll_template', 'fodg'],
                'function' => null
            ],
            'KP_E1803' => [
                'pdf' => null,
                'doctemplate' => ['Klebprotokoll_E1803', 'fods'],
                'function' => null
            ],
            'KP_E18' => [
                'pdf' => null,
                'doctemplate' => ['Klebprotokoll_E18', 'fods'],
                'function' => null
            ],
            'KP_E1802' => [
                'pdf' => null,
                'doctemplate' => ['Klebprotokoll_E1802', 'fods'],
                'function' => null
            ],
            'IHV_E1803' => [
                'pdf' => null,
                'doctemplate' => ['Inbetriebnahmeprotokoll_HV_System_E1803', 'fodt'],
                'function' => null
            ],
            'IHV_E1802' => [
                'pdf' => null,
                'doctemplate' => ['Inbetriebnahmeprotokoll_HV_System_E1802', 'fodt'],
                'function' => null
            ],
            'IHV_E18' => [
                'pdf' => null,
                'doctemplate' => ['Inbetriebnahmeprotokoll_HV_System_E18', 'fodt'],
                'function' => null
            ],
            'DMK_E1803' => [
                'pdf' => null,
                'doctemplate' => ['Drehmomentkarte_E1803', 'fods'],
                'function' => null
            ],
            'DMK_E1802' => [
                'pdf' => null,
                'doctemplate' => ['Drehmomentkarte_E1802', 'fods'],
                'function' => null
            ],
            'DMK_E18' => [
                'pdf' => null,
                'doctemplate' => ['Drehmomentkarte_E18', 'fods'],
                'function' => null
            ],
            'SP_E18' => [
                'pdf' => null,
                'doctemplate' => ['Schraubprotokoll_E18', 'fods'],
                'function' => null
            ],
            'UGP' => [
                'pdf' => null,
                'doctemplate' => ['uebergabeprotokoll', 'fods'],
                'function' => null
            ],
            'IPB' => [
                'pdf' => null,
                'doctemplate' => ['Inbetriebnahmeprotokoll_Batterie', 'fodg'],
                'function' => null
            ],
        ];

        $configurationsTypes = [
            self::DEFAULT_TYPE => ['Deckblatt', 'PT', 'VFM', 'VAP'],
            self::D1610_TYPE => ['Deckblatt', 'EP', 'VAP', 'PT', 'VFM'],
            self::D17XX_TYPE => ['Deckblatt', 'EP', 'VAP', 'VOP', 'VFM', 'IPB_E17', 'EP', 'KP_E17', 'EP_LF', 'SP_E17'],
            self::AFTER_D1702_TYPE => ['Deckblatt', 'EP', 'VAP', 'EP', 'VFM', 'IPB_E17', 'EP', 'KP_E17', 'EP_LF',
                'SP_E17'],
            self::E18XX_TYPE => ['Deckblatt', 'EP', 'VAP', 'EP', 'VFM', 'IHV_E18', 'EP', 'KP_E18', 'EP_LF', 'DMK_E18'],
            self::E1802_TYPE => ['Deckblatt', 'EP', 'VAP', 'EP', 'VFM', 'IHV_E1802', 'KP_E1802', 'EP_LF', 'DMK_E1802'],
            self::E1803_TYPE => ['Deckblatt', 'EP', 'VAP', 'EP', 'VFM', 'IHV_E1803', 'KP_E1803', 'EP_LF', 'DMK_E1803'],
            self::E2001_TYPE => ['Deckblatt', 'EP']
        ];

        $detectType = function ($type, $year, $series) : int {
            if ($type == "D") {
                if ($year == 16) {
                    if ($series == 10) {
                        return self::D1610_TYPE;
                    } else {
                        return self::DEFAULT_TYPE;
                    }
                } elseif ($year == 17) {
                    if ($series < 2) {
                        return self::D17XX_TYPE;
                    } else {
                        return self::AFTER_D1702_TYPE;
                    }
                }
            } elseif ($type < "D") {
                return self::DEFAULT_TYPE;
            } elseif ($type == "E") {
                if ($year == 18) {
                    if ($series == 2) {
                        return self::E1802_TYPE;
                   } elseif ($series == 3) {
                        return self::E1803_TYPE;
                   } else {
                        return self::E18XX_TYPE;
                   }
                } elseif ($year == 20) {
                    if ($series == 1) {
                        return self::E2001_TYPE;
                   } else {
                        return self::DEFAULT_TYPE;
                    }
                } else {
                    return self::DEFAULT_TYPE;
                }
            } else {
                return self::DEFAULT_TYPE;
            }
        };

        $vehicles = [];
        if (! empty($endValue)) {
            $start_vehicle_id = $this->manager->getRepository(Vehicles::class)->getVehicleIdByCol($dbColumn, $startValue);
              $end_vehicle_id = $this->manager->getRepository(Vehicles::class)->getVehicleIdByCol($dbColumn, $endValue);
            if (!is_null($start_vehicle_id) && !is_null($end_vehicle_id)) {
                $vehicles = $this->manager->getRepository(Vehicles::class)->getVehiclesByScope(
                    $start_vehicle_id['vehicleId'], $end_vehicle_id['vehicleId'], $dbColumn);
            }
        }
        else {
            $vehicles = $this->manager->getRepository(Vehicles::class)->getVehiclesFromSalesAndStart($startValue);
        }

        $pdf_merge_files = [];

        foreach ($vehicles as $vehicle) {
            $type = $vehicle['divisionType'];
            $year = (int)$vehicle['divisionYear'];
            $series = (int)$vehicle['divisionSeries'];

           $detectedType = $detectType($type, $year, $series);

           $papersTypes['VAP'] = [
                   'pdf' => 'accompanying_document_' . $vehicle['vin'] . '.pdf',
                   'doctemplate' => null,
                   'function'  => null
               ];

           foreach ($configurationsTypes[$detectedType] as $index) {

               if (!is_null($papersTypes[$index]['pdf']) && !empty($papersTypes[$index]['pdf'])) {
                   $pdf_merge_files[] = $this->path . DIRECTORY_SEPARATOR . 'pdf_temp/' . $papersTypes[$index]['pdf'];
               } elseif (!is_null($papersTypes[$index]['doctemplate'])) {
                   $pdf_merge_files[] = $this->MakeVehicleModifiedDocument($papersTypes[$index]['doctemplate'][0],
                       $papersTypes[$index]['doctemplate'][1], $vehicle);
               } elseif (!is_null($papersTypes[$index]['function'])) {
                   $pdf_merge_files[] = $papersTypes[$index]['function']($vehicle);
               }
           }
        }

        $timestamp = date('Y-m-d_H-i-s');
        $dataDir = $this->path . DIRECTORY_SEPARATOR . "pdf_temp/";
        $outputName = "protocol_". $timestamp . ".pdf";
        $outputPath = $dataDir . $outputName;
        $cmd = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=$outputPath ";

        foreach($pdf_merge_files as $file) {
            $cmd .= $file." ";
        }

        shell_exec($cmd);
        foreach ($pdf_merge_files as $filename) {

            if (file_exists($filename)){
                unlink($filename);
            }
        }

        return $outputName;
    }

    private function MakeVehicleModifiedDocument($filename, $ext, $vehicle)
    {
        $templateFilename = $this->path . DIRECTORY_SEPARATOR . "doctemplate/$filename.$ext";
        $vehicleFilename = $this->path . DIRECTORY_SEPARATOR . "pdf_temp/$filename-{$vehicle['vehicle_id']}";
        $fhandle = fopen($templateFilename, 'r');
        $fcontent = fread($fhandle, filesize($templateFilename));
        $fhandle_new = fopen("$vehicleFilename.$ext", 'w');
        $replacevals = array(
            'VIN_HERE_VIN' => $vehicle['vin']
        );
        $fcontent = str_replace(array_keys($replacevals), array_values($replacevals), $fcontent);

        fwrite($fhandle_new, $fcontent);
        fclose($fhandle);
        fclose($fhandle_new);

        $path = $this->path . DIRECTORY_SEPARATOR ."pdf_temp/";

        exec("export HOME=$path && libreoffice --headless --convert-to pdf $vehicleFilename.$ext --outdir $path");

        if (file_exists("$vehicleFilename.$ext"))
            unlink("$vehicleFilename.$ext");

        return "$vehicleFilename.pdf";
    }

    private function generateExtra($vehicle)
    {
        $vehicle_info = $this->manager->getRepository(Vehicles::class)->getVehicleInfo($vehicle['vehicle_id']);

        $pdf = new FPDFTwoCol();

        $pdf->AddPage('L', 'A4');
        $pdf->SetFont('Arial', '', 18);
        $pdf->Cell(0, 10, 'Penta Kennwort: ', 0, 1);
        $pdf->SetFont('Arial', 'B', 60);
        if (!empty($vehicle_info['pentaKennwort'])) $pdf->Cell(0, 30, $vehicle_info['pentaKennwort'], 0, 1);
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
//        $pdf->AddPage('L', 'A4');
        $type = $vehicle['type'];

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
//            $pdf->AddPage('L', 'A4');
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
        if (preg_match('/WS(.)*/', $vehicle['vin']) && $detparts = []) {

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

        $pdf->Output('F', $this->path . DIRECTORY_SEPARATOR . 'pdf_temp/extra_' . $vehicle['vehicle_id'] . '.pdf', true);
        $extraFileName = $this->path . DIRECTORY_SEPARATOR . "pdf_temp/extra_" . $vehicle['vehicle_id'] . ".pdf";

        return $extraFileName;
    }

}