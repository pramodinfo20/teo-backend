<?php

namespace App\Controller\User\Keys;

use App\Controller\LegacyBaseController;
use App\Controller\SpringBaseTrait;
use App\Entity\Users;
use App\Entity\UsersPublicKeys;
use App\Service\Admin\Keys\Keys;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user/keys")
 */
class UserKeysController extends LegacyBaseController
{
    use SpringBaseTrait;

    /**
     * @Route("/", methods={"GET"}, name="user_keys")
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        $usersPublicKeys = $this->getManager()->getRepository(UsersPublicKeys::class)->findAll();
        $users = array_map(function ($row) {
            $user = $row->getUser();
            return ['id' => $user->getId(), 'label' => 'Name: ' . $user->getFName() . ' Surname: ' . $user->getLName().
            ' Email: ' . $user->getEmail()];
        }, $usersPublicKeys);
        $users = array_filter($users, function ($user) {
            return $user['id'] != $_SESSION['sts_userid'];
        });

        $arguments = [
            'keys' => [
                'userPublicKeyExists' => $this->getManager()->getRepository(UsersPublicKeys::class)
                    ->findOneBy(['user' => $_SESSION['sts_userid']]),
                'publicServerKeyExists' => file_exists(Keys::SERVER_KEYS_LOCATION . "/" .
                    Keys::SERVER_PUBLIC_KEY_NAME)
            ],
            'users' => $users
        ];

        return $this->render("User/Keys/index.html.twig", $arguments);
    }

    /**
     * @Route("/public/upload", methods={"POST"}, name="user_public_key_upload")
     * @param Request $request
     * @param Keys    $keysService
     *
     * @return JsonResponse
     */
    public function uploadUserPublicKey(Request $request, Keys $keysService): JsonResponse
    {
        $userPublicKey = file_get_contents($request->files->get('userPublicKey'));
        $manager = $this->getManager();
        $user = $manager->getRepository(Users::class)->find($_SESSION['sts_userid']);

        if ($keysService->checkKeyFileFormat($userPublicKey)) {
            $userPublicKeyDb = new UsersPublicKeys();
            $userPublicKeyDb->setPublicKey($userPublicKey);
            $userPublicKeyDb->setUser($user);

            $manager->persist($userPublicKeyDb);
            $manager->flush();

            return $this->renderSuccessJson();
        }

        return $this->renderFailureJson();
    }

    /**
     * @Route("/signature/download", methods={"GET"}, name="signature_download")
     * @param Request $request
     *
     * @return Response
     */
    public function downloadSignature(Request $request): Response
    {
        $stream = stream_get_contents(fopen('data://text/plain,'.json_decode($this->springPost("user/keys/signature/create",
            [
                'form_params' => [
                    'userId' => $_SESSION['sts_userid']
                ]
            ]
        ), true)['response']['content'], 'r'));
        $response = new Response($stream);

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'signature.key'
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * @Route("/public/download", methods={"GET"}, name="public_key_download")
     * @param Request $request
     * @param Keys    $keysService
     *
     * @return BinaryFileResponse
     */
    public function downloadPublicKey(Request $request, Keys $keysService): BinaryFileResponse
    {
        return new BinaryFileResponse(Keys::SERVER_KEYS_LOCATION . "/" . Keys::SERVER_PUBLIC_KEY_NAME);
    }

    /**
     * @Route("/user/{userId}/userPublic/download", methods={"GET"}, name="user_public_key_download")
     * @param Request $request
     * @param int $userId
     * @return Response
     */
    public function downloadUsersPublicKey(Request $request, int $userId): Response
    {
        $userPublicKey = $this->getManager()->getRepository(UsersPublicKeys::class)->findOneBy(['user' => $userId])
            ->getPublicKey();
        $stream = stream_get_contents(fopen('data://text/plain,'.$userPublicKey, 'r'));
        $response = new Response($stream);

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'userPublic.key'
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
//
//    /**
//     * @Route("/users/get", methods={"GET"}, name="users_get")
//     * @param Request $request*
//     * @return JsonResponse
//     */
//    public function getUsersList(Request $request): JsonResponse
//    {
//        $usersPublicKeys = $this->getManager()->getRepository(UsersPublicKeys::class)->findAll();
//        $users = array_map(function ($row) {
//            $user = $row->getUser();
//            return ['id' => $user->getId(), 'fName' => $user->getFName(), 'lName' => $user->getLName(), 'email' =>
//                $user->getEmail()];
//        }, $usersPublicKeys);
//        $users = array_filter($users, function ($user) {
//            return $user['id'] != $_SESSION['sts_userid'];
//        });
//
//
//        return $this->json($users);
//    }
}