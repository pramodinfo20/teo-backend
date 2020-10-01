<?php
/**
 * controllerbase.class.php
 * The main class..
 * @author Pradeep Mohan
 */

/**
 * PageController Class, the main class
 */
class ControllerBase {
    protected $requestPtr;

    /**
     * @var LadeLeitWarte
     */
    protected $ladeLeitWartePtr;
    protected $diagnosePtr;

    /**
     * @var DisplayContainer
     */
    protected $container;
    protected $displayHeader;
    protected $displayFooter;
    protected $user;
    protected $debug;
    protected $commonfunctions;
    public $userRole;
    public $root;


    /**
     * Konstruktor
     */
    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        $this->root = $_SERVER['STS_ROOT'];
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->container = $container;
        $this->requestPtr = isset ($requestPtr) ? $requestPtr : new Request ();
        $this->user = $user;

        $this->displayHeader = isset ($container) ? $this->container->getDisplayHeader() : null;
        $this->displayFooter = isset ($container) ? $this->container->getDisplayFooter() : null;

        $this->userRole = "";
    }

    //=============================================================================================================
    function GetObject($name) {
        switch (strtolower($name)) {
            case 'ladeleitwarte':
                return $this->ladeLeitWartePtr;

            case'diagnose':
                return $this->diagnosePtr;

            case 'user':
                return $this->user;

            case 'request':
                return $this->requestPtr;

            case 'displayheader':
            case 'header':
                return $this->displayHeader;

            case 'displayfooter':
            case 'footer':
                return $this->displayFooter;
        }

        $vars = get_object_vars($this->ladeLeitWartePtr);
        return $vars[$name . "Ptr"];
    }

    //=============================================================================================================
    function Init($action) {
        $this->action = $action;
    }

    //=============================================================================================================
    function Execute() {
    }

    //=============================================================================================================
    function PrintPage() {

    }

    //=============================================================================================================
    function printContent() //@todo Remove the inherited printContent functions here .. maybe except for the chrginfra role?
    {
//    var_dump(debug_backtrace());
        include("pages/" . $this->user->getUserRole() . ".php");
    }
}

?>