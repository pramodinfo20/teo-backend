<?php
$requested_fname = filter_var($_GET['fname'], FILTER_SANITIZE_STRING);
$cleaned_fname = basename($requested_fname) . ".csv";
$file = "/tmp/" . $cleaned_fname;
$white_listed_files = scandir("/tmp/");
if (file_exists($file) && in_array($cleaned_fname, $white_listed_files)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $cleaned_fname . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit();
}
?>