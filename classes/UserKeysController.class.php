<?php

class UserKeysController extends SymfonyBaseController
{

    public function __construct($ladeLeitWartePtr, $container, $requestPtr)
    {
        parent::__construct($ladeLeitWartePtr, $container, $requestPtr, 'user/keys',
            [],
            []);
        /*Parameters to avoid */
        //parent::setParametersToAvoid();
        /*Parameters without keys */
        //parent::setParametersWithoutKeys();


        if (isset($_GET['call'])) {
            $name = $_GET['call'];
            $this->$name();
        }
    }
}