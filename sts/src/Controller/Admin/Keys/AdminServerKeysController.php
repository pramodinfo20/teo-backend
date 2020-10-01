<?php

namespace App\Controller\Admin\Keys;

use App\Controller\LegacyBaseController;
use App\Controller\SpringBaseTrait;
use App\Service\Admin\Keys\Keys;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/keys")
 */
class AdminServerKeysController extends LegacyBaseController
{
    use SpringBaseTrait;

    /**
     * @Route("/", methods={"GET"}, name="server_keys")
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {

        $arguments = [
            'keys' => [
                'privateKeyExists' => file_exists(Keys::SERVER_KEYS_LOCATION . "/" . Keys::SERVER_PRIVATE_KEY_NAME),
                'publicKeyExists' => file_exists(Keys::SERVER_KEYS_LOCATION . "/" . Keys::SERVER_PUBLIC_KEY_NAME)
            ]
        ];


        return $this->render("Admin/Keys/index.html.twig", $arguments);
    }

    /**
     * @Route("/upload", methods={"POST"}, name="server_keys_upload")
     * @param Request $request
     * @param Keys    $keysService
     *
     * @return JsonResponse
     */
    public function uploadServerKeys(Request $request, Keys $keysService): JsonResponse
    {
        $privateKey = file_get_contents($request->files->get('privateKey'));
        $publicKey = file_get_contents($request->files->get('publicKey'));

        if ($keysService->checkKeyFileFormat($privateKey) && $keysService->checkKeyFileFormat($publicKey)) {
            $formattedPrivateKey = $keysService->prepareKeyFormat($privateKey);
            $formattedPublicKey = $keysService->prepareKeyFormat($publicKey);
            if (!is_null($formattedPrivateKey) && !is_null($formattedPublicKey)) {
                if (json_decode($this->springPost("admin/keys/validate/format",
                    [
                        'form_params' => [
                            'privateKey' => $formattedPrivateKey,
                            'publicKey' => $formattedPublicKey
                        ]
                    ]
                ), true)['response']) {
                    file_put_contents(Keys::SERVER_KEYS_LOCATION . "/" . Keys::SERVER_PRIVATE_KEY_NAME, $privateKey);
                    file_put_contents(Keys::SERVER_KEYS_LOCATION . "/" . Keys::SERVER_PUBLIC_KEY_NAME, $publicKey);

                    return $this->renderSuccessJson();
                }
            }
        }

        return $this->renderFailureJson();
    }

    /**
     * @Route("/private/download", methods={"GET"}, name="private_key_download")
     * @param Request $request
     * @param Keys    $keysService
     *
     * @return BinaryFileResponse
     */
    public function downloadPrivateKey(Request $request, Keys $keysService): BinaryFileResponse
    {
        return new BinaryFileResponse(Keys::SERVER_KEYS_LOCATION . "/" . Keys::SERVER_PRIVATE_KEY_NAME);
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
}