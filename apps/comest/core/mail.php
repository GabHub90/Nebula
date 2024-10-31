<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require ($_SERVER['DOCUMENT_ROOT'].'/nebula/core/PHPMailer/src/Exception.php');
require ($_SERVER['DOCUMENT_ROOT'].'/nebula/core/PHPMailer/src/PHPMailer.php');
require ($_SERVER['DOCUMENT_ROOT'].'/nebula/core/PHPMailer/src/SMTP.php');

include($_SERVER['DOCUMENT_ROOT']."/nebula/main/baseline.php");
include($_SERVER['DOCUMENT_ROOT']."/nebula/apps/comest/classi/comest.php");

$param=$_POST['param'];

$mail = new PHPMailer(true);

//construct inizializza galileo per COMEST
$c=new nebulaComest($galileo);

$c->init(array('rif'=>$param['commessa']));

/////////////////////////////////////////////

/* recipient email address
//$to = $param['fornit'];
$to = "matteo.cecconi@gabellini.it";

// subject of the email
$subject = "Invio commessa ".$param['commessa'];

// message body
$message = "Augusto Gabellini Srl - commessa di lavorazione ".$param['commessa'];

// from
$from = $param['logged'];

// boundary
$boundary = uniqid();

// header information
$headers = "From: $from\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\".$boundary.\"\r\n";

// attachment
$attachment = chunk_split($c->pdf());

// message with attachment
$message = "--".$boundary."\r\n";
$message .= "Content-Type: text/plain; charset=UTF-8\r\n";
$message .= "Content-Transfer-Encoding: base64\r\n\r\n";
$message .= chunk_split(base64_encode($message));
$message .= "--".$boundary."\r\n";
$message .= "Content-Type: application/octet-stream; name=\"commessa_".$param['commessa'].".pdf\"\r\n";
$message .= "Content-Transfer-Encoding: base64\r\n";
$message .= "Content-Disposition: attachment; filename=\"commessa_".$param['commessa'].".pdf\"\r\n\r\n";
$message .= $attachment."\r\n";
$message .= "--".$boundary."--";

// send email
if (mail($to, $subject, $message, $headers)) {
    echo "Email with attachment sent successfully.";
} else {
    echo "Failed to send email with attachment.";
}*/

try {
    //Server settings
    //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp190.ext.armada.it';                //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'SMTP-PRO-13292';                       //SMTP username
    $mail->Password   = '6zrgHliPkWZ8';                         //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable implicit TLS encryption
    $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom($param['logged'], 'Augusto Gabellini Srl');
    //$mail->setFrom("marco.ghiandoni@gabellini.it", 'Mailer');
    //$mail->addAddress('joe@example.net', 'Joe User');     //Add a recipient
    $mail->addAddress($param['fornit']);               //Name is optional
    //$mail->addAddress("matteo.cecconi@gabellini.it");
    //$mail->addReplyTo('info@example.com', 'Information');
    //$mail->addCC('cc@example.com');
    //$mail->addBCC('bcc@example.com');

    //Attachments
    //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
    //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

    $mail->AddStringAttachment(base64_decode($c->pdf()), "commessa_".$param['commessa'].".pdf" , "base64" , "application/pdf");

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = "Commessa di lavorazione ".$param['commessa'];
    $mail->Body    = '<div>Augusto Gabellini Srl</div><div>Invio commessa di lavorazione.</div>';
    $mail->AltBody = 'Augusto Gabellini Srl - Invio commessa di lavorazione.';

    $mail->send();
    echo 'Message has been sent';
    
    $a=array(
        "commessa"=>$param['commessa'],
        "d"=>date('Ymd:H:i'),
        "mitt"=>$param['logged'],
        "dest"=>$param['fornit']
    );
    $c->logMail($a);
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

?>