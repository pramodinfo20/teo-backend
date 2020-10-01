<?php

class SymfonyBaseController extends PageController {
    protected $ladeLeitWartePtr;
    protected $container;
    protected $requestPtr;
    protected $displayHeader;
    protected $translate;
    private $symfonyView;
    private $mainRoutingPath;
    private $parametersToAvoid;
    private $parametersWithoutKeys;
    private $scripts;
    private $stylesheets;
    private $role;

    /**
     * @var bool
     */
    private $hideLegacyOutput = false;
    use SpringBaseTrait;
    public function __construct($ladeLeitWartePtr, $container, $requestPtr, $mainRoutingPath, $css, $js, $role = 'engg') {
        parent::__construct();

        $this->translate = parent::getTranslationsForDomain();

        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->displayHeader = $this->container->getDisplayHeader();
        $this->displayFooter = $this->container->getDisplayFooter();
        $this->symfonyView = '';
        $this->mainRoutingPath = $mainRoutingPath;
        $this->parametersToAvoid = [];
        $this->parametersWithoutKeys = [];
        $this->stylesheets = $css;
        $this->scripts = $js;
        $this->role = $role;

        if (isset($_GET['method'])) {
            $name = $_GET['method'];
            $this->$name();
        } else if (isset($_GET['spring'])) {
            $name = $_GET['spring'];
            $this->$name();
        } else if (!isset($_GET['call']) && !isset($_GET['spring']) ) {
            $this->printContent();
        }

    }

    function setParametersToAvoid($parameters) {
        $this->parametersToAvoid = $parameters;
    }

    function setParametersWithoutKeys($parameters) {
        $this->parametersWithoutKeys = $parameters;
    }

    function printContent($routing = '') {
        $this->symfonyView = $this->prepareInitialView($routing);

        if (!$this->hideLegacyOutput) {
            $this->displayHeader->printContent();

            include $_SERVER['STS_ROOT'] . "/pages/menu/$this->role.menu.php";
        }

        foreach ($this->stylesheets as $style) {
            echo '<link rel="stylesheet" type="text/css" href="' . $style . '">';
        }

        foreach ($this->scripts as $script) {
            echo '<script type="text/javascript" src="' . $script . '"></script>';
        }

        echo $this->symfonyView;

        if (!$this->hideLegacyOutput) {
            $this->displayFooter->printContent();
        }
    }

    private function prepareInitialView($routing = '')
    {
        try {
            return $this->getMiddleware()
                ->prepare(Middleware::REQUEST_TYPE_GET, $this->mainRoutingPath . $routing)
                ->sentRequest();
        } catch (MiddlewareException $middlewareException) {
            $this->setHideLegacyOutput(true);

            echo $middlewareException->getResponse()->getBody()->getContents();
        }
    }

    protected function createPath($parameters) {
        $path = '';

        foreach ($parameters as $key => $value) {
            if (in_array($key, array_merge(['action', 'method', 'eingeloggt', 'admin', 'call', 'spring'], $this->parametersToAvoid))) {
                continue;
            } else if (in_array($key, array_merge(['ajax', 'path'], $this->parametersWithoutKeys))) {
                $path .= "/$value";
                continue;
            }
            $path .= "/$key/$value";
        }

        return $path;
    }

    /* ------------- Default Action ----------------- */
    private function regenerateView()
    {
        $this->printContent($this->createPath($_REQUEST));
    }
    /* ------------------------------------------- */

    /* ------------- Ajax - GET ----------------- */
    private function ajaxCall($options = [])
    {
        try {
            echo $this->getMiddleware()
                ->prepare(Middleware::REQUEST_TYPE_GET, $this->mainRoutingPath . $this->createPath($_REQUEST), $options)
                ->sentRequest();
        } catch (MiddlewareException $middlewareException) {
            $this->setHideLegacyOutput(true);

            echo $middlewareException->getResponse()->getBody()->getContents();
        }
    }
    /* ------------------------------------------- */

    /* ------------- Ajax - POST ----------------- */
    private function ajaxCallPost($options = [])
    {
        try {
            echo $this->getMiddleware()
                ->prepare(Middleware::REQUEST_TYPE_POST, $this->mainRoutingPath . $this->createPath($_GET), $options)
                ->sentRequest();
        } catch (MiddlewareException $middlewareException) {
            $this->setHideLegacyOutput(true);

            echo $middlewareException->getResponse()->getBody();
        }
    }
    /* ------------------------------------------- */

    /* ------------- Ajax - FILES ----------------- */
    private function ajaxCallFiles($options = [])
    {
        try {
            echo $this->getMiddleware()
                ->prepare(Middleware::REQUEST_TYPE_POST_FILES, $this->mainRoutingPath . $this->createPath($_GET), $options)
                ->sentRequest();
        } catch (MiddlewareException $middlewareException) {
            $this->setHideLegacyOutput(true);

            echo $middlewareException->getResponse()->getBody();
        }
    }
    /* ------------------------------------------- */

    /* ------------- Ajax - DELETE ----------------- */
    private function ajaxCallDelete($options = [])
    {
        try {
            echo $this->getMiddleware()
                ->prepare(Middleware::REQUEST_TYPE_DELETE, $this->mainRoutingPath . $this->createPath($_GET), $options)
                ->sentRequest();
        } catch (MiddlewareException $middlewareException) {
            $this->setHideLegacyOutput(true);

            echo $middlewareException->getResponse();
        }
    }
    /* ------------------------------------------- */

    public function setHideLegacyOutput(bool $hideLegacyOutput): void
    {
        $this->hideLegacyOutput = $hideLegacyOutput;
    }

    /* ----------------- Spring getters ----------*/
    protected function _getParametersToAvoid(){
        return $this->parametersToAvoid;
    }

    protected function _getParametersWithoutKeys() {
        return $this->parametersWithoutKeys;
    }

    protected function _getMainRoutingPath() {
        return $this->mainRoutingPath;
    }
    /*---------------------------------------------*/
}