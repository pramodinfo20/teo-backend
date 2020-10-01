<?php

namespace App\Controller\Parameters;

use App\Controller\LegacyBaseController;
use App\Entity\EcuSwParameters;
use App\Entity\GlobalParameters;
use App\Entity\GlobalParameterValuesSets;
use App\Entity\GlobalParameterValuesSetsMapping;
use App\Entity\Users;
use App\Enum\Entity\VariableTypes;
use App\Form\Parameter\GlobalParametersType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/parameters/global")
 */
class GlobalController extends LegacyBaseController
{
    /**
     * @Route("/", name="global_parameters_index", methods={"GET"})
     */
    public function index(): Response
    {
        $globalParameters = $this->getManager()
            ->getRepository(GlobalParameters::class)
            ->findAll();

        foreach ($globalParameters as $globalParameter) {
            $linkedEcuParameters = $this->getManager()->getRepository(EcuSwParameters::class)
                ->findGlobalsUsageInEcuParameters($globalParameter);
            $globalParameter->setLinkedEcuParameters($linkedEcuParameters);
        }

        return $this->render('Parameters/Global/index.html.twig', [
            'globalParameters' => $globalParameters
        ]);
    }

    /**
     * @Route("/new/{user}", name="global_parameters_new", methods={"GET","POST"})
     * @param Request $request
     * @param Users   $user
     *
     * @return Response
     */
    public function new(Request $request, Users $user): Response
    {
        $globalParameter = new GlobalParameters();
        $globalParameter->setResponsibleUser($user);
        $form = $this->createForm(GlobalParametersType::class, $globalParameter);
        $form->handleRequest($request);

        return $this->render('Parameters/Global/new.html.twig', [
            'globalParameter' => $globalParameter,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/new/{user}/save", name="global_parameters_save_new", methods={"GET", "POST"})
     * @param Request $request
     * @param Users $user
     * @return Response
     */
    public function saveNew(Request $request, Users $user): Response
    {
        $globalParameter = new GlobalParameters();
        $globalParameter->setResponsibleUser($user);
        $form = $this->createForm(GlobalParametersType::class, $globalParameter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $variableType = $globalParameter->getVariableType()->getVariableTypeId();

            switch ($variableType) {
                case VariableTypes::VARIABLE_TYPE_ASCII:
                case VariableTypes::VARIABLE_TYPE_STRING:
                case VariableTypes::VARIABLE_TYPE_BOOLEAN:
                case VariableTypes::VARIABLE_TYPE_BLOB:
                    $globalParameter->setMinValue(null);
                    $globalParameter->setMaxValue(null);
                    break;
            }

            $entityManager = $this->getManager();
            $entityManager->persist($globalParameter);
            $entityManager->flush();

            return $this->renderSuccessJson();
        }

        return $this->renderFormErrors($form);
    }

    /**
     * @Route("/{globalParameterId}/edit", name="global_parameters_edit", methods={"GET"})
     * @param Request $request
     * @param GlobalParameters $globalParameter
     * @return Response
     */
    public function edit(Request $request, GlobalParameters $globalParameter): Response
    {
        $form = $this->createForm(GlobalParametersType::class, $globalParameter);
        $form->handleRequest($request);

        return $this->render('Parameters/Global/edit.html.twig', [
            'globalParameter' => $globalParameter,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{globalParameterId}/edit/save", name="global_parameters_save_edit", methods={"GET","POST"})
     * @param Request $request
     * @param GlobalParameters $globalParameter
     * @return Response
     */
    function saveEdit(Request $request, GlobalParameters $globalParameter): Response
    {

        $form = $this->createForm(GlobalParametersType::class, $globalParameter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $variableType = $globalParameter->getVariableType()->getVariableTypeId();

            switch ($variableType) {
                case VariableTypes::VARIABLE_TYPE_ASCII:
                case VariableTypes::VARIABLE_TYPE_STRING:
                case VariableTypes::VARIABLE_TYPE_BOOLEAN:
                case VariableTypes::VARIABLE_TYPE_BLOB:
                    $globalParameter->setMinValue(null);
                    $globalParameter->setMaxValue(null);
                    break;
            }

            $entityManager = $this->getManager();
            $entityManager->persist($globalParameter);
            $entityManager->flush();

            return $this->renderSuccessJson();
        }

        return $this->renderFormErrors($form);
    }


    /**
     * @Route("/{globalParameterId}", name="global_parameters_delete", methods={"DELETE"})
     * @param GlobalParameters $globalParameter
     *
     * @return Response
     */
    public function delete(GlobalParameters $globalParameter): Response
    {
        $entityManager = $this->getManager();

        $globalSets  = $entityManager->getRepository(GlobalParameterValuesSets::class)
            ->findBy(['globalParameter' => $globalParameter]);

        $globalSetsIds = array_map(function ($global) {
           return $global->getGlobalParameterValuesSetId();
        }, $globalSets);

        $globalMapping = $entityManager->getRepository(GlobalParameterValuesSetsMapping::class)
            ->findBy(['globalParameterValuesSet' => $globalSetsIds]);

        foreach ($globalMapping as $mapping) {
            $entityManager->remove($mapping);
        }

        foreach($globalSets as $set) {
            $entityManager->remove($set);
        }

        $entityManager->flush();

        $entityManager->remove($globalParameter);
        $entityManager->flush();

        return $this->redirectToLegacyUrl('global_parameters_index');
    }
}
