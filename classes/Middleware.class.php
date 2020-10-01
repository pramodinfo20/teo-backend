<?php

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\GuzzleException;
use \GuzzleHttp\Exception\RequestException;
use \GuzzleHttp\Psr7\Uri;
use \GuzzleHttp\Cookie\CookieJar;

/**
 * Created by PhpStorm.
 * User: fev
 * Date: 3/29/19
 * Time: 11:14 AM
 */
class Middleware {
    const REQUEST_TYPE_GET = 'GET';
    const REQUEST_TYPE_POST = 'POST';
    const REQUEST_TYPE_POST_FILES = 'POST_FILES';
    const REQUEST_TYPE_PUT = 'PUT';
    const REQUEST_TYPE_PATCH = 'PATCH';
    const REQUEST_TYPE_DELETE = 'DELETE';

    const HEADER_LEGACY_APPLICATION_LANGUAGE = 'Legacy-Application-Language';
    const HEADER_LEGACY_APPLICATION_URL = 'Legacy-Application-Url';
    const HEADER_LEGACY_PREVIOUS_URL_PARAMETER = 'Legacy-Application-Previous-Action-Url';

    const DEFAULT_MIDDLEWARE_ERROR = 'Middleware-Error';

    const LANGUAGE_DE = 'de';
    const LANGUAGE_EN = 'en';

    const DEFAULT_LANGUAGE = self::LANGUAGE_DE;

    /**
     * @var Client
     */
    private $api;

    private $method;
    private $action;
    private $options = [];
    private $language;

    private $unnecessaryParameters = [
        'action',
        'method',
        'eingeloggt',
    ];

    /**
     * @var array
     */
    private $languages;

    /**
     * Middleware constructor.
     *
     * @param string $url
     * @param string $language
     *
     * @throws MiddlewareException
     */
    public function __construct(string $url, string $language = self::DEFAULT_LANGUAGE) {
        $this->languages = [
            self::LANGUAGE_DE,
            self::LANGUAGE_EN
        ];

        $this->api = new Client(['base_uri' => $url]);

        if (!in_array($language, $this->languages)) {
            throw new MiddlewareException(
                'Middleware-Error-MissingLanguage',
                500
            );
        }

        $this->language = $language;
    }

    /**
     * @param $method
     * @param $action
     * @param $options
     * @return Middleware
     */
    public function prepare($method, $action, array $options = []): self {
        $this->setMethod($method);
        $this->setAction($action);

        if (is_array($options)) {
            $this->setOptions($options);
        }

        return $this;
    }

    /**
     * @return string
     *
     * @throws MiddlewareException
     */
    public function sentRequest(): string {
        session_abort();
        try {
            if ($this->method == self::REQUEST_TYPE_POST_FILES) {
                $response = $this->api->request(self::REQUEST_TYPE_POST, $this->action, $this->options);
                session_start();
                return (string) $response->getBody()->getContents();
            } else {
                $response = $this->api->request($this->method, $this->action, $this->options);

                session_start();
                return (string) $response->getBody();
            }

        } catch (RequestException $requestException) {
            if ($requestException->hasResponse()) {
                throw new MiddlewareException(
                    self::DEFAULT_MIDDLEWARE_ERROR,
                    $requestException->getCode(),
                    $requestException,
                    $requestException->getResponse()
                );
            }
            session_start();

            return (string) $this->createExceptionWithoutQuery($requestException);
        } catch (GuzzleException $exception) {
            session_start();

            return (string) $exception;
        }
    }

    /**
     * @param       $method
     * @param       $action
     * @param array $options
     *
     * @return string
     *
     * @throws MiddlewareException
     * @deprecated
     */
    public function makeRequest($method, $action, $options = []): string {
        @trigger_error(sprintf('Using the "makeRequest" method is deprecated, use "prepare" and "sentRequest" instead.'), E_USER_DEPRECATED);

        return $this->prepare($method, $action, $options)->sentRequest();
    }

    /**
     * @param $method
     */
    private function setMethod($method): void {
        if (!in_array($method, [
            self::REQUEST_TYPE_GET,
            self::REQUEST_TYPE_POST,
            self::REQUEST_TYPE_POST_FILES,
            self::REQUEST_TYPE_PUT,
            self::REQUEST_TYPE_PATCH,
            self::REQUEST_TYPE_DELETE])) {
            throw new RuntimeException('Invalid Method');
        }

        $this->method = $method;
    }

    /**
     * @param $action
     */
    private function setAction($action): void {
        $this->action = $action;
    }

    /**
     * @param $options
     *   $options = [
     *      'unnecessaryParameters' => (array) List of parameter names to remove from $_REQUEST array. Optional
     *      'headers' => (array) Headers. Optional
     *      'deleteQuery' => (array) List of identifiers to remove, works only with DELETE method
     *      'auth' => (array) Credentials for authorization
     *      'multipart' => (array) List of files and it's POST representation
     *    ]
     */
    private function setOptions($options): void {
        $options['headers'] = [
            self::HEADER_LEGACY_APPLICATION_LANGUAGE => $this->language,
            self::HEADER_LEGACY_APPLICATION_URL => $this->getCurrentUrl(),
            self::HEADER_LEGACY_PREVIOUS_URL_PARAMETER => $this->getPreviousActionUrl()
        ];

        $options['cookies'] = CookieJar::fromArray(
            ['PHPSESSID' => $_COOKIE['PHPSESSID']], 'sts.localhost'
        );

        if (isset($options['auth'])) {
            $options['auth'] = [
                $options['auth'][0], $options['auth'][1]
            ];
        }

        if (isset($options['unnecessaryParameters']) && is_array($options['unnecessaryParameters'])) {
            $this->setUnnecessaryParameters($options['unnecessaryParameters']);
        }

        switch ($this->method) {
            case self::REQUEST_TYPE_GET:
                $options['query'] = $this->removeUnnecessaryParams($_GET);
                break;
            case self::REQUEST_TYPE_POST:
            case self::REQUEST_TYPE_PUT:
            case self::REQUEST_TYPE_PATCH:
                $options['form_params'] = $this->removeUnnecessaryParams($_POST);
                $options['headers'] += [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ];
                break;
            case self::REQUEST_TYPE_POST_FILES:
                $options['multipart'] = $this->addPostParameters($_POST);
                $options['multipart'] += $this->addFiles($_FILES);
                break;
            case self::REQUEST_TYPE_DELETE:
                $options['query'] = $this->options['deleteQuery'];
                break;
        }

        $this->options = $options;
    }

    /**
     * @param $unnecessaryParameters
     */
    private function setUnnecessaryParameters($unnecessaryParameters): void {
        if (count(array_filter($unnecessaryParameters, 'is_array')) == 0) {
            $this->unnecessaryParameters = array_merge($this->unnecessaryParameters, $unnecessaryParameters);
        }
    }

    /**
     * @param $parameters
     * @return mixed
     */
    private function removeUnnecessaryParams($parameters): array {
        foreach ($parameters as $key => $parameter) {
            $newKey = $key;
              if(isSet($oldKey))
                  if ($newKey == $oldKey)
                      unset($parameters[$newKey]);

            if (in_array($key, $this->unnecessaryParameters))
                unset($parameters[$key]);

            $oldKey = $newKey;
        }
        /* Only unique parameters */
        return $parameters;
    }

    /**
     * @return string
     */
    private function getCurrentUrl(): string {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * @param RequestException $exception
     * @return RequestException
     */
    private function createExceptionWithoutQuery(RequestException $exception): RequestException {
        $url = $exception->getRequest()->getUri()->withQuery('');
        $uri = new Uri($url);

        return RequestException::create(
            $exception->getRequest()->withUri($uri),
            $exception->getResponse()
        );
    }

    /**
     * @return string
     */
    private function getPreviousActionUrl(): string {
        return $this->removeUrlQuery($this->getCurrentUrl(), 'method');
    }

    /**
     * @param $url
     * @param $key
     * @return string
     */
    public function removeUrlQuery($url, $key): string {
        $url = preg_replace('/(?:&|(\?))' . $key . '=[^&]*(?(1)&|)?/i', "$1", $url);
        $url = rtrim($url, '?');
        $url = rtrim($url, '&');

        return $url;
    }

    /**
     * @param $files
     * @return array
     */
    private function addFiles($files): array {
        $filesArray = [];
        foreach ($files as $key => $file) {
            $filesArray[] = [
                'name' => $key,
                'contents' => fopen($file['tmp_name'], 'rb'),
                'filename' => basename($file['tmp'])
            ];
        }

        return $filesArray;
    }

    /**
     * @param $parameters
     * @return array
     */
    private function addPostParameters($parameters) : array {
        $params = $this->removeUnnecessaryParams($parameters);
        $arrayParameters = [];
        foreach ($params as $key=>$value) {
            $arrayParameters[] = [
                'name' => $key,
                'contents' => $value
            ];
        }

        return $arrayParameters;
    }
}