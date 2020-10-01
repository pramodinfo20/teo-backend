<?php

$uploaddir = '/tmp/uploads/';

echo '<h1>Datein hochladen</h1>';
if (isset($_POST['save_key_upload'])) {

    $uploadfile = $uploaddir . basename($_FILES['sharefile']['name']);


    if (move_uploaded_file($_FILES['sharefile']['tmp_name'], $uploadfile)) {
        echo "Datei hochgeladen!";
    } else {
        echo "Fehler.";
    }

}
$qform = new QuickformHelper ($displayheader, "fileshare");

$qform->fileUpload('Datei', 'key');


echo $qform->getContent();

$files1 = scandir($uploaddir);

echo '<h1>Datein</h1>';

foreach ($files1 as $filename) {
    if ($filename != '.' && $filename != '..')
        echo '<a href="downloadfile.php?filename=' . $filename . '">' . $filename . '</a><br>';
}

?>
<a href="index.php">Zurück</a>