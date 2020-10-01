<?php
$requested_fname = filter_var($_GET['filename'], FILTER_SANITIZE_STRING);
$format = filter_var($_GET['format'], FILTER_SANITIZE_STRING);
$cleaned_fname = basename($requested_fname . '.' . $format);
$file = "/tmp/" . $cleaned_fname;
$white_listed_files = scandir("/tmp");
if (file_exists($file) && in_array($cleaned_fname, $white_listed_files)) {

    // Redirect output to a client’s web browser (Xlsx)
    if ($format == 'xslx') header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    else if ($format == 'ods') header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
    else if ($format == 'csv') header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment;filename="teo_export_' . date('Y-m-d_H_i') . '.' . $format . '"');
    header('Cache-Control: max-age=0');
    // If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');

    // If you're serving to IE over SSL, then the following may be needed
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
    header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header('Pragma: public'); // HTTP/1.0

    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit();
}

?>