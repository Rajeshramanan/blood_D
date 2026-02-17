<?php
/**
 * Send an HTML email notification
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $body_content HTML content of the email
 * @return bool True if mail accepted for delivery, False otherwise
 */
function send_notification_email($to, $subject, $body_content)
{
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Blood Donation App <no-reply@bloodapp.com>" . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <title>{$subject}</title>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; padding: 20px; margin: 0; }
            .container { background-color: #ffffff; max-width: 600px; margin: 0 auto; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
            .header { color: #d9534f; font-size: 24px; font-weight: bold; margin-bottom: 20px; border-bottom: 2px solid #f2f2f2; padding-bottom: 10px; }
            .content { font-size: 16px; color: #333333; line-height: 1.6; }
            .highlight { color: #d9534f; font-weight: bold; }
            .footer { margin-top: 30px; font-size: 12px; color: #888888; text-align: center; border-top: 1px solid #f2f2f2; padding-top: 20px; }
            .btn { display: inline-block; background-color: #d9534f; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-top: 15px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>BLOOD DONATION ALERT</div>
            <div class='content'>
                {$body_content}
            </div>
            <div class='footer'>
                <p>This is an automated message from the Blood Donation Management System.</p>
                <p>Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    // Use @ to suppress warnings if mail server is not configured locally
    return @mail($to, $subject, $message, $headers);
}
?>
