<?php
/**
 * allusers.class.php
 * Klasse für alle users
 * @author Pradeep Mohan
 */

/**
 * Class to handle Users
 */
class AllUsers extends LadeLeitWarte {

    protected $privileges;

    function __construct(DataSrc $dataSrcPtr, $tableName) {
        $this->dataSrcPtr = $dataSrcPtr;
        $this->tableName = $tableName;

        $standard_privileges = array('newusers' => 'Darf er/sie Mitarbeiter Konten für dieser Benutzerrolle (Vertreter/-innen) erstellen/bearbeiten/löschen?',
            'viewflm' => 'Darf er/sie Flotten-Monitoring-Daten (Verbrauchs-/Fahrzeugstatistik) einsehen?');
        $this->privileges = array(
            'zentrale' => $standard_privileges,
            'sales' => $standard_privileges,
            'chrginfra_ebg' => $standard_privileges,
            'chrginfra_ebg' => $standard_privileges,
            'aftersales' => array_merge($standard_privileges, ['grafana_view' => 'Kann Verlaufsdaten einsehen']),
            'engg' => array_merge($standard_privileges, ['add_teo_exceptions' => 'Abweicherlaubnisse hinzufügen?', 'specialapproval' => 'Fahrzeug Sondergenehmigung setzen?', 'grafana_view' => 'Kann Verlaufsdaten einsehen']),//,'grafana_admin'=>'Grafana als Admin?'
            'qs' => array(
                'newusers' => 'Darf er/sie Mitarbeiter Konten für dieser Benutzerrolle (Vertreter/-innen) erstellen/bearbeiten/löschen?',
                'set_finished_status' => 'Darf er/sie Fahrzeuge Status als fertig setzen?',
                'edit_qs_faults' => 'Darf er/sie QS Nacharbeitsfehler eintragen?',
                'set_qs_faults_rectified' => 'Darf er/sie QS Nacharbeitsfehler austragen?',
                'manualsw' => 'Darf er/sie manuell geflashter SW Stand eingeben?'
            ),
            'fuhrparksteuer' => array_merge($standard_privileges, array('addzsplemails' => 'Darf er/sie Email-Adressen und Accounts für ZSPLn hinzufügen?')),
            'fpv' => array_merge($standard_privileges, array('addzspemails' => 'Darf er/sie E-Mail Adressen für einzelne ZSPn hinzufügen'))
        );
    }

    /**
     * Fetches all the emails for this division id. Used in SalesController when sending emails to FPS
     * @param integer $division_id
     */
    function getFPSEmails($division_id) {
        return $this->newQuery()->where('division_id', '=', $division_id)
            ->where('role', '=', 'fuhrparksteuer')
            ->where('email', 'IS', 'NOT NULL')
            ->get('email,fname,lname');
    }

    function getFPVEmails($zspl_id) {
        return $this->newQuery()->where('zspl_id', '=', $zspl_id)
            ->where('role', '=', 'fpv')
            ->where('email', 'IS', 'NOT NULL')
            ->get('email,fname,lname');
    }

    /***
     * Returns the first user with matching email id, used in fuhrparksteuer.php
     * @param string $emailid
     * @return null or single row array with user data
     */
    function getFromEmailId($emailid) {
        return $this->newQuery()->where('email', '=', $emailid)->getOne('*');
    }

    /***
     * Returns the username with the matching user id,used in sales.php
     * @param integer $userid
     * @return boolean|string false or string with username
     */
    function getUserNameFromId($userid) {
        return $this->newQuery()->where('id', '=', $userid)->getVal('username');
    }

    /***
     * Used for checking if username exists already. Used in MitarbeiterController.class.php
     * @param string $username
     * @return null|array user data
     */
    function getFromUserName($username) {
        global $config;


        //if ($config->get_property ('case_insenitive_login', false))
        //{
        //return $this->newQuery()->where('lower(username)','=',strtolower($username))->getOne('*');
        //}

        return $this->newQuery()->where('username', '=', $username)->getOne('*');
    }

    /***
     * write network address, timestamp and session_id into the users table
     * @return null
     */
    function logLastLoggin() {
        $update_cols = ['timestamp_last_logged_in', 'ip_last_logged_in', 'last_session_id'];
        $update_vals = ['now()', $_SERVER['REMOTE_ADDR'], session_id()];
        $this->newQuery()->where('id', '=', $_SESSION['sts_userid'])->update($update_cols, $update_vals);
    }

    /***
     * gets the deputies whose accounts can be managed by the current user
     * @param object $user
     * @param string $role
     * @param integer $div
     * @return mixed
     */
    function getDeputies($user, $role, $div) {
        $deputies = [];
        $usersRole = $user->getUserRole();
        $assigned_div = $user->getAssignedDiv();
        $assigned_zspl = $user->getAssignedZspl();
        $my_user_id = $user->getUserId();

        if ($usersRole == 'fuhrparksteuer') {
            $qry = $this->dataSrcPtr
                ->newQuery('users')
                ->where('division_id', '=', $assigned_div)
                ->where('id', '!=', $my_user_id)
                ->where('role', 'like', "%fuhrparksteuer%");
            $fps_deputies = $qry->get('*', 'id');


            $qry = $this->dataSrcPtr
                ->newQuery('zspl')
                ->where('division_id', '=', $assigned_div);
            $zspl_ids = $qry->getVals('zspl_id');


            $qry = $this->dataSrcPtr
                ->newQuery('users')
                ->where('zspl_id', 'IN', $zspl_ids)
                ->where('id', '!=', $my_user_id)
                ->where('role', 'like', "%fpv%");
            $fpv_deputies = $qry->get('*', 'id');

            if (!empty($fps_deputies) && !empty($fpv_deputies))
                return array_merge($fps_deputies, $fpv_deputies);
            else if (!empty($fps_deputies))
                return $fps_deputies;
            else if (!empty($fpv_deputies))
                return $fpv_deputies;
            else return array();
        }


        if ($usersRole == 'fpv') {
            if ($assigned_zspl != '') // $role!="zentrale" && $role!="chrginfra")
            {
                $qry = $this->dataSrcPtr
                    ->newQuery('users')
                    ->where('zspl_id', '=', $assigned_zspl)
                    ->where('id', '!=', $my_user_id)
                    ->where('role', 'like', "%fpv%");
                $deputies = $qry->get('*', 'id');

            }
        } else {
            $qry = $this->dataSrcPtr
                ->newQuery('users')
                ->where('id', '!=', $my_user_id)
                ->where('role', 'like', "%$role%");

            $deputies = $qry->get('*', 'id');
        }

        return $deputies;
    }

    function getPrivileges($userrole, $user = NULL) {
        $privileges = $this->privileges[$userrole];
        $allowed_privileges = [];
        foreach ($privileges as $privilege_key => $privilege) {
            if ($user->user_can($privilege_key)) $allowed_privileges[$privilege_key] = $privilege;
        }
        return $allowed_privileges;
    }

    /***
     * Used in MitarbeiterController.class.php
     * @param integer $dep_id
     * @return string
     */
    function deleteDeputy($dep_id) {
        $this->delete(array(array('colname' => 'id', 'whereop' => '=', 'colval' => $dep_id)));
        return "Mitarbeiter/in Konto gelöscht!";

    }

    function saveDeputy(&$qform_mt = null, $user = null, $div = null, $zspl = null, $depot = null) //@todo depot???
    {
        $requestPtr = new Request();
        $content = "";
        $save_new_dep = $requestPtr->getProperty('save_new_dep');
        $save_exist_dep = $requestPtr->getProperty('save_exist_dep');

        if ($save_new_dep || $save_exist_dep) {
            $privilege = array();
            $privilege["newusers"] = $requestPtr->getProperty('privilege_newusers');
            $deprole = $requestPtr->getProperty('role');
            $notifications = $requestPtr->getProperty('notifications');

            if (!isset($deprole) || $deprole == '') $deprole = $user->getUserRole(); //@todo added this to enable saving of privileges for deputies

            if ($deprole == "fpv")
                $privilege["addzspemails"] = $requestPtr->getProperty('privilege_addzspemails');
            else if ($deprole == "fuhrparksteuer")
                $privilege["addzsplemails"] = $requestPtr->getProperty('privilege_addzsplemails');

            $privilege["viewflm"] = $requestPtr->getProperty('privilege_viewflm');
            $depprivilege = serialize($privilege);

            $depemail = $requestPtr->getProperty('email');
            $dep_id = $requestPtr->getProperty('id');
            $passwd = $requestPtr->getProperty('passwd');
            $depusername = $requestPtr->getProperty('depusername');

            $depzspl = $requestPtr->getProperty('zspl_id_dep'); //when FPS role saves FPV deputies

            if (!isset($depzspl) || $depzspl == '') {
                $depzspl = $user->getAssignedZspl(); //@todo added this to enable saving of zsplid for deputies of FPVs
            }

            if ($save_exist_dep) {
                //here should the form data be validated before entry?

                $dep_id = (int)$requestPtr->getProperty('id');

                $currentdep["email"] = $depemail;
                $currentdep["privileges"] = $depprivilege;
                $currentdep["id"] = $dep_id;
                $currentdep["username"] = $depusername;
                $currentdep["role"] = $deprole;
                $currentdep["notifications"] = $notifications;

                $qform_mt->fps_deputies_add_edit('', true, $currentdep, $user);
                if (!$qform_mt->formValidate()) {
                    throw new Exception("Ungultige Daten!");
                } else {
                    if ($passwd)
                        $this->save(array("privileges", "email", "passwd", "username", "notifications"), array($depprivilege, $depemail, $passwd, $depusername, $notifications), array('id', '=', $dep_id));
                    else
                        $this->save(array("privileges", "email", "username", "notifications"), array($depprivilege, $depemail, $depusername, $notifications), array('id', '=', $dep_id));
                    $content = "Mitarbeiter/in Konto gespeichert!";
                }


            } else {
                $role = $user->getUserRole();
                if ($role == 'fpv') {
                    $qform_mt->fps_deputies_add_edit($deprole, false, null, $user, $depemail, $depzspl);
                } else
                    $qform_mt->fps_deputies_add_edit('', false, null, $user);

                if (!$qform_mt->formValidate()) {
                    throw new Exception("Ungultige Daten!");

                } else {
                    $names = explode('.', $depusername);
                    $newdep = array(
                        "username" => $depusername,
                        "privileges" => $depprivilege,
                        "email" => $depemail,
                        "passwd" => $passwd,
                        "addedby" => $user->getUserId(),
                        "fname" => $names[0],
                        "lname" => $names[1],
                        "notifications" => $notifications,
                        "division_id" => $div);
                    $deprole = $requestPtr->getProperty('role');

                    if ($deprole == 'fpv') {
                        $newdep['role'] = 'fpv';
                        $newdep['zspl_id'] = $depzspl;
                    } else {
                        $newdep['role'] = $user->getUserRole();
                    }


                    $this->add($newdep);
                    $content = "Neue Mitarbeiter/in Konto erstellt!";
                }
            }
            return $content;

        } else return false;


    }

    /**
     * Used in MitarbeiterController.class.php
     * {@inheritDoc}
     * @see LadeLeitWarte::add()
     */
    function add($insertVals, $addtionalParams = null) {
        $hasher = new PasswordHash(8, false);
        $insertVals["passwd"] = $hasher->HashPassword($insertVals["passwd"]);
        $id = $this->dataSrcPtr->insert('users', $insertVals);
        return $id;
    }

    /***
     * deleteToken used when deleting user or when trying to reset the account of a user
     * @param int $userid
     * @return boolean
     */
    function deleteToken($userid) {
        $whereParams = array();
        $whereParams[] = array('colname' => 'userid', 'whereop' => '=', 'colval' => $userid);
        $results = $this->dataSrcPtr->selectAll('usertokens', "", $whereParams);
        if (empty($results))
            return false;
        foreach ($results as $result) {
            $whereParams = array();
            $whereParams[] = array('colname' => 'selector', 'whereop' => '=', 'colval' => $result['selector']);
            $result = $this->dataSrcPtr->delete("usertokens", $whereParams);
        }
        return true;

    }

    /***
     * used in the MitarbeiterController.Class.php
     * {@inheritDoc}
     * @see LadeLeitWarte::save()
     */
    function save($updateCols, $updateVals, $whereParamsRaw) {

        $whereParams = array();
        $whereParams[] = array('colname' => $whereParamsRaw[0], 'whereop' => $whereParamsRaw[1], 'colval' => $whereParamsRaw[2]);

        if (in_array("passwd", $updateCols)) {
            $hasher = new PasswordHash(8, false);
            while ($dbcolname = current($updateCols)) {
                if ($dbcolname == 'passwd') {
                    $passkey = key($updateCols);
                    $updateVals[$passkey] = $hasher->HashPassword($updateVals[$passkey]);
                }
                next($updateCols);
            }
        }
        $id = $this->dataSrcPtr->update('users', $updateCols, $updateVals, $whereParams);
        return $id;
    }
}