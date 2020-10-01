<?php

/**
 * Class for the HTML footer display
 */
class DisplayFooter extends DisplayHTML {
    protected $finallyCalls = [];
    protected $finallyVars = [];
    protected $userPtr;
    protected $user_logged_in;
    protected $versionNumber;
    protected $versionText;

    function __construct($userPtr) {

        $this->userPtr = $userPtr;
        $this->user_logged_in = $userPtr->loggedin();
        $this->finallyVars['_urlparams'] = '';
        $this->finallyVars['_this_page'] = $_SERVER['PHP_SELF'];

        $versionFilePath = __DIR__ . '/../version.txt';

        if ( !file_exists($versionFilePath) ) {
            $this->versionText = ('Version file not found.');
        }

        $versionFile = fopen($versionFilePath, "r");

        if ( !$versionFile ) {
            $this->versionText = ('Version file not found.');
        } else {
            $this->versionNumber = fread($versionFile,filesize("version.txt"));
            fclose($versionFile);
            $this->versionText = 'StS CloudSystem v' . $this->versionNumber;
        }

    }

    function enqueueFinallyCalls($source, $tag = '') {
        if (empty ($tag))
            $tag = count($this->finallyCalls);
        $this->finallyCalls[$tag] = $source;
    }


    function enqueueFinallyVars($varname, $value = null) {
        if (isset ($value))
            $this->finallyVars[$varname] = $value;
        else
            unset ($this->finallyVars[$varname]);
    }


    function GetFinallyJS() {

        if ((count($this->finallyCalls) == 0) && (count($this->finallyVars) == 0))
            return '';

        $finallyCalls = "";
        $finallyVars = "";
        foreach ($this->finallyVars as $name => $value)
            $finallyVars .= "var $name='$value';\n";


        if (count($this->finallyCalls)) {
            ksort($this->finallyCalls);
            $finallyCalls = "
function OnFinally ()
{
" . implode("\n", $this->finallyCalls) . "
}

document.addEventListener('DOMContentLoaded', OnFinally);
";
        }

        return "\n<script>\n$finallyVars\n$finallyCalls\n</script>\n";
    }


    function getContent($options = "") {
        $divCookieMsg = "";
        $showPrivacy1 = !$this->user_logged_in && !isset ($_SESSION['hide_msg']['privacy1']);
        $showPrivacy2 = $this->user_logged_in && empty ($_SESSION['sts_cookies_accepted']);

        if ($showPrivacy1)
            include $_SERVER['STS_ROOT'] . "/html/hinweis_cookies.html";

        if ($showPrivacy2)
            include $_SERVER['STS_ROOT'] . "/html/hinweis_cookies-intern.php";


        $this->contentHTML = <<<HEREDOC
        </div><!--row-->
      </div><!--container-->
    </div><!--container-->
  </div><!--pagewrap-->
HEREDOC;

        $bShowTicket = false;
        if (isset ($GLOBALS['pageController'])) {
            if ($user = $GLOBALS['pageController']->GetObject('user')) {
                $myEmail = $user->getUserEmail();
                $bShowTicket = (stristr($myEmail, '@deutsche-post.de') > 0);
            }
        }

        $href_ticket = $bShowTicket ? '<span>Haben Sie Fragen oder ein Problem? Dann wenden Sie sich einfach an <a href="mailto:ticket@streetscooter.eu"><img src="/images/symbols/e-mail.png"> mailto:ticket@streetscooter.eu</a></span>' : '';

        $result = strpos("#$options#", 'nolinks');
        if (!$result) {
            $this->contentHTML .= '
    <div class="page_footer">' . $href_ticket . lf;

            if (isset($user) && $user->loggedin())
                $this->contentHTML .= '
        <span><a href="/html/datenschutzerklaerung.html" target="_blank">Datenschutzerklärung (allgemein)</a></span>
        <span><a href="/html/datenschutzerklaerung-intern.php" target="_blank">Datenschutzerkärung (intern)</a></span>';
            else
                $this->contentHTML .= '
        <span><a href="/html/datenschutzerklaerung.html" target="_blank">Datenschutzerkärung</a></span>';

            $this->contentHTML .= '
       <span><a href="/html/impressum.html" target="_blank">Impressum</a></span>
    </div>
    <div class="version-wrapper" style="position: fixed; bottom: 0; right: 0; padding: 1px 5px; background-color:  
    rgba(255,255,255,0.71); font-size: 11px">' . $this->versionText . '</div>';
        }
        $this->contentHTML .= $this->GetFinallyJS();
        $this->contentHTML .= "
<div id=\"streetscooter-loader-mask\">
        <img src=\"images/streetscooter_signet.png\" alt=\"streetscooter-signet\">
    </div>
    <style>
    #streetscooter-loader-mask{
            display: none;
            align-items: center;
            justify-content: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: #FFFFFFD9;
            animation: loader-mask 0.3s ease-in-out;
            z-index: 99999;
        }
        #streetscooter-loader-mask img{
            width: auto;
            height: 80px;
            -webkit-animation: loader-rotate 2s ease-in-out infinite; /* Safari 4.0 - 8.0 */
            animation: loader-rotate 2s ease-in-out infinite;
            animation-delay: 0.2s;
            opacity: 0;
        }
        .show-flex{
            display: flex;
            opacity: 1;
        }
        @keyframes loader-rotate {
            0% {transform: scale(1); opacity:0}
            20% {transform: scale(1.4); opacity:1}
            80% {transform: scale(1.4) rotateY(360deg); opacity:1}
            100% {transform: scale(1) rotateY(360deg); opacity:0}
        }
        @keyframes loader-mask {
            0% {opacity:0}
            100% {opacity:1}
        }
</style>
  </body>
</html>
";

        return $this->contentHTML;
    }
}

?>