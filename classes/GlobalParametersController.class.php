<?php
/**
 * GlobalParametersController.class.php
 * @author Grzegorz Stolarz, FEV
 */
$GLOBALS['search_hide_fieldset'] = true;

class GlobalParametersController extends PageController {
    const METHOD_LIST = 'list';
    const METHOD_NEW = 'new';
    const METHOD_EDIT = 'edit';
    const METHOD_DELETE = 'delete';
    const METHOD_SAVENEW = 'saveNew';
    const METHOD_SAVEEDITED = 'saveEdited';

    public $oQueryHolder;
    protected $translate;
    private $view;

    private static $allowedActions = [
        self::METHOD_LIST => ['method' => 'listGlobalParameters', 'printContent' => true],
        self::METHOD_NEW => ['method' => 'newGlobalParameter', 'printContent' => true],
        self::METHOD_EDIT => ['method' => 'editGlobalParameter', 'printContent' => true],
        self::METHOD_DELETE => ['method' => 'deleteGlobalParameter', 'printContent' => true],
        self::METHOD_SAVENEW => ['method' => 'saveNewGlobalParameter', 'printContent' => false],
        self::METHOD_SAVEEDITED => ['method' => 'saveEditedGlobalParameter', 'printContent' => false]
    ];
    private $types;
    private $units;
    private $msgs;

    public function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        parent::__construct();

        $this->translate = parent::getTranslationsForDomain();

        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->diagnosePtr = $this->ladeLeitWartePtr->getDiagnoseObject();
        $this->displayFooter = $container->getDisplayFooter();
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->user = $user;

        $config_leitwarte = $GLOBALS['config']->get_db('leitwarte');
        $databasePtr = new DatabasePgsql (
            $config_leitwarte['host'],
            $config_leitwarte['port'],
            $config_leitwarte['db'],
            $config_leitwarte['user'],
            $config_leitwarte['password'],
            new DatabaseStructureCommon1()
        );
        $this->oDbPtr = $databasePtr;
        $this->oQueryHolder = new NewQueryPgsql($this->oDbPtr);

        $this->displayHeader = $this->container->getDisplayHeader();
        $this->displayHeader->enqueueJs("jquery-tabledit", "js/jquery.tabledit.js");
        $this->displayHeader->setTitle("StreetScooter Cloud Systems : " . $this->user->getUserRoleLabel());

        $types = $this->oDbPtr->newQuery('parameter_value_types')
            ->where('parameter_value_types_id', '!=', 5)
            ->orderBy('parameter_value_types_id')
            ->get("parameter_value_types_id, value_types");

        foreach ($types as $type)
            $this->types[$type['parameter_value_types_id']] = substr($type['value_types'], 6);

        $units = $this->oDbPtr->newQuery('units')->orderBy('unit_id')->get("unit_id, name");

        foreach ($units as $unit)
            $this->units[$unit['unit_id']] = $unit['name'];

        $method = (isset($_REQUEST['method']) && $_REQUEST['method']) ? $_REQUEST['method'] : self::METHOD_LIST;

        if (!array_key_exists($method, self::$allowedActions))
            throw new RuntimeException(sprintf('Invalid method call, only %s, %s and %s are allowed', self::METHOD_LIST, self::METHOD_NEW, self::METHOD_EDIT));

        if (is_callable(array($this, self::$allowedActions[$method]['method']))) {
            /* Always run default action which is list GP's if it's not ajax call */
            if ($method != self::METHOD_LIST && !self::$allowedActions[$method]['printContent'])
                $this->listGlobalParameters();

            try {
                $this->{self::$allowedActions[$method]['method']}();
            } catch (Exception $e) {
                $this->msgs[] = $e->getMessage();
            }
        }

        if (self::$allowedActions[$method]['printContent']) {
            $this->printContent();
        }
    }

    private function listGlobalParameters() {
        $this->view = $this->getMiddleware()
            ->prepare(Middleware::REQUEST_TYPE_GET, 'parameters/global')
            ->sentRequest();
    }

    //todo: FIX USER_ID, SESSION MECHANISM
    private function newGlobalParameter() {
        if ($_SERVER['REQUEST_METHOD'] == Middleware::REQUEST_TYPE_GET) {
            $this->view = $this->getMiddleware()
                ->prepare(Middleware::REQUEST_TYPE_GET, 'parameters/global/new/'.$_SESSION['sts_userid'])
                ->sentRequest();
        } elseif ($_SERVER['REQUEST_METHOD'] == Middleware::REQUEST_TYPE_POST) {
            $response = $this->getMiddleware()
                ->prepare(Middleware::REQUEST_TYPE_POST, 'parameters/global/new/'.$_SESSION['sts_userid'])
                ->sentRequest();

            $json = json_decode($response);

            if ($json->status == 302) {
                header('Location: ' . $this->getMiddleware()->removeUrlQuery($json->redirect, 'globalParameter'));
                exit;
            }
        }
    }

    private function editGlobalParameter() {
        $globalParameterId = (isset($_GET['globalParameter']) && $_GET['globalParameter'])
            ? intval($_GET['globalParameter'])
            : 0;

        if ($_SERVER['REQUEST_METHOD'] == Middleware::REQUEST_TYPE_GET) {
            $this->view = $this->getMiddleware()
                ->prepare(Middleware::REQUEST_TYPE_GET, "parameters/global/{$globalParameterId}/edit")
                ->sentRequest();

        } elseif ($_SERVER['REQUEST_METHOD'] == Middleware::REQUEST_TYPE_POST) {
            $response = $this->getMiddleware()
                ->prepare(Middleware::REQUEST_TYPE_POST, "parameters/global/{$globalParameterId}/edit")
                ->sentRequest();

            $json = json_decode($response);

            if ($json->status == 302) {
                header('Location: ' . $this->getMiddleware()->removeUrlQuery($json->redirect, 'globalParameter'));
                exit;
            }
        }
    }

    private function saveEditedGlobalParameter() {
        $globalParameterId = (isset($_GET['globalParameter']) && $_GET['globalParameter'])
            ? intval($_GET['globalParameter'])
            : 0;

        $response = $this->getMiddleware()
            ->prepare(Middleware::REQUEST_TYPE_POST, "parameters/global/{$globalParameterId}/edit/save")
            ->sentRequest();

        echo $response;
    }

    private function saveNewGlobalParameter() {
        $response = $this->getMiddleware()
            ->prepare(Middleware::REQUEST_TYPE_POST, 'parameters/global/new/'.$_SESSION['sts_userid'].'/save')
            ->sentRequest();

        echo $response;
    }

    public function deleteGlobalParameter() {
        $globalParameterId = (isset($_GET['globalParameter']) && $_GET['globalParameter'])
            ? intval($_GET['globalParameter'])
            : 0;

        $response = $this->getMiddleware()
            ->prepare(Middleware::REQUEST_TYPE_DELETE, "parameters/global/{$globalParameterId}")
            ->sentRequest();

        $json = json_decode($response);

        if ($json->status == 302) {
            header('Location: ' . $this->getMiddleware()->removeUrlQuery($json->redirect, 'globalParameter'));
            exit;
        }
    }

    public function printContent() {
        $this->displayHeader->printContent();

        include("pages/globalParameters.php");

        $this->displayFooter->printContent();
    }
}