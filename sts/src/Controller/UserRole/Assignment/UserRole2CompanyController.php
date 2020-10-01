<?php

namespace App\Controller\UserRole\Assignment;

use App\Controller\LegacyBaseController;
use App\Entity\PersonsListHr;
use App\Entity\UserRoles;
use App\Entity\Users;
use App\Factory\Menu;
use App\Form\UserRole\Assignment\CompanyStructureAssignmentType;
use App\Model\UserRole\Assignment\CompanyStructureAssignmentModel;
use App\Service\UserRole\Menu\Footer;
use App\Service\UserRole\UserRoleToCompanyStructure;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/userrole/assignment/company")
 */
class UserRole2CompanyController extends LegacyBaseController
{
    /**
     * @Route("/")
     * @param Request $request
     * @param UserRoleToCompanyStructure $userRoleToCompanyStructure
     *
     * @return Response
     */
    public function index(Request $request, UserRoleToCompanyStructure $userRoleToCompanyStructure): Response
    {
        $role = $request->get('role', 0);
        $tab = $request->get('tab', 0);

        /* Set static factory */
        Menu::setObjectManager($this->getManager());
        $arguments = [
            'role' => $role,
            'tab' => $tab
        ];

        $parameters = [
            'arguments' => $arguments,
            'footerMenuButtonsState' => Menu::create(Footer::class)
                ->setArguments([])
                ->build()
                ->getMenu(),
        ];

        if ($tab == 0) {
            $userRole2CompanyStructureModel = $userRoleToCompanyStructure->getCompanyStructAssignmentFromDb();

            $options = [
                'userRolesChoice' => $userRole2CompanyStructureModel->getUserRolesChoice(),
                'companyStructureChoice' => $userRole2CompanyStructureModel->getCompanyStructuresChoice(),
                'customDisabled' => false,
                'companyStructureTree' => $userRole2CompanyStructureModel->getCompanyStructureTree()
            ];

            if ($role != 0) {
                $userRole2CompanyStructureModel->setCurrentRole($role);
                $userRole2CompanyStructureModel
                    ->setCompanyStructuresForCurrentRole($userRoleToCompanyStructure
                        ->findCompanyStructuresIdsByUserRole($userRole2CompanyStructureModel, $role));
            }

            $userrole2companyForm = $this->createForm(CompanyStructureAssignmentType::class,
                $userRole2CompanyStructureModel, $options)
                ->createView();
            $parameters['userrole2companyForm'] = $userrole2companyForm;
        }

        return $this->render('UserRole/Assignment/index.html.twig', $parameters);
    }

    /**
     * @Route("/role/{role}",
     *     methods={"GET"},
     *     name="assignment_index_with_userrole"
     * )
     * @param UserRoles $role
     *
     * @return Response
     */
    public function indexWithUserRole(UserRoles $role): Response
    {
        return $this->forward('App\Controller\UserRole\Assignment\UserRole2CompanyController::index', [
            'role' => $role->getId(),
        ]);
    }

    /**
     * @Route("/tab/{tab}",
     *     methods={"GET"},
     *     name="assignment_index_with_tab"
     * )
     * @param int $tab
     *
     * @return Response
     */
    public function indexWithTab(int $tab): Response
    {
        return $this->forward('App\Controller\UserRole\Assignment\UserRole2CompanyController::index', [
            'tab' => $tab,
        ]);
    }

    /**
     * @Route("/role/{role}/tab/{tab}",
     *     methods={"GET"},
     *     name="assignment_index_with_userrole_and_tab"
     * )
     * @param UserRoles $role
     * @param int $tab
     *
     * @return Response
     */
    public function indexWithUserRoleAndTab(UserRoles $role, int $tab): Response
    {
        return $this->forward('App\Controller\UserRole\Assignment\UserRole2CompanyController::index', [
            'role' => $role->getId(),
            'tab' => $tab
        ]);
    }

    /**
     * @Route("/overwrite/{overwrite}/save",
     *     methods={"POST"},
     *     name="assignment_save"
     * )
     * @param Request $request
     * @param bool $overwrite
     * @param UserRoleToCompanyStructure $userRole2CompanyStructure
     *
     * @return Response
     * @throws /Exception
     */
    public function save(
        Request $request,
        bool $overwrite,
        UserRoleToCompanyStructure $userRole2CompanyStructure): Response
    {
        $assignmentModel = $userRole2CompanyStructure->getCompanyStructAssignmentFromDb();

        $options = [
            'userRolesChoice' => $assignmentModel->getUserRolesChoice(),
            'companyStructureChoice' => $assignmentModel->getCompanyStructuresChoice(),
            'customDisabled' => false,
            'companyStructureTree' => $assignmentModel->getCompanyStructureTree()
        ];

        $form = $this->createForm(CompanyStructureAssignmentType::class, $assignmentModel, $options);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* Save non-entity almost as real entity object but
             * we have to decide how to do this, force developer
             * to use model classes instead of working on controller.
             */
            return $this->json($userRole2CompanyStructure->save($assignmentModel, $overwrite));
        }

        return $this->renderFormErrors($form);
    }

    /**
     * @Route("/graph", methods={"POST"}, name="ajax_graph")
     **
     * @param UserRoleToCompanyStructure $userRoleToCompanyStructureService
     *
     * @return JsonResponse
     */
    public function getGraphData(
        UserRoleToCompanyStructure $userRoleToCompanyStructureService
    ): JsonResponse
    {
        $graph = $this->getManager()->getRepository(PersonsListHr::class)->getOrganizationStructureWithNames();
        $users = $this->getManager()->getRepository(Users::class)->findAllArray();

        $users = array_map(function ($user) {
            $user['hash'] = strtolower(hash('sha256', mb_convert_case($user['fname'], MB_CASE_TITLE, "UTF-8") . "." .
                mb_convert_case($user['lname'], MB_CASE_TITLE, "UTF-8")));

            return $user;
        }, $users);


        $graph = array_map(function ($node) use ($users) {
            $user = array_filter($users, function ($user) use ($node) {
                return $user['hash'] == strtolower($node['person']);
            });

            if (!empty($user)) {
                $node['person'] = current($user)['fname'] . " " . current($user)['lname'];
            }

            return $node;
        }, $graph);

        $graphTreeStructure = $userRoleToCompanyStructureService->buildTreeStructureForGraph($graph);

        $preparedGraph = $userRoleToCompanyStructureService->transformForGraphInput($graphTreeStructure);

        return $this->json($preparedGraph);
    }
}
