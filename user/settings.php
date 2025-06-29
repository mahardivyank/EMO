<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || isAdmin()) {
    header("Location: ../index.php");
    exit;
}

$userId = $_SESSION['user_id'];
$msg = "";

// Fetch user data
$stmt = $pdo->prepare("SELECT u.*, c.slug AS category_slug FROM users u JOIN categories c ON u.category_id = c.id WHERE u.id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}

// Update settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    $userSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $username));
    $baseFolder = $_SERVER['DOCUMENT_ROOT'] . "/EMO/uploads/{$user['category_slug']}/$userSlug";
    $oldFolder = $_SERVER['DOCUMENT_ROOT'] . "/EMO/uploads/{$user['category_slug']}/" . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $user['username']));

    // Rename folder if username changed
    if ($userSlug !== strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $user['username'])) && is_dir($oldFolder)) {
        rename($oldFolder, $baseFolder);
    }

    // Handle profile image update
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        move_uploaded_file($_FILES['profile_image']['tmp_name'], $baseFolder . "/profile.jpg");
    }

    // Update DB
    $update = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    if ($update->execute([$username, $email, $userId])) {
        $msg = "✅ Profile updated successfully.";
    } else {
        $msg = "❌ Failed to update profile.";
    }
}
?>

<?php include '../includes/user_header.php'; ?>

<h2>⚙️ Profile Settings</h2>

<?php if ($msg): ?>
    <p style="color: green; font-weight: bold;"><?= $msg ?></p>
<?php endif; ?>
<?php
$profileImage = "../uploads/{$user['category_slug']}/" . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $user['username'])) . "/profile.jpg";
if (file_exists($profileImage)) {
    echo "<img src='$profileImage' width='100' height='100'><br><br>";
}
?>

<form method="POST" enctype="multipart/form-data">
    <label>Username:</label><br>
    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>

    <label>Replace Profile Image:</label><br>
    <input type="file" name="profile_image" accept="image/*"><br><br>

    <button type="submit">💾 Update Profile</button>
</form>

<br>
<a href="reset_password.php">🔒 Change Password</a><br><br>
<a href="dashboard.php">⬅️ Back to Dashboard</a>

<?php include '../includes/user_footer.php'; ?>
