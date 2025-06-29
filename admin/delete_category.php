<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) die("Invalid ID");

$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();
if (!$category) die("Category not found");

// Delete folder
$folderPath = "../uploads/" . $category['slug'];
function deleteFolder($path) {
    if (!file_exists($path)) return;
    foreach (scandir($path) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (is_dir($path . DIRECTORY_SEPARATOR . $item)) {
            deleteFolder($path . DIRECTORY_SEPARATOR . $item);
        } else {
            unlink($path . DIRECTORY_SEPARATOR . $item);
        }
    }
    rmdir($path);
}

deleteFolder($folderPath);

// Delete from DB
$pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
header("Location: view_categories.php");
exit;
