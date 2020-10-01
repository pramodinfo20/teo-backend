<?php

namespace App\Controller\Hr\history;

use App\Controller\LegacyBaseController;
use App\Entity\HistoryPersonsListHr;
use App\Entity\UserRoles;
use App\Form\Hr\HistoryPersonsListHrType;
use App\Form\UserRole\Assignment\CompanyStructureAssignmentType;
use App\Service\Hr\history\HrListHistory;
use App\Service\Hr\history\Menu\Footer;
use App\Service\UserRole\UserRoleToCompanyStructure;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Factory\Menu;

/**
 * @Route("/hr/history")
 */
class HrListHistoryController extends LegacyBaseController
{
    /**
     * @Route("/", methods={"GET"}, name="upload_hr_list_history_index")
     *
     * @param Request $request
     * @param HrListHistory $hrListHistService
     * @param UserRoleToCompanyStructure $userRole2CompanyStructure
     * @return Response
     */
    public function index(Request $request, HrListHistory $hrListHistService, UserRoleToCompanyStructure $userRole2CompanyStructure): Response
    {
        $role = $request->get('role', 0);
        $history = $request->get('history', 0);

        /* Set static factory */
        Menu::setObjectManager($this->getManager());
        $arguments = [
            'role' => $role,
            'history' => $history
        ];

        if ($history != 0) {
            $historyEntity = $this->getManager()->getRepository(HistoryPersonsListHr::class)->find($history);
        } else {
            $historyEntity = null;
        }

        $historyPersonsListHr = new HistoryPersonsListHr();
        $historyForm = $this->createForm(HistoryPersonsListHrType::class, $historyPersonsListHr, ['history' =>
            $historyEntity])
            ->createView();

        // Display graph
        $historyData = $request->get('historyData', null);
        $historyGraphData = null;

        if ($historyData !== null) {
            // Remap keys in table.
            $remappedNamesHistoryData = $hrListHistService->remapKeysHistoryData($historyData);
            // Process data in service to match for function input.
            $historyGraphStructure = $userRole2CompanyStructure->buildTreeStructureForGraph($remappedNamesHistoryData);
            // Transform for input data for graph draw function.
            $historyGraphData = $userRole2CompanyStructure->transformForGraphInput($historyGraphStructure);
        }


        $parameters = [
            'arguments' => $arguments,
            'footerMenuButtonsState' => Menu::create(Footer::class)
                ->setArguments([])
                ->build()
                ->getMenu(),
            'historyForm' => $historyForm,
            'historyGraphData' => $historyGraphData,
        ];

        if ($history != 0) {
            $hrHistory = $this->getManager()->getRepository(HistoryPersonsListHr::class)->find($history);
            $userRole2CompanyStructureModel = $userRole2CompanyStructure
                ->getCompanyStructAssignmentFromJSON($hrHistory);
            $options = [
                'userRolesChoice' => $userRole2CompanyStructureModel->getUserRolesChoice(),
                'companyStructureChoice' => $userRole2CompanyStructureModel->getCompanyStructuresChoice(),
                'customDisabled' => true,
                'companyStructureTree' => $userRole2CompanyStructureModel->getCompanyStructureTree()
            ];

            if ($role != 0) {
                $userRole2CompanyStructureModel->setCurrentRole($role);
                $userRole2CompanyStructureModel
                    ->setCompanyStructuresForCurrentRole($userRole2CompanyStructure
                        ->findCompanyStructuresIdsByUserRole($userRole2CompanyStructureModel, $role));
            }

            $userrole2companyForm = $this->createForm(CompanyStructureAssignmentType::class,
                $userRole2CompanyStructureModel, $options)
                ->createView();
            $parameters['userrole2companyForm'] = $userrole2companyForm;
        }


        return $this->render('Hr/history/index.html.twig', $parameters);
    }

    /**
     * @Route("/history/{history}",
     *     methods={"GET"},
     *     name="history_index_with_history"
     * )
     * @param HistoryPersonsListHr $history
     *
     * @return Response
     */
    public function indexWithHistory(HistoryPersonsListHr $history): Response
    {
        return $this->forward('App\Controller\Hr\history\HrListHistoryController::index', [
            'history' => $history->getHplhId(),
        ]);
    }


    /**
     * @Route("/history/{history}/role/{role}",
     *     methods={"GET"},
     *     name="history_index_with_history_and_role"
     * )
     * @param HistoryPersonsListHr $history
     * @param UserRoles $role
     *
     * @return Response
     */
    public function indexWithHistoryAndUserRole(HistoryPersonsListHr $history, UserRoles $role): Response
    {
        return $this->forward('App\Controller\Hr\history\HrListHistoryController::index', [
            'history' => $history->getHplhId(),
            'role' => $role->getId()
        ]);
    }

    /**
     * @Route("/history/{history}/graph", methods={"GET"}, name="ajax_history_graph")
     *
     * @param HistoryPersonsListHr $history
     * @return Response
     */
    public function indexWithHistoryData(HistoryPersonsListHr $history): Response
    {
        return $this->forward('App\Controller\Hr\history\HrListHistoryController::index', [
            'historyData' => array_shift(($history->getHistoryData())[0]),
        ]);
    }

}
