<?php

/**
 * user.class.php
 * User class to create,authenticate and remove useres
 * @author Pradeep Mohan
 */

/**
 * User class to create,authenticate and remove useres
 */
class User
{

    protected $username;

    protected $id;

    protected $email;

    protected $rolesList = [];

    protected $rolesParams = [];

    protected $role;

    protected $privileges;

    protected $notifications;

    protected $fname;

    protected $lname;

    protected $divisionId = null;

    protected $zsplId = '';

    protected $workshopId = null;

    protected $twofchk = true;

    protected $ladeLeitWartePtr;

    protected $loggedin = false;

    protected $error_msgs = "";

    protected $keyfilename = "";

    protected $uploadedkeyfile = "";

    protected $keyfiletemp = "";

    protected $user_role_label;

    protected $firstlogin = false;

    protected $show_login_form = true;

    protected $allowChangeDivison = false;

    protected $allowChangeZspl = false;

    protected $allowChangeWorkshop = false;


    /**
     * Konstruktor
     */
    function __construct($username = '', $userpass = '', $ladeLeitWartePtr, $addnOptions = '')
    {

        $this->ladeLeitWartePtr = $ladeLeitWartePtr;

        if (! empty($username) && $this->get_false_attempt_count($username) > 3) {
            $username = '';
            $userpass = '';
            $this->error_msgs = "Benutzername oder Passwort wurden wiederholt falsch eingegeben. <br>" . "Aus Sicherheitsgründen ist der Zugang für 5 Minuten gesperrt. <br>" . "Bitte warten Sie später nochmal, sich erneut einzuloggen";
            $this->show_login_form = false;
        }

        if (! isset($_SESSION['counter']))
            $_SESSION['counter'] = 0;

        if (! isset($_SESSION['role']))
            $_SESSION['role'] = [
                'current' => '',
                'division_id' => 0,
                'zspl_id' => 0
            ];

        $this->role = &$_SESSION['role']['current'];
        $this->divisionId = &$_SESSION['role']['division_id'];
        $this->zsplId = &$_SESSION['role']['zspl_id'];
        $this->workshopId = &$_SESSION['role']['workshop_id'];

        $this->user_role_label = array(
            "zentrale" => "Zentrale",
            "fuhrparksteuer" => "Fuhrparksteuerung",
            "fpv" => "Fuhrparkverwaltung",
            // "chrginfra"=>"Lade Infrastuktur",
            "sales" => "PPS",
            "engg" => "Engineering",
            "aftersales" => "Aftersales",
            "chrginfra_ebg" => "Charging Infrastruktur : EBG Compleo",
            "chrginfra_aix" => "Charging Infrastruktur : AixACCT",
            "chrginfra_innogy" => "Charging Infrastruktur : Innogy",
            "chrginfra_csg" => "Charging Infrastruktur : CSG",
            // "marketing"=>'Marketing',
            "dbhistory" => 'DB History',
            "fleet" => 'Post Fleet',
            "qs" => "QS",
            "qm" => "QM",
            "workshop" => "Werkstätte",
            "hotline" => "Hotline"
            // "fleet18i"=>"18I (Post Auslieferung)",
        );

        if (isset($_FILES["keyfileupload"])) // @todo execute this in a better way
        {
            $this->twofchk = false;
            $this->uploadedkeyfile = $_FILES['keyfileupload']['tmp_name'];
        }

        if (isset($GLOBALS['IS_DEBUGGING']) && $GLOBALS['IS_DEBUGGING'] && ! isset($_SESSION["sts_username"]) && ($username == "")) {
            $username = $GLOBALS['debug']['user'];
            $userpass = $GLOBALS['debug']['password'];
            $_COOKIE['ststoken'] = $GLOBALS['debug']['key'];
        }

        if (isset($_SESSION["sts_username"]) && ! empty($_SESSION["sts_username"])) {
            $this->username = $_SESSION["sts_username"];

            $this->loggedin = true;
            $result = $this->ladeLeitWartePtr->allUsersPtr->newQuery()
                ->where('username', '=', $this->username)
                ->getOne('*');
            $this->id = $result["id"];
            $this->email = $result["email"];
            $GLOBALS['msgseen'] = explode(',', $result['messages_seen']);

            $this->setRoleDef($result["role"], $result["division_id"], $result["zspl_id"], $result["workshop_id"]);

            $this->privileges = unserialize($result["privileges"]);
            $this->notifications = $result["notifications"];
            $this->fname = $result["fname"];
            $this->lname = $result["lname"];
            $this->loggedin = true;

            $resulttoken = $this->ladeLeitWartePtr->userTokensPtr->getUserToken($this->id);
            /**
             * * get result token, if it's empty but the user is logged, then this is user's first login
             * so save a new token for this userid
             */
            if (empty($resulttoken)) {
                if (isset($_GET['eingeloggt'])) {
                    $keyfilename = $this->saveNewToken($this->id);
                    $this->keyfilename = $keyfilename;
                    $this->firstlogin = true;
                }
            }
        } else {
            if (! empty($username) && ! empty($userpass)) {
                $this->userauth($username, $userpass);

                /**
                 * *
                 * part of enabling the back button to work in all scenarios
                 * if user succesfully logs in, and clicks back, then browsers tries to resubmit the post data
                 * to prevent this, if user is logged in, redirect once to url with ?eingeloggt=1
                 * now pressing back button leads back to home without issues
                 */
                if (! isset($_GET['loggedin']) && $this->loggedin) {
                    $appendPost = [];
                    foreach ($_POST as $key => $postvar) {
                        if (($key != 'page' && $key != 'benutzerPwd' && $key != 'benutzerName' && strpos($key, '_qf') === FALSE && strpos($key, '_csrf') === FALSE) || ($key == 'page' && $postvar != 'logout')) {
                            $appendPost[] = $key . '=' . filter_var($postvar, FILTER_SANITIZE_STRING);
                        }
                    }
                    header('Location: ' . $_SERVER['PHP_SELF'] . '?eingeloggt=1&' . implode('&', $appendPost));
                }
            }
        }

        return true;

    }


    function getShowLoginForm()
    {

        return $this->show_login_form;

    }


    function deleteToken()
    {

        return $this->ladeLeitWartePtr->userTokensPtr->newQuery()
            ->where('userid', '=', $this->id)
            ->delete();

    }


    /**
     * Authenticates user with the given username and password
     *
     * @param string $username
     * @param string $userpass
     */
    function userauth($username, $userpass)
    {

        $hasher = new PasswordHash(8, false);
        $result = $this->ladeLeitWartePtr->allUsersPtr->getFromUserName($username);
        if ($result) {
            $username = $result['username'];
            $storedHash = $result["passwd"];
            $userid = $result["id"];
            $gesperrt = substr($username, 0, 1) == '~';

            if (isset($result['expires'])) {
                if (strtotime($result['expires']) < time())
                    $gesperrt = true;
            }

            if ($gesperrt) {
                $this->error_msgs = "Benutzer '$username' für die Anmeldung gesperrt";
                $this->log_false_attempt($username);
                $_SESSION['sts_username'] = "";
                $_SESSION['sts_role'] = "";
                $_POST['benutzerName'] = "";
                $_POST['benutzerPwd'] = "";

                return false;
            }

            if ($hasher->CheckPassword($userpass, $storedHash)) {
                $this->username = $username;

                if ($this->uploadedkeyfile) {
                    $filename = $this->uploadedkeyfile;
                    $fresource = fopen($filename, "r");
                    $selectorandtoken = fread($fresource, filesize($filename));
                    fclose($fresource);
                    $domain = null;
                    if ($domain === null && isset($_SERVER['HTTP_HOST'])) {
                        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
                    }
                    $https = false;
                    // $expires = new \DateTime('24 hours'); dont need
                    setcookie("ststoken", $selectorandtoken, time() + 3600 * 24 * 365, '/', $domain, $https, true);
                    $token = $selectorandtoken;
                    $tokenParts = explode(":", $token);
                    $resulttoken = $this->ladeLeitWartePtr->userTokensPtr->getUserTokenFromSelector($tokenParts[0]);
                    $tokenDb = $resulttoken["token"];
                    $tokenDb = explode(":", $tokenDb);
                    $hasher = new PasswordHash(8, false);
                    if ($hasher->CheckPassword($tokenParts[1], $tokenDb[1]) && $userid == $resulttoken['userid']) {
                        // not exactly error, but used for debugging purposes.
                        $this->error_msgs = "Erfolgreiche Benutzerauthentifizierung";
                    } 
                    else {
                        // error displayed only when the user uploads a false key
                        $this->error_msgs = "Authentifizierung fehlgeschlagen!!";
                        return false; // echo "authentication fail";
                    }
                } else {
                    $resulttoken = $this->ladeLeitWartePtr->userTokensPtr->getUserToken($userid);
                    if (! empty($resulttoken)) {
                        if (! $this->checkToken($userid)) { // error messages set inside the checkToken functions
                                                            // statement allows testing on localhost without always needing a cookie
                                                            // host street and user=Sts.Dev
                            if ($_SERVER['HTTP_HOST'] == 'localhost' && strstr($username, 'Sts.') !== false) {} else if ($username == 'Sts.Cron') {} else
                                return false;
                        }
                    }
                }

                $this->setRoleDef($result["role"], $result['division_id'], $result['zspl_id'], $result["workshop_id"]);

                $_SESSION['sts_username'] = $username;
                $_SESSION['sts_userid'] = $result["id"];
                $_SESSION['sts_cookies_accepted'] = $result['cookies_accepted'];
                $_SESSION['sts_privacy_accepted'] = $result['privacy_accepted'];

                // setcookie("sts_username",$username,time()+3600);
                $this->id = $result["id"];
                $this->email = $result["email"];

                $this->privileges = unserialize($result["privileges"]);
                $this->notifications = $result["notifications"];
                $this->fname = $result["fname"];
                $this->lname = $result["lname"];
                $this->keyfilename = "/tmp/" . $this->username . ".key";
                $this->loggedin = true;

                $this->ladeLeitWartePtr->allUsersPtr->logLastLoggin();
            } else {
                $this->error_msgs = "Benutzername oder Passwort falsch";
                $this->log_false_attempt($username);

                $_SESSION['sts_username'] = "";
                $this->role = "";
            }
        } else {
            $this->error_msgs = "Benutzername oder Passwort falsch";
            $this->log_false_attempt($username);
            $_SESSION['sts_username'] = "";
            $_SESSION['sts_role'] = "";
        }

        // return false;
    }


    function log_false_attempt($username)
    {

        $data = array(
            'username' => $username,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'timestamp_attempt' => date('Y-m-d H:i:sO')
        );
        return $this->ladeLeitWartePtr->newQuery('false_login_attempts')->insert($data);

    }


    function get_false_attempt_count($username)
    {

        $compare_time = time() - 60 * 5;
        return $this->ladeLeitWartePtr->newQuery('false_login_attempts')
            ->where('username', '=', $username)
            ->where('timestamp_attempt', '>', date('Y-m-d H:i:sO', $compare_time))
            ->getVal('count(*)');

    }


    /**
     */
    function checkToken($userid)
    {

        if (isset($_COOKIE["ststoken"])) {
            $token = $_COOKIE["ststoken"];
            $tokenParts = explode(":", $token);
            $result = $this->ladeLeitWartePtr->userTokensPtr->getUserTokenFromSelector($tokenParts[0]);
            $tokenDb = $result["token"];
            $tokenDb = explode(":", $tokenDb);
            $hasher = new PasswordHash(8, false);
            if ($hasher->CheckPassword($tokenParts[1], $tokenDb[1]) && $userid == $result['userid']) {
                $this->error_msgs = "Erfolgreiche Benutzerauthentifizierung";
                return true; // echo "success";
            } 
            else {
                $this->error_msgs = "Kein Schüssel gefunden!";
                return false; // echo "authentication fail due to wrong token";
            }
        } 
        else {
            $this->error_msgs = "Kein Schüssel gefunden!";
            return false; // echo "no cookie found";
        }

    }


    /**
     * Generates a new token and saves the token to the database after hashing
     *
     * @param integer $userid
     */
    function saveNewToken($userid)
    {

        $hasher = new PasswordHash(8, false);

        $randstring = $hasher->get_random_bytes(32);
        $token = base64_encode($randstring);
        $tokenhash = $hasher->HashPassword($token);

        $selector = $hasher->get_random_bytes(8);
        $selector = base64_encode($selector);
        date_default_timezone_set('Europe/Berlin');

        $timestamp24hours = date('Y-m-d H:m:sO', time() + (365 * 24 * 60 * 60));

        $insertValues = array(
            "selector" => $selector,
            "token" => $selector . ":" . $tokenhash,
            "userid" => $userid,
            "expires" => $timestamp24hours
        );
        $result = $this->ladeLeitWartePtr->userTokensPtr->newQuery()->insert($insertValues);
        $domain = null;
        if ($domain === null && isset($_SERVER['HTTP_HOST'])) {
            $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
        }
        $https = false;
        $expires = new \DateTime('24 hours'); // dont need

        $this->keyfilename = "/tmp/" . $this->username . ".key";
        $this->keyfiletemp = fopen($this->keyfilename, "w");
        $fcontent = $selector . ":" . $token;
        fwrite($this->keyfiletemp, $fcontent);

        setcookie("ststoken", $selector . ":" . $token, time() + 3600 * 24 * 365, '/', $domain, $https, true);
        return $this->keyfilename;

    }


    function loggedin()
    {

        if ($this->loggedin)
            return true;
        else
            return false;

    }


    /**
     * Logout
     */
    function logout($destroy_session = true)
    {

        if ($destroy_session) {
            session_destroy();
        } else {
            unset($_SESSION['sts_username']);
            unset($_SESSION['sts_userid']);
            unset($_SESSION['sts_role']);
        }
        $this->loggedin = false;
        if ($this->keyfiletemp)
            fclose($this->keyfiletemp);
        // setcookie ( "sts_username", $this->username, time () - 3600 );

        return $this->username;

    }


    function getAssignedLocation()
    {

        if ($this->role == 'fuhrparksteuer') {
            $result = $this->ladeLeitWartePtr->divisionsPtr->getFromId($this->divisionId);
            $resultname = 'Niederlassung ' . $result['name'];
        }
        if ($this->role == 'fpv') {
            $result = $this->ladeLeitWartePtr->zsplPtr->getFromId($this->zsplId);
            $resultname = 'ZSPL ' . $result['name'];
        }
        if (isset($result))
            return $resultname;
        else
            return '';

    }


    /**
     * Gets the user name
     *
     * @return string
     *
     */
    function getUserName()
    {

        return $this->username;

    }


    /**
     * Returns if this is the users first login
     *
     * @return string
     *
     */
    function getFirstLogin()
    {

        return $this->firstlogin;

    }


    function getKeyFileName()
    {

        return $this->keyfilename;

    }


    /**
     * Gets the user email
     *
     * @return string
     *
     */
    function getUserEmail()
    {

        return $this->email;

    }


    /**
     * Returns the user's first name and last name separataed by a space
     *
     * @return string
     *
     */
    function getUserFullName()
    {

        return $this->fname . " " . $this->lname;

    }


    /**
     * Returns the user'slast name separataed by a space
     *
     * @return string
     *
     */
    function getUserLastName()
    {

        return $this->lname;

    }


    /**
     * Stores any login errors for display in the Login Form
     *
     * @return array
     */
    function getErrorMsgs()
    {

        return $this->error_msgs;

    }


    /**
     * Parses the db-role string into local structure
     *
     * @return void
     *
     */
    function setRoleDef($dbRole, $divisionId, $zsplId, $workshopId = 0)
    {

        $this->rolesList = [];
        $this->rolesParams = [];
        $hasEngg = false;
        $hasQs = false;

        $rolDefs = explode(',', $dbRole);

        foreach ($rolDefs as $rd) {
            $set = explode(':', $rd);
            $role = strtolower(trim($set[0]));
            $this->rolesList[] = $role;

            switch ($role) {
                case 'engg':
                    $hasEngg = true;
                    break;
                case 'qs':
                    $hasQs = true;
                    break;
            }

            if (count($set) > 1)
                $this->rolesParams[$role] = trim($set[1]);
        }

        if ($hasEngg && ! $hasQs)
            $this->rolesList[] = 'qs';

        if (safe_val($_SESSION, 'sts_admin', false)) {
            $this->SetAdmin(true);
        }

        if ($divisionId == - 1) {
            $this->allowChangeDivison = true;
        }

        if ($zsplId == - 1)
            $this->allowChangeZspl = true;

        if ($workshopId == - 1)
            $this->allowChangeWorkshop = true;

        if (empty($this->role))
            $this->role = $this->rolesList[0];

        if (empty($this->divisionId))
            $this->divisionId = $divisionId;

        if (empty($this->zsplId))
            $this->zsplId = $zsplId;

        if (empty($this->workshopId))
            $this->workshopId = $workshopId;

    }


    /**
     * Gets the User's Role
     *
     * @return string
     *
     */
    function getUserRole()
    {

        $roles_list = $this->getListOfUserRoles();

        if ((count($roles_list) > 1) && isset($_REQUEST['setrole'])) {
            $newRole = $_REQUEST['setrole'];
            foreach ($roles_list as $r) {
                if (strcasecmp($r, $newRole) == 0) {
                    $this->role = $newRole;
                    break;
                }
            }
        }

        return $this->role;

    }


    function getListOfUserRoles()
    {

        if ($this->IsAdmin())
            return array_keys($this->user_role_label);

        return $this->rolesList;

    }


    function getUserNotifications()
    {

        return $this->notifications;

    }


    /**
     * Gets the User Role's Label for display
     *
     * @return string
     *
     */
    function getUserRoleLabel($role = "")
    {

        if (empty($role))
            $role = $this->role;
        if (isset($this->user_role_label[$role]))
            return $this->user_role_label[$role];
        return "";

    }


    /**
     * Gets the User's Keyfile
     *
     * @return string
     *
     */
    function getKeyFile()
    {

        if (! $this->keyfilename) {
            // $whereParams=array();
            // $whereParams[]=array('colname'=>'userid','whereop'=>'=','colval'=>$this->id);
            // $result=$this->dataSrcPtr->selectAll ( 'usertokens',"",$whereParams);
            // $tokenDb=$result[0]["token"];
            // $expires = new \DateTime('24 hours');
            $this->keyfilename = "/tmp/" . $this->username . ".key";
            // $this->keyfiletemp = fopen($this->keyfilename, "w");
            // $fcontent=$tokenDb;
            // fwrite($this->keyfiletemp ,$fcontent);
            // $file = $this->keyfilename;
        }

        return $this->keyfilename;

    }


    /**
     * Return the ID of the user
     *
     * @return integer
     */
    function getUserId()
    {

        return $this->id;

    }


    /**
     * Returns the Division Id (Niederlassung) assigned to the user
     *
     * @return integer
     */
    function getAssignedDiv($useFirstAsDefault = false)
    {

        if ($this->allowChangeDivison) {
            if (isset($_REQUEST['setdivision']))
                $this->divisionId = $_REQUEST['setdivision'];

            if ($useFirstAsDefault && ($this->divisionId < 0)) {
                $qry = $this->ladeLeitWartePtr->newQuery('divisions');
                $qry = $qry->where('production_location', '=', 'f');
                $qry = $qry->orderBy('name')->limit(1);
                $this->divisionId = $qry->getVal('division_id');
            }
        }
        return $this->divisionId;

    }


    function getAllowChangeDiv()
    {

        return $this->allowChangeDivison;

    }


    function getSwitchableDivs()
    {

        $qry = $this->ladeLeitWartePtr->newQuery('divisions');
        if ($this->allowChangeDivison)
            $qry = $qry->where('production_location', '=', 'f');
        else if ($this->divisionId)
            $qry = $qry->where('division_id', '=', $this->divisionId);
        else
            return null;

        return $qry->orderBy('name')->get('division_id=>name');

    }


    function getAssignedZspl($defaultFirstFromDivision = 0)
    {

        if ($this->allowChangeZspl && isset($_REQUEST['setzspl']))
            $this->zsplId = $_REQUEST['setzspl'];
        if ($this->zsplId > 0)
            return $this->zsplId;

        if ($defaultFirstFromDivision) {
            if ($defaultFirstFromDivision < 0)
                $defaultFirstFromDivision = $this->getAssignedDiv(true);

            $qry = $this->ladeLeitWartePtr->newQuery('zspl');
            $qry = $qry->where('division_id', '=', $defaultFirstFromDivision);
            $qry = $qry->orderBy('name')->limit(1);
            $this->zsplId = $qry->getVal('zspl_id');
            return $this->zsplId;
        }
        return null;

    }


    function getAllowChangeZspl()
    {

        return $this->allowChangeZspl;

    }


    function getSwitchableZspls($division_id = 0)
    {

        $qry = $this->ladeLeitWartePtr->newQuery('zspl');
        if ($this->allowChangeZspl)
            $qry = $qry->where('division_id', '=', (($division_id > 0) ? $division_id : $this->divisionId));
        else if ($this->zsplId)
            $qry = $qry->where('zspl_id', '=', $this->zsplId);
        else
            return null;

        return $qry->orderBy('name')->get('zspl_id=>name');

    }


    function getAssignedWorkshop($useFirstAsDefault = false)
    {

        if ($this->allowChangeWorkshop && isset($_REQUEST['setworkshop']))
            $this->workshopId = $_REQUEST['setworkshop'];
        if ($this->workshopId > 0)
            return $this->workshopId;
        if ($useFirstAsDefault) {
            $qry = $this->ladeLeitWartePtr->newQuery('workshops');
            $qry = $qry->orderBy('zip_code')->limit(1);
            $this->workshopId = $qry->getVal('workshop_id');
            return $this->workshopId;
        }
        return null;

    }


    function getAllowChangeWorkshop()
    {

        return $this->allowChangeWorkshop;

    }


    function getSwitchableWorkshops()
    {

        $qry = $this->ladeLeitWartePtr->newQuery('workshops');
        if ($this->allowChangeWorkshop)
            $qry = $qry->where('workshop_id', '>', '0')->orderBy('zip_code');
        else if ($this->zsplId)
            $qry = $qry->where('workshop_id', '=', $this->workshopId);
        else
            return null;

        $qryres = $qry->get('workshop_id,name,zip_code,location', 'workshop_id');
        if ($qryres) {
            $result = [];
            foreach ($qryres as $workshop_id => $wkset) {
                $zip_code = str_pad($wkset['zip_code'], 5, '0', STR_PAD_LEFT);
                $result[$workshop_id] = "$zip_code {$wkset['location']}: {$wkset['name']}";
            }
            return $result;
        }
        return null;

    }


    function CanAdmin()
    {

        return in_array('admin', $this->rolesList);

    }


    function SetAdmin($is_admin)
    {

        if ($this->CanAdmin()) {
            $_SESSION['sts_admin'] = $is_admin;
        }

    }


    function IsAdmin()
    {

        if ($this->CanAdmin()) {
            if (isset($_REQUEST['set_admin'])) {
                $this->SetAdmin($_REQUEST['set_admin']);
                unset($_REQUEST['set_admin']);
            }
            return safe_val($_SESSION, 'sts_admin', false);
        }
        return false;

    }


    /**
     * Gets the User's privileges as a string, converts to an array and checks if user has the privilege
     *
     * @return boolean &&
     */
    function user_can($privilege)
    {

        if ($this->IsAdmin())
            return true;

        $user_privileges = $this->privileges;
        if (isset($user_privileges[$privilege]) && $user_privileges[$privilege])
            return true;
        else
            return false;

    }


    function generatePassword($lenth = 16, $pool = '')
    {

        if (empty($pool))
            $pool = "qwertzupasdfghkyxcvbnm" . "23456789" . "QWERTZUPLKJHGFDSAYXCVBNM";

        $poollen = strlen($pool);
        $pass_word = "";

        srand((double) microtime() * 1000000);

        for ($index = 0; $index < $lenth; $index ++) {
            $pass_word .= substr($pool, (rand() % $poollen), 1);
        }

        return $pass_word;

    }


    function acceptCookies()
    {

        return $this->acceptWhat('cookies');

    }


    function acceptPrivacy()
    {

        return $this->acceptWhat('privacy');

    }


    protected function acceptWhat($what)
    {

        $tmNow = time();
        $dbNow = gmdate('Y-m-d G:i:s', $tmNow);

        $_SESSION["sts_{$what}_accepted"] = $dbNow;

        return $this->ladeLeitWartePtr->allUsersPtr->newQuery()
            ->where('id', '=', $_SESSION['sts_userid'])
            ->update([
            "{$what}_accepted"
        ], [
            $dbNow
        ]);

    }


    function IsMessageSeen($msgid)
    {

        return (in_array($msgid, $GLOBALS['msgseen']));

    }


    function SetMessageSeen($msgid)
    {

        if (in_array($msgid, $GLOBALS['msgseen']))
            return;

        $GLOBALS['msgseen'][] = $msgid;
        $messages_seen = implode(',', $GLOBALS['msgseen']);

        $this->ladeLeitWartePtr->allUsersPtr->newQuery()
            ->where('id', '=', $_SESSION['sts_userid'])
            ->update([
            "messages_seen"
        ], [
            $messages_seen
        ]);

    }

}

