<?php


class UserAdministrationSymfonyController extends SymfonyBaseController
{
    public function __construct($ladeLeitWartePtr, $container, $requestPtr)
    {
        parent::__construct($ladeLeitWartePtr, $container, $requestPtr, '/useradmin',
            ['css/select2.min.css'],
            ['js/select2.full.min.js']);


        if (isset($_GET['call'])) {
            $name = $_GET['call'];
            $this->$name();
        }
    }

}