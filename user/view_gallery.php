<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || isAdmin()) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT u.*, c.slug AS category_slug FROM users u JOIN categories c ON u.category_id = c.id WHERE u.id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) die("User not found");

$userSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $user['username']));
$galleryUrl = "../uploads/{$user['category_slug']}/$userSlug/gallery";
$galleryDir = $_SERVER['DOCUMENT_ROOT'] . "/EMO/uploads/{$user['category_slug']}/$userSlug/gallery";
$images = is_dir($galleryDir) ? array_diff(scandir($galleryDir), ['.', '..']) : [];
?>

<?php include '../includes/user_header.php'; ?>

<h2>🖼️ My Gallery</h2>

<?php if (!empty($images)): ?>
    <?php foreach ($images as $img): ?>
        <div style="display:inline-block; margin:10px; text-align:center;">
            <img src="<?= $galleryUrl . '/' . $img ?>" width="100" height="100"><br>
            <a href="delete_image.php?img=<?= urlencode($img) ?>" onclick="return confirm('Delete this image?')">❌ Delete</a>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p><em>No gallery images yet.</em></p>
<?php endif; ?>

<br><br>
<a href="upload_gallery.php">📤 Upload More Images</a>
&nbsp;&nbsp;
<a href="dashboard.php">⬅️ Back to Dashboard</a>
<a href="edit_profile.php">👤 Edit Profile</a>
<?php include '../includes/user_footer.php'; ?>
