<?php

namespace App\Controller\Parameters;

use App\Controller\LegacyBaseController;
use App\Entity\CocParameterRelease;
use App\Entity\SubVehicleConfigurations;
use App\Entity\VehicleConfigurations;
use App\Enum\Entity\HistoryEvents;
use App\Enum\HistoryTypes;
use App\Factory\Menu;
use App\Form\History\HistoryCommentType;
use App\Form\History\HistorySelectorSearchType;
use App\Form\Parameter\CocParametersCollectionType;
use App\Form\Parameter\CocReleasedParametersType;
use App\History\Strategies\CocValuesSetsManagementStrategy;
use App\Model\History\HistoryComment;
use App\Model\History\Search\HistorySelector;
use App\Service\History\Parameter\HistoryCocParameter;
use App\Service\Parameter\CocParameter;
use App\Service\Parameter\Menu\Coc\Footer;
use App\Service\Vehicles\Configuration\Configurations;
use App\Service\Vehicles\Configuration\SubConfiguration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/parameters/coc/values")
 */
class CoCValuesController extends LegacyBaseController
{
    /**
     * @Route("/", methods={"GET"}, name="coc_values_sets")
     * @param Request                         $request
     * @param CocParameter                    $cocParametersService
     * @param SubConfiguration                $subConfigurationService
     * @param Configurations                  $configurations
     * @param CocValuesSetsManagementStrategy $history
     *
     * @return Response
     */
    public function index(
        Request $request,
        CocParameter $cocParametersService,
        SubConfiguration $subConfigurationService,
        Configurations $configurations,
        CocValuesSetsManagementStrategy $history
    ): Response
    {
        $search = null;
        $mode = $request->get('mode', 1);
        $subConf = null;
        $subconfiguration = $request->get('configuration', 0);
        $cocParameters = null;
        $configurationParameters = null;
        $valueSet = null;
        $cocReleasedParameters = null;
        $isMainConfiguration = false;
        $arguments = [];

        if ($request->get('configuration', 0) != 0) {

            $subConf = $this->getManager()->getRepository(SubVehicleConfigurations::class)
                ->find($request->get('configuration'));
            $mainConfiguration = $subConf->getVehicleConfiguration();

            $search = [
                'type' => $mainConfiguration->getVehicleTypeName(),
                'year' => $mainConfiguration->getVehicleTypeYear(),
                'series' => $mainConfiguration->getVehicleSeries(),
                'customerKey' => $this->getDoctrine()->getManager()->getRepository(SubVehicleConfigurations::class)->
                find($request->get('configuration'))->getVehicleConfiguration()->getVehicleCustomerKey(),
                'configuration' => $request->get('configuration', 0),
            ];

            $cocParameters = $cocParametersService->getCocParameters($subConf);
            $configurationParameters = $subConfigurationService->getParametersToView($subConf);
            $cocReleasedParameters = $this->getManager()->getRepository(CocParameterRelease::class)->find($subConf);

            $historySelector = new HistorySelector();
            $historySelector->setEvent(HistoryEvents::UPDATE);

            $options = [
                'historyTable' => $history->getTableName(),
                'fkId' => $request->get('configuration', 0)
            ];

            $arguments['historyForm'] = $this->createForm(HistorySelectorSearchType::class, $historySelector, $options)
                ->createView();
            $arguments['historyLog'] = true;
        }

        if ($request->get('mainConfiguration', 0) != 0) {
            $configuration = $this->getManager()->getRepository(VehicleConfigurations::class)
                ->find($request->get('mainConfiguration'));
            $search = [
                'type' => $configuration->getVehicleTypeName(),
                'year' => $configuration->getVehicleTypeYear(),
                'series' => $configuration->getVehicleSeries(),
                'customerKey' => $configuration->getVehicleCustomerKey(),
                'configuration' => $request->get('mainConfiguration', 0),
            ];
            $configurationParameters = $configurations->getKeyParametersToView($configuration);
            $isMainConfiguration = true;
        }

        /* Set static factory */
        Menu::setObjectManager($this->getManager());

        $parametersButtons = [
            'mode' => $mode,
            'subConf' => $subConf,
            'releaseStatus' => ($cocReleasedParameters) ? $cocReleasedParameters->getReleaseStatus()->getReleaseStatusId() : 1,
        ];

        $arguments = array_merge($arguments, [
            'search' => $search,
            'cocParameters' => is_null($cocParameters) ? $cocParameters : $cocParameters->getParameters(),
            'mode' => $mode,
            'parameters' => $configurationParameters,
            'cocReleasedParameters' => $cocReleasedParameters,
            'parametersButtonsState' => Menu::create(Footer::class)->setArguments($parametersButtons)
                ->build()->getMenu(),
            'isMainConfiguration' => $isMainConfiguration,
            'subconfiguration' => $subconfiguration,
             'historyType' => HistoryTypes::COC_VALUES_SETS_ASSIGNMENT
        ]);

        if ($mode == 2) {
            $arguments['releasedCoCForm'] = $this->createForm(CocReleasedParametersType::class,
                $cocReleasedParameters)->createView();
            $arguments['parametersForm'] = $this->createForm(CocParametersCollectionType::class,
                $cocParameters)->createView();

            $comment = new HistoryComment();
            $arguments['commentForm'] = $this->createForm(HistoryCommentType::class, $comment)->createView();
        }

        return $this->render('Parameters/CoC/Values/index.html.twig', $arguments);
    }

    /**
     * @Route("/mainConfiguration/{mainConfiguration}",
     *     methods={"GET"},
     *     name="index_with_main_configuration")
     *
     * @param int $mainConfiguration
     * @return Response
     */
    public function indexWithMainConfiguration(int $mainConfiguration)
    {
        return $this->forward('App\Controller\Parameters\CoCValuesController::index', [
            'mainConfiguration' => $mainConfiguration
        ]);
    }

    /**
     * @Route("/configuration/{configuration}",
     *     methods={"GET"},
     *     name="index_with_configuration"
     * )
     * @param int $configuration
     *
     * @return Response
     */
    public function indexWithConfiguration(int $configuration): Response
    {
        return $this->forward('App\Controller\Parameters\CoCValuesController::index', [
            'configuration' => $configuration
        ]);
    }

    /**
     * @Route("/type/{type}/year/{year}/series/{series}/configuration/{configuration}/mode/{mode}",
     *     methods={"GET"},
     *     name="index_with_configuration_and_mode"
     * )
     * @param string $type
     * @param int $year
     * @param string $series
     * @param int $configuration
     * @param int $mode
     *
     * @return Response
     */
    public function indexWithConfigurationAndMode(string $type, int $year, string $series, int $configuration, int $mode): Response
    {
        return $this->forward('App\Controller\Parameters\CoCValuesController::index', [
            'type' => $type,
            'year' => $year,
            'series' => $series,
            'configuration' => $configuration,
            'mode' => $mode
        ]);
    }

    /**
     * @Route("/cocs/configuration/{configuration}/save",
     *     methods={"POST"},
     *     name="ajax_cocs_save_set"
     * )
     * @param Request                  $request
     *
     * @param SubVehicleConfigurations $configuration
     * @param CocParameter             $cocParameterService
     * @param HistoryCocParameter      $historyCocParameterService
     *
     * @return Response
     * @throws \Exception
     */
    public function saveCoCValueSet(
        Request $request,
        SubVehicleConfigurations $configuration,
        CocParameter $cocParameterService,
        HistoryCocParameter $historyCocParameterService
    ): Response
    {
        try {
            $collection = $cocParameterService->getCocParameters($configuration);

            $form = $this->createForm(CocParametersCollectionType::class, $collection);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /* Save non-entity almost as real entity object but
                 * we have to decide how to do this, force developer
                 * to use model classes instead of working on controller.
                 */
                $historyCocParameterService->save($collection, $configuration);

                return $this->renderSuccessJson();
            }

            return $this->renderFormErrors($form);
        } catch (\Exception $exception) {
            return $this->renderFailureJson([$exception->getMessage()]);
        }
    }

    /**
     * @Route("/cocs/configuration/{configuration}/save/releasedPreview",
     *      methods={"POST"},
     *      name="ajax_cocs_save_released_set"
     * )
     * @param Request $request
     *
     * @param SubVehicleConfigurations $configuration
     * @param CocParameter $cocParameterService
     * @param HistoryCocParameter $historyCocParameterService
     *
     * @return Response
     * @throws \Exception
     */
    public function saveCoCReleasedPreview(
        Request $request,
        SubVehicleConfigurations $configuration,
        CocParameter $cocParameterService,
        HistoryCocParameter $historyCocParameterService
    ): Response
    {
        try {
            $cocReleasedParameters = $cocParameterService->getCoCReleasedParameters($configuration);

            $form = $this->createForm(CocReleasedParametersType::class, $cocReleasedParameters);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /* Save non-entity almost as real entity object but
                 * we have to decide how to do this, force developer
                 * to use model classes instead of working on controller.
                 */
                $historyCocParameterService->saveCoCReleased($cocReleasedParameters);

                return $this->renderSuccessJson();
            }

            return $this->renderFormErrors($form);
        } catch (\Exception $exception) {
            return $this->renderFailureJson([$exception->getMessage()]);
        }
    }
}