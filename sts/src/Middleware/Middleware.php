<?php

namespace App\Middleware;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Uri;
use http\Exception\RuntimeException;


class Middleware
{
    const REQUEST_TYPE_GET = 'GET';
    const REQUEST_TYPE_POST = 'POST';
    const REQUEST_TYPE_POST_FILES = 'POST_FILES';
    const REQUEST_TYPE_PUT = 'PUT';
    const REQUEST_TYPE_PATCH = 'PATCH';
    const REQUEST_TYPE_DELETE = 'DELETE';

    const DEFAULT_MIDDLEWARE_ERROR = 'Middleware-Error';

    /**
     * @var Client
     */
    private $api;

    private $method;
    private $action;
    private $options = [];

    private $unnecessaryParameters = [];


    /**
     * Middleware constructor.
     *
     * @param $url
     */
    public function __construct($url)
    {
        $this->api = new Client(['base_uri' => $url]);
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
    public function makeRequest($method, $action, $options = []): string
    {
        @trigger_error(sprintf('Using the "makeRequest" method is deprecated, use "prepare" and "sentRequest" instead.'), E_USER_DEPRECATED);

        return $this->prepare($method, $action, $options)->sentRequest();
    }

    /**
     * @return string
     *
     * @throws MiddlewareException
     */
    public function sentRequest(): string
    {
        try {
            if ($this->method == self::REQUEST_TYPE_POST_FILES) {
                $response = $this->api->request(self::REQUEST_TYPE_POST, $this->action, $this->options);
                return (string)$response->getBody()->getContents();
            } else {
                $response = $this->api->request($this->method, $this->action, $this->options);
                return (string)$response->getBody();
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

            return (string)$this->createExceptionWithoutQuery($requestException);
        } catch (GuzzleException $exception) {
            return (string)$exception;
        }
    }

    /**
     * @param RequestException $exception
     *
     * @return RequestException
     */
    private function createExceptionWithoutQuery(RequestException $exception): RequestException
    {
        $url = $exception->getRequest()->getUri()->withQuery('');
        $uri = new Uri($url);

        return RequestException::create(
            $exception->getRequest()->withUri($uri),
            $exception->getResponse()
        );
    }

    /**
     * @param $method
     * @param $action
     * @param $options
     *
     * @return Middleware
     */
    public function prepare($method, $action, array $options = []): self
    {
        $this->setMethod($method);
        $this->setAction($action);

        if (is_array($options)) {
            $this->setOptions($options);
        }

        return $this;
    }

    /**
     * @param $method
     */
    private function setMethod($method): void
    {
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
    private function setAction($action): void
    {
        $this->action = $action;
    }

    /**
     * @param $options
     *      $options = [
     *      'unnecessaryParameters' => (array) List of parameter names to remove from $_REQUEST array. Optional
     *      'headers' => (array) Headers. Optional
     *      'deleteQuery' => (array) List of identifiers to remove, works only with DELETE method
     *      ]
     */
    private function setOptions($options): void
    {
        $options['headers'] = [

        ];

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
                if (!isset($options['form_params'])) {
                    $options['form_params'] = $this->removeUnnecessaryParams($_POST);
                }
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
    private function setUnnecessaryParameters($unnecessaryParameters): void
    {
        if (count(array_filter($unnecessaryParameters, 'is_array')) == 0) {
            $this->unnecessaryParameters = array_merge($this->unnecessaryParameters, $unnecessaryParameters);
        }
    }

    /**
     * @param $parameters
     *
     * @return mixed
     */
    private function removeUnnecessaryParams($parameters): array
    {
        foreach ($parameters as $key => $parameter) {
            if (in_array($key, $this->unnecessaryParameters))
                unset($parameters[$key]);
        }

        /* Only unique parameters */
        $parameters = array_unique($parameters);

        return $parameters;
    }

    /**
     * @param $parameters
     *
     * @return array
     */
    private function addPostParameters($parameters): array
    {
        $params = $this->removeUnnecessaryParams($parameters);
        $arrayParameters = [];
        foreach ($params as $key => $value) {
            $arrayParameters[] = [
                'name' => $key,
                'contents' => $value
            ];
        }

        return $arrayParameters;
    }

    /**
     * @param $files
     *
     * @return array
     */
    private function addFiles($files): array
    {
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
     * @param $url
     * @param $key
     *
     * @return string
     */
    public function removeUrlQuery($url, $key): string
    {
        $url = preg_replace('/(?:&|(\?))' . $key . '=[^&]*(?(1)&|)?/i', "$1", $url);
        $url = rtrim($url, '?');
        $url = rtrim($url, '&');

        return $url;
    }
}