<?php

namespace App\Controller\Ecu\Sw;

use App\Controller\LegacyBaseController;
use App\Entity\ConfigurationEcus;
use App\Entity\EcuSwVersions;
use App\Entity\Users;
use App\Enum\Entity\HistoryEvents;
use App\Enum\HistoryTypes;
use App\Factory\Menu;
use App\Form\HeaderType;
use App\Form\History\HistoryCommentType;
use App\Form\History\HistorySelectorSearchType;
use App\Form\Odx\Odx1ParameterCollectionType;
use App\Form\Odx\Odx2ParameterCollectionType;
use App\History\Strategies\EcuParameterManagementStrategy;
use App\Model\History\HistoryComment;
use App\Model\History\Search\HistorySelector;
use App\Model\Odx1Collection;
use App\Model\Odx2Collection;
use App\Service\Ecu\Sw\Header;
use App\Service\Ecu\Sw\Menu\Footer;
use App\Service\Ecu\Sw\Menu\Parameters;
use App\Service\Ecu\Sw\Parameter;
use App\Service\Ecu\Sw\ResponsiblePersons;
use App\Service\Ecu\Sw\SoftwareVersion;
use App\Service\Ecu\Sw\SubVehicleConfiguration;
use App\Service\History\Ecu\Sw\HistoryParameter;
use App\Service\History\Ecu\Sw\HistoryHeader;
use App\Service\History\Ecu\Sw\HistorySoftwareVersion;
use App\Service\Users\Settings;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ecu/sw/parameters")
 */
class ParametersController extends LegacyBaseController
{
    /**
     * @Route("/", methods={"GET"}, defaults={"odx": 2, "mode": 1}, name="parameters_management_index")
     * @param Request            $request
     * @param Header             $headerModel
     * @param Parameter          $parameter
     * @param ResponsiblePersons $responsiblePersons
     * @param EcuParameterManagementStrategy    $history
     * @return Response
     */
    public function index
    (
        Request $request,
        Header $headerModel,
        Parameter $parameter,
        ResponsiblePersons $responsiblePersons,
        EcuParameterManagementStrategy $history
    ): Response
    {
        //todo: Fix ResponsibleUsers
        $ecu = $request->get('ecu'); //?? ($responsiblePersons->loadResponsibleEcuByUserId($user) ?? $request->get('ecu'));
        $ecu = ($ecu != 0) ? $ecu : null;
        $sws = [];
        $supportOdx2 = null;

        if ($ecu) {
            $sws = $this->getManager()
                ->getRepository(EcuSwVersions::class)
                ->findAllSwsByEcuId($ecu);
            $supportOdx2 = $this->getManager()
                ->getRepository(ConfigurationEcus::class)
                ->findOneBy(['ceEcuId' => $ecu])->getDiagnosticSoftwareSupportsStsOdx2ForThisEcu();
        }

        $sw = null;

        if ($request->get('sw')) {
            $sw = $this->getManager()->getRepository(EcuSwVersions::class)->find($request->get('sw'));
        }

        $odx = $request->get('odx', ((!is_null($supportOdx2)) ? ($supportOdx2 ? 2 : 1) : 2));
        $mode = $request->get('mode', 1);
        $parametersButtons = [
            'mode' => $mode,
            'ecu' => $ecu,
            'sw' => ($sw) ? $sw->getEcuSwVersionId() : null,
            'save' => $request->get('save', 0),
            'cancel' => $request->get('cancel', 0),
            'order' => $request->get('order', 0),
            'add' => $request->get('add', 0),
            'delete' => $request->get('delete', 0),
            'copy' => $request->get('copy', 0),
            'copy_parameter_current' => $request->get('copy_parameter_current', 0),
            'copy_parameter_other' => $request->get('copy_parameter_other', 0),
            'generate_odx' => $request->get('generate_odx', 0)
        ];

        //$ecus = $responsiblePersons->getAllEcuAccessByUserIdWithOrder($user);
        //todo: fix responsible persons
        $entityManagerEcu = $this->getManager()->getRepository(ConfigurationEcus::class);

        $ecus = $entityManagerEcu->findBy([], ['ecuName' => 'ASC']);

        $header = null;
        $parameters = ($odx == 2) ? new Odx2Collection : new Odx1Collection;

        if ($sw) {
            $header = $headerModel->getHeader($sw);
            $parameters = $parameter->getParameters($odx, $header->getProtocol()->getEcuCommunicationProtocolName(), $sw);
        }

        /* Set static factory */
        Menu::setObjectManager($this->getManager());
        $arguments = [
            'odx' => $odx,
            'mode' => $mode,
            'ecu' => $ecu,
            'supportOdx2' => $supportOdx2,
            'sw' => ($sw) ? $sw->getEcuSwVersionId() : null,
            'ecus' => $ecus,
            'sws' => $sws,
            'header' => $header,
            'parameters' => $parameters->getParameters(),
            'parametersButtons' => $parametersButtons,
            'horizontalButtonsState' => Menu::create(Parameters::class)
                ->setArguments([
                    'mode' => $mode,
                    'ecu' => $ecu,
                    'sw' => ($sw) ? $sw->getEcuSwVersionId() : null,
                ])
                ->build()
                ->getMenu(),
            'parametersButtonsState' => Menu::create(Footer::class)
                ->setArguments($parametersButtons)
                ->build()
                ->getMenu(),
        ];

        $options = [
            'ecu' => $ecu
        ];

        if ($mode == 2) {
            $arguments['headerForm'] = $this->createForm(HeaderType::class, $header)
                ->createView();

            $arguments['parametersForm'] = $this->createForm(
                ($odx == 2) ? Odx2ParameterCollectionType::class : Odx1ParameterCollectionType::class,
                $parameters, $options)->createView();
        }

        if ($sw) {
            $historySelector = new HistorySelector();
            $historySelector->setEvent(HistoryEvents::UPDATE);

            $options = [
                'historyTable' => $history->getTableName(),
                'fkId' => $sw
            ];

            $arguments['historyForm'] = $this->createForm(HistorySelectorSearchType::class, $historySelector, $options)
                ->createView();
            $arguments['historyLog'] = true;
        }

        $comment = new HistoryComment();
        $arguments['commentForm'] = $this->createForm(HistoryCommentType::class, $comment)->createView();

        $arguments['historyType'] = HistoryTypes::ECU_PARAMETER_MANAGEMENT;

        return $this->render('Ecu/Sw/Parameters/index.html.twig', $arguments);
    }

    /**
     * @Route("/ecu/{ecu}",
     *     methods={"GET"},
     *     name="parameters_management_index_with_ecu"
     * )
     * @param int      $ecu
     * @param Settings $settings
     *
     * @return Response
     */
    public function indexWithEcu(int $ecu, Settings $settings): Response
    {
        $user = $this->getManager()->getRepository(Users::class)->find($_SESSION['sts_userid']);

        return $this->forward('App\Controller\Ecu\Sw\ParametersController::index', [
            'ecu' => $ecu,
            'sw' => $settings->loadLastSelectedSwByUserIdAndEcuId($user, $ecu),
            'user' => $user->getId()
        ]);
    }

    /**
     * @Route("/ecu/{ecu}/sw/{sw}",
     *     methods={"GET"},
     *     name="parameters_management_index_with_ecu_and_sw"
     * )
     * @param ConfigurationEcus $ecu
     * @param EcuSwVersions     $sw
     * @param Settings          $settings
     *
     * @return Response
     */
    public function indexWithEcuAndSw(ConfigurationEcus $ecu, EcuSwVersions $sw, Settings $settings): Response
    {
        $user = $this->getManager()->getRepository(Users::class)->find($_SESSION['sts_userid']);

        $settings->saveSelectedSwForUserIdAndEcuId($user, $ecu, $sw);

        return $this->forward('App\Controller\Ecu\Sw\ParametersController::index', [
            'ecu' => $ecu->getCeEcuId(),
            'sw' => $sw->getEcuSwVersionId(),
            'user' => $user->getId()
        ]);
    }

    /**
     * @Route("/ecu/{ecu}/sw/{sw}/odx/{odx}",
     *     defaults={"odx": 2},
     *     methods={"GET"},
     *     name="parameters_management_index_with_ecu_and_sw_and_odx"
     * )
     * @param ConfigurationEcus $ecu
     * @param EcuSwVersions     $sw
     * @param int               $odx
     *
     * @return Response
     */
    public function indexWithEcuAndSwAndOdx(ConfigurationEcus $ecu, EcuSwVersions $sw, int $odx): Response
    {
        $user = $_SESSION['sts_userid'];

        return $this->forward('App\Controller\Ecu\Sw\ParametersController::index', [
            'ecu' => $ecu->getCeEcuId(),
            'sw' => $sw->getEcuSwVersionId(),
            'odx' => $odx,
            'user' => $user
        ]);
    }

    /**
     * @Route("/ecu/{ecu}/sw/{sw}/odx/{odx}/mode/{mode}",
     *     defaults={"odx": 2, "mode": 1},
     *     methods={"GET"},
     *     name="parameters_management_index_with_ecu_and_sw_and_odx_and_mode"
     * )
     * @param ConfigurationEcus $ecu
     * @param EcuSwVersions     $sw
     * @param int               $odx
     * @param int               $mode
     *
     * @return Response
     */
    public function indexWithEcuAndSwAndOdxAndMode(
        ConfigurationEcus $ecu,
        EcuSwVersions $sw,
        int $odx,
        int $mode
    ): Response
    {
        $user = $_SESSION['sts_userid'];

        return $this->forward('App\Controller\Ecu\Sw\ParametersController::index', [
            'ecu' => $ecu->getCeEcuId(),
            'sw' => $sw->getEcuSwVersionId(),
            'user' => $user,
            'mode' => $mode,
            'odx' => $odx
        ]);
    }

    /**
     * @Route("/ecu/{ecu}/sw/{sw}/odx/{odx}/menu/{button}/{flag}",
     *     methods={"GET"},
     *     name="parameters_management_index_with_ecu_and_sw_and_button_flag"
     * )
     * @param ConfigurationEcus $ecu
     * @param EcuSwVersions     $sw
     * @param string            $button
     * @param int               $flag
     * @param int               $odx
     *
     * @return Response
     */
    public function indexWithEcuAndSwAndButtonFlag(
        ConfigurationEcus $ecu,
        EcuSwVersions $sw,
        string $button,
        int $flag,
        int $odx
    ): Response
    {
        $user = $_SESSION['sts_userid'];

        return $this->forward('App\Controller\Ecu\Sw\ParametersController::index', [
            'ecu' => $ecu->getCeEcuId(),
            'sw' => $sw->getEcuSwVersionId(),
            'user' => $user,
            $button => $flag,
            'odx' => $odx,
        ]);
    }


    /**
     * @Route("/ecu/{ecu}/sw/{sw}/odx/{odx}/menu/mode/{mode}/header",
     *     methods={"POST"},
     *     name="parameters_management_update_header"
     * )
     * @param Request       $request
     * @param Header        $headerService
     * @param HistoryHeader $historyHeader
     * @param EcuSwVersions $sw
     *
     * @return Response
     */
    public function header(
        Request $request,
        Header $headerService,
        HistoryHeader $historyHeader,
        EcuSwVersions $sw
    ): Response
    {
        try {
            $header = $headerService->getHeader($sw);

            $form = $this->createForm(HeaderType::class, $header);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /* Save non-entity almost as real entity object but
                 * we have to decide how to do this, force developer
                 * to use model classes instead of working on controller.
                 */
                $historyHeader->save($header);

                return $this->renderSuccessJson();
            }

            return $this->renderFormErrors($form);
        } catch (Exception $exception) {
            return $this->renderFailureJson([$exception->getMessage()]);
        }
    }

    /**
     * @Route("/ecu/{ecu}/sw/{sw}/odx/{odx}/menu/mode/{mode}/parameters",
     *     methods={"POST"},
     *     name="parameters_management_update_parameters"
     * )
     * @param Request           $request
     * @param ConfigurationEcus $ecu
     * @param EcuSwVersions     $sw
     * @param Header            $header
     * @param Parameter         $parameterService
     * @param HistoryParameter  $historyParameter
     *
     * @return Response
     */
    public function parameters(
        Request $request,
        ConfigurationEcus $ecu,
        EcuSwVersions $sw,
        Header $header,
        Parameter $parameterService,
        HistoryParameter $historyParameter
    ): Response
    {
        try {
            $odx = $request->get('odx');
            $header = $header->getHeader($sw);

            $collection = $parameterService->getParameters(
                $odx,
                $header->getProtocol()->getEcuCommunicationProtocolName(),
                $sw
            );

            /* Get parameters before parsing by SF, references complains
             * many problems
             */
            $parametersBag = [];

            foreach ($collection->getParameters() as $parameter) {
                if ($parameter->getParameterId()) {
                    $parametersBag[$parameter->getParameterId()] = clone $parameter;
                }
            }


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
                $historyParameter->save($collection, $ecu, $sw, $parametersBag, $odx);

                return $this->renderSuccessJson();

            }

            return $this->renderFormErrors($form);
        } catch (Exception $exception) {
            return $this->renderFailureJson([$exception->getMessage()]);
        }
    }


    /**
     * @Route("/sw/{sw}/order", methods={"POST"}, name="ajax_save_order_management_parameters")
     * @param Request          $request
     * @param EcuSwVersions     $sw
     * @param HistoryParameter $historyParameter
     *
     * @return JsonResponse
     */
    public function order(
        Request $request,
        EcuSwVersions $sw,
        HistoryParameter $historyParameter
    ): JsonResponse
    {
        try {
            $historyParameter->changeOrders($sw, json_decode($request->request->get('orders'), true));

            return $this->json([]);
        } catch (Exception $exception) {
            return $this->renderFailureJson([$exception->getMessage()]);
        }
    }

    /**
     * @Route("/sw/delete/sw/{sw}", methods={"DELETE"}, name="ajax_delete_sw_management_parameters")
     * @param EcuSwVersions          $sw
     * @param SoftwareVersion        $softwareVersion
     * @param HistorySoftwareVersion $historySoftwareVersion
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function deleteSw(
        EcuSwVersions $sw,
        SoftwareVersion $softwareVersion,
        HistorySoftwareVersion $historySoftwareVersion
    ): JsonResponse
    {
        try {
            $historySoftwareVersion->deleteSwById($sw);

            return $this->json([]);
        } catch (Exception $exception) {
            return $this->renderFailureJson([$exception->getMessage()]);
        }
    }

    /**
     * @Route("/sw/check/sw/{sw}", methods={"GET"}, name="ajax_check_if_exists_in_db_management_parameters")
     * @param string $sw
     *
     * @return JsonResponse
     */
    public function check(string $sw): JsonResponse
    {
        $response = $this->getDoctrine()->getManager()->getRepository(EcuSwVersions::class)
            ->checkIfExistsInDB($sw);

        return $this->json($response);
    }

    /**
     * @Route("/sw/add/ecu/{ecu}/sts/{sts}", methods={"GET"}, name="ajax_add_new_sw_revision_management_parameters")
     * @param ConfigurationEcus         $ecu
     * @param string                    $sts
     * @param HistorySoftwareVersion   $historySoftwareVersion
     *
     * @return JsonResponse
     */
    public function addNewSwRevision(
        ConfigurationEcus $ecu,
        string $sts,
        HistorySoftwareVersion $historySoftwareVersion): JsonResponse
    {
        try {
            $newSwVersion = $historySoftwareVersion->createNewSw($ecu, $sts);

            return $this->json(['inserted' => $newSwVersion->getEcuSwVersionId()]);
        } catch (Exception $exception) {
            return $this->renderFailureJson([$exception->getMessage()]);
        }
    }

    /**
     * @Route("/sw/copy/sw/{sw}/sts/{sts}", methods={"GET"}, name="ajax_copy_revision_management_parameters")
     * @param EcuSwVersions          $sw
     * @param string                 $sts
     * @param HistorySoftwareVersion $historySoftwareVersion
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function copyRevision(
        EcuSwVersions $sw,
        string $sts,
        HistorySoftwareVersion $historySoftwareVersion
    ): JsonResponse
    {
        try {
            $newCopySwVersion = $historySoftwareVersion->copySwById($sw, $sts);

            return $this->json(['inserted' => $newCopySwVersion->getEcuSwVersionId()]);
        } catch (Exception $exception) {
            return $this->renderFailureJson([$exception->getMessage()]);
        }
    }

    /**
     * @Route("/subversion/get/sw/{sw}", methods={"GET"}, name="ajax_get_Subversions_for_management_parameters")
     * @param EcuSwVersions $sw
     *
     * @return JsonResponse
     */
    public function getSubVersionsFor(EcuSwVersions $sw): JsonResponse
    {
        $response = $this->getDoctrine()->getManager()->getRepository(EcuSwVersions::class)
            ->getAllSubversionsSuffixForSwByParentId($sw);

        return $this->json($response);
    }

    /**
     * @Route("/subversion/add/sw/{sw}/suffix/{suffix}/copy/{copy}",
     *     methods={"GET"},
     *     name="ajax_add_new_subversion_management_parameters"
     * )
     * @param EcuSwVersions          $sw
     * @param string                 $suffix
     * @param string                 $copy
     * @param HistorySoftwareVersion $historySoftwareVersion
     *
     * @return JsonResponse
     */
    public function addNewSubversion(
        EcuSwVersions $sw,
        string $suffix,
        string $copy,
        HistorySoftwareVersion $historySoftwareVersion
    ): JsonResponse
    {
        try {
            $newSw = $historySoftwareVersion->createNewSubversion($sw, $suffix, $copy);

            return $this->json(['inserted' => $newSw->getEcuSwVersionId()]);
        } catch (Exception $exception) {
            return $this->renderFailureJson([$exception->getMessage()]);
        }
    }

    /**
     * @Route("/configuration/get/sw/{sw}",
     *     methods={"GET"},
     *     name="ajax_get_config_for_sw_version_management_parameters"
     * )
     * @param int                     $sw
     * @param SubVehicleConfiguration $subVehicleConfiguration
     *
     * @return JsonResponse
     */
    public function getConfigsForSwVersion(int $sw, SubVehicleConfiguration $subVehicleConfiguration): JsonResponse
    {
        $subConfigs = $subVehicleConfiguration->getSubVehicleConfigurationsBySwId($sw);

        return $this->json($subConfigs);
    }

    /**
     * @Route("/parameters/clone/sw/{sw}", methods={"POST"}, name="ajax_save_cloned_parameters_management_parameters")
     * @param EcuSwVersions    $sw
     * @param Request          $request
     * @param Parameter        $parameter
     * @param HistoryParameter $historyParameter
     *
     * @return JsonResponse
     */
    public function saveClonedParameters(
        EcuSwVersions $sw,
        Request $request,
        Parameter $parameter,
        HistoryParameter $historyParameter
    ): JsonResponse
    {
        try {
            $params = [];
            parse_str($request->get('clonedForm'), $params);
            $response = $parameter->validateClonedParameters($sw, $params);
            if (empty($response)) {
                $historyParameter->saveClonedParameterForSwId($sw, $params);
            }

            return $this->json($response);
        } catch (Exception $exception) {
            return $this->renderFailureJson([$exception->getMessage()]);
        }
    }

    /**
     * @Route("/sws/get/ecu/{ecu}/sw/{sw}", methods={"GET"}, name="ajax_get_other_sw_management_parameters")
     * @param SoftwareVersion   $softwareVersion
     * @param ConfigurationEcus $ecu
     * @param EcuSwVersions     $sw
     *
     * @return JsonResponse
     */
    public function getOtherSw(
        ConfigurationEcus $ecu,
        EcuSwVersions $sw,
        SoftwareVersion $softwareVersion
    ): JsonResponse
    {
        $sws = $softwareVersion->getOtherSwByEcu($ecu, $sw);

        return $this->json($sws);
    }

    /**
     * @Route("/parameters/conflicts/ecu/{ecu}", methods={"POST"}, name="ajax_check_conflicts_management_parameters")
     * @param Request   $request
     * @param Parameter $parameter
     *
     * @return JsonResponse
     */
    public function checkConflicts(Request $request, Parameter $parameter): JsonResponse
    {
        $conflicts = $parameter->checkConflicts($request->get('sw_current'), $request->get('sw_destination'),
            json_decode($request->get('ecu_parameters'), true)
        );

        return $this->json($conflicts);
    }

    /**
     * @Route("/conflicts/resolve/ecu/{ecu}", methods={"POST"}, name="ajax_resolve_conflicts_management_parameters")
     * @param Request          $request
     * @param HistoryParameter $historyParameter
     *
     * @return JsonResponse
     */
    public function resolveConflicts(Request $request, HistoryParameter $historyParameter): JsonResponse
    {
        try {
            $swName = $historyParameter->resolveConflicts(
                $this->getManager()->getRepository(EcuSwVersions::class)->find($request->get('sw_destination')),
                json_decode($request->get('parameters_conflict'), true),
                json_decode($request->get('parameters_without_conflict'), true)
            );

            return $this->json([$swName]);
        } catch (Exception $exception) {
            return $this->renderFailureJson([$exception->getMessage()]);
        }
    }

    /**
     * @Route("/parameters/copy/ecu/{ecu}", methods={"POST"}, name="ajax_copy_parameters_management_parameters")
     * @param Request          $request
     * @param HistoryParameter $historyParameter
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function copyParameters(Request $request, HistoryParameter $historyParameter): JsonResponse
    {
        try {
            $response = $historyParameter->copyWithoutConflict(
                $this->getManager()->getRepository(EcuSwVersions::class)->find($request->get('sw_destination')),
                json_decode($request->get('ecu_parameters'), true));

            return $this->json([$response]);
        } catch (Exception $exception) {
            return $this->renderFailureJson([$exception->getMessage()]);
        }
    }
}