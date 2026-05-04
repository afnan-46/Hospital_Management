<?php
// auth.php
session_start();

// Regenerate ID on login to prevent fixation
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function verify_csrf() {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF Token Validation Failed.");
    }
}

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /login.php");
        exit;
    }
}

function require_role($allowed_roles) {
    require_login();
    if (!in_array($_SESSION['role'], (array)$allowed_roles)) {
        header("HTTP/1.0 403 Forbidden");
        echo "403 Forbidden - Unauthorized Role.";
        exit;
    }
}

function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
?>