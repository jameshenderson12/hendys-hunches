<?php
require 'vendor/phpmailer/src/Exception.php';
require 'vendor/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendWelcomeEmail($firstname, $username, $userEmail, $changePasswordUrl) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.office365.com'; // Set the SMTP server to send through
        $mail->SMTPAuth = true;
        $mail->Username = 'jameshenderson12@hotmail.com'; // SMTP username
        $mail->Password = '#B_igraFEbO12#'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption; PHPMailer::ENCRYPTION_SMTPS also accepted
        $mail->Port = 587; // TCP port to connect to

        // Recipients
        $mail->setFrom('jameshenderson12@hotmail.com', 'Hendy\'s Hunches');
        $mail->addAddress($userEmail, $username); // Add a recipient

        $imagePath = 'https://www.hendyshunches.co.uk/img/hh-logo-2018.jpg';
        $mail->addEmbeddedImage($imagePath, 'logo'); // Add the logo as an embedded image


        // Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = 'Welcome to Hendy\'s Hunches';

        // Load the HTML template
        $emailTemplate = file_get_contents('../template/email_welcome.html');

        // Replace placeholders with actual values
        $emailTemplate = str_replace('{{firstname}}', $firstname, $emailTemplate);
        $emailTemplate = str_replace('{{username}}', $username, $emailTemplate);
        $emailTemplate = str_replace('{{change_password_url}}', $changePasswordUrl, $emailTemplate);

        $mail->Body = $emailTemplate;

        // Send the email
        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
