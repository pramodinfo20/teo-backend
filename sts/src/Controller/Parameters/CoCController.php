<?php

namespace App\Controller\Parameters;

use App\Controller\LegacyBaseController;
use App\Entity\CocParameters;
use App\Entity\EcuSwParameters;
use App\Entity\Users;
use App\Form\Parameter\CoCParametersType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/parameters/coc")
 */
class CoCController extends LegacyBaseController
{
    /**
     * @Route("/", name="coc_parameters_index", methods={"GET"})
     */
    public function index(): Response
    {
        $cocParameters = $this->getManager()
            ->getRepository(CocParameters::class)
            ->findBy([], ['parameterOrder' => 'DESC']);

        foreach ($cocParameters as $cocParameter) {
            $linkedEcuParameters = $this->getManager()->getRepository(EcuSwParameters::class)
                ->findCoCsUsageInEcuParameters($cocParameter);
            $cocParameter->setLinkedEcuParameters($linkedEcuParameters);
        }

        return $this->render('Parameters/CoC/index.html.twig', [
            'cocParameters' => $cocParameters
        ]);
    }

    /**
     * @Route("/new/{user}", name="coc_parameters_new", methods={"GET","POST"})
     * @param Request $request
     * @param Users   $user
     *
     * @return Response
     */
    public function new(Request $request, Users $user): Response
    {
        $cocParameter = new cocParameters();
        $cocParameter->setResponsibleUser($user);
        $form = $this->createForm(CoCParametersType::class, $cocParameter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getManager();
            $entityManager->persist($cocParameter);
            $entityManager->flush();

            return $this->redirectToLegacyUrl('coc_parameters_index');
        }

        return $this->render('Parameters/CoC/new.html.twig', [
            'cocParameter' => $cocParameter,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/new/{user}/save", name="coc_parameters_save_new", methods={"GET", "POST"})
     * @param Request $request
     * @param Users $user
     * @return Response
     */
    public function saveNew(Request $request, Users $user): Response
    {
        $cocParameter = new CoCParameters();
        $cocParameter->setResponsibleUser($user);
        $form = $this->createForm(CoCParametersType::class, $cocParameter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getManager();
            $entityManager->persist($cocParameter);
            $entityManager->flush();

            return $this->renderSuccessJson();
        }

        return $this->renderFormErrors($form);
    }

    /**
     * @Route("/{cocParameterId}/edit", name="coc_parameters_edit", methods={"GET","POST"})
     * @param Request       $request
     * @param CocParameters $cocParameter
     *
     * @return Response
     */
    public function edit(Request $request, CocParameters $cocParameter): Response
    {
        $form = $this->createForm(CoCParametersType::class, $cocParameter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getManager();
            $entityManager->persist($cocParameter);
            $entityManager->flush();

            return $this->redirectToLegacyUrl('coc_parameters_index', [
                'cocParameterId' => $cocParameter->getCocParameterId(),
            ]);
        }

        return $this->render('Parameters/CoC/edit.html.twig', [
            'cocParameter' => $cocParameter,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{cocParameter}/edit/save", name="coc_parameters_save_edit", methods={"GET","POST"})
     * @param Request $request
     * @param CocParameters $cocParameter
     * @return Response
     */
    function saveEdit(Request $request, CocParameters $cocParameter): Response
    {

        $form = $this->createForm(CoCParametersType::class, $cocParameter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getManager();
            $entityManager->persist($cocParameter);
            $entityManager->flush();

            return $this->renderSuccessJson();
        }

        return $this->renderFormErrors($form);
    }
//    After Product Owner decision, we hide this posibility to remove CoC Parameter
//    We only comment this code, maybe in future we will make decision to put new validators,
//    when users will want to remove CoC parameters
//    (System should check if parameter is used in any configuration)
//
//    /**
//     * @Route("/{cocParameterId}", name="coc_parameters_delete", methods={"DELETE"})
//     * @param CocParameters $cocParameter
//     *
//     * @return Response
//     */
//    public function delete(CocParameters $cocParameter): Response
//    {
//        $entityManager = $this->getManager();
//        $entityManager->remove($cocParameter);
//        $entityManager->flush();
//
//        return $this->redirectToLegacyUrl('coc_parameters_index');
//    }
}
