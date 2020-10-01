<?php
/**
 * CommonFunctions.class.php
 *
 * @author Pradeep Mohan
 */

/**
 * Class to handle common functions
 *
 */
class CommonFunctions {
    protected $ladeLeitWartePtr;
    protected $user;
    protected $displayHeader;
    protected $requestPtr;
//    protected $translations;


    function __construct($ladeLeitWartePtr, $displayHeader, $user, $requestPtr, $getaction) {

        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->user = $user;
        $this->displayHeader = $displayHeader;
        $this->requestPtr = $requestPtr;
        $this->getaction = $getaction;
        $this->translations = new LegacyTranslations();

    }

    protected function getTranslationsForDomain($domain = 'messages') {
      return $this->translations->getTranslationsForDomain($domain);
    }
}
