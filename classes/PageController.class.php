<?php
/**
 * pagecontroller.class.php
 * The main class..
 * @author Pradeep Mohan
 */
require_once __DIR__."/../translations/LegacyTranslations.php";
/**
 * PageController Class, the main class
 */
class PageController extends ControllerBase {
    protected $CAction;
    protected $translations;
    public $oDbPtr;
    protected $languageCookieName;
    protected $selectedLanguage;

    function __construct() {
        $this->CAction = null;
        $this->requestPtr = new Request ();
        $this->root = $_SERVER['STS_ROOT'];
        $this->languageCookieName = $GLOBALS['config']->get_property('languageCookieName', 'selectedSystemLanguage');

        $this->translations = new LegacyTranslations();
    }


    /**
     *
     * To be used as logging utility
     *
     * @param $user - who is responsible for action
     * @param $context - for example action like manageFunctions
     * @param $description - what has been added/changed/removed
     * @return state of query
     */
    protected function logChange($user, $context, $description) {
        $userid = $user->getUserId();
        $q = "INSERT INTO public.change_log_history (user_id, context, description) VALUES ($userid, '$context', '$description')";
        return $this->oQueryHolder->query($q);
    }

    protected function executeSearch() {
//    echo $this->displayHeader;

        $o = new SearchController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
        if (isset($_GET['method'])) {
            $o->printpdf();
        } else {
            $o->initFullPageView();
            $o->printContent();
        }
//    exit;
    }


    //protected function executeHrUpload()
    //{
    //  new UploadController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    //  include_once("pages/zentrale.php");
    //}

    protected function executeUploadHrList()
    {
        new UploadHrListController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeUploadHrListHistory()
    {
        new UploadHrListHistoryController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeUserKeys() {
        new UserKeysController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeAdminHistory() {
        new AdminHistoryController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeDiagnosticSearch() {

        $oAjax = new AjaxController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
        $oAjax->Run(true);

    }

    protected function executeDiagnosticReports() {
        new DiagnosticReportsController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeResponsiblePersons() {
        new ResponsiblePersonsSymfonyController($this->ladeLeitWartePtr, $this->container, $this->requestPtr);
    }

    protected function executeResponsiblePersons2() {
        new ResponsiblePersonsController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeUserAdministration() {
        new UserAdministrationSymfonyController($this->ladeLeitWartePtr, $this->container, $this->requestPtr);
    }

    protected function executeUpload2() {
        new Upload2Controller($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeUserRole2Company() {
        new UserRole2CompanyController($this->ladeLeitWartePtr, $this->container, $this->requestPtr,
            $this->user);
    }

    protected function executeUserRole2Company_DEPRECATED() {
        new UserRole2CompanyController_DEPRECATED($this->ladeLeitWartePtr, $this->container, $this->requestPtr,
            $this->user);
    }

    protected function executeUserRole2Functionality() {
        new UserRole2FunctionalityController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeManageFunctions() {
        new ManagementFunctionController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeVehicleConfigurations() {
        new VehicleConfigurationsController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeAjaxRows() {
        $oAjax = new AjaxController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
        $oAjax->Run();
    }

    protected function executeEcuParameterManagement() {
        $this->action = 'ecuParMan';
        $this->UAction = 'ecuParMan';
        new EcuSwParameterManagementController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    //Symfony parameter management
    protected function executeParameterManagement() {
        new EcuParameterManagementController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeEcuSwConfiguration() {
        new EcuSwConfigurationController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeConversionTable() {
        new ConversionTableController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeEcuDiagnosticValueSetting() {
        new EcuSwDiagnosticValueSettingController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeEcuDiagnosticSwParameterManagement() {
        new DiagnosticSwParameterManagementController($this->ladeLeitWartePtr, $this->container, $this->requestPtr);
    }

    protected function executePropertySettings() {
        new EcuSwPropertySettingsController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeParameterSettings() {
        new EcuParameterSettingsController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeGlobalParameters() {
        new GlobalParametersController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeCoCParameters() {
        new CoCParametersController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeGlobalValuesSets() {
        new GlobalValuesSetsController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeCoCValuesSets() {
        new CoCValuesSetsController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeAddEcu() {
        new EcuAddController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeTranslations() {
        new TranslationsController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeAjax() {
        $oAjax = new AjaxController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
        //$oAjax->Run();
        if (isset($_GET['method'])) {
            $method = $_GET['method'];

            $oAjax->$method();
        }
    }

    protected function executeUploadServerKeys() {
        new UploadServerKeysController($this->ladeLeitWartePtr, $this->container, $this->requestPtr);
    }

    protected function executeDownloadFromDB() {
        new DownloadFromDB($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    public function createModalSearch() {

        $o = new SearchController($this->ladeLeitWartePtr, $this->container, null, null);
        $o->printContent();
    }

    protected function executeExportPdf() {
        new ExportPdfController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeCoCGenerationEngg() {
        new CoCGenerationControllerEngg($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }

    protected function executeCoCGenerationPPS() {
        new CoCGenerationControllerPPS($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
    }
    protected function executeEcuPropertiesManagement() {
        new EcuPropertiesManagementController($this->ladeLeitWartePtr, $this->container, $this->requestPtr,
            $this->user);
    }

    protected function executeHistory() {
        new HistoryController($this->ladeLeitWartePtr, $this->container, $this->requestPtr);
    }

    function Run() {
        /**
         * Get the current domain to decide which database to use.
         */
        $page = $this->requestPtr->getProperty('page');
        $domain = null;
        if ($domain === null && isset($_SERVER['HTTP_HOST'])) {
            $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
        }

        // URL Filter
        $preg_words = array('script','alert','<','>','xss','php/');

        foreach($preg_words as $preg_word) {
            if(stristr($_SERVER["REQUEST_URI"], $preg_word) !== false) {
                exit(header("Location:". $_SERVER['HTTP_HOST'] . "/error.php"));
            }
        }

//var_dump($page);

        if (!isset($GLOBALS['debug']))
            $GLOBALS['debug'] = ['debugout' => 0, 'sql' => ['level' => 0]];

        if ($GLOBALS['config']) {
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

            $config_diagnose = $GLOBALS['config']->get_db('diagnose');

            if (!empty($config_diagnose['db']) && ($config_diagnose['db'] != 'none')) {
                $diagnoseDatabasePtr = new DatabasePgsql (
                    $config_diagnose['host'],
                    $config_diagnose['port'],
                    $config_diagnose['db'],
                    $config_diagnose['user'],
                    $config_diagnose['password'],
                    new DatabaseStructureDiagnose()
                );
            };
        }

        $dataSrcPtr = new DbDataSrc ($databasePtr);
        $this->diagnosePtr = (isset($diagnoseDatabasePtr)) ? new DbDataSrc ($diagnoseDatabasePtr) : null;
        $this->ladeLeitWartePtr = new LadeLeitWarte ($dataSrcPtr, null, $this->diagnosePtr);


        $username = filter_var($this->requestPtr->getProperty('benutzerName'), FILTER_SANITIZE_SPECIAL_CHARS);

        $userpass = $this->requestPtr->getProperty('benutzerPwd');

        if ($username) {
            $user = new User ($username, $userpass, $this->ladeLeitWartePtr);
        } else {
            $user = new User ('', '', $this->ladeLeitWartePtr);
        }
        $this->userPtr = $user;

        if ($page == "logout") {
            $user->logout();
            $this->requestPtr->setProperty('benutzerName', '');
        }

        if (isset ($_REQUEST['ajaxcmd']))
            $this->Ajaxecute($_REQUEST['ajaxcmd'], $user);


        $this->container = new DisplayContainer($this->ladeLeitWartePtr, $user, $this);
        $displayheader = $this->container->getDisplayHeader();
        $displayfooter = $this->container->getDisplayFooter();
        $displayheader->login_form = new QuickformHelper ($displayheader, "login");

        //Custom styles
        $displayheader->enqueueStylesheet("custom-style", "css/custom-style.css");

        //if user is not logged in OR if the current page is logout
        if (!$user->loggedin() || $page == "logout") {
            if (isset($_GET['action'])) {
                $displayheader->printContent('sessionExpired');
            } else {
                $er_content = $user->getErrorMsgs();
                if ($user->getShowLoginForm())
                    $displayheader->login_form->addlogin($er_content);
                $displayheader->addele('errormsgs', $er_content);
                $displayheader->setClass('not-logged-in');
                $displayheader->setTitle("StreetScooter Cloud Systems");
                $displayheader->printContent();
                $displayfooter->printContent();
            }
        } //user is logged in
        else {
            $this->user = $user;
            $userinfo = array('username' => $user->getUserName(),
                'userfullname' => $user->getUserFullName(),
                'user_role_label' => $user->getUserRoleLabel(),
                'userrole' => $user->getUserRole(),
                'user_first_login' => $user->getFirstLogin(),
                'userid' => $user->getUserId(),
                'assigned_location' => $user->getAssignedLocation()
            );

            $displayheader->addele('userinfo', $userinfo);

            if ($user->getFirstLogin() !== false) {
                $displayheader->addele('keyfilename', $user->getKeyFileName());
            }
            $displayheader->setClass('logged-in');


            if (isset($_GET['action'])) {
                if ($_GET['action'] == 'search') {
//        $displayheader->printContent();
                    $this->executeSearch();
                    return;
                } elseif ($_GET['action'] == 'vehicleConfigurations') {
                    $this->executeVehicleConfigurations();
                    return;
                } elseif ($_GET['action'] == 'upload2') {
                    $this->executeUpload2();
                    return;
                } elseif ($_GET['action'] == 'ajaxRowsSearch') {
                    $this->executeAjaxRows();
                    return;
                } elseif ($_GET['action'] == 'ajax') {
                    $this->executeAjax();
                    return;
                } elseif ($_GET['action'] == 'manageFunctions') {
                    $this->executeManageFunctions();
                    return;
                } elseif ($_GET['action'] == 'userrole2Company') {
                    $this->executeUserRole2Company();
                    return;
                } elseif ($_GET['action'] == 'userrole2CompanyDEPRECATED') {
                    $this->executeUserRole2Company_DEPRECATED();
                    return;
                } elseif ($_GET['action'] == 'responsiblePersons') {
                    $this->executeResponsiblePersons();
                    return;
                } elseif ($_GET['action'] == 'responsiblePersons2') {
                    $this->executeResponsiblePersons2();
                    return;
                } elseif ($_GET['action'] == 'userrole2Functionality') {
                    $this->executeUserRole2Functionality();
                    return;
                } elseif ($_GET['action'] == 'adminHistory') {
                    $this->executeAdminHistory();
                    return;
                } elseif ($_GET['action'] == 'diagnosticReports') {
                    $this->executeDiagnosticReports();
                    return;
                } elseif ($_GET['action'] == 'diagnosticSearch') {
                    $this->executeDiagnosticSearch();
                    return;
                } elseif ($_GET['action'] == 'ecuSwConf') {
                    $this->executeEcuSwConfiguration();
                    return;
                } elseif ($_GET['action'] == 'ecuParMan') {
                    $this->userRole = ucfirst(strtolower($user->getUSerRole()));
                    $this->executeEcuParameterManagement();
                    return;
                } elseif ($_GET['action'] == 'serverKeys') {
                    $this->executeUploadServerKeys();
                    return;
                } elseif ($_GET['action'] == 'downloadFromDB') {
                    $this->executeDownloadFromDB();
                    return;
                } elseif ($_GET['action'] == 'conversionTable') {
                    $this->executeConversionTable();
                    return;
                    $this->executeSearch();
                    return;
                } elseif ($_GET['action'] == 'hrupload') {
                    $this->executeHrUpload();
                    return;
                } elseif ($_GET['action'] == 'upload2') {
                    $this->executeUpload2();
                    return;
                } elseif ($_GET['action'] == 'ajaxRowsSearch') {
                    $this->executeAjaxRows();
                    return;
                } elseif ($_GET['action'] == 'ajax') {
                    $this->executeAjax();
                    return;
                } elseif ($_GET['action'] == 'manageFunctions') {
                    $this->executeManageFunctions();
                    return;
                } elseif ($_GET['action'] == 'userrole2Company') {
                    $this->executeUserRole2Company();
                    return;
                } elseif ($_GET['action'] == 'responsiblePersons') {
                    $this->executeResponsiblePersons();
                    return;
                } elseif ($_GET['action'] == 'useradministration') {
                    $this->executeUserAdministration();
                    return;
                } elseif ($_GET['action'] == 'userrole2Functionality') {
                    $this->executeUserRole2Functionality();
                    return;
                } elseif ($_GET['action'] == 'adminHistory') {
                    $this->executeAdminHistory();
                    return;
                } elseif ($_GET['action'] == 'diagnosticReports') {
                    $this->executeDiagnosticReports();
                    return;
                } elseif ($_GET['action'] == 'diagnosticSearch') {
                    $this->executeDiagnosticSearch();
                    return;
                    $this->executeDiagnosticReports();
                    return;
                } elseif ($_GET['action'] == 'ecuSwConf') {
                    $this->executeEcuSwConfiguration();
                    return;
                } elseif ($_GET['action'] == 'ecuParMan') {
                    $this->userRole = ucfirst(strtolower($user->getUSerRole()));
                    $this->executeEcuParameterManagement();
                    return;
                } elseif ($_GET['action'] == 'serverKeys') {
                    $this->executeUploadServerKeys();
                    return;
                } elseif ($_GET['action'] == 'downloadFromDB') {
                    $this->executeDownloadFromDB();
                    return;
                } elseif ($_GET['action'] == 'conversionTable') {
                    $this->executeConversionTable();
                    return;
                } elseif ($_GET['action'] == 'globalParameters') {
                    $this->executeGlobalParameters();
                    return;
                } elseif ($_GET['action'] == 'cocParameters') {
                    $this->executeCoCParameters();
                    return;
                } elseif ($_GET['action'] == 'diagSwValSet') {
                    $this->executeEcuDiagnosticValueSetting();
                    return;
                }
                  elseif ($_GET['action'] == 'diagSwParamManagement') {
                    $this->executeEcuDiagnosticSwParameterManagement();
                    return;
                } elseif ($_GET['action'] == 'propertySettings') {
                    $this->executePropertySettings();
                    return;
                } elseif ($_GET['action'] == 'parameterSettings') {
                    $this->executeParameterSettings();
                    return;
                } elseif ($_GET['action'] == 'parameterManagement') {
                    $this->executeParameterManagement();
                    return;
                } elseif ($_GET['action'] == 'globalValuesSets') {
                    $this->executeGlobalValuesSets();
                    return;
                } elseif ($_GET['action'] == 'cocValuesSets') {
                    $this->executeCoCValuesSets();
                    return;
                } elseif ($_GET['action'] == 'userKeys') {
                    $this->executeUserKeys();
                    return;
                } elseif ($_GET['action'] == 'exportpdf') {
                    $this->executeExportPdf();
                    return;
                } elseif ($_GET['action'] == 'addEcu') {
                    $this->executeAddEcu();
                    return;
                } elseif ($_GET['action'] == 'cocGenerationEngg') {
                    $this->executeCoCGenerationEngg();
                    return;
                } elseif ($_GET['action'] == 'cocGenerationPPS') {
                    $this->executeCoCGenerationPPS();
                    return;
                } elseif ($_GET['action'] == 'propertiesManagement') {
                    $this->executeEcuPropertiesManagement();
                    return;
                } elseif ($_GET['action'] == 'uploadHr') {
                    $this->executeUploadHrList();
                    return;
                } elseif ($_GET['action'] == 'uploadHrHistory') {
                    $this->executeUploadHrListHistory();
                    return;
                } elseif ($_GET['action'] == 'history') {
                    $this->executeHistory();
                    return;
                }
            }

            $fnPrivacyInfo = $_SERVER['STS_ROOT'] . '/html/datenschutzerklaerung-intern.php';
            $reAcceptPrivacy = "";
            if (isset ($_SESSION['sts_privacy_accepted'])) {
                $filetimePrivacy = filemtime($fnPrivacyInfo);
                $timePrivacy = gmdate('Y-m-d G:i:s', $filetimePrivacy);
                $timeAccepted = $_SESSION['sts_privacy_accepted'];
                $reAcceptPrivacy = (strtotime($timeAccepted) < strtotime($timePrivacy));
                $datumPrivacy = date('d.m.Y', $filetimePrivacy);
            }

            if (empty ($_SESSION['sts_privacy_accepted']) || $reAcceptPrivacy) {
                include $fnPrivacyInfo;
                $displayfooter->printContent();
                return;
            }

//      r($page);

            switch ($page) {

                case 'profile':

                    $displayheader->setTitle("StreetScooter Cloud Systems : Profil Bearbeiten");
                    $displayheader->printContent();
                    include("./pages/profile.php");
                    break;

                case 'fileshare':
                    $displayheader->setTitle("StreetScooter Cloud Systems : Datein");
                    $displayheader->printContent();
                    include("./pages/fileshare.php");
                    break;

                case 'mitarbeiter':
                    $controller = new MitarbeiterController($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $user);
                    if (isset($_GET['method'])) {
                        $method = $_GET['method'];
                        $controller->$method();
                    } else {
                        $controller->Init();
                        $controller->Execute();
                    }
                    break;

                case 'anleitungen':
                    $displayheader->setTitle("StreetScooter Cloud Systems : Anleitungen");
                    $displayheader->printContent();
                    include("./pages/anleitungen.php");
                    break;
                    
                case 'feedback':
                    $displayheader->setTitle("StreetScooter Cloud Systems : feedback");
                    $displayheader->printContent();
                    include("./pages/feedback.php");
                    break;
                
                case 'success':
                    $displayheader->setTitle("StreetScooter Cloud Systems : success");
                    $displayheader->printContent();
                    include("./pages/success.php");
                    break;
                
                case 'useradministration':
                    $displayheader->setTitle("StreetScooter Cloud Systems : user administration");
                    $displayheader->printContent();
                    include("./pages/user_administration.php");
                    break;

                case 'commonajax':
                    $controller = new CommonajaxController ($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $user);
                    break;

                default:
                    $this->DisplayRoleSpecific($user->getUserRole());
                    break;
            } // end of switch $page

        } // end of if user is logged in

    }

    //=============================================================================================================
    function Ajaxecute($ajaxcmd, $user) {
        switch ($ajaxcmd) {
            case 'Privacy1-hide':
                $_SESSION['hide_msg']['privacy1'] = true;
                exit(0);

            case 'Cookies-deny':
                $user->logout(false);
                echo 'reload';
                exit(0);

            case 'Cookies-accept':
                $user->acceptCookies();
                echo 'reload';
                exit(0);

            case 'Privacy2-deny':
                $user->logout(false);
                echo 'reload';
                exit(0);

            case 'Privacy2-accept':
                $user->acceptPrivacy();
                echo 'reload';
                exit(0);
        }
    }

    //=============================================================================================================
    function DisplayRoleSpecific($role) {
        $displayheader = $this->container->getDisplayHeader();
        $displayfooter = $this->container->getDisplayFooter();

        $this->userRole = ucfirst(strtolower($role));
        $GLOBALS['role'] = $this->userRole;

        $actionset = explode(':', strtolower($this->requestPtr->getProperty('action')));
        if (count($actionset) > 1) {
            $counter = $actionset[1];
        }
        $this->action = $actionset[0];

        if ($this->action == "") $this->action = "home";
        $this->UAction = ucfirst($this->action);

        if ($this->userPtr->IsAdmin() && $this->action == 'info') {
            phpinfo();
            exit;
        }

        $actionFile = sprintf("%s/actions/%s/%s.php", $this->root, $this->userRole, $this->UAction);
// 	    if (! file_exists($actionFile))
// 	        $actionFile = sprintf( "%s/actions/%s/%s/Execute.php", $this->root, $this->userRole, $this->UAction);

        if (file_exists($actionFile)) {
            $ctrl = "ACtion_" . $this->UAction;

            $this->CAction = new $ctrl ($this);
            $this->CAction->Init();
            $this->CAction->Execute();
            $this->CAction->WriteHtmlPage($displayheader, $displayfooter);
        } else {
            $ctrl = $this->userRole . "Controller";
            $controller = new $ctrl ($this->ladeLeitWartePtr, $this->container, $this->requestPtr, $this->user);
            $controller->Init($this->action);
            $controller->Execute();
            $controller->PrintPage();

            $displayfooter->printContent();
        }
    }

    protected function getMiddleware(): Middleware {
        $url = $GLOBALS['config']->get_property('middlewareApiUrl', 'http://sts.localhost/');
        $languageCookieName = $GLOBALS['config']->get_property('languageCookieName', 'selectedSystemLanguage');

        if (isset($_COOKIE[$languageCookieName])) {
            $language = $_COOKIE[$languageCookieName];
        }
        else {
            $language = $GLOBALS['config']->get_property('language', 'de');
        }

        try {
            return new Middleware($url, $language);
        } catch (MiddlewareException $exception) {
            echo $exception->getMessage();
        }
    }

    protected function getSpringMiddleware() : Middleware {
        $url = $GLOBALS['config']->get_property('springMiddlewareApiUrl', 'http://localhost:8080/');
        $languageCookieName = $GLOBALS['config']->get_property('languageCookieName', 'selectedSystemLanguage');

        if (isset($_COOKIE[$languageCookieName])) {
            $language = $_COOKIE[$languageCookieName];
        }
        else {
            $language = $GLOBALS['config']->get_property('language', 'de');
        }

        try {
            return new Middleware($url, $language);
        } catch (MiddlewareException $exception) {
            echo $exception->getMessage();
        }
    }

    protected function parseJsonResponse(string $response = null) {
        $json = json_decode($response);

        if (json_last_error())
            return;

        /* TODO: Implement PHP 7.3 to detect invalid JSON: https://php.net/manual/en/class.jsonexception.php */
        $return = [];

        if ($json->status == 302) {
            $return['redirect'] = $json->redirect;
        } elseif ($json->status == 500) {
            $return['errors'] = $json->errors;
        }

        return $return;
    }

    protected function getTranslationsForDomain($domain = 'messages') {
        return $this->translations->getTranslationsForDomain($domain);
    }

    function ErrorMsgInputPost() {
        $posts = $_POST;
        foreach ($posts as $key => $val) {
            $posts[$key] = pg_escape_string($val);
            if(stripos($posts[$key], '<script') !== false || stripos($posts[$key], '<') || stripos($posts[$key], '"') !== false || strlen($posts[$key]) > 100) {
                include $_SERVER['HTTP_HOST'] . "/pages/error.php";
                die();
            }
        }
    }

}

?>