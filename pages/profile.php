<?php
/**
 * profile.php
 * Page for Profile management.
 * @author Pradeep Mohan
 */

$div = $user->getAssignedDiv();

if (!$div) $div = $this->requestPtr->getProperty('div');

if (!$div) $div = 1;

$save_exist_dep = $this->requestPtr->getProperty('save_exist_dep');


$keyicon = '';

$content = $msgs = "";
if (isset($_GET['downloadkey'])) {
    $spantxt_icon = '<span class="genericon genericon-key"></span>';
    $spantxt_text = '<span>Ihr neuen Schlüssel herunterladen</span>';

    $keyicon = '<span class="error_msg">Ihr zuvor über diese Funktion heruntergeladenen Schlüssel sind nun ungültig. Ersetzen Sie sie bitte durch diesen.</span><br>';


    $keyfilepath = $user->getKeyFile();
    $filename = explode('/', $keyfilepath);
    if (isset($filename[2]))
        $keyicon .= '<a href="/downloadkey.php?fname=' . $filename[2] . '">' . $spantxt_icon . $spantxt_text . '</a>';
    else
        $keyicon .= "Fehler beim Schlüssel Herunterladen. Bitte melden an support@streetscooter-cloud-system.eu ";

} else {
    $spantxt_icon = '<span class="genericon genericon-key"></span>';
    $spantxt_text = '<span>Schlüssel herunterladen</span>';
    $keyicon .= '<a href="/?page=profile&downloadkey=1">' . $spantxt_icon . $spantxt_text . '</a>';

}

$editThisDep = $this->ladeLeitWartePtr->allUsersPtr->getFromId($user->getUserId());

if ($save_exist_dep) {

    $depemail = $this->requestPtr->getProperty('email');
    $dep_id = $this->requestPtr->getProperty('id');
    $passwd = $this->requestPtr->getProperty('passwd');
    $depusername = $this->requestPtr->getProperty('depusername');

    if ($save_exist_dep) {

        $dep_id = (int)$this->requestPtr->getProperty('id');
        $qform_mt = new QuickformHelper ($displayheader, "fps_dep_add_edit_form");
        $currentdep = array();
        $currentdep["email"] = $depemail;
        $currentdep["id"] = $dep_id;
        $currentdep["username"] = $depusername;
        $currentdep["role"] = $user->getUserRole();
        $qform_mt->fps_deputies_add_edit('', true, $currentdep, $user);
        if (!$qform_mt->formValidate()) {
            $dep_errors = ""; //@todo What Fehler? Error with submitted data.
            $editdep = "y";
        } else {
            if ($passwd)
                $this->ladeLeitWartePtr->allUsersPtr->save(array("email", "passwd", "username"), array($depemail, $passwd, $depusername), array("id", "=", $dep_id));
            else
                $this->ladeLeitWartePtr->allUsersPtr->save(array("email", "username"), array($depemail, $depusername), array("id", "=", $dep_id));
            $msgs[] = "Ihre Änderungen sind gespeichert!";
        }


    }


} else {
    $qform_mt = new QuickformHelper ($displayheader, "fps_dep_add_edit_form");
    $qform_mt->fps_deputies_add_edit('', true, $editThisDep, $user);

}

$content = $qform_mt->getContent();


?>
<div class="inner_container">
    <div class="row ">
        <div class="columns twelve">
            <h1 id="fps_dep_edit">Profil Bearbeiten</h1>
            <?php echo $keyicon; ?>
        </div>
    </div>
    <div class="row ">
        <div class="columns twelve">
		<span class="error_msg">
			<?php if (is_array($msgs)) echo implode('<br>', $msgs); ?>
		</span>
        </div>
    </div>

    <div class="row ">
        <div class="columns twelve">
            <?php echo $content;
            ?>
        </div>
    </div>
</div>
