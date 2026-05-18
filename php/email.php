<?php

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

if (!function_exists('hh_mail_config')) {
    function hh_mail_config(): array
    {
        global $hh_email_config, $title;

        $defaults = [
            'enabled' => false,
            'transport' => 'smtp',
            'from_name' => (string) ($title ?? "Hendy's Hunches"),
            'from_email' => '',
            'reply_to_name' => '',
            'reply_to_email' => '',
            'smtp_host' => '',
            'smtp_port' => 587,
            'smtp_secure' => 'tls',
            'smtp_username' => '',
            'smtp_password' => '',
        ];

        return array_merge($defaults, (array) ($hh_email_config ?? []));
    }
}

if (!function_exists('hh_mail_is_enabled')) {
    function hh_mail_is_enabled(): bool
    {
        $config = hh_mail_config();
        return !empty($config['enabled']);
    }
}

if (!function_exists('hh_mail_require_phpmailer')) {
    function hh_mail_require_phpmailer(): bool
    {
        if (class_exists(PHPMailer::class)) {
            return true;
        }

        $basePath = dirname(__DIR__) . '/vendor/phpmailer/src/';
        $required = [
            $basePath . 'Exception.php',
            $basePath . 'PHPMailer.php',
            $basePath . 'SMTP.php',
        ];

        foreach ($required as $path) {
            if (!file_exists($path)) {
                return false;
            }
            require_once $path;
        }

        return class_exists(PHPMailer::class);
    }
}

if (!function_exists('hh_mail_render_template')) {
    function hh_mail_render_template(string $templatePath, array $replacements): string
    {
        if (!file_exists($templatePath)) {
            return '';
        }

        $contents = (string) file_get_contents($templatePath);
        foreach ($replacements as $key => $value) {
            $contents = str_replace('{{' . $key . '}}', (string) $value, $contents);
        }

        return $contents;
    }
}

if (!function_exists('hh_mail_plain_text')) {
    function hh_mail_plain_text(string $html): string
    {
        $text = preg_replace('/<\s*br\s*\/?>/i', "\n", $html);
        $text = preg_replace('/<\s*\/p\s*>/i', "\n\n", (string) $text);
        $text = strip_tags((string) $text);
        $text = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim((string) preg_replace("/\n{3,}/", "\n\n", (string) $text));
    }
}

if (!function_exists('hh_mail_logo_path')) {
    function hh_mail_logo_path(): ?string
    {
        $candidates = [
            dirname(__DIR__) . '/img/hh-logo-2026-purple.png',
            dirname(__DIR__) . '/img/hendys-hunches-football-predictions-logo.png',
            dirname(__DIR__) . '/img/hh-icon-2024.png',
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}

if (!function_exists('hh_send_email')) {
    function hh_send_email(string $toEmail, string $toName, string $subject, string $htmlBody): bool
    {
        $config = hh_mail_config();
        if (!hh_mail_is_enabled()) {
            return false;
        }

        $toEmail = trim($toEmail);
        if ($toEmail === '') {
            return false;
        }

        $fromEmail = trim((string) ($config['from_email'] ?? ''));
        if ($fromEmail === '') {
            return false;
        }

        $transport = strtolower(trim((string) ($config['transport'] ?? 'smtp')));
        if ($transport === 'smtp') {
            if (!hh_mail_require_phpmailer()) {
                return false;
            }

            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = trim((string) ($config['smtp_host'] ?? ''));
                $mail->Port = (int) ($config['smtp_port'] ?? 587);
                $mail->SMTPAuth = trim((string) ($config['smtp_username'] ?? '')) !== '';
                $mail->Username = trim((string) ($config['smtp_username'] ?? ''));
                $mail->Password = (string) ($config['smtp_password'] ?? '');

                $secure = strtolower(trim((string) ($config['smtp_secure'] ?? 'tls')));
                if ($secure === 'ssl') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } elseif ($secure === 'tls') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                }

                $mail->CharSet = 'UTF-8';
                $mail->setFrom($fromEmail, (string) ($config['from_name'] ?? "Hendy's Hunches"));

                $replyToEmail = trim((string) ($config['reply_to_email'] ?? ''));
                if ($replyToEmail !== '') {
                    $mail->addReplyTo($replyToEmail, (string) ($config['reply_to_name'] ?? ''));
                }

                $mail->addAddress($toEmail, $toName);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $htmlBody;
                $mail->AltBody = hh_mail_plain_text($htmlBody);

                $logoPath = hh_mail_logo_path();
                if ($logoPath !== null) {
                    $mail->addEmbeddedImage($logoPath, 'logo');
                }

                return $mail->send();
            } catch (PHPMailerException $exception) {
                return false;
            }
        }

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . (string) ($config['from_name'] ?? "Hendy's Hunches") . ' <' . $fromEmail . '>',
        ];

        $replyToEmail = trim((string) ($config['reply_to_email'] ?? ''));
        if ($replyToEmail !== '') {
            $headers[] = 'Reply-To: ' . (string) ($config['reply_to_name'] ?? '') . ' <' . $replyToEmail . '>';
        }

        return mail($toEmail, $subject, $htmlBody, implode("\r\n", $headers));
    }
}

if (!function_exists('sendWelcomeEmail')) {
    function sendWelcomeEmail(string $firstname, string $username, string $email): bool
    {
        global $title, $base_url, $signup_url, $developer;

        $templatePath = dirname(__DIR__) . '/template/email_welcome.html';
        $html = hh_mail_render_template($templatePath, [
            'gamename' => $title,
            'firstname' => $firstname,
            'username' => $username,
            'signup_url' => rtrim((string) $signup_url, '/'),
            'login_url' => rtrim((string) $base_url, '/') . '/index.php',
            'forgot_password_url' => rtrim((string) $base_url, '/') . '/forgot-password.php',
            'developer' => $developer,
        ]);

        if ($html === '') {
            return false;
        }

        return hh_send_email($email, $firstname, "Welcome to {$title}", $html);
    }
}

if (!function_exists('sendTempPasswordEmail')) {
    function sendTempPasswordEmail(string $firstname, string $username, string $email, string $tempPassword, string $hashTempPass): bool
    {
        global $title, $base_url, $developer;

        $templatePath = dirname(__DIR__) . '/template/email_temppass.html';
        $html = hh_mail_render_template($templatePath, [
            'gamename' => $title,
            'firstname' => $firstname,
            'username' => $username,
            'temp_password' => $tempPassword,
            'temp_password_link' => rtrim((string) $base_url, '/') . '/forgot-password.php?u=' . rawurlencode($username) . '&p=' . rawurlencode($hashTempPass),
            'developer' => $developer,
        ]);

        if ($html === '') {
            return false;
        }

        return hh_send_email($email, $firstname, "{$title} password reset", $html);
    }
}

if (!function_exists('sendTestEmail')) {
    function sendTestEmail(string $email, string $name = ''): bool
    {
        global $title, $competition, $developer, $base_url;

        $greetingName = trim($name) !== '' ? trim($name) : 'there';
        $subject = "{$title} test email";
        $loginUrl = rtrim((string) $base_url, '/') . '/index.php';

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
  <body style="margin:0;padding:24px;background:#f4f6f1;font-family:Arial,sans-serif;color:#16231d;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid rgba(22,35,29,0.12);border-radius:8px;overflow:hidden;">
      <tr>
        <td style="padding:24px 28px;background:linear-gradient(90deg,#073629,#8f66d8);color:#ffffff;">
          <h1 style="margin:0;font-size:28px;line-height:1.1;">{$title}</h1>
          <p style="margin:10px 0 0;font-size:14px;opacity:0.9;">Test email successful</p>
        </td>
      </tr>
      <tr>
        <td style="padding:24px 28px;">
          <p style="margin:0 0 16px;">Hi {$greetingName},</p>
          <p style="margin:0 0 16px;">This is a test email from the Hendy's Hunches mail configuration page. If this has arrived, your outgoing email setup is working for {$competition}.</p>
          <p style="margin:0 0 16px;">You can now use the same settings for player registration, password resets and future reminder emails.</p>
          <p style="margin:0 0 20px;"><a href="{$loginUrl}" style="display:inline-block;padding:12px 16px;background:#8f66d8;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:700;">Open Hendy's Hunches</a></p>
          <p style="margin:0;color:#59635f;font-size:14px;">Sent by {$developer}</p>
        </td>
      </tr>
    </table>
  </body>
</html>
HTML;

        return hh_send_email($email, $greetingName, $subject, $html);
    }
}
