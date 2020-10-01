<?php

namespace App\Controller\ResponsibilityManagement;

use App\Controller\LegacyBaseController;
use App\Entity\ConfigurationEcus;
use App\Entity\ResponsibilityAssignments;
use App\Entity\ResponsibilityCategories;
use App\Entity\StsOrganizationStructure;
use App\Entity\Users;
use App\Entity\VehicleConfigurations;
use App\Model\ResponsiblePersonCategoryModel;
use App\Service\ResponsibilityManagement\ResponsibilityManagement;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/responsible/management")
 */
class ResponsiblePersonsController extends LegacyBaseController
{
    const RESPONSIBILE = 2;
    const DEPUTY = 1;
    const WRITABLE = 0;

    /**
     * @Route("/")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $selectedResponsibilityId = $request->get('responsibility');
        $selectedSubResponsibilityId = $request->get('subresponsibility');

        // Fill categories
        $categoryList = $this->getManager()->getRepository(ResponsibilityCategories::class)->getCategories();
        $categoryObjList = [];
        foreach ($categoryList as $responsibilities) {
            $respPersonCatObj = new ResponsiblePersonCategoryModel();
            $respPersonCatObj->setId($responsibilities['id']);
            $respPersonCatObj->setName($responsibilities['name']);
            $respPersonCatObj->setIsSelected(($selectedResponsibilityId == $responsibilities['id']) ? true : false);

            $categoryObjList[] = $respPersonCatObj;
        }

        $subCategoryObjList = [];

        $isDeviationPermission = false;

        $displaySubcategorySelect = false;
        $displayUserAssignation = false;


        $responsibleUsersList = [];
        $deputyUsersList = null;
        $writableUsersList = null;

        if ($selectedResponsibilityId == 1) {
            // Fill subcategories
            $subCategoryList = $this->getManager()->getRepository(ConfigurationEcus::class)->getEcuList();
            foreach ($subCategoryList as $subCategory) {
                $subCategoryObj = new ResponsiblePersonCategoryModel();
                $subCategoryObj->setId($subCategory['id']);
                $subCategoryObj->setName($subCategory['name']);
                $subCategoryObj->setIsSelected(($selectedSubResponsibilityId == $subCategory['id']) ? true : false);

                $subCategoryObjList[] = $subCategoryObj;
            }

            $displaySubcategorySelect = true;

            if ($selectedSubResponsibilityId) {
                $responsibleList = $this->getManager()->getRepository(ResponsibilityAssignments::class)
                    ->getResponsiblePersonsForEcu($selectedSubResponsibilityId, self::RESPONSIBILE);
                $deputyList = $this->getManager()->getRepository(ResponsibilityAssignments::class)
                    ->getResponsiblePersonsForEcu($selectedSubResponsibilityId, self::DEPUTY);
                $writableList = $this->getManager()->getRepository(ResponsibilityAssignments::class)
                    ->getResponsiblePersonsForEcu($selectedSubResponsibilityId, self::WRITABLE);

                $responsibleUsersList = array_merge($responsibleList, $deputyList, $writableList);
                $displayUserAssignation = true;
            }

        } elseif ($selectedResponsibilityId == 2) {
            // Fill subcategories
            $subCategoryList = $this->getManager()->getRepository(VehicleConfigurations::class)->findVehicleTypes();
            $subCategoryList = array_filter($subCategoryList, function ($value) {
                return strlen($value['vehicleConfigurationKey']) == 1;
            });

            foreach ($subCategoryList as $subCategory) {
                $subCategoryObj = new ResponsiblePersonCategoryModel();
                $subCategoryObj->setId($subCategory['vehicleConfigurationKey']);
                $subCategoryObj->setName($subCategory['vehicleConfigurationKey']);
                $subCategoryObj->setIsSelected(($selectedSubResponsibilityId == $subCategory['vehicleConfigurationKey']) ? true : false);

                $subCategoryObjList[] = $subCategoryObj;
            }

            $displaySubcategorySelect = true;

            if ($selectedSubResponsibilityId) {
                $responsibleList = $this->getManager()->getRepository(ResponsibilityAssignments::class)
                    ->getResponsiblePersonsForModelRange($selectedSubResponsibilityId, self::RESPONSIBILE);
                $deputyList = $this->getManager()->getRepository(ResponsibilityAssignments::class)
                    ->getResponsiblePersonsForModelRange($selectedSubResponsibilityId, self::DEPUTY);
                $writableList = $this->getManager()->getRepository(ResponsibilityAssignments::class)
                    ->getResponsiblePersonsForModelRange($selectedSubResponsibilityId, self::WRITABLE);

                $responsibleUsersList = array_merge($responsibleList, $deputyList, $writableList);
                $displayUserAssignation = true;
            }

        } elseif ($selectedResponsibilityId == 8) {
            $responsibleUsersList = $this->getManager()->getRepository(ResponsibilityAssignments::class)
                ->getResponsiblePersons($selectedResponsibilityId, self::WRITABLE);
            $isDeviationPermission = true;
            $displayUserAssignation = true;

        } elseif ($selectedResponsibilityId) {
            $responsibleUsersList = $this->getManager()->getRepository(ResponsibilityAssignments::class)
                ->getResponsiblePersons($selectedResponsibilityId, self::RESPONSIBILE);
            $deputyUsersList = $this->getManager()->getRepository(ResponsibilityAssignments::class)
                ->getResponsiblePersons($selectedResponsibilityId, self::DEPUTY);
            $writableUsersList = $this->getManager()->getRepository(ResponsibilityAssignments::class)
                ->getResponsiblePersons($selectedResponsibilityId, self::WRITABLE);
            $responsibleUsersList = array_merge($responsibleUsersList, $deputyUsersList, $writableUsersList);
            $displayUserAssignation = true;
        }


        $argument = [
            'responsibilitiesList' => $categoryObjList,
            'subresponsibilitiesList' => $subCategoryObjList,
            'selectedResponsibilityId' => $selectedResponsibilityId,
            'selectedSubResponsibilityId' => $selectedSubResponsibilityId,
            'responsibleUsersList' => $responsibleUsersList,
            'displaySubcategorySelect' => $displaySubcategorySelect,
            'displayUsersAssignation' => $displayUserAssignation,
            'isDeviationPermission' => $isDeviationPermission,
        ];

        return $this->render('ResponsiblePersons/index.html.twig', $argument);
    }

    /**
     * @Route("/resp/{resp}", methods={"GET"}, name="responsible_person_index_with_responsibility")
     * @param int $resp
     * @return Response
     */
    public function indexWithResponsibilities(int $resp): Response
    {
        return $this->forward('App\Controller\ResponsibilityManagement\ResponsiblePersonsController::index', [
            'responsibility' => $resp
        ]);
    }

    /**
     * @Route("/resp/{resp}/subResp/{subResp}", methods={"GET"}, name="responsible_person_index_with_responsibility_subresponsibility")
     * @param int $resp
     * @param string $subResp
     * @return Response
     */
    public function indexWithRespAndSubResp(int $resp, string $subResp): Response
    {
        return $this->forward('App\Controller\ResponsibilityManagement\ResponsiblePersonsController::index', [
            'responsibility' => $resp,
            'subresponsibility' => $subResp
        ]);
    }

    /**
     * @Route("/user/autocomplete/{search}", methods={"GET"}, name="ajax_autocomplete_users")
     * @param string $search
     * @return JsonResponse
     */
    public function getFilteredUser(string $search)
    {
        $result = $this->getManager()->getRepository(Users::class)->getUsersList($search);

        return $this->json($result);
    }

    /**
     * @Route("/structure/autocomplete/{search}", methods={"GET"}, name="ajax_autocomplete_structures")
     * @param string $search
     * @return JsonResponse
     */
    public function getFilteredStructure(string $search)
    {
        $result = $this->getManager()->getRepository(StsOrganizationStructure::class)->getStructureList($search);

        return $this->json($result);
    }

    /**
     * @Route("/remove/resp/{resp}/subResp/{subResp}/user/{userId}/userType/{userType}", methods={"DELETE"}, name="remove_user_role")
     * @param ResponsibilityCategories $resp
     * @param string $subResp
     * @param int $userId
     * @param bool $userType
     * @param ResponsibilityManagement $responsibilityManagement
     * @return JsonResponse
     */
    public function removeResponsiblePerson(
        ResponsibilityCategories $resp,
        string $subResp,
        int $userId,
        bool $userType,
        ResponsibilityManagement $responsibilityManagement
    ): JsonResponse
    {
        try {
            $responsibilityManagement->removeUserRole($resp, $subResp, $userId, $userType);
            return $this->renderSuccessJson();
        } catch (Exception $exception) {
            return $this->json($exception->getMessage());
        }
    }

    /**
     * @Route("/switch/resp/{resp}/subResp/{subResp}/user/{userId}/userType/{userType}/role/{role}", methods={"GET"}, name="switch_user_role")
     * @param ResponsibilityCategories $resp
     * @param string $subResp
     * @param int $userId
     * @param bool $userType
     * @param int $role
     * @param ResponsibilityManagement $responsibilityManagement
     * @return JsonResponse
     */
    public function switchUserRole(
        ResponsibilityCategories $resp,
        string $subResp,
        int $userId,
        bool $userType,
        int $role,
        ResponsibilityManagement $responsibilityManagement
    ): JsonResponse
    {
        try {
            $responsibilityManagement->changeUserRole($resp, $subResp, $userId, $userType, $role);
            return $this->renderSuccessJson();

        } catch (Exception $exception) {
            return $this->json($exception->getMessage());
        }
    }

    /**
     * @Route("/addUser", methods={"GET", "POST"}, name="add_responsible_user")
     * @param Request $request
     * @param ResponsibilityManagement $responsibilityManagement
     * @return JsonResponse
     */
    public function addUser(Request $request, ResponsibilityManagement $responsibilityManagement): JsonResponse
    {
        $categoryId = $request->get('category');
        $subcategoryId = $request->get('subcategory');
        $responsibilityCategories = $this->getManager()->getRepository(ResponsibilityCategories::class)->find($categoryId);

        $mode = $request->get('mode');
        $responsibility_role = $request->get("res_role");
        $user = $this->getManager()->getRepository(Users::class)->find($request->get('user_id'));

        try {
            $responsibilityManagement->addUser($responsibilityCategories, $subcategoryId, $user, $responsibility_role, $mode);
            return $this->renderSuccessJson();
        } catch (Exception $exception) {
            return $this->json($exception->getMessage());
        }
    }
    

    /**
     * @Route("/addStructure", methods={"GET", "POST"}, name="add_responsible_structure")
     * @param Request $request
     * @param ResponsibilityManagement $responsibilityManagement
     * @return JsonResponse
     */
    public function addStructure(Request $request, ResponsibilityManagement $responsibilityManagement): JsonResponse
    {
        $categoryId = $request->get('category');
        $subcategoryId = $request->get('subcategory');
        $responsibilityCategories = $this->getManager()->getRepository(ResponsibilityCategories::class)->find($categoryId);


        $structureId = $request->get('structure_id');
        $structure_details = $request->get("str_details");
        $role = $request->get('role');
        $stsOrgStr = $this->getManager()->getRepository(StsOrganizationStructure::class)->find($structureId);

        try {
            $responsibilityManagement->addStructure($responsibilityCategories, $subcategoryId, $stsOrgStr, $structure_details, $role);
            return $this->renderSuccessJson();
        } catch (Exception $exception) {
            return $this->json($exception->getMessage());
        }
    }

}