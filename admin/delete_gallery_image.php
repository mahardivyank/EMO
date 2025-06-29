<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
$img = $_GET['img'] ?? null;

if (!$id || !$img) die("Missing data");

$stmt = $pdo->prepare("SELECT u.*, c.slug AS category_slug FROM users u JOIN categories c ON u.category_id = c.id WHERE u.id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) die("User not found");

$userSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $user['username']));
$imgPath = $_SERVER['DOCUMENT_ROOT'] . "/EMO/uploads/{$user['category_slug']}/$userSlug/gallery/" . basename($img);

if (file_exists($imgPath)) {
    unlink($imgPath);
    header("Location: view_gallery.php?id=$id&deleted=1");
    exit;
} else {
    die("Image not found.");
}
