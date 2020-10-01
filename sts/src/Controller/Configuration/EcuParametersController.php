<?php

namespace App\Controller\Configuration;

use App\Controller\LegacyBaseController;
use App\Entity\ConfigurationEcus;
use App\Entity\EcuSubConfigurationVehicleContainment;
use App\Entity\EcuSwVersions;
use App\Entity\SubVehicleConfigurations;
use App\Entity\VehicleConfigurations;
use App\Enum\Entity\HistoryEvents;
use App\Enum\HistoryTypes;
use App\Factory\Menu;
use App\Form\Configuration\HeaderType;
use App\Form\Configuration\Odx\Odx1ParameterCollectionType;
use App\Form\Configuration\Odx\Odx2ParameterCollectionType;
use App\Form\History\HistoryCommentType;
use App\Form\History\HistorySelectorSearchType;
use App\History\Strategies\SoftwareManagementStrategy;
use App\Model\History\HistoryComment;
use App\Model\History\Search\HistorySelector;
use App\Service\Configuration\Header;
use App\Service\History\Configuration\HistoryHeader;
use App\Service\History\Configuration\HistoryParameter;
use App\Service\History\Configuration\HistorySoftwareVersion;
use App\Service\Configuration\Menu\Footer;
use App\Service\Configuration\Parameter;
use App\Service\Vehicles\Configuration\Configurations;
use App\Service\Vehicles\Configuration\SubConfiguration;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/configuration")
 */
class EcuParametersController extends LegacyBaseController
{
    /**
     * @Route("/", methods={"GET"}, name="vehicle_parameters")
     * @param Request                       $request
     * @param SubConfiguration              $subConfigurationService
     * @param Parameter                     $parameter
     * @param Header                        $header
     * @param SoftwareManagementStrategy    $history
     *
     * @return Response
     */
    public function index(
        Request $request,
        SubConfiguration $subConfigurationService,
        Parameter $parameter,
        Header $header,
        SoftwareManagementStrategy $history
    ): Response
    {
        $search = null;
        $supportOdx2 = null;
        $swParameters = null;
        $headerParameters = null;
        $configurationParameters = null;
        $sw = null;
        $mode = $request->get('mode', 1);
        $configuration = $request->get('configuration', null);
        $ecu = $request->get('ecu', 0); //?? ($responsiblePersons->loadResponsibleEcuByUserId($user) ?? $request->get('ecu'));
        $ecu = ($ecu != 0) ? $ecu : null;
        $subConfId = null;


        if ($request->get('regenerateView', false)) {
            $subConfigurationObj = $this->getManager()
                ->getRepository(SubVehicleConfigurations::class)->find($request->get('configuration'));
            $search = [
                'type' => ($request->get('type', 0) == 0) ?
                    $subConfigurationObj->getVehicleConfiguration()->getVehicleTypeName() : $request->get('type', 0),
                'year' => ($request->get('year', 0) == 0) ?
                    $subConfigurationObj->getVehicleConfiguration()->getVehicleTypeYear() : $request->get('year', 0),
                'series' => ($request->get('series', 0) == 0) ?
                    $subConfigurationObj->getVehicleConfiguration()->getVehicleSeries() : $request->get('series', 0),
                'customerKey' =>  $this->getDoctrine()->getManager()->getRepository(SubVehicleConfigurations::class)->
                find($request->get('configuration'))->getVehicleConfiguration()->getVehicleCustomerKey(),
                'configuration' => $request->get('configuration', 0),
                'ecu' => $request->get('ecu', 0),
                'sw' => $request->get('sw', 0),
            ];
            if ($request->get('sw', 0) != 0) {
                $supportOdx2 = $this->getManager()
                    ->getRepository(ConfigurationEcus::class)
                    ->findOneBy(['ceEcuId' => $request->get('ecu', 0)])->getDiagnosticSoftwareSupportsStsOdx2ForThisEcu();

                $odx = $request->get('odx', ((!is_null($supportOdx2)) ? ($supportOdx2 ? 2 : 1) : 2));
                $subConfId = $this->getDoctrine()->getManager()->getRepository(SubVehicleConfigurations::class)->find($request->get('configuration'));
                $sw = $this->getDoctrine()->getManager()->getRepository(EcuSwVersions::class)->find($request->get('sw'));
                $headerParameters = $header->getHeader($sw, $subConfId);
                $swParameters = $parameter->getParameters($odx, $headerParameters->getProtocol()->getEcuCommunicationProtocolName(), $sw, $subConfId);
                $headerParameters = $header->getHeader($sw, $subConfId);
            }

            $subConfId = $this->getDoctrine()->getManager()->getRepository(SubVehicleConfigurations::class)->find($request->get('configuration'));
            $configurationParameters = $subConfigurationService->getParametersToView($subConfId);
        }

        $odx = $request->get('odx', ((!is_null($supportOdx2)) ? ($supportOdx2 ? 2 : 1) : 2));

        /* Set static factory */
        Menu::setObjectManager($this->getManager());
        $parametersButtons = [
            'mode' => $mode,
            'subConfId' => $subConfId,
        ];

        $arguments = [
            'search' => $search,
            'parameters' => $configurationParameters,
            'swParameters' => (!is_null($swParameters)) ? $swParameters->getParameters() : null,
            'header' => $headerParameters,
            'configuration' => $configuration,
            'odx' => $odx,
            'mode' => $mode,
            'ecu' => $ecu,
            'supportOdx2' => $supportOdx2,
            'sw' => ($sw) ? $sw->getEcuSwVersionId() : null,
            'parametersButtonsState' => Menu::create(Footer::class)->setArguments($parametersButtons)
                ->build()->getMenu()
        ];

        if ($mode == 2) {
            $arguments['headerForm'] = $this->createForm(HeaderType::class, $headerParameters)
                ->createView();

            $arguments['parametersForm'] = $this->createForm(
                ($odx == 2) ? Odx2ParameterCollectionType::class : Odx1ParameterCollectionType::class,
                $swParameters)->createView();
        }

        if ($configuration) {
            $historySelector = new HistorySelector();
            $historySelector->setEvent(HistoryEvents::UPDATE);

            $options = [
                'historyTable' => $history->getTableName(),
                'fkId' => $configuration
            ];

            $arguments['historyForm'] = $this->createForm(HistorySelectorSearchType::class, $historySelector, $options)
                ->createView();
            $arguments['historyLog'] = true;
        }

        $comment = new HistoryComment();
        $arguments['commentForm'] = $this->createForm(HistoryCommentType::class, $comment)->createView();

        $arguments['historyType'] = HistoryTypes::SOFTWARE_MANAGEMENT;


        return $this->render("Configuration/index.html.twig", $arguments);
    }

    /**
     * @Route("/configuration/{configuration}",
     *     methods={"GET"},
     *     name="index_with_configurationId"
     * )
     * @param int    $configuration
     *
     * @return Response
     */
    public function indexWithConfigurationId(

        int $configuration
    ): Response
    {
        return $this->forward('App\Controller\Configuration\EcuParametersController::index', [
            'configuration' => $configuration,
            'regenerateView' => true
        ]);
    }

    /**
     * @Route("/type/{type}/year/{year}/series/{series}/configuration/{configuration}",
     *     methods={"GET"},
     *     name="index_with_configuration_conf"
     * )
     * @param string $type
     * @param int    $year
     * @param string $series
     * @param int    $configuration
     *
     * @return Response
     */
    public function indexWithConfiguration(
        string $type,
        int $year,
        string $series,
        int $configuration
    ): Response
    {
        return $this->forward('App\Controller\Configuration\EcuParametersController::index', [
            'type' => $type,
            'year' => $year,
            'series' => $series,
            'configuration' => $configuration,
            'regenerateView' => true
        ]);
    }

    /**
     * @Route("/configuration/{configuration}/ecu/{ecu}/sw/{sw}",
     *     methods={"GET"},
     *     name="index_with_confId_and_sw"
     * )
     * @param int    $configuration
     * @param int    $ecu
     * @param int    $sw
     *
     * @return Response
     */
    public function indexWithConfigurationIdAndSw(int $configuration, int $ecu, int $sw): Response
    {
        return $this->forward('App\Controller\Configuration\EcuParametersController::index', [
            'configuration' => $configuration,
            'ecu' => $ecu,
            'sw' => $sw,
            'regenerateView' => true
        ]);
    }

    /**
     * @Route("/type/{type}/year/{year}/series/{series}/configuration/{configuration}/ecu/{ecu}/sw/{sw}",
     *     methods={"GET"},
     *     name="index_with_sw"
     * )
     * @param string $type
     * @param int    $year
     * @param string $series
     * @param int    $configuration
     * @param int    $ecu
     * @param int    $sw
     *
     * @return Response
     */
    public function indexWithSw(string $type, int $year, string $series, int $configuration, int $ecu, int $sw): Response
    {
        return $this->forward('App\Controller\Configuration\EcuParametersController::index', [
            'type' => $type,
            'year' => $year,
            'series' => $series,
            'configuration' => $configuration,
            'ecu' => $ecu,
            'sw' => $sw,
            'regenerateView' => true
        ]);
    }

    /**
     * @Route("/type/{type}/year/{year}/series/{series}/configuration/{configuration}/ecu/{ecu}/sw/{sw}/mode/{mode}",
     *     methods={"GET"},
     *     name="index_with_sw_and_mode"
     * )
     * @param string $type
     * @param int    $year
     * @param string $series
     * @param int    $configuration
     * @param int    $ecu
     * @param int    $sw
     * @param int mode
     *
     * @return Response
     */
    public function indexWithSwAndMode(string $type, int $year, string $series, int $configuration, int $ecu, int $sw, int $mode): Response
    {
        return $this->forward('App\Controller\Configuration\EcuParametersController::index', [
            'type' => $type,
            'year' => $year,
            'series' => $series,
            'configuration' => $configuration,
            'ecu' => $ecu,
            'sw' => $sw,
            'regenerateView' => true,
            'mode' => $mode
        ]);
    }


    /**
     * @Route("/type/{type}/year/{year}/series/{series}/configuration/{configuration}/ecu/{ecu}/sw/{sw}/odx/{odx}",
     *     methods={"GET"},
     *     name="index_with_sw_and_odx"
     * )
     * @param string $type
     * @param int    $year
     * @param string $series
     * @param int    $configuration
     * @param int    $ecu
     * @param int    $sw
     * @param int mode
     *
     * @return Response
     */
    public function indexWithSwAndOdx(string $type, int $year, string $series, int $configuration, int $ecu, int $sw, int $odx): Response
    {
        return $this->forward('App\Controller\Configuration\EcuParametersController::index', [
            'type' => $type,
            'year' => $year,
            'series' => $series,
            'configuration' => $configuration,
            'ecu' => $ecu,
            'sw' => $sw,
            'regenerateView' => true,
            'odx' => $odx
        ]);
    }

    /**
     * @Route("/type/{type}/year/{year}/series/{series}/configuration/{configuration}/ecu/{ecu}/sw/{sw}/odx/{odx}/mode/{mode}",
     *     methods={"GET"},
     *     name="index_with_sw_and_odx_and_mode"
     * )
     * @param string $type
     * @param int    $year
     * @param string $series
     * @param int    $configuration
     * @param int    $ecu
     * @param int    $sw
     * @param int    $odx
     * @param int    $mode
     *
     * @return Response
     */
    public function indexWithSwAndOdxAndMode
    (
        string $type,
        int $year,
        string $series,
        int $configuration,
        int $ecu,
        int $sw,
        int $odx,
        int $mode
    ): Response
    {
        return $this->forward('App\Controller\Configuration\EcuParametersController::index', [
            'type' => $type,
            'year' => $year,
            'series' => $series,
            'configuration' => $configuration,
            'ecu' => $ecu,
            'sw' => $sw,
            'regenerateView' => true,
            'odx' => $odx,
            'mode' => $mode
        ]);
    }

    /**
     * @Route("/{id}/main/showConfiguration", methods={"GET"}, name="show_main_configuration")
     * @param VehicleConfigurations $vehicleConfigId
     * @param Configurations        $configurationService
     *
     * @return Response
     */
    public function showMainConfiguration(
        VehicleConfigurations $vehicleConfigId,
        Configurations $configurationService
    ): Response
    {
        $parameters = $configurationService->getKeyParametersToView($vehicleConfigId);

        return $this->render("Vehicles/Configuration/partials/keyComponents.html.twig", ['parameters' => $parameters]);
    }

    /**
     * @Route("/{id}/showConfiguration", methods={"GET"}, name="show_configuration")
     * @param SubVehicleConfigurations $subVehicleConfigId
     * @param SubConfiguration         $subConfigurationService
     *
     * @return Response
     */
    public function showConfiguration(
        SubVehicleConfigurations $subVehicleConfigId,
        SubConfiguration $subConfigurationService
    ): Response
    {
        $parameters = $subConfigurationService->getParametersToView($subVehicleConfigId);

        return $this->render("Vehicles/Configuration/partials/keyComponents.html.twig", ['parameters' => $parameters]);
    }

    /**
     * @Route("/getSupportedECUs/{id}", methods={"GET"}, name="ajaxGetSupportedECUs")
     * @param $id
     *
     * @return JsonResponse
     */
    public function getSupportedECUs($id): JsonResponse
    {
        $supportedEcus = $this->getDoctrine()->getManager()
            ->getRepository(EcuSubConfigurationVehicleContainment::class)->findAllEcusByConfigurationById($id);

        return $this->json($supportedEcus);
    }

    /**
     * @Route("/ecu/{ecuId}/subconfiguration/{subConfId}/primary/{primary}", methods={"GET"}, name="ajax_get_sws")
     * @param $ecuId
     * @param $subConfId
     * @param $primary
     *
     * @return JsonResponse
     */
    public function getSws(int $ecuId, int $subConfId, int $primary): JsonResponse
    {
        $primarySws = $this->getDoctrine()->getManager()
            ->getRepository(EcuSwVersions::class)->findAllReleasedSwsByEcuIdAndSubConfIdAndPrimary($ecuId, $subConfId, (bool)$primary);

        return $this->json($primarySws);
    }

    /**
     * @Route("/subconfiguration/{subconf}/sw/{swId}/ecu/{ecu}/primary/{primary}/assign", methods={"GET"},
     *                                                                                    name="ajax_assign_sw")
     * @param HistorySoftwareVersion   $historySoftwareVersionService
     * @param SubVehicleConfigurations $subconf
     * @param int                      $swId
     * @param ConfigurationEcus        $ecu
     * @param int                      $primary
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function assignSw
    (
        HistorySoftwareVersion $historySoftwareVersionService,
        SubVehicleConfigurations $subconf,
        ConfigurationEcus $ecu,
        int $swId,
        int $primary
    ): JsonResponse
    {
        try {
            if ($swId != 0) {
                $sw = $this->getDoctrine()->getManager()->getRepository(EcuSwVersions::class)->find($swId);
                $historySoftwareVersionService->assignSw($subconf, $sw, $ecu, (bool)$primary);
            } else {
                $historySoftwareVersionService->removeSwAssignment($subconf, $ecu, (bool)$primary);
            }

            return $this->renderSuccessJson();
        } catch (\Exception $exception) {
            return $this->renderFailureJson([$exception->getMessage()]);
        }
    }

    /**
     * @Route("/ecu/{ecu}/sw/{sw}/odx/{odx}/subConfiguration/{subConfig}/header",
     *     methods={"POST"},
     *     name="ecu_parameters_update_header"
     * )
     * @param Request                  $request
     * @param Header                   $headerService
     * @param HistoryHeader            $historyHeaderService
     * @param ConfigurationEcus        $ecu
     * @param EcuSwVersions            $sw
     * @param SubVehicleConfigurations $subConfig
     *
     * @return Response
     * @throws \Exception
     */
    public function header
    (
         Request $request,
         Header $headerService,
         HistoryHeader $historyHeaderService,
         ConfigurationEcus $ecu,
         EcuSwVersions $sw,
         SubVehicleConfigurations $subConfig
    ): Response
    {
        try {
            $header = $headerService->getHeader($sw, $subConfig);

            $form = $this->createForm(HeaderType::class, $header);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /* Save non-entity almost as real entity object but
                 * we have to decide how to do this, force developer
                 * to use model classes instead of working on controller.
                 */
                $historyHeaderService->save($header, $ecu, $subConfig);

                return $this->renderSuccessJson();
            }

            return $this->renderFormErrors($form);
        } catch (\Exception $exception) {
            return $this->renderFailureJson([$exception->getMessage()]);
        }
    }


    /**
     * @Route("/ecu/{ecu}/sw/{sw}/odx/{odx}/subConfiguration/{subConfig}/parameters",
     *     methods={"POST"},
     *     name="ecu_parameters_update_parameters"
     * )
     * @param Request                  $request
     * @param ConfigurationEcus        $ecu
     * @param EcuSwVersions            $sw
     * @param SubVehicleConfigurations $subConfig
     * @param Header                   $header
     * @param Parameter                $parameterService
     * @param HistoryParameter         $historyParameterService
     * @param int                      $odx
     *
     * @return Response
     * @throws \Exception
     */
    public function parameters(
        Request $request,
        ConfigurationEcus $ecu,
        EcuSwVersions $sw,
        SubVehicleConfigurations $subConfig,
        Header $header,
        Parameter $parameterService,
        HistoryParameter $historyParameterService,
        int $odx
    ): Response
    {
        try{
            $header = $header->getHeader($sw, $subConfig);

            $collection = $parameterService->getParameters(
                $odx,
                $header->getProtocol()->getEcuCommunicationProtocolName(),
                $sw,
                $subConfig
            );

            $form = $this->createForm(
                ($odx == 2) ? Odx2ParameterCollectionType::class : Odx1ParameterCollectionType::class,
                $collection
            );
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /* Save non-entity almost as real entity object but
                 * we have to decide how to do this, force developer
                 * to use model classes instead of working on controller.
                 */
                $historyParameterService->save($collection, $sw, $subConfig, $odx);

                return $this->renderSuccessJson();

            }

            return $this->renderFormErrors($form);
        } catch (\Exception $exception) {
            return $this->renderFailureJson([$exception->getMessage()]);
        }
    }


}