<?php

namespace App\Controller\Ecu\Diagnostic;

use App\Controller\LegacyBaseController;
use App\Factory\Menu;
use App\Form\Diagnostic\DynamicParametersCollectionType;
use App\Service\Ecu\Diagnostic\Parameter\DiagnosticParameterManagement;
use App\Service\Ecu\Diagnostic\Parameter\Menu\Footer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ecu/diagnostic/parameter/management")
 */
class DiagnosticSwParameterManagementController extends LegacyBaseController
{
    /**
     * @Route("/", methods={"GET"}, name="diagnostic_parameter_management_index")
     * @param Request                       $request
     *
     * @param DiagnosticParameterManagement $parameter
     *
     * @return Response
     */
    public function index(
        Request $request,
        DiagnosticParameterManagement $parameter
    ):Response {
        $mode = $request->get('mode', 1);

        $parameters = $parameter->getParameters();

        /* Set static factory */
        Menu::setObjectManager($this->getManager());
        $parametersButtons = [
            'mode' =>  $request->get('mode', 1),
            'save' => $request->get('save', 0),
            'cancel' => $request->get('cancel', 0)
        ];

        $arguments = [
            'mode' => $mode,
            'parameters' => $parameters->getParameters(),
            'parametersButtons' => $parametersButtons,
            'parametersButtonsState' => Menu::create(Footer::class)
                ->setArguments($parametersButtons)
                ->build()
                ->getMenu(),
        ];

        if ($mode == 2) {

            $arguments['parametersForm'] = $this->createForm(
                DynamicParametersCollectionType::class, $parameters)->createView();
        }

        return $this->render('Ecu/Diagnostic/Parameter/Management/index.html.twig', $arguments);
    }


    /**
     * @Route("/mode/{mode}",
     *     methods={"GET"},
     *     name="diagnostic_parameter_management_index_with_mode"
     * )
     * @param int      $mode
     *
     * @return Response
     */
    public function indexWitMode(int $mode): Response
    {
        return $this->forward('App\Controller\Ecu\Diagnostic\DiagnosticSwParameterManagementController::index', [
            'mode' => $mode
        ]);
    }

    /**
     * @Route("/save",
     *     methods={"POST"},
     *     name="ajax_diagnostic_parameter_management_save"
     * )
     * @param Request                       $request
     *
     * @param DiagnosticParameterManagement $diagnostic
     *
     * @return Response
     * @throws \Exception
     */
    public function save(
        Request $request,
        DiagnosticParameterManagement $diagnostic
    ): Response
    {
        $collection = $diagnostic->getParameters();

        /* Get parameters before parsing by SF, references complains
         * many problems
         */
        $parametersBag = [];

        foreach ($collection->getParameters() as $parameter) {
            if ($parameter->getParameterId()) {
                $parametersBag[$parameter->getParameterId()] = clone $parameter;
            }
        }


        $form = $this->createForm(DynamicParametersCollectionType::class, $collection);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Save non-entity almost as real entity object but
             * we have to decide how to do this, force developer
             * to use model classes instead of working on controller.
             */
            $diagnostic->save($collection, $parametersBag);

            return $this->renderSuccessJson();

        }

        return $this->renderFormErrors($form);
    }

}
