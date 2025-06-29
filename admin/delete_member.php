<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) die("Invalid member ID");

// Fetch member
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) die("User not found.");

// Get category slug
$catStmt = $pdo->prepare("SELECT slug FROM categories WHERE id = ?");
$catStmt->execute([$user['category_id']]);
$cat = $catStmt->fetch();

if (!$cat) die("Invalid category.");

// Remove folder
$folder = $_SERVER['DOCUMENT_ROOT'] . "/EMO/uploads/" . $cat['slug'] . "/" . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $user['username']));

function deleteFolder($path) {
    if (!file_exists($path)) return;
    foreach (scandir($path) as $item) {
        if ($item === '.' || $item === '..') continue;
        $itemPath = $path . DIRECTORY_SEPARATOR . $item;
        is_dir($itemPath) ? deleteFolder($itemPath) : unlink($itemPath);
    }
    rmdir($path);
}
deleteFolder($folder);

// Delete from DB
$pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
header("Location: view_members.php");
exit;
