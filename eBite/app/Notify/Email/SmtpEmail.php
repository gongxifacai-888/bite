<?php


namespace App\Notify\Email;


use App\Exceptions\ThrowException;
use App\Models\Setting\Setting;
use App\Notify\Notify;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class SmtpEmail extends Notify
{
    public function config()
    {
        $this->send_type = self::EMAIL_DRIVER;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws ThrowException
     */
    public function send()
    {
        $mail            = new PHPMailer();
        $mail->CharSet   = 'UTF-8';
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host     = 'smtp.163.com';                    // Set the SMTP server to send through
        $mail->SMTPAuth = true;                                   // Enable SMTP authentication
        $mail->Username = 'kyokorod23@163.com';                     // SMTP username
        $mail->setFrom('kyokorod23@163.com');
        $mail->Password   = 'JIMBAXUJFIPYVHSH';                               // SMTP password
        $mail->SMTPSecure = 'ssl';         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = 465;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
        $mail->addAddress($this->to);     // 收件人
        $mail->Subject = 'Verification Code';
        $mail->Body    = 'Your verification code is ' . $this->template->content['code'];
        $mail->send();
    }
}
