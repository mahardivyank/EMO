<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || isAdmin()) {
    header("Location: ../login.php");
    exit;
}

$img = $_GET['img'] ?? null;
if (!$img) die("Image not specified");

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT u.*, c.slug AS category_slug FROM users u JOIN categories c ON u.category_id = c.id WHERE u.id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) die("User not found");

$userSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $user['username']));
$imgPath = $_SERVER['DOCUMENT_ROOT'] . "/EMO/uploads/{$user['category_slug']}/$userSlug/gallery/" . basename($img);

if (file_exists($imgPath)) {
    unlink($imgPath);
    header("Location: view_gallery.php?deleted=1");
} else {
    die("Image not found");
}
