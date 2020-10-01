<?php

namespace App\Controller;

session_start();

use App\Middleware\Middleware;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class LegacyBaseController extends AbstractController
{
    use HistoryValidationErrorTrait;

    const RETURN_PARAMETER_STATUS = 'status';
    const RETURN_PARAMETER_REDIRECT = 'redirect';
    const RETURN_PARAMETER_ERRORS = 'errors';

    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';

    const LEGACY_CURRENT_URL_PARAMETER = 'currentLegacyUrl';
    const LEGACY_PREVIOUS_URL_PARAMETER = 'previousLegacyUrl';

    const REGEX_EXPLODE_COLLECTION_PATH = "/^(data.)|(.data)|(\\])|(\\[)|children/";

    /**
     * @var string
     */
    private $urlToLegacyApplication;

    /**
     * @var
     */
    private $urlToPreviousActionLegacyApplication;

    /**
     * Legacy method to redirect current URL
     *
     * @param string $route
     * @param array  $parameters
     * @param int    $status
     *
     * @return JsonResponse|RedirectResponse
     */
    public function redirectToLegacyUrl(string $route, array $parameters = [], int $status = 302)
    {
        if ($this->urlToPreviousActionLegacyApplication) {
            return $this->json([
                self::RETURN_PARAMETER_STATUS => $status,
                self::RETURN_PARAMETER_REDIRECT => $this->urlToPreviousActionLegacyApplication
            ]);
        }

        return parent::redirectToRoute($route, $parameters, $status);
    }

    /**
     * @param $urlToLegacyApplication
     */
    public function setUrlToLegacyApplication($urlToLegacyApplication): void
    {
        $this->urlToLegacyApplication = $urlToLegacyApplication;
    }

    /**
     * @param $urlToPreviousActionLegacyApplication
     */
    public function setUrlToPreviousActionLegacyApplication($urlToPreviousActionLegacyApplication): void
    {
        $this->urlToPreviousActionLegacyApplication = $urlToPreviousActionLegacyApplication;
    }

    /**
     * Shortcut for Doctrine
     *
     * @param string $manager
     *
     * @return ObjectManager
     */
    public function getManager($manager = null): ObjectManager
    {
        return $this->getDoctrine()->getManager($manager);
    }

    /**
     * Attach extra parameters without forcing developers
     * to include it in every needed actions
     *
     * @param string        $view
     * @param array         $parameters
     * @param Response|null $response
     *
     * @return Response
     */
    public function render(string $view, array $parameters = [], Response $response = null): Response
    {
        $parameters = array_merge($parameters, [
            self::LEGACY_CURRENT_URL_PARAMETER => $this->urlToLegacyApplication,
            self::LEGACY_PREVIOUS_URL_PARAMETER => $this->urlToPreviousActionLegacyApplication
        ]);

        return parent::render($view, $parameters, $response);
    }

    /**
     * Prepare JSON with form errors to add some information why save action fails
     *
     * @param FormInterface $form
     *
     * @return JsonResponse
     */
    protected function renderFormErrors(FormInterface $form): JsonResponse
    {
        $errors = [];

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                foreach ($child->getErrors(true) as $error) {
                    /* Special treatment for CollectionType, we need to get full propertyPath */
                    if ($child->getConfig()->getType()->getInnerType() instanceof CollectionType) {
                        $propertyPath = $error->getCause()->getPropertyPath();
                        $path = preg_replace(self::REGEX_EXPLODE_COLLECTION_PATH, '', $propertyPath);

                        list($collection, $index, $field) = explode('.', $path);

                        $errors[(string)$form->getPropertyPath()][$collection][$index][$field] = $error->getMessage();
                    } else {
                        $errors[$error->getOrigin()->getName()] = $error->getMessage();
                    }
                }
            }
        }
        $counter = 0;
        foreach ($form->getErrors() as $error) {
            $errors['Error' . (++$counter)] = $error->getMessage();
        }

        /* Remove broken entry */
        $this->resetBrokenHistory();

        return $this->json([
            self::RETURN_PARAMETER_STATUS => Response::HTTP_BAD_REQUEST,
            self::RETURN_PARAMETER_ERRORS => $errors
        ]);
    }

    /**
     * Render JSON with success, it's link for clean code
     *
     * @param array|null $additional
     *
     * @return JsonResponse
     */
    protected function renderSuccessJson(array $additional = null): JsonResponse
    {
        $parameters = [
            self::RETURN_PARAMETER_STATUS => self::STATUS_SUCCESS
        ];

        if ($additional) {
            $parameters = array_merge($parameters, $additional);
        }

        return $this->json($parameters);
    }

    /**
     * Render JSON with failure, it's link for clean code
     *
     * @param array|null $additional
     *
     * @return JsonResponse
     */
    protected function renderFailureJson(array $additional = null): JsonResponse
    {
        $parameters = [
            self::RETURN_PARAMETER_STATUS => self::STATUS_FAILURE
        ];

        if ($additional) {
            $parameters = array_merge($parameters, $additional);
        }

        return $this->json($parameters);
    }

    /**
     * Render Spring Middleware
     *
     * @return Middleware
     */
    protected function getSpringMiddleware(): Middleware
    {
        $url = 'http://localhost:8080/';

        return new Middleware($url);
    }
}