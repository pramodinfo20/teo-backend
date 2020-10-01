<?php
/**
 * Class for the container
 */

class DisplayContainer extends DisplayHTML {
    /**
     * @var DisplayHeader
     */
    protected $displayheader;

    /**
     * @var DisplayFooter
     */
    protected $displayfooter;

    /**
     * @var PageController
     */
    public $container;

    /**
     * Konstruktor
     * @param boolean $user_logged_in If user is logged in or not
     * @return boolean
     */
    function __construct($ladeLeitWartePtr = null, $userPtr = null, $controller) {
        $this->container = $controller;
        $this->displayheader = new DisplayHeader ($userPtr, $controller);
        $this->displayfooter = new DisplayFooter ($userPtr);
        $this->contentHTML = '';
        $this->contentHTML .= '<div class="inner_container">';
        if ($ladeLeitWartePtr) $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        return true;
    }

    function getDisplayHeader() {
        return $this->displayheader;
    }

    function getDisplayFooter() {
        return $this->displayfooter;
    }

}

?>