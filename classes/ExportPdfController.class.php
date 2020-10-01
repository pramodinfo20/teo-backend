<?php


class ExportPdfController extends SymfonyBaseController
{
    protected $user;

    public function __construct($ladeLeitWartePtr, $container, $requestPtr, $user)
    {   parent::__construct($ladeLeitWartePtr, $container, $requestPtr, 'export',
        [], [], 'sales');

        /*Parameters to avoid */
        //parent::setParametersToAvoid();
        /*Parameters without keys */
        //parent::setParametersWithoutKeys();

        $this->user = $user;

        if (isset($_GET['call'])) {
            $name = $_GET['call'];
            $this->$name();
        }
    }
    private function exportPdfAjaxCallPost($options = [])
    {
        try {
            $exportedPdfName = $this->getMiddleware()
                ->prepare(Middleware::REQUEST_TYPE_POST, 'export'. $this->createPath($_GET), $options)
                ->sentRequest();
            $exportedPdfNameDecoded = json_decode($exportedPdfName);
            $mergedPdfName = $this->saveExportPdf($exportedPdfNameDecoded->{'accompanying_document_name'});
//            if (file_exists($exportedPdfName)){
//                unlink($filename);
//            }

            if (!empty($exportedPdfNameDecoded->{'accompanying_document_name'}) && !empty($mergedPdfName)) {
                echo json_encode(array(
                    'status' => 'success',
                    'exported_pdf_name' => $mergedPdfName,
                ));
            } else {
                echo json_encode(array(
                    'status' => 'error',
                ));
            }

        } catch (MiddlewareException $middlewareException) {
            $this->setHideLegacyOutput(true);

            echo $middlewareException->getResponse();
        }
    }

    private function saveExportPdf($accompanyingDocumentName)
    {
        $request = new Request();

        $dbCol = $request->getProperty('db_col');
        $startVal = $request->getProperty('start_val');
        $endVal = $request->getProperty('end_val');

        if (! empty($endVal)) {
            $start_vehicle_id = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                ->where($dbCol, '=', $startVal)
                ->getVal('vehicle_id');
            $end_vehicle_id = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                ->where($dbCol, '=', $endVal)
                ->getVal('vehicle_id');
            $vehicles = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                ->where("vehicle_id", ">=", $start_vehicle_id)
                ->where("vehicle_id", "<=", $end_vehicle_id)
                ->join('colors', 'vehicles.color_id=colors.color_id', 'FULL OUTER JOIN')
                ->orderBy($dbCol, 'ASC')
                ->get('vehicle_id,vin,code,ikz,colors.name as colorname');
        } else {

            $vehicles = $this->ladeLeitWartePtr->vehiclesPtr->newQuery()
                ->join('vehicles_sales', 'vehicles_sales.vehicle_id=vehicles.vehicle_id', 'INNER JOIN')
                ->join('colors', 'vehicles.color_id=colors.color_id', 'FULL OUTER JOIN')
                ->where("vehicles_sales.vorhaben", "=", $startVal)
                ->get('vehicles.vehicle_id,vin,code,vehicles.ikz,colors.name  as colorname');
        }

        $salesProtokoll = new SalesProtokoll($vehicles, $this->ladeLeitWartePtr, $accompanyingDocumentName);
        $mergedPdfName = $salesProtokoll->getMergedPdfName();

        return $mergedPdfName;
    }
}