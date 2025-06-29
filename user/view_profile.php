<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$viewerId = $_SESSION['user_id'];
$viewedId = $_GET['id'] ?? null;

if (!$viewedId || !is_numeric($viewedId)) {
    die("Invalid profile ID.");
}

// Prevent self-view from affecting view limit
if ($viewerId != $viewedId) {
    // Check current viewer's limit
    $stmt = $pdo->prepare("SELECT view_limit FROM users WHERE id = ?");
    $stmt->execute([$viewerId]);
    $viewer = $stmt->fetch();

    if (!$viewer) die("Viewer not found.");

    if ($viewer['view_limit'] <= 0) {
        die("❌ You have reached your profile view limit.");
    }

    // Decrement view limit
    $pdo->prepare("UPDATE users SET view_limit = view_limit - 1 WHERE id = ?")
        ->execute([$viewerId]);
}

// Fetch viewed user
$stmt = $pdo->prepare("
    SELECT u.*, c.slug AS category_slug
    FROM users u
    JOIN categories c ON u.category_id = c.id
    WHERE u.id = ?
");
$stmt->execute([$viewedId]);
$profile = $stmt->fetch();

if (!$profile) die("Profile not found.");

$slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $profile['username']));
$profileImage = "../uploads/{$profile['category_slug']}/$slug/profile.jpg";
$galleryPath = "../uploads/{$profile['category_slug']}/$slug/gallery";
$galleryImages = is_dir($galleryPath) ? array_diff(scandir($galleryPath), ['.', '..']) : [];

include 'user_header.php';
?>

<h2>👤 <?= htmlspecialchars($profile['username']) ?>'s Profile</h2>

<p><strong>Email:</strong> <?= htmlspecialchars($profile['email']) ?></p>

<p><strong>Profile Image:</strong><br>
    <?php if (file_exists($profileImage)): ?>
        <img src="<?= $profileImage ?>" width="120">
    <?php else: ?>
        <em>No image</em>
    <?php endif; ?>
</p>

<h3>🖼️ Gallery</h3>
<?php if ($galleryImages): ?>
    <?php foreach ($galleryImages as $img): ?>
        <img src="<?= $galleryPath . '/' . $img ?>" width="100" style="margin: 5px;">
    <?php endforeach; ?>
<?php else: ?>
    <p>No gallery images.</p>
<?php endif; ?>

<br><br>
<a href="dashboard.php">⬅️ Back to Dashboard</a>

<?php include 'user_footer.php'; ?>
