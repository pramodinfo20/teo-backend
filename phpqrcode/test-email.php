<?php
    define("DOMAIN", '/home/sbk/eclipse-workspace/arbeitsauftragstreetscooter/'); 
    define("EXAMPLE_TMP_URLRELPATH", 'EXAMPLE_TMP_SERVERPATH');
    include('qrlib.php');

    $tempDir = DOMAIN . 'phpqrcode/';


    // here our data 
    $email = 'ismail.sbika@streetscooter.eu'; 
    $subject = 'question'; 
    $body = 'please write your question here'; 
     
    // we building raw data 
    $codeContents = 'mailto:'.$email.'?subject='.urlencode($subject).'&body='.urlencode($body);

     
    // outputs image directly into browser, as PNG stream 
    // QRcode::png($codeText);
    QRcode::png($codeContents, $tempDir.'example_5.png', QR_ECLEVEL_L, 3);

    // displaying 
    echo '<img src="example_5.png" />'; 
    
