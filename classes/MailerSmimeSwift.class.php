<?php
/**
 * MailerSmimeSwift.class.php
 * send encrypted email with smime
 */

class MailerSmimeSwift {
    function __construct($to, $toname, $subject, $mailmsg, $publickey = null, $html = false, $ccadd = null) {
        global $config;
        require_once './swift-mailer/swift_required.php';

        if (isset($config))
            $smtpSet = $config->get_property('smtp_local_test', null);

        if (!isset ($smtpSet)) {
            $transport = Swift_SmtpTransport::newInstance('smtp.1und1.de', 587)
                ->setUsername('fahrzeugreport@streetscooter-cloud-system.eu')
                ->setPassword('Do3eiNor');
        } else
            if (is_array($smtpSet)) {
                $to = $smtpSet['to'];
                $toname = 'testuser';
                $subject = 'Test LOKAL : ' . $subject;
                $ccadd = null;

                $transport = Swift_SmtpTransport::newInstance($smtpSet['server'], $smtpSet['port'], $smtpSet['encryption'])
                    ->setUsername($smtpSet['user'])
                    ->setPassword($smtpSet['password']);
            } else {
                $f = fopen($smtpSet, 'at+');
                if ($f) {
                    fwrite($f, "

**********************************************************
** mail to: $to
** subject: $subject
**********************************************************");

                    fwrite($f, $mailmsg);
                    fclose($f);

                    chmod($smtpSet, 0664);
                }
                return;
            }

        $mailer = Swift_Mailer::newInstance($transport);


        // Set the To addresses with an associative array

        $domain = null;
        if ($domain === null && isset($_SERVER['HTTP_HOST'])) {
            $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
        }


        // Create the message
        $message = Swift_Message::newInstance()
            ->setSubject($subject)
            // Set the From address with an associative array
            ->setFrom(array('fahrzeugreport@streetscooter-cloud-system.eu' => 'StreetScooter Cloud System'));


        if (is_array($to)) {
            $message->setTo($to);
        } else
            $message->setTo(array($to => $toname));

        if (isset($ccadd)) {
            $message->setCC($ccadd);
        }

        if ($html === true) {
            $mailmsg = str_replace("\r\n", '<br>', $mailmsg);
            $message->setBody($mailmsg, 'text/html');
        } else $message->setBody($mailmsg);
        if (isset($publickey)) {
            $smimeSigner = Swift_Signers_SMimeSigner::newInstance();
            $smimeSigner->setEncryptCertificate($publickey);
            $message->attachSigner($smimeSigner);
        }


        // Send the message
        $result = $mailer->send($message);
        if ($result)
            return true;
        else
            return false;


    }
}