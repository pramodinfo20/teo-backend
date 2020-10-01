<?php
    $thisdir = __DIR__;
    
    if (! file_exists("$thisdir/temp"))
        mkdir ("$thisdir/temp");

    if (! file_exists("$thisdir/qrtemp"))
        mkdir ("$thisdir/qrtemp");
    
    include('qrlib.php');

    // $tempDir = DOMAIN . 'phpqrcode/';
    $tempDir = 'phpqrcode/';

    $param = $_GET['id']; // remember to sanitize that - it is user input! 
 
     // Path where the images will be saved
    $filepath = 'qrtemp/qrimage.png';

    // we need to be sure ours script does not output anything!!! 
    // otherwise it will break up PNG binary! 
     
    ob_start("callback"); 
     
    // here DB request or some processing 
    $codeText = 'https://auftrag.streetscooter-cloud-system.eu/?vin='.$param; 
     
    // end of processing here 
    $debugLog = ob_get_contents(); 
    ob_end_clean(); 

     
    // outputs image directly into browser, as PNG stream 
    QRcode::png($codeText, $outfile = false, $level = QR_ECLEVEL_H, $size = 8, $margin = 2, $saveandprint=false); 
    // QRcode::png($codeText);
    
