<?php

/**
 * DeputiesController.class.php
 * Controller for Deputies Add/Edit/Delete
 * @author Pradeep Mohan
 * @todo if client side validation fails, and server side validation is done, then how is data retained after an update,
 * if the only return value form the saveDeputy function are the msgs? How about returning the failed $qform variable if the submitted fata is found to be invalid
 */


class DeputiesController {
    protected $ladeLeitWartePtr;
    protected $container;
    protected $deputies;
    protected $user;
// 	protected $breadcrumb;

    /**
     * Konstruktor
     */
    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {


        $this->requestPtr = $requestPtr; //new Request();
        $this->ladeLeitWartePtr = $ladeLeitWartePtr; //new Request();
        $this->container = $container;
        $this->user = $user;
        $qform_mt = null;

        $assigned_div = $user->getAssignedDiv();

        if (isset($assigned_div)) // for role FPS specifically
        {
            $div = $assigned_div;
        } else
            $div = $this->requestPtr->getProperty('div');

        //@todo for zsp deputies?
        $zspl = $this->requestPtr->getProperty('zspl');
        $addDep = $this->requestPtr->getProperty('addDep');
        $editDep = $this->requestPtr->getProperty('editdep');
        $deldep = $this->requestPtr->getProperty('deldep');
        $role = $this->requestPtr->getProperty('role');

        $content = $msgs = "";

        if (!$role) $role = $this->user->getUserRole();

        $this->deputies = $this->ladeLeitWartePtr->allUsersPtr->getDeputies($this->user, $role, $div);


        $displayheader = $this->container->getDisplayHeader();

        $qform_mt = new QuickformHelper ($displayheader, "fps_dep_add_edit_form");

        $save_new_dep = $requestPtr->getProperty('save_new_dep');

        $save_exist_dep = $requestPtr->getProperty('save_exist_dep');

        if ($save_new_dep) {
            try {
                $msgs[] = $this->ladeLeitWartePtr->allUsersPtr->saveDeputy($qform_mt, $this->user, $div, $zspl, $role);

            } catch (Exception $e) {
                $msgs[] = $e->getMessage();
                $addDep = "y";

            }

        } else if ($save_exist_dep) {
            try {
                $msgs[] = $this->ladeLeitWartePtr->allUsersPtr->saveDeputy($qform_mt, $this->user, $div, $zspl, $role);

            } catch (Exception $e) {
                $msgs[] = $e->getMessage();
                $editDep = "y";

            }

        } else if ($deldep && $this->user->user_can('newusers')) {
            $msgs[] = $this->ladeLeitWartePtr->allUsersPtr->deleteDeputy($qform_mt, $this->user, $div, $zspl);
        } else {
            if ($addDep == "y") {
                if (isset($role) && $role == 'fpv') {
                    $zspl = $requestPtr->getProperty('zspl');
                    $givenemail = $requestPtr->getProperty('givenemail');
                    $qform_mt->fps_deputies_add_edit($role, false, null, $this->user, $givenemail, $zspl);

                } else
                    $qform_mt->fps_deputies_add_edit('', false, null, $this->user);
            } else if ($editDep == "y") {
                $dep_id = (int)$requestPtr->getProperty('id');
                $editThisDep = $this->ladeLeitWartePtr->allUsersPtr->getFromId($dep_id);
                $qform_mt->fps_deputies_add_edit('', true, $editThisDep[0], $this->user);
            }
        }

        $this->deputies = $this->ladeLeitWartePtr->allUsersPtr->getDeputies($this->user, $role, $div);


        include_once("pages/deputies.php");

    }
} ?>