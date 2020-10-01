<?php

namespace App\Controller\Parameters;

use App\Controller\LegacyBaseController;
use App\Entity\GlobalParameters;
use App\Entity\SubVehicleConfigurations;
use App\Entity\VehicleConfigurations;
use App\Factory\Menu;
use App\Form\Parameter\GlobalParametersValuesType;
use App\Service\Parameter\GlobalParameter;
use App\Service\Parameter\Menu\Footer;
use App\Service\Vehicles\Configuration\SubConfiguration;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/parameters/global/values")
 */
class GlobalValuesController extends LegacyBaseController
{
    /**
     * @Route("/", methods={"GET"}, name="global_values_sets")
     * @param Request $request
     * @param GlobalParameter $globalParametersService
     * @param SubConfiguration $subConfigurationService
     * @return Response
     * @throws Exception
     */
    public function index(Request $request, GlobalParameter $globalParametersService, SubConfiguration $subConfigurationService): Response
    {
        $search = null;
        $mode = $request->get('mode', 1);
        $subConf = null;
        $globalParameters = null;
        $global = null;
        $configurationParameters = null;
        $valueSet = null;

        if ($request->get('global', 0) != 0) {
            $search = [
                'type' => $request->get('type', 0),
                'year' => $request->get('year', 0),
                'series' => $request->get('series', 0),
                'customerKey' =>  $this->getDoctrine()->getManager()->getRepository(SubVehicleConfigurations::class)->
                    find($request->get('configuration'))->getVehicleConfiguration()->getVehicleCustomerKey(),
                'configuration' => $request->get('configuration', 0),
                'global' => $request->get('global', 0)
            ];

            $subConf = $this->getManager()->getRepository(SubVehicleConfigurations::class)
                ->find($request->get('configuration'));
            $global = $this->getManager()->getRepository(GlobalParameters::class)
                ->find($request->get('global', 0));
            $globalParameters = $globalParametersService->getValues($subConf, $global);
            $configurationParameters = $subConfigurationService->getParametersToView($subConf);
        }

        /* Set static factory */
        Menu::setObjectManager($this->getManager());

        $parametersButtons = [
            'mode' => $mode,
            'global' => $global,
            'subConf' => $subConf,
            'globalParameters' => $globalParameters
        ];

        $arguments = [
            'search' => $search,
            'globalParameters' => $globalParameters,
            'global' => $global,
            'mode' => $mode,
            'parameters' => $configurationParameters,
            'parametersButtonsState' => Menu::create(Footer::class)->setArguments($parametersButtons)
                ->build()->getMenu()
        ];

        if ($mode == 2) {
            $arguments['parametersForm'] = $this->createForm(GlobalParametersValuesType::class,
                $globalParameters)->createView();
        }

        return $this->render('Parameters/Global/Values/index.html.twig', $arguments);
    }

    /**
     * @Route("/type/{type}/year/{year}/series/{series}/configuration/{configuration}/global/{global}",
     *     methods={"GET"},
     *     name="index_with_global"
     * )
     * @param string $type
     * @param int    $year
     * @param string $series
     * @param int    $configuration
     * @param int    $global
     *
     * @return Response
     */
    public function indexWithGlobal(string $type, int $year, string $series, int $configuration, int $global): Response
    {
        return $this->forward('App\Controller\Parameters\GlobalValuesController::index', [
            'type' => $type,
            'year' => $year,
            'series' => $series,
            'configuration' => $configuration,
            'global' => $global
        ]);
    }

    /**
     * @Route("/type/{type}/year/{year}/series/{series}/configuration/{configuration}/global/{global}/mode/{mode}",
     *     methods={"GET"},
     *     name="index_with_global_and_mode"
     * )
     * @param string $type
     * @param int    $year
     * @param string $series
     * @param int    $configuration
     * @param int    $global
     * @param int    $mode
     *
     * @return Response
     */
    public function indexWithGlobalAndMode(string $type, int $year, string $series, int $configuration, int $global, int $mode): Response
    {
        return $this->forward('App\Controller\Parameters\GlobalValuesController::index', [
            'type' => $type,
            'year' => $year,
            'series' => $series,
            'configuration' => $configuration,
            'global' => $global,
            'mode' => $mode
        ]);
    }

    /**
     * @Route("/subconfiguration/{subconfiguration}/globals/get",
     *     methods={"GET"},
     *     name="ajax_globals_get"
     * )
     * @param SubVehicleConfigurations $subconfiguration
     * @param GlobalParameter $globalParameterService
     *
     * @return JsonResponse
     */
    public function getGlobals(SubVehicleConfigurations $subconfiguration, GlobalParameter $globalParameterService):
    JsonResponse
    {
        $globals = $globalParameterService->getGlobalsForSubConf($subconfiguration);

        return $this->json($globals);
    }

    /**
     * @Route("/globals/configuration/{subVehicleConfigurations}/global/{global}/save",
     *     methods={"POST"},
     *     name="ajax_globals_save_set"
     * )
     * @param Request                  $request
     *
     * @param SubVehicleConfigurations $subVehicleConfigurations
     * @param GlobalParameters         $global
     * @param GlobalParameter          $globalParameterService
     *
     * @return Response
     * @throws Exception
     */
    public function saveGlobalValueSet(
        Request $request,
        SubVehicleConfigurations $subVehicleConfigurations,
        GlobalParameters $global,
        GlobalParameter $globalParameterService
    ): Response
    {
        $globalParameter = $globalParameterService->getValues($subVehicleConfigurations, $global);

        $form = $this->createForm(GlobalParametersValuesType::class, $globalParameter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Save non-entity almost as real entity object but
             * we have to decide how to do this, force developer
             * to use model classes instead of working on controller.
             */
            $globalParameterService->save($globalParameter, $subVehicleConfigurations);

            return $this->renderSuccessJson();
        }

        return $this->renderFormErrors($form);
    }

    /**
     * @Route("/configurations/get",
     *     methods={"GET"},
     *     name="ajax_configurations_get"
     * )
     * @return JsonResponse
     */
    public function getConfigurations(): JsonResponse
    {
        $configurations = $this->getDoctrine()->getManager()->getRepository(VehicleConfigurations::class)->getVehicleConfigurationsInDevelopmentStatus();

        $filteredConfigurations = [];

        foreach ($configurations as $configuration) {
            $filteredConfigurations[$configuration['vehicleTypeName']][$configuration['vehicleTypeYear']]
            [$configuration['vehicleSeries']][$configuration['vehicleConfigurationId']]
            [$configuration['subVehicleConfigurationId']]['configuration'] = $configuration['vehicleConfigurationKey'];
            $filteredConfigurations[$configuration['vehicleTypeName']][$configuration['vehicleTypeYear']]
            [$configuration['vehicleSeries']][$configuration['vehicleConfigurationId']]
            [$configuration['subVehicleConfigurationId']]['subconfiguration'] = $configuration['subVehicleConfigurationName'];
        }

        return $this->json($filteredConfigurations);
    }


    /**
     * @Route("/globals/configuration/{subVehicleConfigurations}/global/{global}/copy",
     *     methods={"POST"},
     *     name="ajax_copy_value"
     * )
     * @param Request                  $request
     * @param SubVehicleConfigurations $subVehicleConfigurations
     * @param GlobalParameters         $global
     * @param GlobalParameter          $globalService
     *
     * @return JsonResponse
     */
    public function copyValue(
        Request $request,
        SubVehicleConfigurations $subVehicleConfigurations,
        GlobalParameters $global,
        GlobalParameter $globalService
    ): JsonResponse
    {

        $result = $globalService->copyValue($subVehicleConfigurations, $global, json_decode($request->get('jsonWithSubConfigurations')), true);
        return $this->json($result);
    }
}