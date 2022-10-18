<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendmail($email_to, $receiver, $subject, $body, $alt_body)
{

    require '../PHPMailer/src/Exception.php';
    require '../PHPMailer/src/PHPMailer.php';
    require '../PHPMailer/src/SMTP.php';
    require $_SERVER['DOCUMENT_ROOT'] . '/tunnukset.php';


    try {
        $phpmailer = new PHPMailer();
        $phpmailer->isSMTP();
        $phpmailer->CharSet = 'UTF-8';
        $phpmailer->Host = 'smtp.mailtrap.io';
        $phpmailer->SMTPAuth = true;
        $phpmailer->Port = 2525;
        $phpmailer->Username = $mail_username;
        $phpmailer->Password = $mail_password;

        //Sender and receiver
        $phpmailer->setFrom('noreply@homelibrary.test', 'Home Library');
        $phpmailer->addAddress($email_to, $receiver);

        //Content
        $phpmailer->isHTML(true); //Set email format to HTML
        $phpmailer->Subject = $subject;
        $phpmailer->Body = $body;
        $phpmailer->AltBody = $alt_body;

        $phpmailer->send();
    } catch (Exception $e) {
        $log = __DIR__ . "/error_log.txt";
        $txt = "Message could not be sent. Mailer Error: {$phpmailer->ErrorInfo}";
        file_put_contents($log, $txt, FILE_APPEND);
    }
}
