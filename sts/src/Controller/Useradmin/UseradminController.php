<?php

namespace App\Controller\Useradmin;

use App\Controller\LegacyBaseController;
use App\Entity\EcuReleaseDeadline;
use App\Model\ResponsiblePersonCategoryModel;
use App\Service\ResponsibilityManagement\ResponsibilityManagement;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/useradmin")
 */
class UseradminController extends LegacyBaseController
{

    /**
     * @Route("/")
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        // $user = $this->getManager()->getRepository(Users::class)->find($_SESSION['sts_userid']);
        // $x_weeks = $request-> $request->get('xweeks');
        // $y_weeks = $request-> $request->get('yweeks');
        // $z_weeks = $request-> $request->get('zweeks');

        // $arguments = [
        //     'xweeks' => $x_week,
        //     'yweeks' => $y_week,
        //     'zweeks' => $z_week,
        // ];

        return $this->render('useradmin/index.html.twig');
    }

}