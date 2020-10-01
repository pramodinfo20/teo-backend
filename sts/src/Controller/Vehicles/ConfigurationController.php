<?php

namespace App\Controller\Vehicles;

use App\Controller\LegacyBaseController;
use App\Entity\SubVehicleConfigurations;
use App\Entity\Users;
use App\Entity\VehicleConfigurationPropertiesSymbols;
use App\Entity\VehicleConfigurations;
use App\Entity\Vehicles;
use App\Enum\Entity\HistoryEvents;
use App\Enum\HistoryTypes;
use App\Factory\Menu;
use App\Form\History\HistoryCommentType;
use App\Form\History\HistorySelectorSearchType;
use App\Form\SubConfiguration\LongKeyEditType;
use App\Form\SubConfiguration\LongKeyFixType;
use App\Form\SubConfiguration\LongKeyType;
use App\Form\SubConfiguration\ShortKeyEditType;
use App\Form\SubConfiguration\ShortKeyFixType;
use App\Form\SubConfiguration\ShortKeyType;
use App\Form\SubConfiguration\ConfigurationLongKeyEditType;
use App\Form\SubConfiguration\ConfigurationShortKeyEditType;
use App\History\Strategies\VehicleConfigurationSvcManagementStrategy;
use App\History\Strategies\VehicleConfigurationVcManagementStrategy;
use App\Model\History\HistoryComment;
use App\Model\History\Search\HistorySelector;
use App\Model\LongKeyModel;
use App\Model\ShortKeyModel;
use App\Service\History\Vehicles\Configuration\HistoryConfigurations;
use App\Service\History\Vehicles\Configuration\HistorySubConfiguration;
use App\Service\Vehicles\Configuration\Configurations;
use App\Service\Vehicles\Configuration\Menu\Bottom;
use App\Service\Vehicles\Configuration\SubConfiguration;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/vehicles/configuration")
 */
class ConfigurationController extends LegacyBaseController
{
    /**
     * @Route("/", methods={"GET"}, name="ajax_vehicles_configurations")
     * @param Request                                   $request
     *
     * @param Configurations                            $configurationsService
     * @param SubConfiguration                          $subConfigurationService
     *
     * @param VehicleConfigurationVcManagementStrategy  $historyConfiguration
     * @param VehicleConfigurationSvcManagementStrategy $historySubConfiguration
     *
     * @return Response
     */
    public function index(
        Request $request,
        Configurations $configurationsService,
        SubConfiguration $subConfigurationService,
        VehicleConfigurationVcManagementStrategy $historyConfiguration,
        VehicleConfigurationSvcManagementStrategy $historySubConfiguration
    ): Response
    {
        $configurationId = $request->get('vc', null);
        $subConfigurationId = $request->get('subVc', null);
        $mode = $request->get('mode', Bottom::START_MODE);
        $action = $request->get('action', null);
        $keyType = $request->get('type', null);
        $parameters = null;
        $form = null;

        $configuration = null;
        $subConfiguration = null;
        $search = null;

        $arguments['commentForm'] = [];
        $arguments['historyType']  = null;

        if ($mode != Bottom::START_MODE) {
            if (!is_null($configurationId)) {
                $arguments['historyType'] = HistoryTypes::VEHICLE_CONFIGURATION_VC_MANAGEMENT;

                $options = [
                    'historyTable' => $historyConfiguration->getTableName(),
                    'fkId' => $configurationId
                ];


                $configuration = $this->getManager()->getRepository(VehicleConfigurations::class)
                    ->find($configurationId);
                $parameters = $configurationsService->getKeyParametersToView($configuration);

                if ($mode == Bottom::EDIT_MODE) {
                    $configurationModel = $configurationsService->getModelToEdit($configuration);

                    if ($configurationsService->getConfigurationType($configuration) == SubConfiguration::SHORT_KEY) {
                        $keyType = SubConfiguration::SHORT_KEY;
                        $form = $this->createForm(ConfigurationShortKeyEditType::class, $configurationModel)
                            ->createView();
                    } else if ($configurationsService->getConfigurationType($configuration) == SubConfiguration::LONG_KEY) {
                        $keyType = SubConfiguration::LONG_KEY;
                        $form = $this->createForm(ConfigurationLongKeyEditType::class, $configurationModel)
                            ->createView();
                    }
                } else if ($mode == Bottom::FIX_MODE) {
                    $configurationModel = $configurationsService->getModelToEdit($configuration);

                    if ($configurationsService->getConfigurationType($configuration) == SubConfiguration::SHORT_KEY) {
                        if ($configuration->getDraft()) {
                            $keyType = SubConfiguration::SHORT_KEY;
                            $form = $this->createForm(ConfigurationShortKeyEditType::class, $configurationModel)
                                ->createView();
                        }
                    } else if ($configurationsService->getConfigurationType($configuration) == SubConfiguration::LONG_KEY) {
                        if ($configuration->getDraft()) {
                            $keyType = SubConfiguration::LONG_KEY;
                            $form = $this->createForm(ConfigurationLongKeyEditType::class, $configurationModel)
                                ->createView();
                        }
                    }
                }

                $search = [
                    'type' => ($request->get('type1', 0) == 0) ?
                        $configuration->getVehicleTypeName() : $request->get('type1', 0),
                    'year' => ($request->get('year', 0) == 0) ?
                        $configuration->getVehicleTypeYear() : $request->get('year', 0),
                    'series' => ($request->get('series', 0) == 0) ?
                        $configuration->getVehicleSeries() : $request->get('series', 0),
                    'customerKey' => $configuration->getVehicleCustomerKey(),
                    'configuration' => $configurationId,
                    'dataType' => 'configuration'
                ];
            }

            if (!is_null($subConfigurationId)) {
                $arguments['historyType'] = HistoryTypes::VEHICLE_CONFIGURATION_SVC_MANAGEMENT;

                $options = [
                    'historyTable' => $historySubConfiguration->getTableName(),
                    'fkId' => $subConfigurationId
                ];

                $subConfiguration = $this->getManager()->getRepository(SubVehicleConfigurations::class)
                    ->find($subConfigurationId);
                $parameters = $subConfigurationService->getParametersToView($subConfiguration);

                if ($mode == Bottom::EDIT_MODE) {
                    $subConfigurationModel = $subConfigurationService->getSubConfiguration($subConfiguration);

                    if ($subConfigurationService->getConfigurationType($subConfiguration) == SubConfiguration::SHORT_KEY) {
                        $keyType = SubConfiguration::SHORT_KEY;
                        $form = $this->createForm(ShortKeyEditType::class, $subConfigurationModel)
                            ->createView();
                    } elseif ($subConfigurationService->getConfigurationType($subConfiguration) == SubConfiguration::LONG_KEY) {
                        $keyType = SubConfiguration::LONG_KEY;
                        $form = $this->createForm(LongKeyEditType::class, $subConfigurationModel)
                            ->createView();
                    }
                } else if ($mode == Bottom::FIX_MODE) {

                    $subConfigurationModel = $subConfigurationService->getSubConfiguration($subConfiguration);

                    if ($subConfigurationService->getConfigurationType($subConfiguration) == SubConfiguration::SHORT_KEY) {
                        if ($subConfiguration->getDraft()) {
                            $keyType = SubConfiguration::SHORT_KEY;
                            $form = $this->createForm(ShortKeyFixType::class, $subConfigurationModel)
                                ->createView();
                        }
                    } elseif ($subConfigurationService->getConfigurationType($subConfiguration) == SubConfiguration::LONG_KEY) {
                        if ($subConfiguration->getDraft()) {
                            $keyType = SubConfiguration::LONG_KEY;
                            $form = $this->createForm(LongKeyFixType::class, $subConfigurationModel)
                                ->createView();
                        }
                    }
                } else if ($mode == Bottom::CREATE_COPY_MODE) {
                    $subConfigurationModel = $subConfigurationService->getSubConfiguration($subConfiguration);

                    if ($subConfigurationService->getConfigurationType($subConfiguration) == SubConfiguration::SHORT_KEY) {
                        $keyType = SubConfiguration::SHORT_KEY;
                        $form = $this->createForm(ShortKeyType::class, $subConfigurationModel)
                            ->createView();
                    } elseif ($subConfigurationService->getConfigurationType($subConfiguration) == SubConfiguration::LONG_KEY) {
                        $keyType = SubConfiguration::LONG_KEY;
                        $form = $this->createForm(LongKeyType::class, $subConfigurationModel)
                            ->createView();
                    }
                }

                $search = [
                    'type' => ($request->get('type1', 0) == 0) ?
                        $subConfiguration->getVehicleConfiguration()->getVehicleTypeName() : $request->get('type1', 0),
                    'year' => ($request->get('year', 0) == 0) ?
                        $subConfiguration->getVehicleConfiguration()->getVehicleTypeYear() : $request->get('year', 0),
                    'series' => ($request->get('series', 0) == 0) ?
                        $subConfiguration->getVehicleConfiguration()->getVehicleSeries() : $request->get('series', 0),
                    'customerKey' => $subConfiguration->getVehicleConfiguration()->getVehicleCustomerKey(),
                    'configuration' => $subConfigurationId,
                    'dataType' => 'subconfiguration'
                ];
            }

            if ($mode == Bottom::CREATE_MODE) {
                $subConfigurationModel = ($keyType == 1) ? new ShortKeyModel() : new LongKeyModel();

                if ($keyType == 1) {
                    $form = $this->createForm(ShortKeyType::class, $subConfigurationModel)
                        ->createView();
                } else {
                    $form = $this->createForm(LongKeyType::class, $subConfigurationModel)
                        ->createView();
                }

                if (is_null($arguments['historyType'])) {
                    $arguments['historyType'] = HistoryTypes::VEHICLE_CONFIGURATION_VC_MANAGEMENT;

                    $comment = new HistoryComment();
                    $arguments['commentForm'] = $this->createForm(HistoryCommentType::class, $comment)->createView();
                }
            } else {

                $historySelector = new HistorySelector();
                $historySelector->setEvent(HistoryEvents::UPDATE);

                $arguments['historyForm'] = $this->createForm(HistorySelectorSearchType::class, $historySelector, $options)
                    ->createView();

                $comment = new HistoryComment();
                $arguments['commentForm'] = $this->createForm(HistoryCommentType::class, $comment)->createView();
            }
        }




        $parametersButtons = [
            'configuration' => $configuration,
            'subConfiguration' => $subConfiguration,
            'mode' => $mode
        ];

        /* Set static factory */
        Menu::setObjectManager($this->getManager());
        $arguments = array_merge($arguments, [
            'menuState' => Menu::create(Bottom::class)
                ->setArguments($parametersButtons)
                ->build()
                ->getMenu(),
            'configurationId' => $configurationId,
            'subVehConfigId' => $subConfigurationId,
            'parameters' => $parameters,
            'action' => $action,
            'form' => $form,
            'mode' => $mode,
            'search' => $search,
            'type' => $keyType
        ]);

        return $this->render('Vehicles/Configuration/index.html.twig', $arguments);
    }

    /**
     * @Route("/{subVehicleConfigId}/view", name="index_with_subvc", methods={"GET"})
     * @param SubVehicleConfigurations $subVehicleConfigId
     *
     * @return Response
     */
    public function indexWithSubVcIdAndViewMode(
        SubVehicleConfigurations $subVehicleConfigId
    ): Response
    {
        return $this->forward('App\Controller\Vehicles\ConfigurationController::index', [
            'subVc' => $subVehicleConfigId->getSubVehicleConfigurationId(),
            'mode' => Bottom::PREVIEW_MODE
        ]);
    }

    /**
     * @Route("/{configurationId}/main/view", name="index_with_vc",
     *     methods={"GET"})
     * @param VehicleConfigurations $configurationId
     *
     * @return Response
     */
    public function indexWithVcIdAndViewMode(
        VehicleConfigurations $configurationId
    ): Response
    {
        return $this->forward('App\Controller\Vehicles\ConfigurationController::index', [
            'vc' => $configurationId->getVehicleConfigurationId(),
            'mode' => Bottom::PREVIEW_MODE
        ]);
    }

    /**
     * @Route("/{subVehicleConfigId}/type/{type}/year/{year}/series/{series}/view", name="index_with_subvc_view", methods={"GET"})
     * @param SubVehicleConfigurations $subVehicleConfigId
     *
     * @param string                   $type
     * @param int                      $year
     * @param string                   $series
     *
     * @return Response
     */
    public function indexWithSubVcAndViewMode(
        SubVehicleConfigurations $subVehicleConfigId,
        string $type,
        int $year,
        string $series
    ): Response
    {
        return $this->forward('App\Controller\Vehicles\ConfigurationController::index', [
            'subVc' => $subVehicleConfigId->getSubVehicleConfigurationId(),
            'mode' => Bottom::PREVIEW_MODE,
            'type1' => $type,
            'year' => $year,
            'series' => $series
        ]);
    }

    /**
     * @Route("/{configurationId}/type/{type}/year/{year}/series/{series}/main/view",
     *     name="index_with_vc_view",
     *     methods={"GET"})
     * @param VehicleConfigurations $configurationId
     * @param string                $type
     * @param int                   $year
     * @param string                $series
     *
     * @return Response
     */
    public function indexWithVcAndViewMode(
        VehicleConfigurations $configurationId,
        string $type,
        int $year,
        string $series
    ): Response
    {
        return $this->forward('App\Controller\Vehicles\ConfigurationController::index', [
            'vc' => $configurationId->getVehicleConfigurationId(),
            'mode' => Bottom::PREVIEW_MODE,
            'type1' => $type,
            'year' => $year,
            'series' => $series
        ]);
    }

    /**
     * @Route("/{subVehicleConfigId}/type/{type}/year/{year}/series/{series}/edit", methods={"GET"}, name="index_with_subVc_edit")
     * @param SubVehicleConfigurations $subVehicleConfigId
     *
     * @param string                   $type
     * @param int                      $year
     * @param string                   $series
     *
     * @return Response
     */
    public function indexWithSubVcAndEditMode(
        SubVehicleConfigurations $subVehicleConfigId,
        string $type,
        int $year,
        string $series
    ): Response
    {
        return $this->forward('App\Controller\Vehicles\ConfigurationController::index', [
            'subVc' => $subVehicleConfigId->getSubVehicleConfigurationId(),
            'mode' => Bottom::EDIT_MODE,
            'type1' => $type,
            'year' => $year,
            'series' => $series
        ]);
    }

    /**
     * @Route("/{configurationId}/type/{type}/year/{year}/series/{series}/configuration/edit", methods={"GET"}, name="index_with_vc_edit")
     * @param VehicleConfigurations $configurationId
     * @param string                $type
     * @param int                   $year
     * @param string                $series
     *
     * @return Response
     */
    public function indexWithVcAndEditMode(
        VehicleConfigurations $configurationId,
        string $type,
        int $year,
        string $series
    ): Response
    {
        return $this->forward('App\Controller\Vehicles\ConfigurationController::index', [
            'vc' => $configurationId->getVehicleConfigurationId(),
            'mode' => Bottom::EDIT_MODE,
            'action' => 'edit',
            'type1' => $type,
            'year' => $year,
            'series' => $series
        ]);
    }

    /**
     * @Route("/{subVehicleConfigId}/type/{type}/year/{year}/series/{series}/fix", methods={"GET"}, name="index_with_subVc_fix")
     * @param SubVehicleConfigurations $subVehicleConfigId
     *
     * @param string                   $type
     * @param int                      $year
     * @param string                   $series
     *
     * @return Response
     */
    public function indexWithSubVcAndFixMode(
        SubVehicleConfigurations $subVehicleConfigId,
        string $type,
        int $year,
        string $series
    ): Response
    {
        return $this->forward('App\Controller\Vehicles\ConfigurationController::index', [
            'subVc' => $subVehicleConfigId->getSubVehicleConfigurationId(),
            'mode' => Bottom::FIX_MODE,
            'type1' => $type,
            'year' => $year,
            'series' => $series
        ]);
    }

    /**
     * @Route("/{configurationId}/type/{type}/year/{year}/series/{series}/configuration/fix", methods={"GET"}, name="index_with_vc_fix")
     * @param VehicleConfigurations $configurationId
     *
     * @param string                $type
     * @param int                   $year
     * @param string                $series
     *
     * @return Response
     */
    public function indexWithVcAndFixMode(
        VehicleConfigurations $configurationId,
        string $type,
        int $year,
        string $series
    ): Response
    {
        return $this->forward('App\Controller\Vehicles\ConfigurationController::index', [
            'vc' => $configurationId->getVehicleConfigurationId(),
            'mode' => Bottom::FIX_MODE,
            'action' => 'fix',
            'type1' => $type,
            'year' => $year,
            'series' => $series
        ]);
    }

    /**
     * @Route("/type/{type}/create", methods={"GET"}, name="index_with_type_create")
     * @param int $type
     *
     * @return Response
     */
    public function indexWithTypeCreateMode(int $type): Response
    {
        return $this->forward('App\Controller\Vehicles\ConfigurationController::index', [
            'type' => $type,
            'mode' => Bottom::CREATE_MODE,
        ]);
    }

    /**
     * @Route("/{subVehicleConfigId}/copy/create", methods={"GET"}, name="index_with_subVc_create_copy")
     * @param SubVehicleConfigurations $subVehicleConfigId

     * @return Response
     */
    public function indexWithSubVcAndCopyMode(SubVehicleConfigurations $subVehicleConfigId): Response
    {
        return $this->forward('App\Controller\Vehicles\ConfigurationController::index', [
            'subVc' => $subVehicleConfigId->getSubVehicleConfigurationId(),
            'mode' => Bottom::CREATE_COPY_MODE,
        ]);
    }

    /**
     * @Route("/type", methods={"GET"}, name="ajax_vehicles_get_type")
     */
    public function findTypes(): JsonResponse
    {
        $vehicleType = $this->getManager()->getRepository(VehicleConfigurations::class)
            ->findVehicleTypes();

        $result = [];

        foreach ($vehicleType as $key => $value)
            array_push($result, $value['vehicleConfigurationKey']);

        return $this->json($result);
    }

    /**
     * @Route("/type/{type}/year", methods={"GET"}, name="ajax_vehicles_get_type_and_year")
     * @param $type
     *
     * @return JsonResponse
     */
    public function findYears(string $type): JsonResponse
    {
        $vehicleYear = $this->getManager()->getRepository(VehicleConfigurations::class)
            ->findVehicleYears($type);

        $result = [];

        foreach ($vehicleYear as $key => $value)
            array_push($result, $value['vehicleConfigurationKey']);

        return $this->json($result);
    }

    /**
     * @Route("/type/{type}/year/{year}/series", methods={"GET"}, name="ajax_vehicles_get_type_and_year_and_series")
     * @param $type
     * @param $year
     *
     * @return JsonResponse
     */
    public function findSeries(string $type, int $year): JsonResponse
    {
        $vehicleSeries = $this->getManager()->getRepository(VehicleConfigurations::class)
            ->findVehicleSeries($type, $year);

        return $this->json($vehicleSeries);
    }

    /**
     * @Route("/type/{type}/year/{year}/series/{series}/customerKey/{customerKey}/search", methods={"GET"},
     *     name="ajax_search_list")
     * @param $type
     * @param $year
     * @param $series
     * @param $customerKey
     *
     * @return JsonResponse
     */
    public function configuration(string $type, int $year, string $series, string $customerKey): JsonResponse
    {
        $key = ($customerKey != 'null') ? $customerKey : null;
        $configuration = $this->getManager()->getRepository(SubVehicleConfigurations::class)
            ->findByTypeYearSeriesDraftDetection($type, $year, $series, $key);

        return $this->json($configuration);
    }

    /**
     * @Route("/search/{configuration}", methods={"GET"}, name="ajax_search_list_by_text")
     * @param string $configuration
     *
     * @return JsonResponse
     */
    public function searchListByText(string $configuration)
    {
        $vehicleSearchList = $this
            ->getManager()
            ->getRepository(SubVehicleConfigurations::class)
            ->findByConfigurationName($configuration);

        return $this->json($vehicleSearchList);
    }

    /**
     * @Route("/search/vin/{vin}", methods={"GET"})
     * @param string $vin
     * @return JsonResponse
     */
    public function searchListByVin(string $vin)
    {
        $vehicleSearchList = $this
            ->getManager()
            ->getRepository(SubVehicleConfigurations::class)
            ->findByVinNumber($vin);

        return $this->json($vehicleSearchList);
    }

    /**
     * @Route("/search/licPlate/{licPlate}", methods={"GET"})
     * @param string $licPlate
     * @return JsonResponse
     */
    public function searchListByLicPlate(string $licPlate)
    {
        $vehicleSearchList = $this
            ->getManager()
            ->getRepository(SubVehicleConfigurations::class)
            ->findByLicencePlate($licPlate);

        return $this->json($vehicleSearchList);
    }

    /**
     * @Route("/search/location/{location}", methods={"GET"})
     * @param string $location
     * @return JsonResponse
     */
    public function searchListByLocation(string $location)
    {
        $vehicleSearchList = $this
            ->getManager()
            ->getRepository(SubVehicleConfigurations::class)
            ->findByLocation($location);

        return $this->json($vehicleSearchList);
    }

    /**
     * @Route("/autocomplete", methods={"GET"}, name="ajax_autocomplete_configurations")
     * @return JsonResponse
     */
    public function getAllConfigurations()
    {
        $vehicleSearchList = $this->getManager()->getRepository(SubVehicleConfigurations::class)
            ->findAllConfigurations();

        return $this->json($vehicleSearchList);
    }

    /**
     * @Route("/vinsAutocomplete", methods={"GET"}, name="ajax_autocomplete_vins")
     * @return JsonResponse
     */
    public function getAllVins()
    {
        $vinSearchList = $this
            ->getManager()
            ->getRepository(Vehicles::class)
            ->findAllVins();

        return $this->json($vinSearchList);
    }

    /**
     * @Route("/locationsAutocomplete", methods={"GET"}, name="ajax_autocomplete_locations")
     * @return JsonResponse
     */
    public function getAllLocations()
    {
        $locationsSearchList = $this
            ->getManager()
            ->getRepository(Vehicles::class)
            ->findAllLocation();

        return $this->json($locationsSearchList);
    }

    /**
     * @Route("/licPlatAutocomplete", methods={"GET"}, name="ajax_autocomplete_licence_plates")
     * @return JsonResponse
     */
    public function getAllLicencePlates()
    {
        $licencePlatesSearchList = $this
            ->getManager()
            ->getRepository(Vehicles::class)
            ->findAllLicencePlates();

        return $this->json($licencePlatesSearchList);
    }

    /**
     * @Route("/type/{type}/create/save",
     *     methods={"POST"},
     *     name="ajax_save_new_configuration"
     * )
     * @param Request $request
     * @param int $type
     * @param HistorySubConfiguration $historySubConfigurationService
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function save(
        Request $request,
        int $type,
        HistorySubConfiguration $historySubConfigurationService
    ): JsonResponse
    {
        try {
            $subConf = ($type == 1) ? new ShortKeyModel() : new LongKeyModel();

            if ($type == 1) {
                $form = $this->createForm(ShortKeyType::class, $subConf);
            } else {
                $form = $this->createForm(LongKeyType::class, $subConf);
            }

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /* Save non-entity almost as real entity object but
                 * we have to decide how to do this, force developer
                 * to use model classes instead of working on controller.
                 */
                if ($type == 1) {
                    return $this->json($historySubConfigurationService->saveShortKey($subConf));
                } else {
                    return $this->json($historySubConfigurationService->saveLongKey($subConf));
                }
            }

            return $this->renderFormErrors($form);
        } catch (Exception $exception) {
            return $this->renderFailureJson([$exception->getMessage()]);
        }
    }

    /**
     * @Route("/type/{type}/edit/save",
     *     methods={"POST"},
     *     name="ajax_save_edit_configuration"
     * )
     * @param Request $request
     * @param int $type
     * @param HistorySubConfiguration $historySubConfigurationService
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function saveEdition(
        Request $request,
        int $type,
        HistorySubConfiguration $historySubConfigurationService
    ): JsonResponse
    {
        try {
            $user = $this->getManager()->getRepository(Users::class)->find($_SESSION['sts_userid']);

            $subConf = ($type == 1) ? new ShortKeyModel() : new LongKeyModel();

            if ($type == 1) {
                $form = $this->createForm(ShortKeyEditType::class, $subConf);
            } else {
                $form = $this->createForm(LongKeyEditType::class, $subConf);
            }

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /* Save non-entity almost as real entity object but
                 * we have to decide how to do this, force developer
                 * to use model classes instead of working on controller.
                 */
                if ($type == 1) {
                    return $this->json($historySubConfigurationService->saveEditionShortKey($subConf, $user));
                } else {
                    return $this->json($historySubConfigurationService->saveEditionLongKey($subConf, $user));
                }
            }

            return $this->renderFormErrors($form);
        } catch (Exception $exception) {
            return $this->renderFailureJson([$exception->getMessage()]);
        }
    }

    /**
     * @Route("/type/{type}/fix/save",
     *     methods={"POST"},
     *     name="ajax_save_fixed_configuration"
     * )
     * @param Request $request
     * @param int $type
     * @param HistorySubConfiguration $historySubConfigurationService
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function saveFix(
        Request $request,
        int $type,
        HistorySubConfiguration $historySubConfigurationService
    ): JsonResponse
    {
        try {
            $subConf = ($type == 1) ? new ShortKeyModel() : new LongKeyModel();

            if ($type == 1) {
                $form = $this->createForm(ShortKeyFixType::class, $subConf);
            } else {
                $form = $this->createForm(LongKeyFixType::class, $subConf);
            }

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /* Save non-entity almost as real entity object but
                 * we have to decide how to do this, force developer
                 * to use model classes instead of working on controller.
                 */
                if ($type == 1) {
                    return $this->json($historySubConfigurationService->saveFixShortKey($subConf));
                } else {
                    return $this->json($historySubConfigurationService->saveFixLongKey($subConf));
                }
            }

            return $this->renderFormErrors($form);
        } catch (Exception $exception) {
            return $this->renderFailureJson([$exception->getMessage()]);
        }
    }

    /**
     * @Route("/{configuration}/type/{type}/mode/{mode}/save",
     *     methods={"POST"},
     *     name="ajax_save_main_configuration"
     * )
     * @param Request $request
     * @param VehicleConfigurations $configuration
     * @param int $type
     * @param int $mode
     * @param HistoryConfigurations $historyConfigurationsService
     * @return JsonResponse
     * @throws Exception
     */
    public function saveConfiguration(
        Request $request,
        VehicleConfigurations $configuration,
        int $type,
        int $mode,
        HistoryConfigurations $historyConfigurationsService
    ): JsonResponse
    {
        try {
            $model = ($type == 1) ? new ShortKeyModel() : new LongKeyModel();

            if ($type == 1) {
                $form = $this->createForm(ConfigurationShortKeyEditType::class, $model);
            } else {
                $form = $this->createForm(ConfigurationLongKeyEditType::class, $model);
            }

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /* Save non-entity almost as real entity object but
                 * we have to decide how to do this, force developer
                 * to use model classes instead of working on controller.
                 */
                if ($type == 1) {
                    if ($mode == Configurations::FIX_MODE) {
                        return $this->json($historyConfigurationsService->saveFixShortKey($model, $configuration));
                    } else {
                        return $this->json($historyConfigurationsService->saveEditionShortKey($model, $configuration));
                    }
                } else {
                    if ($mode == Configurations::FIX_MODE) {
                        return $this->json($historyConfigurationsService->saveFixLongKey($model, $configuration));
                    } else {
                        return $this->json($historyConfigurationsService->saveEditionLongKey($model, $configuration));
                    }
                }
            }

            return $this->renderFormErrors($form);
        } catch (Exception $exception) {
            return $this->renderFailureJson([$exception->getMessage()]);
        }
    }

    /**
     * @Route("/{subconfiguration}/delete", methods={"DELETE"}, name="ajax_delete_configuration")
     * @param SubVehicleConfigurations $subconfiguration
     * @param HistorySubConfiguration $historySubconfigurationService
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function deleteConfiguration(
        SubVehicleConfigurations $subconfiguration,
        HistorySubConfiguration $historySubconfigurationService
    ): JsonResponse
    {
        try {
            $historySubconfigurationService->deleteSubConfiguration($subconfiguration);

            return $this->renderSuccessJson();
        } catch (Exception $exception) {
            return $this->renderFailureJson([$exception->getMessage()]);
        }
    }

    /**
     * @Route("/{configuration}/main/delete", methods={"DELETE"}, name="ajax_delete_main_configuration")
     * @param VehicleConfigurations $configuration
     * @param HistoryConfigurations $historyConfigurationService
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function deleteMainConfiguration(
        VehicleConfigurations $configuration,
        HistoryConfigurations $historyConfigurationService
    ): JsonResponse
    {
        try {
            $historyConfigurationService->deleteConfiguration($configuration);

            return $this->renderSuccessJson();
        } catch (Exception $exception) {
            return $this->renderFailureJson([$exception->getMessage()]);
        }
    }

    /**
     * @Route("/{id}/cancel", methods={"GET"}, name="ajax_cancel_configuration")
     * @param int $id
     *
     * @return JsonResponse
     */
    public function cancel(int $id): Response
    {
        $redirected = $this->redirectToRoute('view_vehicle_configuration', ['subVehicleConfigId' => $id]);

        return $redirected;
    }

    /**
     * @Route("/{propertyId}/optionsForProperty", methods={"GET"}, name="ajax_options_for_property")
     * @param int $propertyId
     *
     * @return JsonResponse
     */
    public function availableOptionsForProperty(int $propertyId): JsonResponse
    {
        $availableOptions = $this->getDoctrine()->getManager()->getRepository(VehicleConfigurationPropertiesSymbols::class)->getOptionsForProperty($propertyId);

        return $this->json($availableOptions);
    }
}