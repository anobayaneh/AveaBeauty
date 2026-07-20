<?php
session_start();
error_log("SESSION: " . print_r($_SESSION, true));
$flow = $_SESSION['avea_verify_flow'] ?? [];
if (empty($_SESSION['passed_apply'])) {
    header('Location: apply.php');   // ibalik sa apply kung hindi dumaan
    exit;
}
/*
|--------------------------------------------------------------------------
| SAFETY CHECK
|--------------------------------------------------------------------------
*/
if (!is_array($flow) || empty($flow)) {
    header("Location: confirm.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| INIT STEP POINTER
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['step_index'])) {
    $_SESSION['step_index'] = 0;
}

$index = $_SESSION['step_index'];

/*
|--------------------------------------------------------------------------
| VALIDATE STEP
|--------------------------------------------------------------------------
*/
if (!isset($flow[$index])) {
    session_destroy();
    header("Location: confirm.php");
    exit;
}

$next = $flow[$index];

/*
|--------------------------------------------------------------------------
| MOVE TO NEXT STEP
|--------------------------------------------------------------------------
*/
$_SESSION['step_index'] = $index + 1;

/*
|--------------------------------------------------------------------------
| REDIRECT USER
|--------------------------------------------------------------------------
*/
header("Location: " . $next);
exit;
