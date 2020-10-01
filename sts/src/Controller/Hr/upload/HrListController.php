<?php
namespace App\Controller\Hr\upload;

use App\Controller\LegacyBaseController;
use App\Entity\PersonsListHr;
use App\Repository\PersonsListHrRepository;
use App\Service\Hr\upload\HrUpload;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/hr/upload")
 */
class HrListController extends LegacyBaseController
{
    /**
     * @Route("/", methods={"GET"}, name="upload_hr_list_index")
     * @return Response
     */
    public function index(): Response
    {
        $storedHrList = $this->getManager()->getRepository(PersonsListHr::class)->getLastUploadList();

        return $this->render('Hr/upload/index.html.twig', [
            'storedHrList' => $storedHrList
        ]);

    }

    /**
     * @Route("/save", methods={"POST"}, name="save_hr_list_index")
     * @param Request $request
     * @param HrUpload      $hrService
     * @return JsonResponse
     */
    public function saveHrList(Request $request, HrUpload $hrService): JsonResponse
    {
        $csvFile = $request->files->get('csvfile');

        return $this->json($hrService->processUpload($csvFile));
    }
}
