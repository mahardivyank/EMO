<?php
require_once '../includes/db.php';

$categoryId = $_GET['category_id'] ?? null;
if (!$categoryId) exit;

$stmt = $pdo->prepare("SELECT u.*, c.slug AS category_slug FROM users u JOIN categories c ON u.category_id = c.id WHERE u.category_id = ?");
$stmt->execute([$categoryId]);
$users = $stmt->fetchAll();

foreach ($users as $user) {
    $userSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $user['username']));
    $imgPath = "../uploads/{$user['category_slug']}/{$userSlug}/profile.jpg";
    $imgUrl = file_exists($imgPath) ? $imgPath : '../assets/images/default.jpg'; // fallback

    echo "<label style='display:inline-block; width:140px; margin:5px; text-align:center;'>
            <img src='{$imgUrl}' width='60' height='60' style='display:block; border-radius:50%; border:1px solid #ccc; margin-bottom:5px;'>
            <input type='checkbox' class='user-checkbox' name='view_users[]' value='{$user['id']}'> 
            <div style='font-size:12px;'>{$user['username']}</div>
          </label>";
}
?>
