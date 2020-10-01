<?php

namespace App\Controller\Ecu\Sw;

use App\Controller\LegacyBaseController;
use App\Entity\CanVersions;
use App\Entity\ConfigurationEcus;
use App\Entity\EcuSwProperties;
use App\Entity\EcuSwVersions;
use App\Factory\Menu;
use App\Form\EcuSwProperties\EcuSwPropAddFromListCollectionType;
use App\Form\EcuSwProperties\EcuSwPropertiesCollectionType;
use App\Service\Ecu\Sw\Menu\Properties;
use App\Service\Ecu\Sw\Menu\PropertiesBottomButtonsState;
use App\Service\Ecu\Sw\SoftwareVersion;
use App\Service\Users\Settings;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Ecu\Sw\Property;
use App\Enum\Menu as Mode;

/**
 * @Route("/ecu/sw/properties")
 */
class PropertiesController extends LegacyBaseController
{
    /**
     * @Route("/", methods={"GET"}, name="ecu_properties_management_index")
     * @param Request $request
     * @param Property $property
     * @return Response
     */
    public function index(Request $request, Property $property): Response
    {
        $ecu = $request->get('ecu'); //?? ($responsiblePersons->loadResponsibleEcuByUserId($user) ?? $request->get('ecu'));
        $ecu = ($ecu != 0) ? $ecu : null;
        $sws = [];
        $assignedCan = null;
        $canVersionList = [];

        if ($ecu) {
            $sws = $this->getManager()
                ->getRepository(EcuSwVersions::class)
                ->findAllSwsByEcuId($ecu);
        }

        $mode = $request->get('mode', 1);

        if ($request->get('order', 0))
            $mode = 3;

        $sw = null;
        $ecuSwPropertiesList = [];

        if ($request->get('sw')) {
            $sw = $this->getManager()->getRepository(EcuSwVersions::class)->find($request->get('sw'));
            $ecuSwPropertiesList = $this->getManager()->getRepository(EcuSwProperties::class)->getPropertiesForEcuSwVersion($request->get('sw'));
            $assignedCan = !is_null($sw->getCanVersion()) ? $sw->getCanVersion()->getName() : 'Undefined';
            $canVersionList = $this->getManager()->getRepository(CanVersions::class)->findAll();
        }

        $entityManagerEcu = $this->getManager()->getRepository(ConfigurationEcus::class);

        $ecus = $entityManagerEcu->findBy([], ['ecuName' => 'ASC']);

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
            'generate_odx' => $request->get('generate_odx', 0),
        ];

        Menu::setObjectManager($this->getManager());


        $propertiesForm = null;

        if ($mode == Mode::MODE_CHANGE_ORDER or $mode == Mode::MODE_ADD_NEW_PROPERTY) {
            $ecuSwPropertiesCollection = $property->getProperties($sw->getEcuSwVersionId());
            $propertiesForm = $this
                ->createForm(EcuSwPropertiesCollectionType::class, $ecuSwPropertiesCollection)
                ->createView();
        }

        $addParamFromListForm = null;
        if ($mode == Mode::MODE_VIEW and $sw) {
            $ecuSwPropertiesCollection = $property->getPropForAddFromList($sw->getEcuSwVersionId());
            $addParamFromListForm = $this
                ->createForm(EcuSwPropAddFromListCollectionType::class, $ecuSwPropertiesCollection)
                ->createView();
        }


        $arguments = [
            'mode' => $mode,
            'ecu' => $ecu,
            'sw' => ($sw) ? $sw->getEcuSwVersionId() : null,
            'ecus' => $ecus,
            'sws' => $sws,
            'parametersButtons' => $parametersButtons,
            'topHorizontalButtonsState' => Menu::create(Properties::class)
                ->setArguments([
                    'mode' => $mode,
                    'ecu' => $ecu,
                    'sw' => ($sw) ? $sw->getEcuSwVersionId() : null,
                ])
                ->build()
                ->getMenu(),
            'bottomHorizontalButtonsState' => Menu::create(PropertiesBottomButtonsState::class)
                ->setArguments($parametersButtons)
                ->build()
                ->getMenu(),
            'ecuSwPropertiesList' => $ecuSwPropertiesList,
            'propertiesForm' => $propertiesForm,
            'addPropertyFromList' => $addParamFromListForm,
            'assignedCan' => $assignedCan,
            'canVersionsList' => $canVersionList,
        ];


        return $this->render('Ecu/Sw/Properties/index.html.twig', $arguments);
    }

    /**
     * @Route("/ecu/{ecu}",
     *     methods={"GET"},
     *     name="properties_management_index_with_ecu"
     * )
     * @param int $ecu
     * @param Settings $settings
     *
     * @return Response
     */
    public function indexWithEcu(int $ecu, Settings $settings): Response
    {
        return $this->forward('App\Controller\Ecu\Sw\PropertiesController::index', [
            'ecu' => $ecu
        ]);
    }


    /**
     * @Route("/ecu/{ecu}/sw/{sw}",
     *     methods={"GET"},
     *     name="properties_management_index_with_ecu_and_sw"
     * )
     * @param ConfigurationEcus $ecu
     * @param EcuSwVersions $sw
     * @return Response
     */
    public function indexWithEcuAndSw(ConfigurationEcus $ecu, EcuSwVersions $sw): Response
    {
        return $this->forward('App\Controller\Ecu\Sw\PropertiesController::index', [
            'ecu' => $ecu->getCeEcuId(),
            'sw' => $sw->getEcuSwVersionId()
        ]);
    }


    /**
     * @Route("/ecu/{ecu}/sw/{sw}/menu/{button}/{flag}",
     *     methods={"GET"},
     *     name="properties_management_index_with_ecu_and_sw_and_button_flag"
     * )
     * @param ConfigurationEcus $ecu
     * @param EcuSwVersions $sw
     * @param string $button
     * @param int $flag
     *
     * @return Response
     */
    public function indexWithEcuAndSwAndButtonFlag(
        ConfigurationEcus $ecu,
        EcuSwVersions $sw,
        string $button,
        int $flag
    ): Response
    {
        return $this->forward('App\Controller\Ecu\Sw\PropertiesController::index', [
            'ecu' => $ecu->getCeEcuId(),
            'sw' => $sw->getEcuSwVersionId(),
            $button => $flag,
        ]);
    }

    /**
     * @Route("/changeOrder/sw/{sw}", methods={"POST"}, name="ajax_save_order_properties_management")
     * @param Request $request
     * @param Property $property
     * @param EcuSwVersions $sw
     * @return JsonResponse
     */
    public function order(Request $request, Property $property, EcuSwVersions $sw): JsonResponse
    {
        $collection = $property->getProperties($sw->getEcuSwVersionId());

        $form = $this->createForm(
            EcuSwPropertiesCollectionType::class,
            $collection
        );
        $form->handleRequest($request);

//        if ($form->isSubmitted() && $form->isValid()) {
        if ($form->isSubmitted()) {
            /* Save non-entity almost as real entity object but
             * we have to decide how to do this, force developer
             * to use model classes instead of working on controller.
             */
            $property->changeOrders($collection, $sw);

            return $this->renderSuccessJson();
        }

        return $this->renderFormErrors($form);
    }


    /**
     * @Route("/addNew/sw/{swVersions}", methods={"GET", "POST"}, name="ajax_add_new_property_properties_management")
     * @param Request $request
     * @param Property $property
     * @param EcuSwVersions $swVersions
     * @return JsonResponse
     */
    public function addNewProperty(Request $request, Property $property, EcuSwVersions $swVersions): JsonResponse
    {
        $collection = $property->getProperties($swVersions->getEcuSwVersionId());

        $form = $this->createForm(
            EcuSwPropertiesCollectionType::class,
            $collection
        );
        $form->handleRequest($request);

//        $propertiesBag = [];
//
//        foreach ($collection->getProperties() as $propertyInCollection) {
//            if ($propertyInCollection->getId()){
//                $propertiesBag[$propertyInCollection->getId()] = clone $propertyInCollection;
//            }
//        }

        if ($form->isSubmitted() && $form->isValid()) {
            /* Save non-entity almost as real entity object but
             * we have to decide how to do this, force developer
             * to use model classes instead of working on controller.
             */
            $property->addNewProperty($collection, $swVersions);

            return $this->renderSuccessJson();
        }

        return $this->renderFormErrors($form);
    }

    /**
     * @Route("/addPropFromList/sw/{swVersions}", methods={"GET", "POST"}, name="ajax_add_property_from_list_properties_management")
     * @param Request $request
     * @param Property $property
     * @param EcuSwVersions $swVersions
     * @return JsonResponse
     */
    public function addPropertyFromList(Request $request, Property $property, EcuSwVersions $swVersions): JsonResponse
    {
        $collection = $property->getPropForAddFromList($swVersions->getEcuSwVersionId());

        $form = $this->createForm(
            EcuSwPropAddFromListCollectionType::class,
            $collection
        );
        $form->handleRequest($request);

        $response = $property->checkConflictAddFromList($collection, $swVersions);

        return $this->json($response);
    }

    /**
     * @Route("/list/add/conflict/resolve/sw/{swVersions}", methods={"GET", "POST"}, name="ajax_add_property_from_list_resolve_conflicts_properties_management")
     * @param Request $request
     * @param Property $property
     * @param EcuSwVersions $swVersions
     * @return JsonResponse
     */
    public function resolveConflictAddFromList(Request $request, Property $property, EcuSwVersions $swVersions): JsonResponse
    {
        $conflictToResolve = json_decode($request->get('properties_conflict'), true);

        if (!empty($conflictToResolve)) {
            $property->addPropFromListConfResolve($conflictToResolve, $swVersions);
        }

        return $this->json('success');
    }

    /**
     * @Route("/ecusSwsList/get", methods={"GET"}, name="ajax_get_other_ecu_sw_management_properties")
     * @return JsonResponse
     */
    public function getOtherSw(): JsonResponse
    {
        $ecusSwsList = $this->getManager()->getRepository(ConfigurationEcus::class)->getEcuSwList();

        $filteredEcuSw = [];

        foreach ($ecusSwsList as $ecuSw) {
            $filteredEcuSw[$ecuSw['ceEcuId']][$ecuSw['ecuSwVersionId']] = [
                'ecu' => $ecuSw['ecuName'],
                'sw' => $ecuSw['swVersion'],
                'suffix' => $ecuSw['suffix']
            ];
        }

        return $this->json($filteredEcuSw);
    }

    /**
     * @Route("/conflicts",
     *     methods={"POST"},
     *     name="ajax_copy_check_conflict_properties"
     * )
     * @param Request $request
     * @param Property $property
     * @return JsonResponse
     */
    public function checkCopyConflicts(Request $request, Property $property): JsonResponse
    {
        $swCurrent = $request->get('sw_current');
        $swDestination = $request->get('sw_destination');
        $propertiesToCopy = json_decode($request->get('ecu_properties'), true);

        $conflicts = $property->checkConflicts($swCurrent, $swDestination, $propertiesToCopy);

        return $this->json($conflicts);
    }

    /**
     * @Route("/conflicts/resolve", methods={"POST"}, name="ajax_resolve_conflicts_management_properties")
     * @param Request $request
     * @param Property $property
     *
     * @return JsonResponse
     */
    public function resolveConflicts(Request $request, Property $property): JsonResponse
    {
        $swName = $property->resolveConflicts(
            $request->get('sw_destination'),
            json_decode($request->get('properties_conflict'), true),
            json_decode($request->get('properties_without_conflict'), true)
        );

        return $this->json([$swName]);
    }

    /**
     * @Route("/copy", methods={"POST"}, name="ajax_copy_properties_management")
     * @param Request $request
     * @param Property $property
     *
     * @return JsonResponse
     */
    public function copyProperties(Request $request, Property $property): JsonResponse
    {
        $response = $property->copyWithoutConflict(
            $request->get('sw_destination'),
            json_decode($request->get('ecu_properties'), true)
        );

        return $this->json([$response]);
    }

    /**
     * @Route("/can/save/sw/{sw}", methods={"POST"}, name="ajax_save_can_version_properties_management")
     * @param Request $request
     * @param Property $property
     * @param EcuSwVersions $sw
     * @return JsonResponse
     */
    public function saveCanVersion(Request $request, Property $property, EcuSwVersions $sw): JsonResponse
    {
        $selectedCanVersion = json_decode($request->get('can_version'));
        $property->saveCanVersion($sw, $selectedCanVersion);
        return $this->json(null);
    }
}