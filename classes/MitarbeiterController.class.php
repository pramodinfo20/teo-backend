<?php

/**
 * MitarbeiterController.class.php
 * Controller for Deputies Add/Edit/Delete
 * @author Pradeep Mohan
 * @todo if client side validation fails, and server side validation is done, then how is data retained after an update,
 * if the only return value form the saveDeputy function are the msgs? How about returning the failed $qform variable if the submitted fata is found to be invalid
 */


class MitarbeiterController {
    protected $ladeLeitWartePtr;
    protected $container;
    protected $displayHeader;
    protected $msgs;
    protected $deputies = [];
    protected $user;

    protected $qform_mt;
    protected $qform_new;
    protected $qform_exist;

// 	protected $breadcrumb;

    /**
     * Konstruktor
     */
    function __construct($ladeLeitWartePtr, $container, $requestPtr, $user) {
        $this->ladeLeitWartePtr = $ladeLeitWartePtr;
        $this->container = $container;
        $this->requestPtr = $requestPtr;
        $this->user = $user;
        $this->action = NULL;
        $this->qform_mt = NULL;
        $this->qform_new = NULL;
        $this->qform_exist = NULL;
    }

    function checkIfKeyExists() {
        $version = $this->ladeLeitWartePtr->newQuery("server_keys")->orderBy("id", "DESC")->limit("1")->getVal("id");
        return $this->ladeLeitWartePtr->newQuery("user_key")
            ->where("sts_userid", "=", $_SESSION["sts_userid"])
            ->where("version", "=", $version)
            ->get("signature") == null ? false : true;
    }

    function Init() {

    }

    function Execute() {
        $assigned_div = $this->user->getAssignedDiv();

        if (isset($assigned_div)) // for role FPS specifically
            $this->div = $assigned_div;
        else
            $this->div = $this->requestPtr->getProperty('div');

        //@todo for zsp deputies?
        $zspl = $this->requestPtr->getProperty('zspl');
        $addDep = $this->requestPtr->getProperty('addDep');
        $editDep = $this->requestPtr->getProperty('editdep');
        $deldep = $this->requestPtr->getProperty('deldep');
        $role = $this->requestPtr->getProperty('role');

        if (!$role) $role = $this->user->getUserRole();

        $this->deputies = $this->ladeLeitWartePtr->allUsersPtr->getDeputies($this->user, $role, $this->div);

        $this->displayHeader = $this->container->getDisplayHeader();
        $this->displayHeader->setTitle("StreetScooter Cloud Systems : Mitarbeiter/in Konten verwalten");
        $this->displayHeader->enqueueJs("sts-jquery-steps", "js/jquery.steps.js");
        $this->displayHeader->enqueueJs("sts-custom-mitarbeiter", "js/sts-custom-mitarbeiter.js");
        $this->displayHeader->enqueueJs("zxcvbn", "js/zxcvbn.js");
        $this->displayHeader->enqueueStylesheet("css-jquery-steps", "css/jquery.steps.css");

        $this->action = $this->requestPtr->getProperty('action');

        if (isset($this->action))
            call_user_func(array($this, $this->action));


        $qform_mt = new QuickformHelper ($this->displayHeader, "fps_dep_add_edit_form");

        $save_new_dep = $this->requestPtr->getProperty('save_new_dep');
        $save_exist_dep = $this->requestPtr->getProperty('save_exist_dep');

        $this->deputies = $this->ladeLeitWartePtr->allUsersPtr->getDeputies($this->user, $role, $this->div);

        if ($save_new_dep) {
            try {
                $msgs[] = $this->ladeLeitWartePtr->allUsersPtr->saveDeputy($qform_mt, $this->user, $this->div, $zspl, $role);

            } catch (Exception $e) {
                $msgs[] = $e->getMessage();
                $addDep = "y";

            }

        } else if ($save_exist_dep) {
            try {
                $msgs[] = $this->ladeLeitWartePtr->allUsersPtr->saveDeputy($qform_mt, $this->user, $this->div, $zspl, $role);

            } catch (Exception $e) {
                $msgs[] = $e->getMessage();
                $editDep = "y";
            }
        } else if ($deldep && $this->user->user_can('newusers')) {
            $msgs[] = $this->ladeLeitWartePtr->allUsersPtr->deleteDeputy($qform_mt, $this->user, $this->div, $zspl);
        } else {
            if ($addDep == "y") {
                if (isset($role) && $role == 'fpv') {
                    $zspl = $this->requestPtr->getProperty('zspl');
                    $givenemail = $this->requestPtr->getProperty('givenemail');
                    $qform_mt->fps_deputies_add_edit($role, false, null, $this->user, $givenemail, $zspl);

                } else
                    $qform_mt->fps_deputies_add_edit('', false, null, $this->user);
            } else if ($editDep == "y") {
                $dep_id = (int)$this->requestPtr->getProperty('id');
                $deputy_ids = array_column($this->deputies, 'id');
                if (is_numeric($userid) && in_array($dep_id, $deputy_ids))
                    $editThisDep = $this->ladeLeitWartePtr->allUsersPtr->getFromId($dep_id);
                else
                    $this->msgs[] = 'Keine Berechtigung!';
                $qform_mt->fps_deputies_add_edit('', true, $editThisDep, $this->user);
            }
        }

        $this->displayHeader->enqueueJS("jquery-get-user", "js/jquery.user_cs_picker.js");
        $this->displayHeader->printContent();
        $this->printContent();
    }


    function getSmimeKeyDP($email) {
        $email = strtolower($email);
        $certname = str_replace(array('.', '@'), array('_', '-AT-'), $email);

        $dpcertname = null;
        $certificates_directory = opendir('/var/www/teo-backend/certificates');
        if ($certificates_directory !== false) {
            while (($file = readdir($certificates_directory)) !== false) {

                if (stripos($file, $certname) !== FALSE) {
                    $dpcertname = $file;
                }
            }

        }


        if (!isset($dpcertname)) {
            if (stripos($email, 'deutschepost.de') !== false || stripos($email, 'dpdhl.com') !== false) //isset($emailid[1]) && $emailid[1]=='deutschepost.de')
            {
                // create curl resource
                $ch = curl_init();

                // set url
                curl_setopt($ch, CURLOPT_URL, "http://keyserver.dhl.com/download.php?id=eds-$email");

                $fp = fopen('/var/www/teo-backend/certificates/' . $certname . ".cer", "w");

                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_HEADER, 0);

                // $output contains the output string
                curl_exec($ch);

                // close curl resource to free up system resources
                curl_close($ch);
                fclose($fp);
                $dpcertname = $certname . '.cer';
            }
        }

        if ($dpcertname)
            $dpcert = file_get_contents("/var/www/teo-backend/certificates/" . $dpcertname, "r");
        else
            $dpcert = '';

        if (strpos($dpcert, '-----BEGIN CERTIFICATE-----') !== false)
            return "/var/www/teo-backend/certificates/" . $dpcertname; //SwiftMailer needs the filename and not the certificate as a string
        else
            return false;
    }

    function saveneu($requestPtr = null) {
        if (!isset($requestPtr))
            $requestPtr = $this->requestPtr;

        $deprole = $requestPtr->getProperty('role'); // $deprole is set only if FPS creats FPV deputy
        $inheritedRoles = $requestPtr->getProperty('inheritedRoles');
        $notifications = $requestPtr->getProperty('notifications');
        $privileges = $requestPtr->getProperty('privileges');
        $depprivilege = serialize($privileges);
        $depemail = $requestPtr->getProperty('email');
        $passwd = $requestPtr->getProperty('passwd');
        $depusername = $requestPtr->getProperty('depusername');
        $workshop_id = $requestPtr->getProperty('workshop_id');
        $division_id = $this->div;

        if (empty($deprole))
            $deprole = $this->user->getUserRole();  //if this condition is true then we create a deputy with the same role as the current user

        if ($inheritedRoles && count($inheritedRoles)) {
            $iRole = array_search($deprole, $inheritedRoles);
            if ($iRole) {
                unset ($inheritedRoles[$iRole]);
                array_unshift($inheritedRoles, $deprole);
            } else {
                $deprole = $inheritedRoles[0];
            }
        } else {
            $inheritedRoles = [$deprole];
        }


        $names = explode('.', $depusername);
        $newdep = array(
            "username" => $depusername,
            "privileges" => $depprivilege,
            "email" => $depemail,
            "passwd" => $passwd,
            "addedby" => $this->user->getUserId(),
            "fname" => $names[0],
            "lname" => (isset($names[1]) ? $names[1] : ''),
            "notifications" => $notifications,
        );

        $locallist = array('127.0.0.1', "::1");
        if(!in_array($_SERVER['REMOTE_ADDR'], $locallist)) {
            $ssl = 'https://';
        } else {
            $ssl = 'http://';
        }

        if (!$this->user->user_can('newusers') || in_array('admin', $inheritedRoles)) {
            header("Location:". $ssl . $_SERVER['HTTP_HOST'] . "/error.php");
            exit;            
            // exit(0);
        }

        if (in_array('fuhrparksteuer', $inheritedRoles) || in_array('fpv', $inheritedRoles)) {
            if ($this->user->getAllowChangeDiv() && $requestPtr->getProperty('allowChangeDiv', false))
                $division_id = -1;
            else
                if (!$division_id)
                    $division_id = $this->user->getAssignedDiv(true);

            $newdep['division_id'] = $division_id;
        }

        if (in_array('fpv', $inheritedRoles)) {
            if ($this->user->getAllowChangeZspl() && $requestPtr->getProperty('allowChangeDepot', false)) {
                $newdep['zspl_id'] = -1;
            } else {
                $depzspl = $requestPtr->getProperty('zspl_id_dep'); //this variable exists only  if FPS creats FPV deputy

                if (!isset($depzspl) || empty($depzspl)) {
                    $depzspl = $this->user->getAssignedZspl($division_id); //added this to enable saving of zsplid for deputies of FPVs
                }

                $newdep['zspl_id'] = $depzspl;
            }
        }

        if (in_array('workshop', $inheritedRoles)) {
            if ($this->user->getAllowChangeZspl() && $requestPtr->getProperty('allowChangeWorkshop', false))
                $workshop_id = -1;
            else
                if (empty ($workshop_id))
                    $workshop_id = $this->user->getAssignedWorkshop(true);

            $newdep['workshop_id'] = $workshop_id;
        }


        $newdep['role'] = implode(',', $inheritedRoles);


        include $_SERVER['STS_ROOT'] . "/html/datenschutzerklaerung-email.php";
        $rawmail = "
Sehr geehrte Damen und Herren,
Hiermit erhalten Sie Ihre Login-Daten zum StreetScooter-Cloud-System. Bitte lesen Sie die folgenden Datenschutzhinweise.

$privacyContent


IHRE ZUGANGSDATEN:
=================================================
Neues Konto:   $depusername
Benutzer Name: $depusername
%PASSWORDTEXT%

   Mit freundlichen Grüßen
   Ihr StreetScooter Cloud-System Team

--------------------------------------------------------------------------------------
Diese Mail wurde automatisch generiert. Eine Antwort ist nicht möglich.
Bitte wenden Sie sich bei Fragen an ticket@streetscooter-cloud-system.eu

";
        $mailmsg = str_replace("\n", "\r\n", str_replace("\r\n", "\n", $rawmail));

        $status = array();
        if ($this->ladeLeitWartePtr->allUsersPtr->add($newdep))
            $status['user'] = "1";


        $to = $depemail;
        $toname = $depusername;
        $subject = 'Ihr Neues Konto streetscooter-cloud-system.eu';
        $publickey = $this->getSmimeKeyDP($depemail);

        if ($publickey) {
            $mailtext = str_replace('%PASSWORDTEXT%', "Ihr neues Passwort : $passwd", $mailmsg);
            $mailer = new MailerSmimeSwift ($to, $toname, $subject, $mailtext, $publickey);
        } else {
            echo json_encode($status);
            exit(0);
        }
        if (isset($mailer) && $mailer)
            $status['email'] = "1";

        $publickey = $this->getSmimeKeyDP($this->user->getUserEmail());

        if ($publickey) {
            $mailtext = str_replace('%PASSWORDTEXT%', "", $mailmsg);
            $mailer = new MailerSmimeSwift ($this->user->getUserEmail(), $this->user->getUserFullName(), 'Neues Konto ' . $depusername . ' streetscooter-cloud-system.eu', $mailtext, $publickey);
        }
        $mailer = 1;
        if (isset($mailer) && $mailer) //@todo what if only one email was sent?
            $status['email'] = "1";
        else
            unset($status['email']);

        echo json_encode($status);
        exit(0);

        $this->action = null;
    }


    function saveexist() {

        $savecols = array();
        $savevals = array();
        $dep_id = (int)$this->requestPtr->getProperty('deputy');
        $account_action = $this->requestPtr->getProperty('accountaction');
        $result = array('titlestr' => 'Fehler', 'contentstr' => 'Fehler');

        $extramessage = '';

        if ($account_action == 'op_deleteuser') {
            $delete_user_confirm = $this->requestPtr->getProperty('delete_user_confirm');

            if ($delete_user_confirm == 1 && $this->user->user_can('newusers')) {

                if ($this->ladeLeitWartePtr->allUsersPtr->deleteDeputy($dep_id))
                    $result = array('titlestr' => 'Status', 'contentstr' => 'Konto gelöscht');
                else
                    $result = array('titlestr' => 'Fehler', 'contentstr' => 'Konto konnte nicht gelöscht werden!');
            }

        } else if ($account_action == 'op_key') {
            if ($this->user->deleteToken($this->user->getUserId()) === false) {
                $result = array('titlestr' => 'Fehler', 'contentstr' => 'Fehler beim alten Schlüssel Löschen! Bitte melden an support@streetscooter-cloud-system.eu ');
            } else {
                $keyfilepath = $this->user->saveNewToken($this->user->getUserId());

                $keyicon = '<span class="error_msg">Ihr zuvor über diese Funktion heruntergeladenen Schlüssel sind nun ungültig. Ersetzen Sie sie bitte durch diesen.</span><br><br>';

                $filename = explode('/', $keyfilepath);
                if (isset($filename[2]))
                    $keyicon .= '<a href="/downloadkey.php?fname=' . $filename[2] . '"><span class="genericon genericon-key"></span><span>Ihr neuen Schlüssel herunterladen</span></a>';
                else
                    $keyicon .= "Fehler beim Schlüssel Herunterladen. Bitte melden an support@streetscooter-cloud-system.eu ";

                $result = array('titlestr' => 'Neuen Schlüssel herunterladen', 'contentstr' => '<strong>Neuen Schlüssel herunterladen</strong><br><br>' . $keyicon);
            }
        } else {
            if ($account_action == 'op_useremail') {
                $savecols[] = 'email';
                $savevals[] = $this->requestPtr->getProperty('email');

            } else if ($account_action == 'op_privileges') {
                $savecols[] = 'privileges';
                $privileges = $this->requestPtr->getProperty('privileges');
                $savevals[] = serialize($privileges);

            } else if ($account_action == 'op_passwd') {
                $savecols[] = 'passwd';
                $savevals[] = $passwd = $this->requestPtr->getProperty('passwdone');

            } else if ($account_action == 'op_konto') {
                $savecols[] = 'passwd';
                $savevals[] = $passwd = $this->requestPtr->getProperty('passwd');
                $this->ladeLeitWartePtr->allUsersPtr->deleteToken($dep_id);
                $deputy = $this->ladeLeitWartePtr->allUsersPtr->getFromId($dep_id);
                $publickey = $this->getSmimeKeyDP($deputy['email']);

                $mailmsg = "Sehr geehrte Damen und Herren, \r\n \r\n \r\n \r\nIhr Konto wurde zurückgesetzt und der bisherige Schlüssel gelöscht. Bitte vergessen Sie nicht, Ihren neuen Schlüssel beim ersten Einloggen herunterzuladen.
							\r\nIhr Benutzername : " . $deputy['username'] .
                    "\r\nIhr neues Passwort : " . $passwd .
                    "\r\n\r\nMit freundlichen Grüßen," .
                    "\r\n\r\nIhr StreetScooter Cloud-System Team" .
                    "\r\nDiese Mail wurde automatisch generiert. Eine Antwort ist nicht möglich.\r\n\r\n" .
                    "Bitte wenden Sie sich bei Fragen an support@streetscooter-cloud-system.eu";
                if ($publickey)
                    $mailer = new MailerSmimeSwift ($deputy['email'], $deputy['fname'] . ' ' . $deputy['lname'], 'Ihr Konto ist zurückgesetzt', $mailmsg, $publickey);
                $extramessage = '';
                if (!isset($mailer) || $mailer === false) {
                    $result['email'] = 1;
                    $extramessage = '<br>Email wurden versandt. <br>Bitte teilen Sie dieses Passwort auf sicherem Weg z.B. in eine verschlüsselte E-Mail dem/-der Benutzer/-in mit.<br><br><strong>Benutzername </strong> : ' . $deputy['username'] . '<h2>' . $passwd . '</h2>';
                }
                $mailmsg = "Sehr geehrte Damen und Herren, \r\n \r\n \r\n \r\nKonto " . $deputy['username'] . " ist zurückgesetzt. Der aktuelle Schlüssel ist gelöscht. Der neue Schlüssel muss beim ersten Einloggen heruntergeladen werden.
							\r\n" . $deputy['username'] .
                    "\r\n\r\nMit freundlichen Grüßen," .
                    "\r\n\r\nIhr StreetScooter Cloud-System Team" .
                    "\r\nDiese Mail wurde automatisch generiert. Eine Antwort ist nicht möglich.\r\n\r\n" .
                    "Bitte wenden Sie sich bei Fragen an support@streetscooter-cloud-system.eu";
                $publickey = $this->getSmimeKeyDP($this->user->getUserEmail());
                if ($publickey)
                    $mailer = new MailerSmimeSwift ($this->user->getUserEmail(), $this->user->getUserFullName(), $deputy['username'] . ' Konto ist zurückgesetzt', $mailmsg, $publickey);
                if (!isset($mailer) || $mailer === false) {
                    $result['email'] = 1;
                    if (!empty($extramessage))
                        $extramessage = '<br>Keine Emails wurden versandt. <br>Bitte teilen Sie dieses Passwort auf sicherem Weg z.B. in eine verschlüsselte E-Mail dem/-der Benutzer/-in mit.<h2>' . $passwd . '</h2>';
                    else
                        $extramessage .= '<br>Email wurde an ' . deputy['email'] . ' versandt. Keine Emails wurden an  ' . $this->user->getUserEmail() . '<br>Das neue Passwort.<h2>' . $passwd . '</h2>';
                }

            } else if ($account_action == 'op_notifications') {
                $savecols[] = 'notifications';
                $savevals[] = $this->requestPtr->getProperty('notifications');

            }

            if ($this->ladeLeitWartePtr->allUsersPtr->save($savecols, $savevals, array('id', '=', $dep_id)))
                $result = array('titlestr' => 'Status', 'contentstr' => 'Änderungen gespeichert!' . $extramessage);

        }

        echo json_encode($result);


        exit(0);

    }

    /**
     *
     * ajaxGetUserInfo
     * 'userid' passed as POST variable from jquery
     * returns false if no data is found
     * returns user data as json string if the username is found
     * @return string json encoded user data or 'false'
     */
    function ajaxGetUserInfo() {
        $userid = $this->requestPtr->getProperty('userid');
        $deputy_ids = array_column($this->deputies, 'id');

        if (is_numeric($userid) && in_array($userid, $deputy_ids))
            $user = $this->ladeLeitWartePtr->allUsersPtr->getFromId($userid, array('privileges', 'email', 'notifications', 'username'));

        if ($user) {
            if ($user['privileges'])
                $user['privileges'] = unserialize($user['privileges']);
            $allowed_privileges = $this->ladeLeitWartePtr->allUsersPtr->getPrivileges($this->user->getUserRole(), $this->user);
            //first argument has to be $user['privileges'] since we want to preserve 1|0 and not the label of the privilege which exists in $allowed_privileges
            $user['privileges'] = array_intersect_key($user['privileges'], $allowed_privileges);
            echo json_encode($user);
        } else
            echo 'false';
        exit(0);
    }

    /**
     *
     * ajaxCheckUserName
     * 'depusername' passed as POST variable from the jquery validation
     * returns false if the username exists already
     * returns true if the username does not exist and can be used for the new deputy
     * @return string 'true' or 'false'
     */
    function ajaxCheckUserName() {
        $username = $this->requestPtr->getProperty('depusername');
        $existuser = $this->ladeLeitWartePtr->allUsersPtr->getFromUserName($username);
        if ($existuser)
            echo 'false';
        else
            echo 'true';
        exit(0);
    }

    /**
     *
     * ajaxSuggestUserName
     * 'depusername' passed as POST variable from the jquery validation
     * returns false if the username exists already
     * returns true if the username does not exist and can be used for the new deputy
     * @return string 'true' or 'false'
     */
    function ajaxSuggestUserName() {
        $username = $this->requestPtr->getProperty('depusername');
        $existuser = $this->ladeLeitWartePtr->allUsersPtr->getFromUserName($username);
        if (!empty($existuser)) {
            $cnt = 1;
            while (!empty($existuser)) {

                $existuser = $this->ladeLeitWartePtr->allUsersPtr->getFromUserName($username . $cnt);
                $cnt++;
            }
            $cnt--;
            $username .= $cnt;
        }

        echo $username;
        exit(0);
    }

    /**
     * neu
     * Used to generate the wizard for creating a new deputy
     */
    function neu() {
        //$role and $zspl are !empty only when a FPS user tries to create a FPV deputy
        $role = $this->requestPtr->getProperty('role');
        $zspl = $this->requestPtr->getProperty('zspl');
        $givenemail = $this->requestPtr->getProperty('givenemail');

        if (empty($role)) $role = $this->user->getUserRole();

        $this->qform_new = new QuickformHelper ($this->displayHeader, "fps_dep_add_edit_form");
        $privileges = $this->ladeLeitWartePtr->allUsersPtr->getPrivileges($role, $this->user);

        $this->qform_new->wizard_new_user($privileges, $this->user, $role, $zspl, $givenemail);

    }

    /**
     * existing
     * Used to generate the wizard for editing an existing user
     */
    function aktuelle() {
        $role = $this->user->getUserRole();

        $deputies = $this->ladeLeitWartePtr->allUsersPtr->getDeputies($this->user, $role, $this->div);

        $this->qform_exist = new QuickformHelper ($this->displayHeader, "fps_dep_edit_form");
        $privileges = $this->ladeLeitWartePtr->allUsersPtr->getPrivileges($role, $this->user);

        $listOptions = array('' => '');
        foreach ($deputies as $deputy)
            $listOptions[$deputy['id']] = $deputy['username'] . ' ( ' . $deputy['email'] . ' )';
// 			$listOptions[$deputy['id']]=$deputy['username'].' / '.$deputy['email'].'/'.$deputy['role'].'/'.$deputy['division_id'].'/'.$deputy['zspl_id'];

        $userid = $this->requestPtr->getProperty('id');

        $deputy_ids = array_column($this->deputies, 'id');
        if (is_numeric($userid) && (in_array($userid, $deputy_ids) || $userid == $this->user->getUserId())) //not just deputies but also used for editing own profile
            $depuser = $this->ladeLeitWartePtr->allUsersPtr->getFromId($userid); //when FPS is trying to edit existing FPV user
        else $depuser = null;
        $this->qform_exist->wizard_existing_user($listOptions, $privileges, $this->user, $depuser); //userid is passed only in the case of FPS tryign to edit FPV account

    }

    function printContent() {
        include_once("pages/mitarbeiter.php");
    }

    function getFnameLnameEmail($user) {
        return sprintf("%s %s (%s)", $user['fname'], $user['lname'], $user['email']);
    }
}

?>

