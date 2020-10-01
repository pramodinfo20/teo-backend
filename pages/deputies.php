<?php
/**
 * deputies.php
 * Mitarbeitern Verwaltung
 * @author Pradeep Mohan
 */

$addDep = $requestPtr->getProperty('addDep');
$editDep = $requestPtr->getProperty('editdep');

if ($this->user->user_can('newusers')) {
    $heading1 = array('Mitarbeiter/in Konto', '');
    $heading2 = array('Funktionen', '');

    $processedkontos[] = array('headingone' => array($heading1, $heading2));

    foreach ($this->deputies as $dep_single) {

        $privileges = unserialize($dep_single["privileges"]);

        $editicon = "<a href='?page=mitarbeiter&editdep=y&id=" . $dep_single["id"] . "#fps_dep_edit'><span class='genericon genericon-edit'></span><span>Bearbeiten</span></a>";

        $delicon = "<a href='?page=mitarbeiter&deldep=y&id=" . $dep_single["id"] . "' ><span class='genericon genericon-close'></span><span class='dep_req_confirm'>Löschen</span></a>";

        $dep_columnone = $dep_single["username"] . "<br>" . $editicon . "<br>" . $delicon;

        $privileges_str = array();

        if ($privileges["newusers"])
            $privileges_str[] = "Mitarbeiter Konto erstellen/bearbeiten/löschen";
        if (isset($privileges["addzsplemails"]) && $privileges["addzsplemails"])
            $privileges_str[] = "ZSPL Email Addressen hinzufügen";
        //@todo more privilege strings to be added
        $processedkontos[] = array($dep_columnone, implode("<br>", $privileges_str));

    }


    if (empty($this->deputies))
        $kontosTable = 'Keine Mitarbeitern gefunden.';

    else {
        $displaytable = new DisplayTable ($processedkontos);
        $kontosTable = $displaytable->getContent();
    }

} //if($this->user->user_can('newusers'))
else
    $kontosTable = 'Keine Berechtigung Mitarbeitern verwalten.';

?>
<div class="inner_container">
    <div class="row ">
        <div class="columns twelve">
            <?php
            // 			echo $this->breadcrumb;
            ?>
        </div>
    </div>
    <?php
    if (is_array($msgs)) :?>
        <div class="row ">
            <div class="columns twelve">
                <?php if (is_array($msgs)) echo implode('<br>', $msgs); ?>
            </div>
        </div>
    <?php endif;
    if ($addDep == "y" || $editDep == "y"): ?>
        <div class="row ">
            <div class="columns twelve">
                <?php
                if ($addDep == "y"): ?>
                    <h1 id="fps_dep_add">Mitarbeiter/in Konten erstellen</h1>
                <?php
                elseif ($editDep == "y"): ?>
                    <h1 id="fps_dep_edit">Mitarbeiter/in Konto Bearbeiten</h1>
                <?php
                endif;
                echo $qform_mt->getContent();
                ?>
            </div>
        </div>
    <?php
    endif; ?>
    <div class="row ">
        <div class="columns eight">
            <h1 id="fps_dep_add">Mitarbeiter/in Konten verwalten</h1>
            <?php if ($addDep != "y"): ?>
                <a href="?addDep=y&page=mitarbeiter#fps_dep_add"><span class="genericon genericon-plus"></span>Neue
                    Mitarbeiter/in hinzufügen</a>
            <?php
            endif;
            ?>
        </div>
    </div>
    <div class="row ">
        <div class="columns eight">
            <?php
            echo $kontosTable;
            ?>
        </div>
    </div>
</div>