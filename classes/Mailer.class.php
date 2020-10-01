<?php
/**
 * Mailer.class.php
 * send email
 */

class Mailer {
    function __construct($to, $toname, $subject, $mailmsg) {
        include("Mail.php");
        $recipients = $to;
        $headers["From"] = "StreetScooter Cloud System <fahrzeugreport@streetscooter-cloud-system.eu>";
        $headers["To"] = $to;
        $headers["Subject"] = $subject;
        $headers["Content-Type"] = "text/html; charset=UTF-8";
        // $mailmsg = "Hallo, \r\n$cntvehicles neue Fahrzeuge würden beauftragt. \r\n";
        // $mailmsg.="MfG,\r\nStreetScooter Cloud System \r\n";
        // $mailmsg.= date('d-m-Y h:i:s A');
        /* SMTP server name, port, user/passwd */
        $smtpinfo["host"] = "smtp.1und1.de";
        $smtpinfo["port"] = "587";
        $smtpinfo["auth"] = true;
        $smtpinfo["username"] = "fahrzeugreport@streetscooter-cloud-system.eu";
        $smtpinfo["password"] = "Do3eiNor";
        /* Create the mail object using the Mail::factory method */
        $mail_object = Mail::factory("smtp", $smtpinfo);
        /* Ok send mail */
        if ($mail_object->send($recipients, $headers, $mailmsg))
            return true;
        else
            return false;

    }
}