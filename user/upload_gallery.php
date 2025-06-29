<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || isAdmin()) {
    header("Location: ../login.php");
    exit;
}

// Get user info
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT u.*, c.slug AS category_slug FROM users u JOIN categories c ON u.category_id = c.id WHERE u.id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}

$userSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $user['username']));
$galleryPath = $_SERVER['DOCUMENT_ROOT'] . "/EMO/uploads/{$user['category_slug']}/$userSlug/gallery";

// Create folder if not exists
if (!is_dir($galleryPath)) {
    mkdir($galleryPath, 0755, true);
}

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['gallery_images'])) {
    $count = count($_FILES['gallery_images']['name']);
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];

    for ($i = 0; $i < $count; $i++) {
        $tmp = $_FILES['gallery_images']['tmp_name'][$i];
        $name = basename($_FILES['gallery_images']['name'][$i]);
        $type = $_FILES['gallery_images']['type'][$i];

        if (in_array($type, $allowedTypes)) {
            move_uploaded_file($tmp, "$galleryPath/$name");
        }
    }

    $msg = "✅ Images uploaded successfully!";
}
?>

<?php include '../includes/user_header.php'; ?>

<h2>📤 Upload Gallery Images</h2>

<?php if ($msg): ?>
    <p style="color: green;"><?= $msg ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="gallery_images[]" multiple required accept="image/*"><br><br>
    <button type="submit">Upload</button>
</form>

<br>
<a href="dashboard.php">⬅️ Back to Dashboard</a>

<?php include '../includes/user_footer.php'; ?>
