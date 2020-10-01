<?php

/**
 * EcuPropertiesManagementController.class.php
 * The sub-class
 * @author Mateusz Wnęk, FEV Polska
 */

class EcuPropertiesManagementController extends SymfonyBaseController
{
    protected $user;

    public function __construct($ladeLeitWartePtr, $container, $requestPtr, $user)
    {
        parent::__construct(
            $ladeLeitWartePtr,
            $container,
            $requestPtr,
            'ecu/sw/properties',
            ['fontawesome/css/all.min.css'],
            ['js/jquery.collection.js', 'js/Collapsible-Tree-View/hummingbird-treeview.js'],
            'engg');

        /*Parameters to avoid */
        //parent::setParametersToAvoid();
        /*Parameters without keys */
        //parent::setParametersWithoutKeys();

        $this->user = $user;

        if (isset($_GET['call'])) {
            $name = $_GET['call'];
            $this->$name();
        }
    }

}