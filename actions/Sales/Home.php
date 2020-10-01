<?php

class ACtion_Home extends AClass_FormBase {
    function __construct(PageController $pageController) {
        parent::__construct($pageController);
    }

    function Execute() {
    }

    function WriteHtmlContent($options = "") {
        parent::WriteHtmlContent();
        include $_SERVER['STS_ROOT'] . "/actions/Sales/Home/Home.welcome.php";
    }
}

?>