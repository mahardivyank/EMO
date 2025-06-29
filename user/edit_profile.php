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

$msg = "";
$userSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $user['username']));
$folderPath = $_SERVER['DOCUMENT_ROOT'] . "/EMO/uploads/{$user['category_slug']}/$userSlug";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $newSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $username));
    $newFolderPath = $_SERVER['DOCUMENT_ROOT'] . "/EMO/uploads/{$user['category_slug']}/$newSlug";

    // Rename user folder if username changed
    if ($userSlug !== $newSlug && is_dir($folderPath)) {
        rename($folderPath, $newFolderPath);
        $folderPath = $newFolderPath;
    }

    // Handle profile image
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        move_uploaded_file($_FILES['profile_image']['tmp_name'], $folderPath . "/profile.jpg");
    }

    // Update DB
    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
    if ($stmt->execute([$username, $email, $password, $userId])) {
        $msg = "✅ Profile updated successfully.";
        $_SESSION['username'] = $username;
    } else {
        $msg = "❌ Failed to update profile.";
    }
}
?>

<?php include '../includes/user_header.php'; ?>

<h2>✏️ Edit My Profile</h2>

<form method="POST" enctype="multipart/form-data">
    <label>Username:</label><br>
    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>

    <label>Password:</label><br>
    <input type="text" name="password" value="<?= htmlspecialchars($user['password']) ?>" required><br><br>

    <label>Replace Profile Image:</label><br>
    <input type="file" name="profile_image" accept="image/*"><br><br>

    <button type="submit">💾 Update Profile</button>
</form>

<?php if ($msg): ?>
    <p style="color: green; font-weight: bold;"><?= $msg ?></p>
<?php endif; ?>

<br>
<a href="dashboard.php">⬅️ Back to Dashboard</a>

<?php include '../includes/user_footer.php'; ?>
