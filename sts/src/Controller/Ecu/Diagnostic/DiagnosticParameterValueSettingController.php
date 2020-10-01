<?php

namespace App\Controller\Ecu\Diagnostic;


use App\Controller\LegacyBaseController;
use App\Entity\ConfigurationEcus;
use App\Service\Ecu\Diagnostic\Parameter\DiagnosticParameterValueSetting;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ecu/diagnostic/parameter")
 */
class DiagnosticParameterValueSettingController extends LegacyBaseController
{
    /**
     * @Route("/", name="diagnostic_parameter_index", methods={"GET"})
     */
    public function index(): Response
    {
        $Ecus = $this->getManager()
            ->getRepository(ConfigurationEcus::class)
            ->findBy([], ['ecuName' => 'ASC']);

        return $this->render("Ecu/Diagnostic/Parameter/index.html.twig", [
            'ecus' => $Ecus,
        ]);
    }

    /**
     * @Route("/save_support/ecu/{ecu}", name="save_support_flag", methods={"POST"})
     * @ParamConverter ("ecu", class="App:ConfigurationEcus")
     * @param Request                         $request
     * @param ConfigurationEcus               $ecu
     * @param DiagnosticParameterValueSetting $diagnosticParameter
     *
     * @return JsonResponse
     */
    public function updateSupportODX(Request $request, ConfigurationEcus $ecu, DiagnosticParameterValueSetting $diagnosticParameter): JsonResponse
    {
        $response = $diagnosticParameter->saveSupportFlagById($ecu, $request->request->get('supportODX'));
        return $this->json($response);
    }

}