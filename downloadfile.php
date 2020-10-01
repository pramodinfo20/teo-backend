<?php
$requested_fname = filter_var($_GET['filename'], FILTER_SANITIZE_STRING);
$cleaned_fname = basename($requested_fname);
$ext = filter_var($_GET['format'], FILTER_SANITIZE_STRING);
if (!empty($ext)) $cleaned_fname .= '.' . $ext;

$fromloc_list = [
    'teoexceptions' => '/var/www/teo_exceptions/',
    'exportedpdfdownload' => $_SERVER['DOCUMENT_ROOT'].'/pdf_temp/',
    ];

if (isset($_GET['fromloc'])) {
    $fromloc = filter_var($_GET['fromloc'], FILTER_SANITIZE_STRING);
    $file = $fromloc_list[$fromloc] . $cleaned_fname;
    $white_listed_files = scandir($fromloc_list[$fromloc]);
} else {
    $file = "/tmp/uploads/" . $cleaned_fname;
    $white_listed_files = scandir("/tmp/uploads");
}

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