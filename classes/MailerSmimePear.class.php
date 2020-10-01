<?php
/**
 * MailerSmimePear.class.php
 * send encrypted email with smime
 */

class MailerSmimePear extends Mailer {
    function __construct($to, $toname, $subject, $mailmsg, $publickey) {
        include_once("Mail.php");
        include_once('Mail/mime.php');

        // Setup mail headers.
        $headers = array("From" => "fahrzeugreport@streetscooter-cloud-system.eu", "To" => $to, "Subject" => "Encrypted mail readable with most clients", "X-Mailer" => "PHP/" . phpversion());

        // Get the public key certificate.
        $pubkey = $publickey;
        $enc_header = '';
        // Header for encrypted part
        $eol = "\r\n";
        $enc_header .= "From: " . $headers['From'] . $eol;
        $enc_header .= "To: " . $headers['To'] . $eol;
        $enc_header .= "Subject: " . $headers['Subject'] . $eol;
        $enc_header .= "Content-Type: text/plain; format=flowed; charset=\"utf-8\"; reply-type=original" . $eol;
        $enc_header .= "Content-Transfer-Encoding: 7bit" . $eol;
        $enc_header .= "\n";

        // Prepend header for encrypted message
        $body = $mailmsg;
        $msg = $enc_header . $body;

        $timestamp = time();

        $plainfile = "/tmp/plain_" . $timestamp;
        file_put_contents($plainfile, $msg);

        // Remove some double headers for mail()
        $headers_msg = $headers;
        unset($headers_msg['To']);


        $encfile = "/tmp/enc_" . $timestamp;

        // Encrypt message
        openssl_pkcs7_encrypt($plainfile, $encfile, $pubkey, $headers_msg, 0, 1);

        // Seperate headers and body for mail()
        $data = file_get_contents($encfile);
        $parts = explode("\n\n", $data, 2);


        $crlf = "\n";
        $mime = new Mail_mime(array('eol' => $crlf));

        $mime->setTXTBody($parts[1]);

        $myheaders = array('From' => $headers["From"],
            'Subject' => $subject,
            'MIME-Version' => '1.0',
            'Content-Disposition' => 'attachment; filename="smime.p7m"',
            'Content-Type' => 'application/pkcs7-mime; smime-type=enveloped-data; name="smime.p7m"',
            'Content-Transfer-Encoding' => 'base64'
        );

        $body = $mime->get();
        $hdrs = $mime->headers($myheaders);

        /* SMTP server name, port, user/passwd */
        $smtpinfo["host"] = "smtp.1und1.de";
        $smtpinfo["port"] = "587";
        $smtpinfo["auth"] = true;
        $smtpinfo["username"] = $headers["From"];
        $smtpinfo["password"] = "Do3eiNor";
        /* Create the mail object using the Mail::factory method */
        $mail_object = Mail::factory("smtp", $smtpinfo);
        /* Ok send mail */
        if ($mail_object->send($headers['To'], $myheaders, $body))
            return true;
        else
            return false;

// 		$mail =& Mail::factory('mail');
// 		if($mail->send($headers['To'], $myheaders, $body))
// 			return true;
// 		else 
// 			return false;


    }
}