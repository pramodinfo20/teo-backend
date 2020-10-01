<?php

class TestController extends PageController {
    protected $content;

    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {


        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->user = $user;
        $this->msgs = null;
        $this->content = null;
        $this->displayHeader = $this->container->getDisplayHeader();

        $this->displayHeader->setTitle("StreetScooter Cloud Systems : " . 'test');

        $action = $this->requestPtr->getProperty('action');


        if (isset($action))
            call_user_func(array($this, $action));

        $this->displayHeader->printContent();

        $this->printContent();
    }

    function firstaction() {
        $depots = $this->ladeLeitWartePtr->depotsPtr->newQuery()
            ->join('zspl', 'zspl.zspl_id=depots.zspl_id', 'INNER JOIN')
            ->join('divisions', 'divisions.division_id=zspl.division_id', 'INNER JOIN')
            ->orderBy('divisions.name')
            ->limit(19)
            ->get('*');
        $table = new DisplayTable($depots);
        $this->content = $table->getContent();
    }
}