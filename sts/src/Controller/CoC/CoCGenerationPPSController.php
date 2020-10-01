<?php

namespace App\Controller\CoC;

use App\Controller\LegacyBaseController;
use App\Service\CoCGenerate\ExportCoCToPdf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/coc-pps")
 */
class CoCGenerationPPSController extends LegacyBaseController
{
    /**
     * @Route("/", methods={"GET"}, name="coc_pps_generation_index")
     * @return Response
     */
    public function index(): Response {
        return $this->render('CoCGeneration/pps.html.twig');
    }

    /**
     * @Route("/pdf/generate-pps", methods={"POST"}, name="pps_generate_coc_pdf")
     * @param Request        $request
     *
     * @return Response
     */
    public function generateCoCPdfEngineering(Request $request, ExportCoCToPdf $generateCoCToPdf): Response {

        $dbCol = $request->get('db_col');
        $startValue = $request->get('start_val');
        $endValue = $request->get('end_val');
        $lang = $request->get('lang');
        $isTestPrint = $request->get('is_test_print');
        $cocDate = $request->get('coc_date');
        $authSignatoryConcated = $request->get('auth_signatory');

        if(empty($cocDate)) {
            $cocDate = date('Y-m-d');
        }

        $generatedPdfName = $generateCoCToPdf->beginGeneratingCoCPdf($dbCol, $startValue, $endValue, $cocDate, $lang,
            $isTestPrint, $authSignatoryConcated);

        if (!empty($generatedPdfName)){
            return $this->json([
                'status' => 'success',
                'generated_pdf_name' => $generatedPdfName,
            ]);
        } else {
            return $this->json([
                'status' => 'error',
            ]);
        }

    }
}
