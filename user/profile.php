<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || isAdmin()) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT u.*, c.name AS category, c.slug AS category_slug 
                       FROM users u 
                       JOIN categories c ON u.category_id = c.id 
                       WHERE u.id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) die("User not found");

$userSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $user['username']));
$folderPath = "../uploads/{$user['category_slug']}/$userSlug";
$galleryPath = "$folderPath/gallery";
$galleryImages = is_dir($galleryPath) ? array_diff(scandir($galleryPath), ['.', '..']) : [];
?>

<?php include '../includes/user_header.php'; ?>

<h2>👤 My Profile</h2>

<p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
<p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
<p><strong>Category:</strong> <?= htmlspecialchars($user['category']) ?></p>
<p><strong>View Limit:</strong> <?= $user['view_limit'] ?></p>

<p><strong>Profile Image:</strong><br>
<?php
$profileImage = "$folderPath/profile.jpg";
if (file_exists($profileImage)) {
    echo "<img src='$profileImage' width='120' height='120'>";
} else {
    echo "<em>No profile image uploaded.</em>";
}
?>
</p>

<hr>
<h3>🖼️ Gallery</h3>
<?php if (!empty($galleryImages)): ?>
    <?php foreach ($galleryImages as $img): ?>
        <img src="<?= $galleryPath . '/' . $img ?>" width="100" height="100" style="margin: 5px;">
    <?php endforeach; ?>
<?php else: ?>
    <p><em>No gallery images available.</em></p>
<?php endif; ?>

<br><br>
<a href="upload_gallery.php">📤 Upload More Images</a>
&nbsp;&nbsp;
<a href="settings.php">⚙️ Edit Profile</a>
&nbsp;&nbsp;
<a href="dashboard.php">⬅️ Back to Dashboard</a>

<?php include '../includes/user_footer.php'; ?>
