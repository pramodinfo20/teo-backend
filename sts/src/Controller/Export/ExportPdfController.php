<?php

namespace App\Controller\Export;

use App\Controller\LegacyBaseController;
use App\Service\ExportPDF\ExportVehicleToPdf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/export")
 */
class ExportPdfController extends LegacyBaseController
{

    /**
     * @Route("/", methods={"GET"}, name="export_pdf_index")
     * @return Response
     */
    public function index(): Response {
        return $this->render('Export/pdf/index.html.twig');
    }

    /**
     * @Route("/pdf/generate", methods={"POST"}, name="generate_vehicle_pdf_list")
     * @param Request $request
     * @param ExportVehicleToPdf $exportVehicleToPdf
     * @return Response
     */
    public function generatePdf(Request $request, ExportVehicleToPdf $exportVehicleToPdf): Response {

        $dbCol = $request->get('db_col');
        $startValue = $request->get('start_val');
        $endValue = $request->get('end_val');
        $exportedPdfName = $exportVehicleToPdf->generateVehiclePdf($dbCol, $startValue, $endValue);

        if (!empty($exportedPdfName)){
            return $this->json([
                'status' => 'success',
                'exported_pdf_name' => $exportedPdfName,
            ]);
        } else {
            return $this->json([
                'status' => 'error',
            ]);
        }

    }
}