<?php

namespace App\Service\CoCGenerate;

use App\Entity\CocParameters;
use App\Entity\CocParameterValuesSetsMapping;
use App\Entity\SubVehicleConfigurations;
use App\Entity\VehicleConfigurationPropertiesMapping;
use App\Entity\VehicleConfigurations;
use App\Entity\Vehicles;
use App\Service\Vehicles\Configuration\SubConfiguration;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Yaml\Yaml;


/**
 * Class ExportCoCToPdf
 * @package App\Service\CoCGenerate
 */
class ExportCoCToPdf extends AbstractController
{
    var $widths;
    var $aligns;
    protected $manager;
    protected $subConfiguration;
    protected $font;
    protected $fontB;
    protected $exportPath;
    protected $SSLogoPath;
    protected $isVehicleComplete;

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
        $this->exportPath = $this->path . DIRECTORY_SEPARATOR . 'pdf_temp/';
        $this->SSLogoPath = __DIR__.'/../../../../images/Logo_StreetScooter_Vertical.png';
        $this->isVehicleComplete = true;
    }
    
    public function beginGeneratingCoCPdf(String $dbColumn, String $startValue, String $endValue, String $cocDate,
                                          String $lang, String $isTestPrint, String $authSignatoryConcated)
    {
        $pdfLabels = [];
        $isTestPrint = boolval($isTestPrint);
        $authSignatoryUnconcatedArray = explode('__', $authSignatoryConcated);
        $undersigned = $authSignatoryUnconcatedArray[0];
        $officialPosition = $authSignatoryUnconcatedArray[1];
        //pdfData is data from db
        $pdfData = [];
        //value to checking witch print should be generated (complete or incomplete)
        $pdfData['is_test_print'] = $isTestPrint;
        $pdfData['units'] = 'metric';
        $pdfData['driver_side'] = 'right';
        $pdfData['language'] = $lang;
        $pdfData['coc_date'] = $cocDate;
        $pdfData['undersigned'] = $undersigned;
        $pdfData['official_position'] = $officialPosition;

        $translationsPath = $this->getParameter('kernel.project_dir').'/translations/cocGenTemp.' . $lang . '.yml';
        $parsedTranslations = Yaml::parse(file_get_contents($translationsPath));

        foreach ($parsedTranslations['cocTranslations'] as $key => $value) {
            $pdfLabels[$key] = $value;
        }
        $pdf = new CoCPdfGenerator();

        if ($dbColumn == 'vehicle_configuration') {

            $vehicleConfiguration = $this->manager->getRepository(VehicleConfigurations::class)->findOneBy(['vehicleConfigurationKey' => $startValue]);
            if (!empty($vehicleConfiguration)) {
                $vehicleConfigurationId = $vehicleConfiguration->getVehicleConfigurationId();
                $this->checkIsCompleteVehicle($vehicleConfigurationId);
            } else {
                return false;
            }
            try {
                $subVehicleConfigurations = $this->getDoctrine()->getRepository(SubVehicleConfigurations::class)->findBy
                (['vehicleConfiguration' => $vehicleConfiguration], ['subVehicleConfigurationId' => 'ASC']);
                $vehicles = array();
            } catch (\Doctrine\ORM\NoResultException $e) {
                return false;
            }
            $i = 0;

            foreach ($subVehicleConfigurations as $subVehicleConfiguration) {
                $vehiclesForSubConf = $this->getVehiclesForSubConf($subVehicleConfiguration);

                if (!empty($vehiclesForSubConf)) {
                    $this->getPdfDataAndGeneratePdf($pdf, $vehiclesForSubConf, $pdfLabels, $pdfData, $dbColumn);

                    foreach ($vehiclesForSubConf as $car) {
                        $vehicles[$i] = $car;
                        $i++;
                    }
                }
            }

            if (empty($vehicles)) {
                return false;
            }
//            var_dump($vehicles);

        } else if ($dbColumn == 'date_of_delivery') {
            $startDate = new \DateTime($startValue.' 00:00:00');
            $endDate = new \DateTime($startValue.' 23:59:59.999999');
            $vehicles = $this->getDoctrine()->getRepository(Vehicles::class)->getVinByDeliveryDate($startDate, $endDate);

            if (!empty($vehicles)) {
                $this->getPdfDataAndGeneratePdf($pdf,$vehicles, $pdfLabels, $pdfData, $dbColumn);
            } else {
                return false;
            }
        }
        else if ($dbColumn == 'pentaKennwort') {
            try {
                $vehicles = $this->getDoctrine()->getRepository(Vehicles::class)->getVehiclesByPentaKennwort($startValue, $endValue);
            } catch (\Doctrine\ORM\NoResultException $e) {

                return false;
            }

            if (!empty($vehicles)) {
                foreach ($vehicles as $vehicle) {
                    $vehicleConfigurationId = $vehicle['vehicleConfigurationId'];
                    $this->checkIsCompleteVehicle($vehicleConfigurationId);
                }
            } else {
                return false;
            }

            $this->getPdfDataAndGeneratePdf($pdf, $vehicles, $pdfLabels,$pdfData, $dbColumn);
        }
        else {
            try {
                $vehicles = $this->getDoctrine()->getRepository(Vehicles::class)->getVehiclesId($dbColumn, $startValue, $endValue);
            } catch (\Doctrine\ORM\NoResultException $e) {
                return false;
            }

            if (!empty($vehicles)) {
                foreach ($vehicles as $vehicle) {
                    $vehicleConfigurationId = $vehicle['vehicleConfigurationId'];
                    $this->checkIsCompleteVehicle($vehicleConfigurationId);
                }
            } else {
                return false;
            }

            $this->getPdfDataAndGeneratePdf($pdf, $vehicles, $pdfLabels,$pdfData, $dbColumn);
        }

        if (!empty($isTestPrint)) {
            $prePdfExportName = 'CoC-Testdruck-';
        } else {
            $prePdfExportName = 'CoC-';
        }
     
        $this->role = &$_SESSION['role']['current'];
        
        if (isset($vehicles) && !empty($vehicles)) {
            $vinFrom = $vehicles[0]['vin'];
            $approvalDate = $vehicles[0]['approvalDate'];
            $vinTo = $vehicles[count($vehicles) - 1]['vin'];
            $postPdfExportName = $vinFrom . '-' . $vinTo . '_';
        }

        $datePdfExportName = date('Y-m-d_H-i_');
        $pdfExportName = $prePdfExportName . $datePdfExportName . $postPdfExportName . $lang . '.pdf';

        $pdfExportPath = $this->exportPath . $pdfExportName;
        $pdf->Output('F', $pdfExportPath, true);
        if (empty($approvalDate) && $this->role == 'sales')
            return false;
        else 
            return $pdfExportName;
    }

    protected  function  getPdfDataAndGeneratePdf(&$pdf, &$vehicles, &$pdfLabels, &$pdfData,$dbColumn)
    {
        $pdf->AddFont($this->font, '', 'OpenSans-Regular.php');
        $pdf->AddFont($this->fontB, 'B', 'OpenSans-Bold.php');

        foreach ($vehicles as $vehicle){
            $pdfData['vehicle_id'] = $vehicle['vehicleId'];
            $pdfData['seq_number'] = $vehicle['seqNumber'];
            $pdfData['year'] = $vehicle['year'];
            $pdfData['approval_code'] = $vehicle['approvalCode'];
            $pdfData['approval_date'] = $vehicle['approvalDate'];

            $pdfData = $this->getCpmvsm($vehicle['subVehicleConfigurationId'], $pdfData);

            $pdfData['vin'] = $vehicle['vin'];

            if ($dbColumn == 'date_of_delivery')
                $this->checkIsCompleteVehicle($vehicle['vehicleConfigurationId']);

            $this->generatePdfForCoc($pdfData, $pdfLabels, $pdf);

        }

        return $pdf;
    }

    // protected  function  getPdfDataAndGeneratePdf(&$pdf, &$vehicles, &$pdfLabels, &$pdfData,$dbColumn)
    // {
    //     $pdf->AddFont($this->font, '', 'OpenSans-Regular.php');
    //     $pdf->AddFont($this->fontB, 'B', 'OpenSans-Bold.php');
    //     var_dump($vehicles);
    //     foreach ($vehicles as $vehicle){
    //         $pdfData['vehicle_id'] = $vehicle['vehicleId'];
    //         $pdfData['seq_number'] = $vehicle['seqNumber'];
    //         $pdfData['year'] = $vehicle['year'];
    //         $pdfData['approval_code'] = $vehicle['approvalCode'];
    //         $pdfData['approval_date'] = $vehicle['approvalDate'];

    //         $pdfData = $this->getCpmvsm($vehicle['subVehicleConfigurationId'], $pdfData);

    //         $pdfData['vin'] = $vehicle['vin'];

    //         if ($dbColumn == 'date_of_delivery')
    //             $this->checkIsCompleteVehicle($vehicle['vehicleConfigurationId']);
            
    //         // print COC-Paper just for complete Vehicle or test-print
    //         /*if(!empty($pdfData['approval_code']) && !empty($pdfData['approval_date']) || $pdfData['is_test_print'] == true)
    //             $this->generatePdfForCoc($pdfData, $pdfLabels, $pdf);
    //         else 
    //             $this->generatePdfBlankPage($pdfData, $pdfLabels, $pdf);*/
    //         $this->generatePdfForCoc($pdfData, $pdfLabels, $pdf);

    //     }

    //     return $pdf;
    // }

    protected  function getCpmvsm (&$subVehConfId, &$pdfData)
    {
        $cocAll = $this->getDoctrine()->getRepository(CocParameters::class)->findAll();
        $cpmvsm = $this->getDoctrine()->getRepository(CocParameterValuesSetsMapping::class)
            ->getCpnBySubVehConfId($subVehConfId);

        $cocMappedNames = [];

        foreach($cpmvsm as $item) {
            $cocMappedNames[] = $item['cocParameterName'];
        }

        foreach($cocAll as $coc) {
            foreach ($cpmvsm as $item) {
                $cocName = $coc->getCocParameterName();
                if (!in_array($cocName, $cocMappedNames)) {
                    $pdfData[$cocName] = '';
                } else {
                    if (!empty($item['valueS']))
                      $pdfData[$item['cocParameterName']] = $item['valueS'];
                    if (!empty($item['valueB']))
                      $pdfData[$item['cocParameterName']] = $item['valueB'];
                    if (!empty($item['valueD']))
                      $pdfData[$item['cocParameterName']] = $item['valueD'];
                    if (!empty($item['valueH']))
                      $pdfData[$item['cocParameterName']] = $item['valueH'];
                    if (!empty($item['valueI']))
                      $pdfData[$item['cocParameterName']] = $item['valueI'];
                }
            }
        }

        return $pdfData;
    }

    protected function checkIsCompleteVehicle($vehicleConfigurationId)
    {
        $vehicleIncomplete = $this->getDoctrine()->getRepository(VehicleConfigurationPropertiesMapping::class)
            ->findOneBy([
                'vehicleConfiguration' => $vehicleConfigurationId,
                'allowedSymbols' => 21,
                'vcProperty' => 15,
            ]);

        if(empty($vehicleIncomplete))
            $this->isVehicleComplete = true;
        else
            $this->isVehicleComplete = false;
    }

    protected function generatePdfForCoc($pdfData, $pdfLabels, &$pdf)
    {
        $pdfData['is_vehicle_complete'] = $this->isVehicleComplete;
        $pdfData['font'] = $this->font;
        $pdfData['fontB'] = $this->fontB;
        $config = [
            'font' => $this->font,
            'fontB' => $this->fontB,
            'page_number' => 1,
            'SS_logo_path' => $this->SSLogoPath,
            'is_vehicle_complete' => $pdfData['is_vehicle_complete'],
            'is_test_print' => $pdfData['is_test_print'],
            'page_text' => $pdfLabels['page'],
            'watermark' => $pdfLabels['watermark'],
            'legal_paper_label' => $pdfLabels['legalPaperLabel'],
            'year_label' => $pdfLabels['year'],
            'seq_number_label' => $pdfLabels['seqNumber'],
            'year' => $pdfData['year'],
            'seq_number' => $pdfData['seq_number'],
            'eccofc' => $pdfLabels['eccofc'],
            'complete_vehicle' => $pdfLabels['completeVehicle'],
            'incomplete_vehicle' => $pdfLabels['incompleteVehicle'],
        ];
        //first page of coc
        $pdf->makeBlankPage($config);
        $pdf->makeFirstPageTable($this->font, $pdfLabels, $pdfData);
        $pdf->makeFirstPageLongText($pdfLabels, $pdfData);
        $pdf->makeSignaturePlaces($pdfLabels, $pdfData);
        //second page od coc
        $config['page_number'] = 2;
        $pdf->makeBlankPage($config);
        $pdf->makeSecondPageUniversal($this->font, $pdfLabels, $pdfData);

        if ($pdfData['language'] == 'de') {
            $pdf->makeAdditionalApprovalData($pdfData);
        }
    }

    protected function getVehiclesForSubConf($subVehicleConfiguration) {
        $subVehConfId = $subVehicleConfiguration->getSubVehicleConfigurationId();

        try {
            $vehicles = $this->getDoctrine()
                ->getRepository(Vehicles::class)
                ->getVinBySubConfigurationId($subVehConfId);
            return $vehicles;
        } catch (\Doctrine\ORM\NoResultException $e) {
            return false;
        }
    }
}