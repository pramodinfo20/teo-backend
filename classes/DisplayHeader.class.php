<?php

/**
 * Class for the HTML header display, allows including javascript files and stylesheets
 */
class DisplayHeader extends DisplayHTML {
/**
 * Array of stylesheets where array Key is the stylesheetname and Value is the stylesheet url
 */
protected $stylesheets = [];
/**
 * Array of local styles
 */
protected $local_styles = [];
/**
 * Array of javascript files where array Key is the javascript file name and Value is the javascript file location
 */
protected $jsfiles = [];
/**
 * Array of script tags
 */
protected $scripttags = [];
/**
 * Array of local scripts
 */
protected $local_scripts = [];
/**
 * @var string $title Title of the page
 */
protected $title;
/**
 * Login Form
 */
protected $loginform;
/**
 * Display Elements
 */
protected $disp_elements = [];
/**
 * * @var array $body_classes Body Classes
 */
protected $body_classes = [];
/**
 *
 * @var boolean $user_logged_in Boolean value of whether user is logged in or not
 */
protected $user_logged_in;
/**
 * String with cookie name to check selected language
 */
protected $languageCookieName;

public $translate;

public $controller;

/**
 * Konstruktor
 */
function __construct($userPtr, $controller)
{
    $this->controller = $controller;
    $this->enqueueStylesheet("css2", "css/skeleton.css");
    $this->enqueueStylesheet("css1", "css/style.css");
    $this->enqueueStylesheet("css-genericons", "css/genericons/genericons.css");
    // autocomplete style
    $this->enqueueStylesheet("css-chosen", "css/chosen.min.css");

    // Kalendar CSS
    $this->enqueueStylesheet("css-calendar", "css/calendar.css");

    $this->enqueueStylesheet("css-jquery-ui", "js/newjs/jquery-ui.css");
    $this->enqueueStylesheet("css-jquery-ui-struct", "js/newjs/jquery-ui.structure.css");
    $this->enqueueStylesheet("css-jquery-ui-theme", "js/newjs/jquery-ui.theme.css");

    $this->enqueueJs("sts-tools", "js/sts-tools.js");
    $this->enqueueJs("sts-jquery", "js/jquery-2.2.0.min.js");
    $this->enqueueJs("sts-jquery-ui", "js/newjs/jquery-ui.min.js");


    $this->enqueueJs("sts-custom-js", "js/sts-custom.js");
    $this->enqueueJs("jquery-validation", "js/jquery.validate.min.js");

    $this->enqueueStylesheet('tablesorter-default', "css/theme.default.css");
    $this->enqueueJs("jquery-tablesorter", "js/jquery.tablesorter.min.js");
    $this->enqueueJs("jquery-tablesorter-pager", "js/jquery.tablesorter.pager.js");
    $this->enqueueJs("jquery-tablesorter-widgets", "js/jquery.tablesorter.widgets.js");

    // autocomplete JS
    $this->enqueueJs("jquery-chosen", "js/chosen.jquery.min.js");

    $this->userPtr = $userPtr;
    $this->user_logged_in = $userPtr->loggedin();
    $this->translate = (new LegacyTranslations())->getTranslationsForDomain();
    $this->languageCookieName = $GLOBALS['config']->get_property('languageCookieName', 'selectedSystemLanguage');
}

//******************************************************************************************
/**
 * push a stylesheet into the array $this->stylesheets
 *
 * @param string $stylesheetname
 * @param string $stylesheeturl
 */
function enqueueStylesheet($stylesheetname, $stylesheeturl)
{
    $this->stylesheets [$stylesheetname] = $stylesheeturl . '?' . filesize($stylesheeturl);
}
//******************************************************************************************
/**
 * remove all stylesheet files with regex. matching name
 *
 * @param string $pattern
 */
function removeStylesheet($pattern)
{
    $keys = array_keys($this->stylesheets);
    foreach ($keys as $key) {
        if (preg_match($pattern, $key))
            unset ($this->stylesheets [$key]);
    }
}
//******************************************************************************************
/**
 * push a style tag into the array $this->local_styles
 *
 * @param string $scripttag
 */
function enqueueLocalStyle($styletag)
{
    if ($styletag != "")
        $this->local_styles [] = $styletag;
}
//******************************************************************************************
/**
 * push a javascript file into the array $this->jsfiles
 *
 * @param string $jsname
 * @param string $jsurl
 */
function enqueueJs($jsname, $jsurl)
{
    $this->jsfiles [$jsname] = $jsurl . '?filever=' . @filesize($jsurl);
}
//******************************************************************************************
/**
 * remove all javascript files with regex. matching name
 *
 * @param string $pattern
 */
function removeJs($pattern = '.*')
{
    $keys = array_keys($this->jsfiles);
    foreach ($keys as $key) {
        if (preg_match("/$pattern/i", $key))
            unset ($this->jsfiles [$key]);
    }
}
//******************************************************************************************
/**
 * push a script html tag into the array $this->scripttags
 *
 * @param string $scripttag
 */
function enqueueScriptTags($scripttag)
{
    $this->scripttags [] = $scripttag;
}

//******************************************************************************************
/**
 * remove all javascript tags with regex. matching src-attribute
 *
 * @param string $pattern
 */
function removeScriptTags($pattern = '.*')
{
    $keys = array_keys($this->scripttags);
    foreach ($keys as $key) {
        if (preg_match("/src=\"$pattern\"/i", $this->scripttags[$key]))
            unset ($this->scripttags[$key]);
    }
}

//******************************************************************************************

/**
 * push a local script into the array $this->local_scripts
 *
 * @param string $script
 */
function enqueueLocalJs($script)
{
    $this->local_scripts [] = $script;
}
//******************************************************************************************

/**
 * set the title tag for the page
 *
 * @param string $title
 */
function setTitle($title)
{
    $this->title = $title;
}

//******************************************************************************************
/**
 * add a display element, can be predefined in the printContent() function or defined by also passing value for the $displayelementval
 *
 * @param string $displayelementkey identifying key for a display element
 * @param string $displayelementval has to be passed if the content for the $displayelementkey is not already defined in the printContent() function below
 */
function addele($displayelementkey, $displayelementval = '')
{
    $this->disp_elements[$displayelementkey] = $displayelementval;
}

function setClass($classname)
{
    $this->body_classes[] = $classname;
}


/**
 * Output the content of the header
 */
function printHtmlHead ($options = '')
{
global $IS_DEBUGGING;


?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <title><?php echo $this->title ?></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    foreach ($this->stylesheets as $stylesheet)
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $stylesheet . "\">\n";

    foreach ($this->jsfiles as $javascriptfile)
        echo "<script type=\"text/javascript\" src=\"" . $javascriptfile . "\"></script>\n";

    foreach ($this->scripttags as $scripttag)
        echo $scripttag . "\n";

    if (count($this->local_scripts)) {
        echo "<script>\n";
        foreach ($this->local_scripts as $script)
            echo $script . "\n";
        echo "</script>\n";
    }

    if (count($this->local_styles)) {
        echo "<style>\n";
        foreach ($this->local_styles as $styletag)
            echo "    " . $styletag . "\n";
        echo "</style>\n";
    }
    if ($GLOBALS['debug']['debugout'] && !strpos("#$options", 'nodebug'))
        echo "
<script>
    function DebugOut (aString)  {
        var divbug= document.getElementById('debugview');
        divbug.style.visibility = 'visible';
        var divbug= document.getElementById('debugarea');
        divbug.innerHTML += aString + '<br>';
    }

    function HideDebugView () {
    	document.getElementById('debugview').style.visibility='hidden';
    }

    function ClearDebugView () {
    	document.getElementById('debugarea').innerHTML = '';
    }
</script>
";
    else
        echo '<script> function DebugOut (aString) {} </script>
	<link rel="icon" type="image/png" href="favicon.ico">';

    echo '</head>';
    }



    /**
     * Output the content of the header
     */
    function printContent($options = '')
    {

    if (!strpos("#$options#", 'nohead')) {
        $this->printHtmlHead($options);
    }

    $bodyClasses = (is_array($this->body_classes)) ? implode(',', $this->body_classes) : "";
    if ($_SERVER['SERVER_NAME'] != 'streetscooter-cloud-system.eu')
      $_domain = '<span class="domain">' . $_SERVER['SERVER_NAME'] . '</span>';
    echo <<<HEREDOC
<body class="$bodyClasses">
  <div class="pagewrap">
    <div class="container">

HEREDOC;

    if (!strpos("#$options#", 'nologo')) {
        echo <<<HEREDOC
		<div class="row">
			<div class="six columns div4logo">
				<a href="https://{$_SERVER['SERVER_NAME']}/"><img src="images/Logo_StreetScooter_Long.svg"
					class="sts_logo" alt="StreetScooter"></a>{$_domain}
			</div>

HEREDOC;

        if ($GLOBALS['debug']['debugout']) {
            echo <<<HEREDOC_DEBUGOUT
   			<div id="debugframe" style="position: fixed; top:0;left:0px;width:100%;height:128px;visibility:hidden;display:flex;align-items: center;justify-content:center;z-index:100; ">
                <div id="debugview" style="position:relative;border: 2px solid #808080; font-family:monospace; margin:4px; height:124px;width:402px;background-color:#f0f0f0;">
                   <input type="button" style="height:20px;width:70px;float:left;padding:0px;" onClick="ClearDebugView()" value="Clear">
		           <input type="button" style="height:20px;width:70px;float:right;padding:0px;margin-right:10px;" onClick="HideDebugView()" value="[x] close">
                   <div id="debugarea" style="height:100px;width:400px;border-top: 2px solid #808080;overflow-y:scroll;"></div>
   			    </div>
  			</div>

HEREDOC_DEBUGOUT;

        }

        echo <<<HEREDOC
			<div class="six columns div4logo" >			
			    <div class="one columns float-right languages-flags-wrapper" >
			        <a href="#" id="language-german">
			            <img src="images/german_flag.png" alt="german-language-flag">
			        </a><br>
			        <a href="#" id="language-english">
			            <img src="images/british_flag.png" alt="british-language-flag">
			        </a>
		        </div>	    
                <div class="six columns float-right" >
			        <img src="images/dplogo.svg" class="dp_logo" alt="StreetScooter">
			    </div>
			</div>
		</div>

HEREDOC;

    }


    if (strpos("#$options#", 'nomenu'))
        return;

    if (strpos("#$options#", 'sessionExpired')) {
        echo "<style> .pagewrap  {display: none;}
                              .page_footer {display: none; } </style>";
        echo '<script>$("<div>'.
            $this->translate['PageController']['sessionExpiration'].
            '</div>").dialog({
                        autoOpen: true, 
                        title: "Error",
                        close: function() {
                            window.location.href = "index.php";
                        },
                        buttons: {
                            "' . $this->translate['PageController']['reload'] . '" : function() {
                                                            window.location.href = "index.php";
                             }
                             }
                    });</script>';
    }
    if ($this->user_logged_in):
        ?>
        <div class="row user_login_info">
            <div class="six columns" style="text-align: left; padding:9px 0; white-space: nowrap;">
		  <span><?php
              echo $this->translate['BlackHeader']['itemRole'].': ';
              $action = '';

              if (isset($_REQUEST['action']))
                  $action = $_REQUEST['action'];

              $roleList = [];
              if ($this->userPtr)
                  $roleList = $this->userPtr->getListOfUserRoles();

              $current_role = $this->userPtr->getUserRole();
              $numRole = 0;

              if (count($roleList) > 1) {
                  echo <<<HEREDOC
<select id="selectRole" OnChange="javascript:var index=this.selectedIndex;var role=this.options[index].value;document.location.href='{$_SERVER['PHP_SELF']}?setrole='+role + '&action=home';">
HEREDOC;
                  echo '<option value="" selected disabled hidden>Select Role</option>';

                  foreach ($roleList as $role) {
                      $optSel = (($role == $current_role) ? " selected" : "");
                      $label = $this->userPtr->getUserRoleLabel($role);
                      if (!empty ($label)) {
                          echo "<option value=\"$role\"$optSel>$label</option>";
                          $numRole++;
                      }
                  }
                  echo "</select>\n";
              } else {
                  echo $this->disp_elements["userinfo"]["user_role_label"];
              }


              if (($current_role == 'fuhrparksteuer') || ($current_role == 'fpv')) {
                  if ($this->userPtr->getAllowChangeDiv()) {
                      $current_division = $this->userPtr->getAssignedDiv(true);
                      $divListe = $this->userPtr->getSwitchableDivs();

                      echo <<<HEREDOC
<select id="selectDivision" OnChange="javascript:var index=this.selectedIndex;var division=this.options[index].value;document.location.href='{$_SERVER['PHP_SELF']}?action=$action&setdivision='+division;">
HEREDOC;
                      foreach ($divListe as $division_id => $name) {
                          if (empty ($current_division))
                              $current_division = $division_id;
                          $optSel = (($division_id == $current_division) ? " selected" : "");
                          echo "<option value=\"$division_id\"$optSel>$name</option>";
                      }
                      echo "</select>";
                  } else {
                      if (!empty($this->disp_elements["userinfo"]["assigned_location"]))
                          echo "-" . $this->disp_elements["userinfo"]["assigned_location"];
                  }
              }

              if ($current_role == 'fpv') {
                  if ($this->userPtr->getAllowChangeZspl()) {
                      $current_zspl = $this->userPtr->getAssignedZspl();
                      $zsplListe = $this->userPtr->getSwitchableZspls($current_division);

                      if (count($zsplListe) > 0) {

                          echo <<<HEREDOC
<select id="selectZspl" OnChange="javascript:var index=this.selectedIndex;var zspl=this.options[index].value;document.location.href='{$_SERVER['PHP_SELF']}?action=$action&setzspl='+zspl;">
HEREDOC;
                          foreach ($zsplListe as $zspl_id => $name) {
                              $optSel = (($zspl_id == $current_zspl) ? " selected" : "");
                              echo "<option value=\"$zspl_id\"$optSel>$name</option>";
                          }
                          echo "</select>";
                      }
                  } elseif (($current_role != 'fuhrparksteuer')) {
                      if (!empty($this->disp_elements["userinfo"]["assigned_location"]))
                          echo "-" . $this->disp_elements["userinfo"]["assigned_location"];
                  }
              }

              if ($current_role == 'workshop') {
                  if ($this->userPtr->getAllowChangeWorkshop()) {
                      $current_workshop = $this->userPtr->getAssignedWorkshop();
                      $workshopListe = $this->userPtr->getSwitchableWorkshops();

                      if (count($workshopListe) > 1) {

                          echo <<<HEREDOC
<select id="selectWorkshop" OnChange="javascript:var index=this.selectedIndex;var workshop=this.options[index].value;document.location.href='{$_SERVER['PHP_SELF']}?action=$action&setworkshop='+workshop;">
HEREDOC;
                          foreach ($workshopListe as $workshop_id => $name) {
                              $optSel = (($workshop_id == $current_workshop) ? " selected" : "");
                              echo "<option value=\"$workshop_id\"$optSel>$name</option>";
                          }
                          echo "</select>";
                      }
                  } else {
                      $workshopListe = $this->userPtr->getSwitchableWorkshops();
                      if ($workshopListe)
                          echo "-" . $workshopListe['name'];
                  }

              }

              if ($this->userPtr->CanAdmin()) {
                  $checked = '';
                  $class = 'user_level';
                  if ($this->userPtr->IsAdmin()) {
                      $checked = 'checked';
                      $class = 'admin_level';
                  }

                  echo <<<HEREDOC
<input type="checkbox" $checked OnCLick="var is_admin=(this.checked ? 1:0);document.location.href='{$_SERVER['PHP_SELF']}?action=$action&set_admin='+is_admin;"><span class="$class">ADMIN</span>
HEREDOC;
              }
              ?>
          </span>

                <span style="margin-left: 40px;"><?php echo $this->translate['BlackHeader']['itemLoggedAs'].': '.
                        $this->disp_elements["userinfo"]['userfullname']; ?></span>
            </div>
            <div class="six columns" style="text-align: right; padding:6px 0">
                <?php if ($this->disp_elements["userinfo"]['userrole'] == "zentrale") {
                    ?>
                    <span style="text-align: right; width: 10%;"><a href="?page=fileshare"
                                                                    class="editprofile"><?php echo $this->translate['BlackHeader']['itemFiles'] ?></a></span>
                    <span style="text-align: right; width: 5%;">|</span>
                <?php }
                ?>
                <span style="text-align: right; width: 10%;"><a href="?page=anleitungen"
                                                                class="editprofile"><?php echo $this->translate['BlackHeader']['itemInstructions'] ?></a></span>
                <span style="text-align: right; width: 5%;">|</span>
                <?php if ($this->userPtr->user_can('newusers')) { ?>
                    <span style="text-align: right; width: 15%;"><a href="?page=mitarbeiter&action="
                                                                    class="editprofile"><?php echo $this->translate['BlackHeader']['itemManageEmployeesAccounts'] ?></a></span>
                    <span style="text-align: right; width: 5%;">|</span>
                <?php } ?>
                <?php if ($this->userPtr->CanAdmin()) { ?>
                <span style="text-align: right; width: 15%;"><a
                            href="?action=userKeys"
                            class="editprofile"><?php echo $this->translate['BlackHeader']['itemUserKeys'] ?></a></span>
                <span style="text-align: right; width: 5%;">|</span>
                <?php } ?>
                <span style="text-align: right; width: 15%;"><a
                            href="?page=mitarbeiter&action=aktuelle&id=<?php echo $this->disp_elements["userinfo"]["userid"]; ?>"
                            class="editprofile"><?php echo $this->translate['BlackHeader']['itemProfileEdit'] ?></a></span>
                            <span style="text-align: right; width: 5%;">|</span>
                <span style="text-align: right; width: 10%;"><a href="?page=feedback&id=<?php echo $this->disp_elements["userinfo"]["userid"]; ?>"
                                                                class="editprofile">Feedback</a></span>
                        
                <span style="text-align: right; width: 15%;"><a href="?page=logout"
                                                                class="logoutbutton"><?php echo $this->translate['BlackHeader']['itemLogout'] ?></a></span>
            </div>
        </div>

    <?php

    else:
        ?>
        <div class="banner">
            <div class="container">
                <div class="row loginerror">
                    <?php if (isset($this->disp_elements['errormsgs']))
                        echo $this->disp_elements['errormsgs']; ?>
                </div>
                <div class="row">
                    <?php echo $this->login_form->printContent(); ?><br><br>
                </div>

            </div>
        </div>

    <?php endif; ?>

    <div class="container">

        <?php if ($this->user_logged_in && isset($_GET['page']) && $_GET['page'] != 'mitarbeiter'): ?>

            <div class="row">
                <div class="twelve columns">
                    <a href="index.php">Home</a>
                </div>
            </div>
        <?php endif;
        if (isset($this->disp_elements["userinfo"]["user_first_login"]) && $this->disp_elements["userinfo"]["user_first_login"] !== false):
            ?>
            <div class="row">
                <div class="twelve columns">
                    <?php
                    $keyfilepath = $this->disp_elements["keyfilename"];
                    $filename = explode('/', $keyfilepath);
                    if (isset($filename[2]))
                        $keylink = '<a href="/downloadkey.php?fname=' . $filename[2] . '"><span class="genericon genericon-key"></span><span> hier klicken </span></a>';
                    else
                        $keylink = "Fehler beim Schlüssel Herunterladen link generieren. Bitte melden an support@streetscooter-cloud-system.eu ";
                    ?><p>Dies ist Ihre erste Anmeldung beim StreetScooter Cloud System.
                        Ein Cookie wurde in Ihren Browser importiert. Dieser wird neben Ihrem Passwort zusätzlich zum
                        Login benötigt und automatisch aus Ihrem Browser gelesen. Sie können nun <?php echo $keylink; ?>
                        und
                        auch später jederzeit über die Funktion „Profil bearbeiten > Schlüssel herunterladen“ nach dem
                        Login den im Cookie enthaltenen Schlüssel als Datei herunterladen.
                        Um sich an einem anderen Rechner, oder mit einem anderen Browser, wo der Cookie noch nicht in
                        Ihrem Browser importiert wurde, oder in den Fall, dass der Cookie
                        aus Ihrem Browser gelöscht wurde, anzumelden, geben bitte den Pfad zu dieser Schlüsseldatei im
                        Feld „Schlüsselpfad“ an. Danach wird der Cookie auch auf diesem Rechner / erneut in Ihrem
                        Browser gespeichert.
                    </p>


                    <p>
                        Bitte ändern Sie Ihr Passwort unter „Profil bearbeiten“ für Ihren Benutzernamen.
                    </p>
                    <p>
                        Bei Fragen wenden Sie sich bitte an „support@streetscooter-cloud-system.eu“.</p>
                </div>
            </div>
        <?php

        endif;

        echo '
		<div class="row"><div id="modal-search">';

        //        $this->controller->createModalSearch()
        ?>


        <style>
            #modal-search {
                display: none;
            }

            .float-right {
                float: right;
            }

            .languages-flags-wrapper {
                margin-top: 15px;
            }
        </style>
        <script type="text/javascript">
            $(document).ready(function () {
                let germanFlag = $('#language-german');
                let englishFlag = $('#language-english');

                germanFlag.click(function () {
                    document.cookie = "<?php echo $this->languageCookieName; ?>=de";

                    location.reload();
                });
                englishFlag.click(function () {
                    document.cookie = "<?php echo $this->languageCookieName; ?>=en";

                    location.reload();
                });
            });
        </script>
        <?php

        echo '</div>';
        }
        }
        ?>
