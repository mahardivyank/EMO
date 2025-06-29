<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

function login($email, $password, $is_admin = false) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_admin = ?");
    $stmt->execute([$email, $is_admin ? 1 : 0]);
    $user = $stmt->fetch();

    if ($user && $user['password'] === $password) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['is_admin'] = $user['is_admin'];
        return true;
    }
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function logout() {
    session_unset();
    session_destroy();
}
?>
